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
 * Generator for behat
 *
 * @package   tool_gdpr_plus
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_tool_gdpr_plus_generator extends behat_generator_base {
    /**
     * Get all entities that can be create through this behat_generator
     *
     * @return array
     */
    protected function get_creatable_entities(): array {
        return [
            'gdpr_policies' => [
                'singular' => 'gdpr_policy',
                'datagenerator' => 'policy',
                'required' => ['name'],
                'switchids' => [],
            ],
        ];
    }

}
