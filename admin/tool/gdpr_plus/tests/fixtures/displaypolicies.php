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

/**
 * Display policies
 *
 * @package   tool_gdpr_plus
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_gdpr_plus\utils;
use tool_policy\api;
use tool_policy\policy_version;

require_once(__DIR__ . '/../../../../../config.php');
global $CFG;
require_login();
// Only run through behat or if we are in debug mode.
debugging() || defined('BEHAT_SITE_RUNNING') || die();

global $CFG, $OUTPUT, $PAGE;
$PAGE->set_url(new moodle_url('/local/gdpr_plus/tests/fixtures/displaypolicies.php'));
$PAGE->set_context(context_system::instance());
$policytype = policy_version::AUDIENCE_GUESTS;
if (isloggedin() && !isguestuser()) {
    $policytype = policy_version::AUDIENCE_ALL;
}
echo $OUTPUT->header();
$policies = api::list_current_versions($policytype);
$policies = utils::retrieve_policies_with_acceptance($policies);
$policyagreed = utils::has_policy_been_agreed();

if (isloggedin()) {
    global $USER;
    echo $OUTPUT->box("User :" . fullname($USER));
}
echo $OUTPUT->box("Policy Agreed ?" . ($policyagreed ? 'yes' : 'no'));
echo $OUTPUT->box_start("Policies");
if ($policies) {
    echo html_writer::alist(array_map(
        function($policy) {
            return "$policy->name : " . ($policy->policyagreed ? 'yes' : 'no');
        },
        $policies));
}
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
