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
 * @copyright   2017 Lars Herwegh <lars.herwegh@mni.thm.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// General identifiers
$string['talsname'] = 'THM Attendance Logging System';
$string['talssettings'] = 'TALS Einstellungen';
$string['talsfieldset'] = 'TALS Feldsatz';
$string['tals'] = '';
$string['modulename'] = 'THM Attendance Logging System';
$string['modulenameplural'] = 'THM Attendance Logging System';
$string['modulenamesimple'] = 'THM Attendance Logging System';
$string['pluginname'] = 'THM Attendance Logging System';
$string['pluginadministration'] = 'TALS Administration';

// Help messages
$string['talsname_help'] = 'Tals Hilfe';
$string['modulename_help'] = 'Das \'THM Attendance Logging System\'- Modul ermöglicht es Lehrkräften die  Anwesenheit während Veranstaltungen festzustellen und Studierenden, ihre Anwesenheiten zu erfassen. 

Die Lehrkraft kann Termine verschiedener Art erstellen (z.B. \'Vorlesungen\', oder \'Übungen\'). Wenn die Lehrkraft die Anwesenheit abfragen will, kann sie_er eine PIN erstellt und den Studierenden mitgeteilt werden. Nachdem die PIN freigeschaltet wurde, kann jede_r Studierende die eigene Anwesenheit eintragen. Die Lehrkraft kann daraufhin die Anwesenheitsliste kontrollieren und den Status der Studierenden ändern.';

// Text-list
$string['acronym'] = 'Akronym';
$string['nonewmodules'] = 'Keine neuen Module';
$string['missingidandcmid'] = 'Fehlende ID und CM ID';

// Acronyms and description for default tals_type_attendance
$string['Present_acronym'] = 'A';
$string['Present_full'] = 'Anwesend';
$string['Absent_acronym'] = 'F';
$string['Absent_full'] = 'Fehlend';
$string['Excused_acronym'] = 'E';
$string['Excused_full'] = 'Entschuldigt';

//Acronyms and description for default tals_type_appointment
$string['Lecture_acronym'] = 'VRL';
$string['Lecture_full'] = 'Vorlesungen';
$string['Excercise_acronym'] = 'ÜBG';
$string['Excercise_full'] = 'Übung';
$string['Seminar_acronym'] = 'SMR';
$string['Seminar_full'] = 'Seminar';
$string['Training_acronym'] = 'PRK';
$string['Training_full'] = 'Praktikum';
$string['Other_acronym'] = 'DIV';
$string['Other_full'] = 'Diverse';

// Error messages and warnings
$string['nopermission'] = 'Keine Zugangsberechtigung!';
$string['alreadylogged'] = 'Die Anwesenheit des Users ist bereits bestätigt.';
$string['noappointment'] = 'Keinen Termin gefunden.';
$string['pinnotenabled'] = 'PIN ist noch nicht freigeschaltet. Bitte später noch einmal versuchen.';
$string['pinwrong'] = 'Die eingegebene PIN ist falsch.';
$string['success'] = 'Erfolgreich';
$string['fail'] = 'Gescheitert';
$string['error'] = 'Fehler';
$string['lognotexist'] = 'Fehler: Kein Eintrag gefunden.';
$string['typenoattendance'] = 'Fehler: Anwesenheitstyp nicht gefunden.';
$string['typenonet'] = 'Fehler: Netzwerktyp nicht gefunden.';
$string['logupdated'] = 'Anwesenheit wurde aktualisiert.';
$string['lognotupdated'] = 'Anwesenheit wurde nicht aktualisiert.';
$string['pinnotexist'] = 'Fehler: PIN nicht gefunden.';

// Info messages
$string['instance'] = 'Dieser Kurs hat bereits eine TALS Instanz. Es ist möglich eine weitere Instanz hinzuzufügen. Dies hat keinen Vorteil.';

// Module UI
$string['label_header_date'] = 'Termin';
$string['label_header_add'] = 'Termin hinzufügen';
$string['label_header_report'] = 'Bericht';
$string['label_date'] = 'Zusammenfassung';
$string['label_id'] = 'ID';
$string['label_name'] = 'Name';
$string['label_description'] = 'Beschreibung';
$string['label_start'] = 'Start';
$string['label_end'] = 'Ende';
$string['label_duration'] = 'Dauer';
$string['label_type'] = 'Typ';
$string['label_edit'] = 'Bearbeiten';
$string['label_count'] = 'Anwesende';
$string['label_report'] = 'Bericht';
$string['label_email'] = 'E-Mail';
$string['label_status'] = 'Status';
$string['label_net'] = 'Netz';
$string['label_comment'] = 'Kommentar';
$string['label_reportdetail'] = 'Details';
$string['label_legend'] = 'Legende';
$string['label_green'] = 'Grün';
$string['label_yellow'] = 'Gelb';
$string['label_red'] = 'Rot';
$string['label_net_green'] = 'Hausinternes Netzwerk';
$string['label_net_blue'] = 'VPN';
$string['label_net_grey'] = 'Externes Netzwerk';
$string['label_daysabsent'] = 'Verpasst';
$string['label_attendance'] = 'Anwesenheit';
$string['label_excused'] = 'Entschuldigt';
$string['label_minute'] = 'Min';
$string['label_pin'] = 'PIN';
$string['label_show'] = 'ist sichtbar';
$string['label_hide'] = 'ist unsichtbar';
$string['label_trash'] = 'löschen';
$string['label_cancel'] = 'abbrechen';
$string['label_attendance_in'] = 'Anwesenheit in';
$string['label_arecompulsory'] = 'sind verpflichtend';
$string['label_iscompulsory'] = 'ist verplichtend';
$string['label_activatepin'] = 'PIN-Eingabe ermöglichen';
$string['label_countappointments'] = 'Anzahl der Termine';
$string['label_newdate'] = 'Neuer Termin';
$string['label_occurrence'] = 'Auftreten';
$string['label_at'] = 'am';
$string['label_from'] = 'von';
$string['label_until'] = 'bis';
$string['label_more'] = 'mehr';
$string['label_repeat'] = 'wiederholen';
$string['label_every'] = 'jede';
$string['label_next'] = 'nächste';
$string['label_weeks'] = 'Woche(n)';
$string['label_pininfo'] = '';
$string['label_safe'] = 'Speichern';
$string['label_myattendance'] = 'Meine Anwesenheit';
$string['label_courseoverview'] = 'Kursübersicht';
$string['label_compulsory'] = 'Verplichtend';
$string['label_period'] = 'Zeitraum';
$string['label_required'] = 'erforderlich';
$string['label_issure'] = 'Sind Sie wirklich sicher, dass Sie diesen Termin unwiderruflich löschen wollen? ';
$string['label_reload'] = 'neu laden';
$string['label_download'] = 'Herunterladen';
$string['label_hour'] = 'Uhr';
$string['label_cancel'] = 'Abbrechen';

// Moodle UI - Capabilities and so on...
$string['tals:addinstance'] = 'Instanz hinhzufügen';
$string['tals:change'] = 'Bericht ändern';
$string['tals:manage'] = 'Plugin verwalten';
$string['tals:takeattendances'] = 'Anwesenheit eintragen';
$string['tals:view'] = 'Profil ansehen';
$string['tals:viewreports'] = 'Bericht ansehen';
