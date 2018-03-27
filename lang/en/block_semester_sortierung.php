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

$string['migrate_title'] = 'Migrate settings from deprecated semsort block';
$string['migrate'] = 'Migrate settings';
$string['migrate:results'] = '<strong>Migration complete:</strong><br />';
$string['migrate:defaultdashboard'] = 'Replace block_semestersortierung with block_semester_sortierung on default dashboardpage';
$string['migrate:defaultdashboard_help'] = 'By selecting this checkbox, the migration script will replace block_semsort instance with a block_semester_sortierung instance on the <strong>Default Dashboard page</strong>. Here, both "Migrate for current user only" and "Migrate for all users" have the same effect.';
$string['migrate:alldashboards'] = 'Replace block_semestersortierung with block_semester_sortierung on all users dashboardpages';
$string['migrate:alldashboards_help'] = 'By selecting this checkbox, the migration script will replace all block_semsort instances with block_semester_sortierung instances on <strong>all Dashboard pages</strong> except for the Default Dashboard page. For testing purposes, this option can be executed only for the current user.';
$string['migrate:usersettings'] = 'Migrate user settings (favorite, private order) from block_semestersortierung to block_semester_sortierung';
$string['migrate:usersettings_help'] = 'By selecting this checkbox, the migration script will copy all users\' personal settings from block_semsort tp block_semester_sortierung. Personal users settings are courses marked as favorite, order of courses inside semester boxes, and which courses are expanded.  For testing purposes, this option can be executed only for the current user.';
$string['migrate:adminsettings'] = 'Migrate admin settings from block_semestersortierung to block_semester_sortierung';
$string['migrate:adminsettings_help'] = 'By selecting this checkbox, the migration script will copy (and replace) all admin settings from block_semsort to block_semester_sortierung. Here, both "Migrate for current user only" and "Migrate for all users" have the same effect.';
$string['migrate:defaultdashboard:success'] = 'Default Dashboard updated';
$string['migrate:alldashboards:success'] = 'All users\' Dashboard pages updated';
$string['migrate:alldashboards_currentuser:success'] = 'Current user\'s Dashboard page updated';
$string['migrate:usersettings:success'] = 'User settings: <p class="p-l-1">Updated db records: {$a->updated}<br />Newly inserted db records: {$a->inserted}<br />Unchanged db records: {$a->unchanged}</p>';
$string['migrate:adminsettings:success'] = 'Admin settings migrated';
$string['migrateone'] = 'Migrate for current user only';
$string['migrateall'] = 'Migrate for all users';

$string['setting:archivedesc'] = 'Older than {$a} terms';
$string['setting:archive'] = 'Archive';

$string['setting:autoclose'] = 'Autocollapse courses';
$string['setting:autoclosedesc'] = 'Automatically collapse expanded courses older than ... semesters';

$string['setting:skipevents'] = 'Skip older events';
$string['setting:skipeventsdesc'] = 'Skip events that are older than ... months';