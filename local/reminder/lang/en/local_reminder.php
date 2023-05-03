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
 * Lang strings
 *
 * @package    local
 * @subpackage reminder
 * @copyright  2023 Juan Pablo de Castro  <juan.pablo.de.castro@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Feedback Reminder';
$string['remindermessage'] = '<p>There is an important upcoming deadline in the "{$a->coursefullname}" Scheme: </p>' .
'<p>The deadline is: {$a->eventstart}. Please check that you have completed your tasks.</p>' .
'<p>(This message is an automatic reminder. If you have already made your delivery you can ignore this prompt.)</p>' .
'<h3>{$a->eventname}</h3>' . 
'<p>{$a->description}</p>' .
'<a href="{$a->url}"><b>Click here to go to the form</b></a>' .
'<hr/><p>Greetings.</p><p>The "{$a->sitefullname}" team</p>';
$string['remindersubject'] = 'Notice of upcoming important deadline in {$a->coursefullname}';
$string['messageprovider:reminder'] = 'Notice of upcoming important deadline for a data reporting form';