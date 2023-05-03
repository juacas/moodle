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
 * Helper to generated policies and elements for this module
 *
 * @package   tool_gdpr_plus
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_gdpr_plus_generator extends component_generator_base {

    /**
     * Policy fields
     */
    const POLICY_FIELDS = [
        'name',
        'revision',
        'status',
        'audience',
        'type',
        'content',
        'summary',
        'agreementstyle',
        'optional',
    ];

    /**
     * Create a policy from given records
     *
     * @param null $record
     * @param array|null $options
     * @return \tool_policy\policy_version
     * @throws coding_exception
     */
    public function create_policy($record = null, array $options = null) {
        $elementdata = (array) $record;
        $data = (object) [
            'audience' => \tool_policy\policy_version::AUDIENCE_ALL,
            'archived' => 0,
            'type' => 0
        ];
        $elementdata = array_change_key_case($elementdata, CASE_LOWER);
        foreach ($elementdata as $key => $value) {
            if ($key === 'status') {
                $data->archived = ($value === 'archived');
            } else if ($key === 'audience') {
                if ($value === 'guest') {
                    $data->audience = \tool_policy\policy_version::AUDIENCE_GUESTS;
                } else if ($value === 'loggedin') {
                    $data->audience = \tool_policy\policy_version::AUDIENCE_LOGGEDIN;
                }
            } else if (($key === 'summary' || $key === 'content') && !empty($value)) {
                $data->{$key . '_editor'} = ['text' => $value, 'format' => FORMAT_MOODLE];
            } else if (in_array($key, self::POLICY_FIELDS) && $value !== '') {
                $data->$key = $value;
            }
        }
        if (empty($data->name) || empty($data->content_editor) || empty($data->summary_editor)) {
            throw new Exception('Policy is missing at least one of the required fields: name, content, summary');
        }

        if (!empty($data->policyid)) {
            $version = tool_policy\api::form_policydoc_update_new($data);
        } else {
            $version = \tool_policy\api::form_policydoc_add($data);
        }
        if (empty($elementdata['status']) || $elementdata['status'] === 'active') {
            \tool_policy\api::make_current($version->get('id'));
        }
        return $version;
    }
}
