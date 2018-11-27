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
$string['tals:addinstance'] = 'Add a new TALS block';
$string['tals:myaddinstance'] = 'Add a new TALS block to the \'My Moodle\' page';
$string['blocktitle'] = 'TALS';
$string['instance'] = 'There is no instance of the THM Attendance Logging System installed. Therefore, this block can\'t be used.';

$string['label_pin'] = 'PIN';
$string['label_noapp'] = 'No appointment';
$string['label_nextapp'] = 'Next appointment';
$string['label_minute'] = 'Min';
$string['label_pin'] = 'PIN';
$string['label_show'] = 'Is visible';
$string['label_hide'] = 'Is invisible';
$string['label_until'] = 'until';
$string['label_hour'] = 'h';
$string['label_count'] = 'Attendees';
$string['label_minute'] = 'Min';
$string['label_pinnotenabled'] = 'PIN is not enabled yet.
Please try again later.';
$string['label_alreadyattending'] = 'You\'re already attending.';
$string['label_pinformat'] = 'Please enter at least 4 digits. No letters allowed!';
$string['label_pinexample'] = 'e.g. 1234';
$string['label_ok'] = 'OK';
$string['label_attendancesuccess'] = 'Attendance is inserted!';
$string['label_attendancefailure'] = 'Attendance could not be inserted!';
$string['label_failure'] = 'Failure';
$string['label_success'] = 'Success';
$string['label_enlist'] = 'enlist';
$string['label_acceptmissed_first'] = 'I hereby confirm that I missed'; // these two strings are consecutive and are
$string['label_acceptmissed_last'] = 'Appointment(s) so far.';          // seperated by count of missed appointments
