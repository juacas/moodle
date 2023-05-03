<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace tiny_multilang2;

use context;
use editor_tiny\editor;
use editor_tiny\plugin;
use editor_tiny\plugin_with_menuitems;
use editor_tiny\plugin_with_buttons;
use editor_tiny\plugin_with_configuration;

/**
 * Plugin for Moodle 'Multilingual content' drop down menu in TinyMCE 6.
 *
 * @package     tiny_multilang2
 * @author      Iñaki Arenaza <iarenaza@mondragon.edu>
 * @author      Stephan Robotta <stephan.robotta@bfh.ch>
 * @copyright   2015 onwards Iñaki Arenaza & Mondragon Unibertsitatea
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugininfo extends plugin implements plugin_with_menuitems, plugin_with_buttons, plugin_with_configuration {

    /**
     * Check if user has sufficient rights to use the plugin.
     *
     * @param context $context
     * @param array $options
     * @param array $fpoptions
     * @param editor|null $editor
     * @return bool
     * @throws \coding_exception
     */
    public static function is_enabled(
        context $context,
        array $options,
        array $fpoptions,
        ?\editor_tiny\editor $editor = null
    ): bool {
        // Users must have permission to embed content.
        return has_capability('tiny/multilang2:viewlanguagemenu', $context);
    }

    /**
     * Get a list of the menu items provided by this plugin.
     *
     * @return string[]
     */
    public static function get_available_menuitems(): array {
        return [
            'tiny_multilang2',
        ];
    }

    /**
     * Get a list of the buttons provided by this plugin.
     * @return string[]
     */
    public static function get_available_buttons(): array {
        return [
            'tiny_multilang2',
        ];
    }

    /**
     * Returns the configuration values the plugin needs to take into consideration
     *
     * @param context $context
     * @param array $options
     * @param array $fpoptions
     * @param editor|null $editor
     * @return array
     * @throws \dml_exception
     */
    public static function get_plugin_configuration_for_context(context $context, array $options, array $fpoptions,
                                                                ?editor $editor = null): array {

        // The settings of the langages that are available. If this is empty, the plugin will not be active.
        $config = [
            'languages' => [],
        ];

        // When the setting to require the multilang2 filter is set to true,
        // also check if the multilang2 filter is available.
        if ((bool)get_config('tiny_multilang2', 'requiremultilang2')) {
            $filters = filter_get_active_in_context($context);
            if (!array_key_exists('multilang2', $filters)) {
                return $config;
            }
        }

        // We need to pass the list of languages to tinymce.
        if (get_config('tiny_multilang2', 'showalllangs')) {
            $langs = get_string_manager()->get_list_of_languages();
        } else {
            $langs = get_string_manager()->get_list_of_translations();
        }

        if (count($langs) > 1) {
            asort($langs);
            foreach ($langs as $iso => $label) {
                $config['languages'][] = [
                    'iso' => $iso,
                    'label' => str_replace(["\xe2\x80\x8e", "\xe2\x80\x8f"], '', $label),
                ];
            }
            $config['languages'][] = [
                'iso' => 'other',
                'label' => get_string('multilang2:other', 'tiny_multilang2') . ' (other)',
            ];
        }

        $config['highlight'] = (bool)get_config('tiny_multilang2', 'highlight');

        if ($config['highlight']) {
            $css = trim(get_config('core', 'highlight_css'));
            if (empty($css)) {
                $css = self::get_default_css();
            }
            $config['css'] = $css;
        }

        return $config;
    }

    /**
     * Return the default css for highlighting {lang} tags when tiny_multilang2 | highlight is
     * set to true.
     * @return string
     */
    public static function get_default_css(): string {
        return '
            .multilang-begin, .multilang-end {
                outline: 1px dotted;
                padding: 0.1em;
                margin: 0em 0.1em;
                background-color: #ffffaa;
            }
        ';
    }

}
