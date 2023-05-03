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
 * Commands for the plugin logic of the Moodle tiny_multilang2 plugin.
 *
 * @module      tiny_multilang2
 * @author      Iñaki Arenaza <iarenaza@mondragon.edu>
 * @author      Stephan Robotta <stephan.robotta@bfh.ch>
 * @copyright   2015 onwards Iñaki Arenaza & Mondragon Unibertsitatea
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getHighlightCss, isContentToHighlight} from './options';


// This class inside a <span> identified the {mlang} tag that is encapsulated in a span.
const spanClass = 'class="multilang-begin mceNonEditable"';
// This is the <span> element with the data attribute.
const spanFixedAttrs = '<span ' + spanClass + ' data-mce-contenteditable="false"';
// The begin span needs the language attributes inside the span and the mlang attribute.
const spanMultilangBegin = spanFixedAttrs + ' lang="%lang" xml:lang="%lang">{mlang %lang}</span>';
// The end span doesn't need information about the used language.
const spanMultilangEnd = spanFixedAttrs.replace('begin', 'end') + '>{mlang}</span>';
// Helper functions
const trim = v => v.toString().replace(/^\s+/, '').replace(/\s+$/, '');
const isNull = a => a === null || a === undefined;

/**
 * Convert {mlang xx} and {mlang} strings to spans, so we can style them visually.
 * Remove superflous whitespace while at it.
 * @param {tinymce.Editor} ed
 * @return {string}
 */
const addVisualStyling = function(ed) {
    let content = ed.getContent();

    // Do not use a variable whether text is already highlighted, do a check for the existing class
    // because this is safe for many tiny element windows at one page.
    if (content.indexOf(spanClass) !== -1) {
        return content;
    }

    content = content.replace(new RegExp('{\\s*mlang\\s+([^}]+?)\\s*}', 'ig'), function(match, p1) {
        return spanMultilangBegin.replace(new RegExp('%lang', 'g'), p1);
    });
    content = content.replace(new RegExp('{\\s*mlang\\s*}', 'ig'), spanMultilangEnd);

    return content;
};

/**
 * Remove the spans we added in _add_visual_styling() to leave only the {mlang xx} and {mlang} tags.
 * Also make sure we lowercase the multilang 'tags'
 * @param {tinymce.Editor} ed
 */
const removeVisualStyling = function(ed) {
    ['begin', 'end'].forEach(function(t) {
        let nodes = ed.dom.select('span.multilang-' + t);
        for (let n = 0, l = nodes.length; n < l; n++) {
            const span = nodes[n];
            ed.dom.setOuterHTML(span, span.innerHTML.toLowerCase());
        }
    });
};

/**
 * At the current selection lookup for the current node. If we are inside a special span that encapsulates
 * the {lang} tag, then look for the corresponding opening or closing tag, depending on what's set in the
 * search param.
 * @param {tinymce.Editor} ed
 * @param {string} search
 * @return {Node|null} The encapsulating span tag if found.
 */
const getHighlightNodeFromSelect = function(ed, search) {
    let span;
    ed.dom.getParents(ed.selection.getStart(), elm => {
        // Are we in a span that highlights the lang tag.
        if (!isNull(elm.classList)) {
            // If we are on an opening/closing lang tag, we need to search for the corresponding opening/closing tag.
            const pair = search === 'begin' ? 'end' : 'begin';
            if (elm.classList.contains('multilang-' + pair)) {
                span = elm;
                do {
                    // If we look for begin, go back siblings, otherwise look fnext siblings until end is found.
                    span = search === 'begin' ? span.previousSibling : span.nextSibling;
                } while (!isNull(span) && (isNull(span.classList) || !span.classList.contains('multilang-' + search)));
            } else if (elm.classList.contains('multilang-' + search)) {
                // We are already on the correct tag we search for
                span = elm;
            }
        }
    });
    return span;
};

/**
 * When loading the editor for the first time, add the spans for highlighting the content.
 * @param {tinymce.Editor} ed
 */
const onInit = function(ed) {
    if (isContentToHighlight(ed)) {
        ed.dom.addStyle(getHighlightCss(ed));
        ed.setContent(addVisualStyling(ed));
    }
};

