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
 * Local plugin settings
 *
 * @package   tool_gdpr_plus
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Do nothing if we are not set as the site policies handler.
if (empty($CFG->sitepolicyhandler) || $CFG->sitepolicyhandler !== 'tool_gdpr_plus') {
    return;
}

$managecaps = [
    'tool/policy:managedocs',
    'tool/policy:viewacceptances',
];

if ($hassiteconfig || has_any_capability($managecaps, context_system::instance())) {

    $ADMIN->add('privacy', new admin_externalpage(
        'tool_policy_managedocs',
        new lang_string('managepolicies', 'tool_policy'),
        new moodle_url('/admin/tool/policy/managedocs.php'),
        ['tool/policy:managedocs', 'tool/policy:viewacceptances']
    ));
    $ADMIN->add('privacy', new admin_externalpage(
        'tool_policy_acceptances',
        new lang_string('useracceptances', 'tool_policy'),
        new moodle_url('/admin/tool/policy/acceptances.php'),
        ['tool/policy:viewacceptances']
    ));
}
