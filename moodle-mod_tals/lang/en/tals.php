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
 * @package     mod_tals
 * @category    string
 * @copyright   2017 Technische Hochschule Mittelhessen - University of Applied Sciences - Giessen, Germany
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// General identifiers.
$string['talsname'] = 'THM Attendance Logging System';
$string['talssettings'] = 'TALS Setting';
$string['talsfieldset'] = 'TALS Field-Set';
$string['tals'] = '';
$string['modulename'] = 'THM Attendance Logging System';
$string['modulenameplural'] = 'THM Attendance Logging System';
$string['modulenamesimple'] = 'THM Attendance Logging System';
$string['pluginname'] = 'THM Attendance Logging System';
$string['pluginadministration'] = 'TALS administration';

// Help messages.
$string['talsname_help'] = 'Tals Help';
$string['modulename_help'] = 'The \'THM Attendance Logging System\' activity module enables a teacher to take attendance during class and students to view their own attendance record.

The teacher can create multiple appointments of different types (e.g. \'Lecture\' or \'Excercise\'). If the teacher wants to take the attendance they can specify a PIN and provide it to the students. After the PIN is set active each student is able to commit their attendance by themself. The teacher then can check the list and is able to change their statuses.';

// Text-list.
$string['acronym'] = 'Acronym';
$string['nonewmodules'] = 'No New Modules';
$string['missingidandcmid'] = 'Missing ID and CM ID';

// Acronyms and description for default tals_type_attendance.
$string['Present_acronym'] = 'P';
$string['Present_full'] = 'Present';
$string['Absent_acronym'] = 'A';
$string['Absent_full'] = 'Absent';
$string['Excused_acronym'] = 'E';
$string['Excused_full'] = 'Excused';

// Acronyms and description for default tals_type_appointment.
$string['Lecture_acronym'] = 'LCT';
$string['Lecture_full'] = 'Lecture';
$string['Excercise_acronym'] = 'EXC';
$string['Excercise_full'] = 'Excercise';
$string['Seminar_acronym'] = 'SMR';
$string['Seminar_full'] = 'Seminar';
$string['Training_acronym'] = 'TRN';
$string['Training_full'] = 'Training';
$string['Other_acronym'] = 'OTH';
$string['Other_full'] = 'Other';

// Error messages and warnings.
$string['nopermission'] = 'No permission!';
$string['alreadylogged'] = 'The Users attendance is already taken.';
$string['noappointment'] = 'No appointment found.';
$string['pinnotenabled'] = 'PIN is not enabled yet. Please try again later.';
$string['pinwrong'] = 'The entered PIN is wrong.';
$string['success'] = 'Success';
$string['fail'] = 'Fail';
$string['error'] = 'Error';
$string['lognotexist'] = 'Error: No log found.';
$string['typenoattendance'] = 'Error: Attendance-Type not found.';
$string['typenonet'] = 'Error: Network-Type not found.';
$string['logupdated'] = 'Attendance is updated.';
$string['lognotupdated'] = 'Attendance is not updated.';
$string['pinnotexist'] = 'Error: PIN not found.';

// Info messages.
$string['instance'] = 'This course has a TALS instance already. Its possible to add another, but it has no effort.';

// Module UI.
$string['label_header_date'] = 'Appointment';
$string['label_header_add'] = 'Add Appointment';
$string['label_header_report'] = 'Report';
$string['label_date'] = 'Summary';
$string['label_id'] = 'ID';
$string['label_name'] = 'Name';
$string['label_description'] = 'Description';
$string['label_start'] = 'Start';
$string['label_end'] = 'End';
$string['label_duration'] = 'Duration';
$string['label_type'] = 'Type';
$string['label_edit'] = 'Edit';
$string['label_count'] = 'Attendees';
$string['label_report'] = 'Report';
$string['label_email'] = 'E-Mail';
$string['label_status'] = 'Status';
$string['label_net'] = 'Net';
$string['label_comment'] = 'Comment';
$string['label_reportdetail'] = 'Details';
$string['label_legend'] = 'Legend';
$string['label_green'] = 'Green';
$string['label_yellow'] = 'Yellow';
$string['label_red'] = 'Red';
$string['label_net_green'] = 'In-house Network';
$string['label_net_blue'] = 'VPN';
$string['label_net_grey'] = 'External Network';
$string['label_daysabsent'] = 'Missed';
$string['label_attendance'] = 'Attendance';
$string['label_excused'] = 'Excused';
$string['label_minute'] = 'Min';
$string['label_pin'] = 'PIN';
$string['label_show'] = 'Is visible';
$string['label_hide'] = 'Is invisible';
$string['label_trash'] = 'Delete';
$string['label_cancel'] = 'Cancel';
$string['label_attendance_in'] = 'Attendance in';
$string['label_arecompulsory'] = 'are compulsory';
$string['label_iscompulsory'] = 'is compulsory';
$string['label_activatepin'] = 'enable PIN-Request';
$string['label_countappointments'] = 'Count of appointments';
$string['label_newdate'] = 'New Appointment';
$string['label_occurrence'] = 'Occurrence';
$string['label_at'] = 'at';
$string['label_from'] = 'from';
$string['label_until'] = 'until';
$string['label_more'] = 'more';
$string['label_repeat'] = 'Repeat';
$string['label_every'] = 'every';
$string['label_next'] = 'next';
$string['label_weeks'] = 'Week(s)';
$string['label_pininfo'] = 'For each appointment a random PIN is created.';
$string['label_safe'] = 'Safe';
$string['label_myattendance'] = 'My Attendance';
$string['label_courseoverview'] = 'Course Overview';
$string['label_compulsory'] = 'Compulsory';
$string['label_period'] = 'Period';
$string['label_required'] = 'Required';
$string['label_issure'] = 'Are you really sure you want to delete this appointment? (This is irreversible!)';
$string['label_reload'] = 'Reload';
$string['label_download'] = 'Download';
$string['label_hour'] = 'h';

// Moodle UI - Capabilities and so on...
$string['tals:addinstance'] = 'Add instance';
$string['tals:change'] = 'Change reports';
$string['tals:manage'] = 'Manage plugin';
$string['tals:takeattendances'] = 'Take attendance';
$string['tals:view'] = 'View profile';
$string['tals:viewreports'] = 'View reports';
