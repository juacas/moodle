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

namespace tool_gdpr_plus\external;

defined('MOODLE_INTERNAL') || die();

use context_user;
use external_api;
use external_description;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_warnings;
use invalid_parameter_exception;
use restricted_context_exception;
use tool_gdpr_plus\utils;

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * Accept policy
 *
 * @package   tool_gdpr_plus
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class accept_policies extends external_api {

    /**
     * Parameter description for execute_parameters().
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'policies' => new external_multiple_structure(
                    new external_single_structure([
                        'policyversionid' => new external_value(PARAM_INT, 'The policy version ID', VALUE_REQUIRED),
                        'accepted' => new external_value(PARAM_BOOL, 'Policy accepted ?', VALUE_REQUIRED),
                    ])
                )
            ]
        );
    }

    /**
     * Accept the policy for current user
     *
     * @param array $policies
     * @return array
     * @throws \moodle_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     */
    public static function execute(array $policies) {
        global $USER;
        $warnings = [];
        $params = external_api::validate_parameters(self::execute_parameters(), [
            'policies' => $policies,
        ]);
        $policies = $params['policies'];
        if (isloggedin()) {
            $context = context_user::instance($USER->id);
            $agreed = $USER->policyagreed;
            $USER->policyagreed = true; // To prevent policy check from being done here.
            self::validate_context($context);
            $USER->policyagreed = $agreed;
        }
        $validpolicies = [];
        if (!empty($policies)) {
            $validpolicies = self::filter_existing_version_acceptance(
                $policies
            );
        }
        utils::set_policies_acceptances($validpolicies);
        if (count($validpolicies) != count($policies)) {
            $invalidpolicies = array_diff_ukey($policies, $validpolicies, function($policy1, $policy2) {
                return $policy1['policyversionid'] == $policy2['policyversionid'];
            });
            foreach ($invalidpolicies as $policy) {
                $warnings[] = [
                    'item' => 'policyversionid',
                    'itemid' => $policy['policyversionid'],
                    'warningcode' => 'invalidpolicyversionid',
                    'message' => 'Invalid policy version id'
                ];
            }
        }
        return [
            'warnings' => $warnings
        ];
    }

    /**
     * Parameter description for get_policy_version().
     *
     * @return external_description
     */
    public static function execute_returns() {
        return new external_single_structure([
            'warnings' => new external_warnings()
        ]);
    }

    /**
     * Return a filtered set of existing policies id
     *
     * @param array $versionacceptance associative array with policyversionid and accepted fields
     * @return array
     */
    public static function filter_existing_version_acceptance(array $versionacceptance): array {
        return array_filter($versionacceptance, function($policyversion) {
            global $DB;
            return $DB->record_exists('tool_policy_versions', ['id' => $policyversion['policyversionid']]);
        });
    }
}
