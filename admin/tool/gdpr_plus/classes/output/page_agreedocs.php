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

namespace tool_gdpr_plus\output;

defined('MOODLE_INTERNAL') || die();

use cache;
use context_system;
use core\output\notification;
use core\session\manager;
use core_user;
use html_writer;
use moodle_url;
use renderable;
use renderer_base;
use single_button;
use templatable;
use tool_policy\api;
use tool_policy\policy_version;

/**
 * Provides tool_policy\output\renderer class.
 *
 * This fixes an issue with tool_policy as it should allow user to accept non mandatory policies as a guest.
 *
 * @package   tool_gdpr_plus
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class page_agreedocs extends \tool_policy\output\page_agreedocs {

    /**
     * Prepare the page for rendering.
     *
     * @param array $listdocs List of policy version ids that were displayed to the user to agree with.
     * @param array $agreedocs List of policy version ids that the user actually agreed with.
     * @param array $declinedocs List of policy version ids that the user declined.
     * @param int $behalfid The userid to accept the policy versions as (such as child's id).
     * @param string $action Form action to identify when user agreeds policies.
     */
    public function __construct(array $listdocs, array $agreedocs = [], array $declinedocs = [], $behalfid = 0, $action = null) {
        global $USER;
        $realuser = manager::get_realuser();

        $this->listdocs = $listdocs;
        $this->agreedocs = $agreedocs;
        $this->declinedocs = $declinedocs;
        $this->action = $action;
        $this->isexistinguser = isloggedin() && !isguestuser();

        $behalfid = $behalfid ?: $USER->id;
        if ($realuser->id != $behalfid) {
            $this->behalfuser = core_user::get_user($behalfid, '*', MUST_EXIST);
            $this->behalfid = $this->behalfuser->id;
        }

        $this->policies = api::list_current_versions(policy_version::AUDIENCE_LOGGEDIN);

        if (cache::make('core', 'presignup')->get('tool_policy_issignup')) {
            // During the signup, show compulsory policies only.
            foreach ($this->policies as $ix => $policyversion) {
                if ($policyversion->optional == policy_version::AGREEMENT_OPTIONAL) {
                    unset($this->policies[$ix]);
                }
            }
            $this->policies = array_values($this->policies);
        }

        if (empty($this->behalfid)) {
            $userid = $USER->id;
        } else {
            $userid = $this->behalfid;
        }

        $this->accept_and_revoke_policies();
        $this->prepare_global_page_access($userid);
        $this->prepare_user_acceptances($userid);
    }

}