/**
 * When the source code view dialogue is show, we must remove the highlight spans from the editor content
 * and also add them again when the dialogue is closed.
 * @param {tinymce.Editor} ed
 * @param {object} content
 */
const onBeforeGetContent = function(ed, content) {
    if (typeof content.source_view !== 'undefined' && content.source_view === true) {
        // If the user clicks on 'Cancel' or the close button on the html
        // source code dialog view, make sure we re-add the visual styling.
        var onClose = function(ed) {
            ed.off('close', onClose);
            ed.setContent(addVisualStyling(ed));
        };
        ed.on('CloseWindow', () => {
            onClose(ed);
        });

        if (isContentToHighlight(ed)) {
            removeVisualStyling(ed);
        }
    }
};

/**
 * Add an observer to the onPreProcess event to remove the highlighting spans while saving the content.
 * @param {tinymce.Editor} ed
 * @param {Element} node
 */
const onPreProcess = function(ed, node) {
    if (typeof node.save !== 'undefined' && node.save === true) {
        if (isContentToHighlight(ed)) {
            removeVisualStyling(ed);
        }
    }
};

/**
 * Check for key press <del> when something is deleted. If that happens inside a highlight span
 * tag, then remove this tag and the corresponding that open/closes this lang tag.
 * @param {tinymce.Editor} ed
 * @param {Object} event
 */
const onDelete = function(ed, event) {
    if (event.isComposing || (event.keyCode !== 46 && event.keyCode !== 8) || !isContentToHighlight(ed)) {
        return;
    }
    // Key <del> was pressed, to delete some content. Check if we are inside a span for the lang.
    const begin = getHighlightNodeFromSelect(ed, 'begin');
    const end = getHighlightNodeFromSelect(ed, 'end');
    // Only if both, start and end tag are found, then delete the nodes here and prevent the default handling
    // because the stuff to be deleted is already gone.
    if (!isNull(begin) && !isNull(end)) {
        event.preventDefault();
        ed.dom.remove(begin);
        ed.dom.remove(end);
    }
};

/**
 * The action when a language icon or menu entry is clicked. This adds the {mlang} tags at the current content
 * position or around the selection.
 * @param {tinymce.Editor} ed
 * @param {string} iso
 */
const applyLanguage = function(ed, iso) {
    if (iso === null) {
        return;
    }
    let text = ed.selection.getContent();
    // Selection is empty, just insert the lang opening and closing tag
    // together with a space where the user may add the content.
    if (trim(text) === '') {
        let newtext;
        if (isContentToHighlight(ed)) {
            newtext = spanMultilangBegin.replace(new RegExp('%lang', 'g'), iso) + ' ' + spanMultilangEnd;
        } else {
            newtext = '{mlang ' + iso + '}' + ' ' + '{mlang}';
        }
        ed.insertContent(newtext);
        return;
    }
    // Selection contains something, we need to place the open and closing lang tags around the selection.
    // However, there are a few exceptions, e.g. when the selection is inside the lang tag itself. In this case
    // just change the tag without encapsulating the selection.
    if (!isContentToHighlight(ed)) {
        ed.selection.setContent('{mlang ' + iso + '}' + text + '{mlang}');
        return;
    }
    // Syntax highlighting is on. Check if we are on a special span that encapsulates the language tags. Search
    // for the start span tag.
    const span = getHighlightNodeFromSelect(ed, 'begin');
    // If we have a span, then it's the opening tag, and we just replace this one with the new iso.
    if (!isNull(span)) {
        ed.dom.setOuterHTML(span, spanMultilangBegin.replace(new RegExp('%lang', 'g'), iso));
        return;
    }
    // Not inside a lang tag, insert a new opening and closing tag with the selection inside.
    const newtext = spanMultilangBegin.replace(new RegExp('%lang', 'g'), iso) + text + spanMultilangEnd;
    ed.selection.setContent(newtext);
};


export {
    onInit,
    onBeforeGetContent,
    onPreProcess,
    onDelete,
    applyLanguage
};