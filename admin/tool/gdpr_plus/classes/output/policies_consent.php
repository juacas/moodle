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

use moodle_url;
use renderable;
use renderer_base;
use stdClass;
use templatable;
use tool_gdpr_plus\utils;
use tool_policy\api;
use tool_policy\policy_version;

/**
 * Renderer for the policies plugin.
 *
 * @package   tool_gdpr_plus
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class policies_consent implements renderable, templatable {

    /**
     * Export the page data for the mustache template.
     *
     * @param renderer_base $output renderer to be used to render the page elements.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $PAGE;

        $data = (object) [];
        $data->pluginbaseurl = (new moodle_url('/admin/tool/gdpr_plus'))->out(true);
        if (strpos(qualified_me(), '/tool/gdpr_plus/view.php') === false) {
            // Current page is not a policy doc, so returnurl parameter will be it.
            $data->returnurl = qualified_me();
        } else {
            // If current page is also a policy doc to view, get previous returnurl parameter to avoid error.
            $returnurl = $PAGE->url->get_param('returnurl');
            if (isset($returnurl)) {
                $data->returnurl = $returnurl;
            }
        }
        $data->returnurl = urlencode($data->returnurl);
        $policytype = policy_version::AUDIENCE_GUESTS;
        if (isloggedin() && !isguestuser()) {
            $policytype = policy_version::AUDIENCE_ALL;
        }
        $policies = api::list_current_versions($policytype);
        $data->haspolicies = !empty($policies);
        $policies = utils::retrieve_policies_with_acceptance($policies);
        $data->policyagreed = utils::has_policy_been_agreed();
        $data->mandatorypolicies = [];
        $data->optionalpolicies = [];
        foreach ($policies as $policy) {
            $policy->shortname = preg_replace('/\s+/', '-', strtolower($policy->name));
            if ($policy->mandatory) {
                $data->mandatorypolicies[] = $policy;
            } else {
                $data->optionalpolicies[] = $policy;
            }
        }
        $data->hasmandatorypolicies = !empty($data->mandatorypolicies);
        $data->hasoptionalpolicies = !empty($data->optionalpolicies);
        return $data;
    }
}
