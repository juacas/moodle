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
 * Strings for 'Multilang v2' plugin.
 *
 * @package   tiny_multilang2
 * @copyright 2015 onwards Iñaki Arenaza & Mondragon Unibertsitatea
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['highlightcss'] = 'CSS für Sprachtag';
$string['highlightcss_desc'] = "CSS für die Marker um dem sprachabhängigen Inhalt anzuzeigen.

Wenn Sie die Spache anzeigen möchten, die in einem sprachabhängigen Block verwendet wird, kann folgendes CSS benutzt werden (dieses Beispiel ist für Deutsch, Fraben sind eventuell nicht optimal gewählt):

<pre>
.multilang-begin:lang(de):before {
    content: \"de\";
    position: relative;
    top: -0.5em;
    font-weight: bold;
    background-color: #e05e5e;
    color: #ffffff;
}
</pre>
";
$string['highlight'] = 'Trennzeichen hervorheben';
$string['highlight_desc'] = 'Die Trennzeichen für den sprachabhänigen Inhalt sollen hervorgehoben werden (z.B., {mlang XX} und {mlang}) im WYSIWYG Editor';
$string['pluginname'] = 'Multi-Language Content (v2)';
$string['settings'] = 'Tiny Multi-Language Content (v2) Einstellungen';
$string['requiremultilang2'] = 'Benötigt Multi-Language Content (v2) Filter';
$string['requiremultilang2_desc'] = 'Wenn aktiviert, wird das Menü und der Button im Editor nur dann angezeigt, wenn der Multi-Language Content (v2) Filter aktiv ist.';
$string['showalllangs'] = 'Zeige alle Sprachen';
$string['showalllangs_desc'] = 'Wenn aktiviert, werden im Editor alle Sprachen angezeigt, die von Moodle unterstützt werden. Wenn nicht aktivert, werden nur die installierten und aktiven Sprachen angezeigt.';
$string['privacy:metadata'] = 'Das Tiny Multi-Language Content (v2) Plugin speichert keine personenbezogenen Daten.';

/* All lang strings used from TinyMCE JavaScript code must be named 'pluginname:stringname', no need to create langs/en_dlg.js */
$string['multilang2:desc'] = 'Unterstützung zum Hinzufügen von sprachabhänigem Inhalt (der Multi-Language Content (v2) filter muß aktiviert sein)';
$string['multilang2:language'] = 'Sprache';
$string['multilang2:viewlanguagemenu'] = 'Anzeigen der Sprachen als Liste im TinyMCE editor';
$string['multilang2:other'] = 'andere';
