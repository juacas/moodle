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
$string['remindermessage'] = '<p>Hay una fecha límite importante próxima a cumplirse en el Esquema "{$a->coursefullname}": </p>' .
    '<p>La fecha límite es: {$a->eventstart}. Por favor compruebe que ha completado sus tareas.</p>' .
    '<p>(Este mensaje es un recordatorio automático. Si ya ha hecho su entrega puede ignorar este aviso.)</p>'.
    '<h3>{$a->eventname}</h3>' . 
    '<p>{$a->description}</p>' .
    '<a href="{$a->url}">Haga clic aquí para ir al formulario</a>' .
    '<hr/><p>Un cordial saludo.</p><p>El equipo de "{$a->sitefullname}"</p>';
$string['remindersubject'] = 'Aviso de próxima fecha límite de evento importante in {$a->coursefullname}';
$string['messageprovider:reminder'] = 'Aviso de la próxima fecha límite importante para un formulario de recogida de datos.';