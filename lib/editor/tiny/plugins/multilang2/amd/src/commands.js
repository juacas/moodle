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

/**
 * Commands helper for the Moodle tiny_multilang2 plugin.
 *
 * @module      tiny_multilang2
 * @author      Iñaki Arenaza <iarenaza@mondragon.edu>
 * @author      Stephan Robotta <stephan.robotta@bfh.ch>
 * @copyright   2015 onwards Iñaki Arenaza & Mondragon Unibertsitatea
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getLanguageList} from './options';
import {component} from './common';
import {get_strings as getStrings} from 'core/str';
import {applyLanguage, onInit, onBeforeGetContent, onPreProcess, onDelete} from './ui';

/**
 * Get the setup function for the button and the menu entry.
 *
 * This is performed in an async function which ultimately returns the registration function as the
 * Tiny.AddOnManager.Add() function does not support async functions.
 *
 * @returns {function} The registration function to call within the Plugin.add function.
 */
export const getSetup = async() => {
    const [
        buttonText,
        tooltip,
    ] = await getStrings(['multilang2:language', 'multilang2:desc'].map((key) => ({key, component})));

    return (editor) => {

        const languageList = getLanguageList(editor);
        // If there is just one language, we don't need the plugin.
        if (languageList.length < 2) {
            return;
        }

        editor.ui.registry.addSplitButton(component, {
            icon: 'language',
            tooltip: tooltip,
            fetch: function(callback) {
                const items = languageList.map((lang) => ({
                    type: 'choiceitem',
                    value: lang.iso,
                    text: lang.label,
                }));
                callback(items);
            },
            onAction: () => {
                applyLanguage(editor, null);
            },
            onItemAction: (_splitButtonApi, value) => {
                applyLanguage(editor, value);
            }
        });

        editor.ui.registry.addNestedMenuItem(component, {
            icon: 'language',
            text: buttonText,
            getSubmenuItems: () => languageList.map((lang) => ({
                type: 'menuitem',
                text: lang.label,
                onAction: () => {
                    applyLanguage(editor, lang.iso);
                },
            }))
        });

        editor.on('init', () => {
            onInit(editor);
        });
        editor.on('BeforeGetContent', (format) => {
            onBeforeGetContent(editor, format);
        });
        editor.on('PreProcess', (node) => {
            onPreProcess(editor, node);
        });
        editor.on('keydown', (event) => {
            onDelete(editor, event);
        });
    };
};
