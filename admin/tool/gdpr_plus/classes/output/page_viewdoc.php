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

use context_system;
use moodle_exception;
use moodle_url;
use renderer_base;
use stdClass;
use tool_policy\api;
use tool_policy\policy_version;

defined('MOODLE_INTERNAL') || die();


/**
 * Represents a page for showing the given policy document version.
 *
 * This fixes an issue with tool_policy as it should allow user to accept non mandatory policies as a guest.
 *
 * @package   tool_gdpr_plus
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class page_viewdoc extends \tool_policy\output\page_viewdoc  {
    /**
     * Sets up the global $PAGE and performs the access checks.
     */
    protected function prepare_global_page_access() {
        global $CFG, $PAGE, $SITE, $USER;

        $myurl = new moodle_url('/admin/tool/gdpr_plus/view.php', [
            'policyid' => $this->policy->policyid,
            'versionid' => $this->policy->id,
            'returnurl' => $this->returnurl,
            'behalfid' => $this->behalfid,
            'manage' => $this->manage,
            'numpolicy' => $this->numpolicy,
            'totalpolicies' => $this->totalpolicies,
        ]);

        if ($this->manage) {
            require_once($CFG->libdir.'/adminlib.php');
            admin_externalpage_setup('tool_policy_managedocs', '', null, $myurl);
            require_capability('tool/policy:managedocs', context_system::instance());
            $PAGE->navbar->add(format_string($this->policy->name),
                new moodle_url('/admin/tool/policy/managedocs.php', ['id' => $this->policy->policyid]));
        } else {
            if ($this->policy->status != policy_version::STATUS_ACTIVE) {
                require_login();
            } else if (isguestuser() || empty($USER->id) || !$USER->policyagreed) {
                // Disable notifications for new users, guests or users who haven't agreed to the policies.
                $PAGE->set_popup_notification_allowed(false);
            }
            $PAGE->set_url($myurl);
            $PAGE->set_heading($SITE->fullname);
            $PAGE->set_title(get_string('policiesagreements', 'tool_policy'));
            $PAGE->navbar->add(get_string('policiesagreements', 'tool_policy'), new moodle_url('/admin/tool/gdpr_plus/index.php'));
            $PAGE->navbar->add(format_string($this->policy->name));
        }

        if (!api::can_user_view_policy_version($this->policy, $this->behalfid)) {
            throw new moodle_exception('accessdenied', 'tool_policy');
        }
    }

    /**
     * Export the page data for the mustache template.
     *
     * @param renderer_base $output renderer to be used to render the page elements.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $USER;

        $data = (object) [
            'pluginbaseurl' => (new moodle_url('/admin/gdpr_plus/policy'))->out(false),
            'returnurl' => $this->returnurl ? (new moodle_url($this->returnurl))->out(false) : null,
            'numpolicy' => $this->numpolicy ? : null,
            'totalpolicies' => $this->totalpolicies ? : null,
        ];
        if ($this->manage && $this->policy->status != policy_version::STATUS_ARCHIVED) {
            $paramsurl = ['policyid' => $this->policy->policyid, 'versionid' => $this->policy->id];
            $data->editurl = (new moodle_url('/admin/tool/policy/editpolicydoc.php', $paramsurl))->out(false);
        }

        if ($this->policy->agreementstyle == policy_version::AGREEMENTSTYLE_OWNPAGE) {
            if (!api::is_user_version_accepted($USER->id, $this->policy->id)) {
                unset($data->returnurl);
                $data->accepturl = (new moodle_url('/admin/tool/gdpr_plus/index.php', [
                    'listdoc[]' => $this->policy->id,
                    'status'.$this->policy->id => 1,
                    'submit' => 'accept',
                    'sesskey' => sesskey(),
                ]))->out(false);
                if ($this->policy->optional == policy_version::AGREEMENT_OPTIONAL) {
                    $data->declineurl = (new moodle_url('/admin/tool/gdpr_plus/index.php', [
                        'listdoc[]' => $this->policy->id,
                        'status'.$this->policy->id => 0,
                        'submit' => 'decline',
                        'sesskey' => sesskey(),
                    ]))->out(false);
                }
            }
        }

        $data->policy = clone($this->policy);

        return $data;
    }
}
