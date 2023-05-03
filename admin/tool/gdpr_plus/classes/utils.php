<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace tool_gdpr_plus;

use tool_policy\api;
use tool_policy\policy_version;

defined('MOODLE_INTERNAL') || die();

/**
 * Utils
 *
 * @package   tool_gdpr_plus
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils {
    /**
     * The key for storing the policies in the session.
     */
    const CACHE_KEY_POLICIES_ID_AGREED = 'tool_policy_policyversionidsagreed';
    /**
     * The key to store global acceptance status.
     */
    const CACHE_KEY_POLICIES_ACCEPTED = 'tool_policy_userpolicyagreed';

    /**
     * Helper to determine the current is logged in but not as guest.
     *
     * @return bool
     */
    public static function is_loggedin_no_guest(): bool {
        return isloggedin() && !isguestuser();
    }

    /**
     * Set policy acceptance from an array of definition
     *
     * Note this will store information in the current session if user not yet logged in.
     *
     * @param array $policies associative array with two items : policyversionid and accepted
     */
    public static function set_policies_acceptances(array $policies): void {
        if (static::is_loggedin_no_guest()) {
            foreach ($policies as $policy) {
                $policiyid = $policy['policyversionid'];
                if ($policy['accepted']) {
                    api::accept_policies($policiyid);
                } else {
                    api::decline_policies($policiyid);
                }
            }
            api::update_policyagreed();
        } else {
            global $_SESSION, $DB;
            $_SESSION[self::CACHE_KEY_POLICIES_ID_AGREED] = $policies;
            // Get  just the one we agreed to.
            $policies = array_filter($policies, function($p) {
                return $p['accepted'];
            });
            $policiesversionid = array_map(function($policyv) {
                return $policyv['policyversionid'];
            }, $policies);

            // Retrieve all mandatory policies for guest.
            $params['mandatory'] = policy_version::AGREEMENT_COMPULSORY;
            $params['audienceguest'] = policy_version::AUDIENCE_GUESTS;
            $params['audienceall'] = policy_version::AUDIENCE_ALL;
            $mandatorypolicies = $DB->get_fieldset_sql(
                "SELECT DISTINCT v.id
                  FROM {tool_policy} d
            INNER JOIN {tool_policy_versions} v ON v.policyid = d.id AND v.id = d.currentversionid
            WHERE v.optional = :mandatory AND ( v.audience = :audienceguest OR v.audience = :audienceall )
            ORDER BY v.id",
                $params);

            $presignupcache = \cache::make('core', 'presignup');
            $presignupcache->set(self::CACHE_KEY_POLICIES_ACCEPTED, empty(array_diff($mandatorypolicies, $policiesversionid)));
            $presignupcache->set(self::CACHE_KEY_POLICIES_ID_AGREED, $policiesversionid);
        }
    }

    /**
     * Retrieve policy acceptance from an array of definition
     *
     * Note this will store information in the current session if user not yet logged in.
     *
     * @param array $policiescurrentversions
     * @return array
     */
    public static function retrieve_policies_with_acceptance(array $policiescurrentversions): array {
        global $USER;
        if (empty($policiescurrentversions)) {
            return [];
        }
        if (static::is_loggedin_no_guest()) {
            $acceptances = api::get_user_acceptances($USER->id);
            foreach ($policiescurrentversions as $policyversion) {
                if (!empty($acceptances[$policyversion->id])) {
                    $policyversion->policyagreed = $acceptances[$policyversion->id]->status == "1";
                }
                $policyversion->mandatory = !($policyversion->optional == "1");
                if ($policyversion->mandatory) {
                    $policyversion->policyagreed = 1;
                }
            }
        } else {
            $presignupcache = \cache::make('core', 'presignup');
            $agreedpoliciesid = $presignupcache->get(self::CACHE_KEY_POLICIES_ID_AGREED);
            foreach ($policiescurrentversions as $policyversion) {
                $policyversion->policyagreed = false;
                if ($agreedpoliciesid) {
                    foreach ($agreedpoliciesid as $agreedpolicyid) {
                        if ($agreedpolicyid == $policyversion->id) {
                            $policyversion->policyagreed = true;
                        }
                    }
                }
                $policyversion->mandatory = !($policyversion->optional == "1");
                if ($policyversion->mandatory) {
                    $policyversion->policyagreed = true;
                }
            }
        }
        return $policiescurrentversions;
    }

    /**
     * Has policy been agreed
     *
     * @return bool
     */
    public static function has_policy_been_agreed(): bool {
        global $USER;
        if (static::is_loggedin_no_guest()) {
            return $USER->policyagreed;
        } else {
            $presignupcache = \cache::make('core', 'presignup');
            return $presignupcache->get(self::CACHE_KEY_POLICIES_ACCEPTED);
        }
    }
}
