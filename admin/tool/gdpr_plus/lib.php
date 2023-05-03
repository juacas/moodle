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
 * GDPR Plus tool policy
 *
 * @package   tool_gdpr_plus
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_user\output\myprofile\tree;
use tool_policy\api;
use tool_policy\policy_version;

defined('MOODLE_INTERNAL') || die();

/**
 * Load policy message for guests.
 *
 * @return string The HTML code to insert before the head.
 */
function tool_gdpr_plus_standard_footer_html() {
    global $CFG, $PAGE;

    $message = null;
    if (!empty($CFG->sitepolicyhandler)
        && $CFG->sitepolicyhandler == 'tool_gdpr_plus') {
        $output = $PAGE->get_renderer('tool_gdpr_plus');
        try {
            $page = new \tool_gdpr_plus\output\policies_consent();
            $message = $output->render($page);
            $policies = api::get_current_versions_ids();
            if (!empty($policies)) {
                $url = new moodle_url('/admin/tool/policy/viewall.php', ['returnurl' => $PAGE->url]);
                $output = html_writer::link($url, get_string('userpolicysettings', 'tool_policy'));
                $message .= html_writer::div($output, 'policiesfooter');
            }
        } catch (dml_read_exception $e) {
            // During upgrades, the new plugin code with new SQL could be in place but the DB not upgraded yet.
            $message = null;
        }
    }

    return $message;
}

/**
 * Hooks redirection to policy acceptance pages before sign up.
 */
function tool_gdpr_plus_pre_signup_requests() {
    global $CFG;

    // Do nothing if we are not set as the site policies handler.
    if (empty($CFG->sitepolicyhandler) || $CFG->sitepolicyhandler !== 'tool_gdpr_plus') {
        return;
    }

    $policies = api::get_current_versions_ids(policy_version::AUDIENCE_LOGGEDIN);
    $userpolicyagreed = cache::make('core', 'presignup')->get('tool_policy_userpolicyagreed');
    if (!empty($policies) && !$userpolicyagreed) {
        cache::make('core', 'presignup')->delete('tool_policy_userpolicyagreed');
        // Redirect to "Policy" pages for consenting before creating the user.
        cache::make('core', 'presignup')->set('tool_policy_issignup', 1);
        redirect(new \moodle_url('/admin/tool/gdpr_plus/index.php'));
    }
}

/**
 * Add nodes to myprofile page.
 *
 * @param tree $tree Tree object
 * @param stdClass $user User object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 * @return bool
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function tool_gdpr_plus_myprofile_navigation(tree $tree, $user, $iscurrentuser, $course) {
    global $CFG;

    // Do nothing if we are not set as the site policies handler.
    if (empty($CFG->sitepolicyhandler) || $CFG->sitepolicyhandler !== 'tool_gdpr_plus') {
        return;
    }

    // Get the Privacy and policies category.
    if (!array_key_exists('privacyandpolicies', $tree->__get('categories'))) {
        // Create the category.
        $categoryname = get_string('privacyandpolicies', 'admin');
        $category = new core_user\output\myprofile\category('privacyandpolicies', $categoryname, 'contact');
        $tree->add_category($category);
    } else {
        // Get the existing category.
        $category = $tree->__get('categories')['privacyandpolicies'];
    }

    // Add "Policies and agreements" node only for current user or users who can accept on behalf of current user.
    $usercontext = \context_user::instance($user->id);
    if ($iscurrentuser || has_capability('tool/policy:acceptbehalf', $usercontext)) {
        $url = new moodle_url('/admin/tool/policy/user.php', ['userid' => $user->id]);
        $node = new core_user\output\myprofile\node('privacyandpolicies', 'tool_policy',
            get_string('policiesagreements', 'tool_policy'), null, $url);
        $category->add_node($node);
    }

    return true;
}
