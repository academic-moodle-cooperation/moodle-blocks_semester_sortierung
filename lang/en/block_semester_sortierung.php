<?php
// This file is part of block_semester_sortierung for Moodle - http://moodle.org/
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
 * English language file
 *
 * @package       block_semester_sortierung
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Simeon Naydenov (moniNaydenov@gmail.com)
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Semester overview';
$string['sortcourse'] = 'Sort courses by semester';
$string['sortcoursedesc'] = 'Instance-wide on/off';
$string['wintermonths'] = 'Months of the winter semester';
$string['monthsdesc'] = 'Not marked months = Months of the summer semester. Months January - June still count for the winter semester from the previous year';
$string['summersem'] = 'Summer term';
$string['wintersem'] = 'Winter term';

$string['favorites'] = 'My favorites';
$string['addtofavorites'] = 'Add to favorites';
$string['removefromfavorites'] = 'Remove from favorites';
$string['enablefavorites'] = 'Show Favorites';
$string['enablefavoritesdesc'] = 'Show a separate section for courses chosen as favorites';
$string['enablepersonalsort'] = 'Enable personal sort';
$string['enablepersonalsortdesc'] = 'Enable the user to sort the courses according to his personal wish';
$string['semester_sortierung:addinstance'] = 'Add a new Semester overview block';
$string['semester_sortierung:myaddinstance'] = 'Add a new Semester overview block to My home';

$string['setting:archivedesc'] = 'Older than {$a} terms';
$string['setting:archive'] = 'Archive';

$string['setting:autoclose'] = 'Autocollapse courses';
$string['setting:autoclosedesc'] = 'Automatically collapse expanded courses older than ... semesters';

$string['setting:skipevents'] = 'Skip older events';
$string['setting:skipeventsdesc'] = 'Skip events that are older than ... months';

$string['setting:showunreadforumposts'] = 'Show unread forum posts count';
$string['setting:showunreadforumpostsdesc'] = 'if checked, a balloon icon will appear next to each course that contains the number of unread forum posts.';


// Privacy API strings!
$string['privacy:metadata:usersorting:userid'] = 'The ID of the user for which the course order is stored.';
$string['privacy:metadata:usersorting:semester'] = 'The semester for which the course order is stored.';
$string['privacy:metadata:usersorting:courseorder'] = 'The order of the courses as they appear in the block and ordered by the user.';
$string['privacy:metadata:usersorting:lastmodified'] = 'A timestamp of the last modification date';
$string['privacy:metadata:usersorting'] = 'Table which stores the personal order of the courses per user per semester.';
$string['privacy:metadata:preference:semester_sortierung_favorites'] = 'Stores courses marked as favorites';
$string['privacy:metadata:preference:semester_sortierung_courses'] = 'Stores the state of courses inside the block (opened or closed).';
$string['privacy:metadata:preference:semester_sortierung_semesters'] = 'Stores the state of semesters inside the block (opened or closed).';
$string['privacy:exportdata:preference:semester_sortierung_semesters'] = 'List of comma-separated semesters which remain expanded after the user leaves MOODLE. Semesters are given in format YYYYW, where YYYY stands for the year, and W stands for Winter or Summer semester';
$string['privacy:exportdata:preference:semester_sortierung_courses'] = 'List of comma-separated courses which remain expanded after the user leaves MOODLE';
$string['privacy:exportdata:preference:semester_sortierung_favorites'] = 'List of comma-separated course ids marked as favorite';

$string['privacy:exportdata:usersort'] = 'User courses sorting';
