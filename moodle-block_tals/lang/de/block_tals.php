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
 * Plugin strings are defined here.
 *
 * @package     block_tals
 * @copyright   2018 Lars Herwegh <lars.herwegh@mni.thm.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'THM Attendance Logging System Block';
$string['tals'] = 'TALS';
$string['tals:addinstance'] = 'Einen neuen TALS Block hinzufügen';
$string['tals:myaddinstance'] = 'Einen neuen TALS Block zur \'My Moodle\' Seite hinzufügen'; 
$string['blocktitle'] = 'TALS';
$string['instance'] = 'Es ist keine Instanz des \'THM Attendance Logging System\' vorhanden. Deswegen kann der Block nicht benutzt werden.';

$string['label_pin'] = 'PIN';
$string['label_noapp'] = 'Kein Termin';
$string['label_nextapp'] = 'Nächster Termin';
$string['label_minute'] = 'Min';
$string['label_pin'] = 'PIN';
$string['label_show'] = 'ist sichtbar';
$string['label_hide'] = 'ist unsichtbar';
$string['label_until'] = 'bis';
$string['label_hour'] = 'Uhr';
$string['label_count'] = 'Anwesende';
$string['label_minute'] = 'Min';
$string['label_pinnotenabled'] = 'PIN ist noch nicht aktiviert.
Bitte später noch einmal versuchen.';
$string['label_alreadyattending'] = 'Sie sind bereits eingetragen.';
$string['label_pinformat'] = 'Bitte nur vier Ziffern eintragen. Keine Buchstaben erlaubt!';
$string['label_pinexample'] = 'z.B: 1234';
$string['label_ok'] = 'OK';
$string['label_attendancesuccess'] = 'Anwesenheit ist eingetragen!';
$string['label_attendancefailure'] = 'Anwesenheit kann nicht eingetragen werden!';
$string['label_failure'] = 'fehlgeschlagen';
$string['label_success'] = 'erfolgreich';
$string['label_enlist'] = 'einschreiben';
$string['label_acceptmissed_first'] = 'Hiermit bestätige ich, dass ich bisher an'; // these two strings are consecutive and are
$string['label_acceptmissed_last'] = 'Termin(en) nicht anwesend war. ';            // seperated by count of missed appointments
