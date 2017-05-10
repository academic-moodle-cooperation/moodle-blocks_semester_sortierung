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
 * This file updates the user_preference for the block boxes' status
 *
 * @package       block_semester_sortierung
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Simeon Naydenov (moniNaydenov@gmail.com)
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');


$boxid = required_param('id', PARAM_ALPHANUM);
$state = required_param('state', PARAM_INT);
$courseajax = optional_param('ajax', 0, PARAM_INT);
$boxtype = required_param('boxtype', PARAM_ALPHA);


require_login();

if ($boxtype == 's') {
    $prefname = 'semester_sortierung_semesters';
} else {
    $prefname = 'semester_sortierung_courses';
}
if ($expanded = get_user_preferences($prefname, '')) {
    $expanded = explode(',', $expanded);
    $expanded = array_flip($expanded);
} else {
    $expanded = array();
}

if ($state) {
    $expanded[$boxid] = 1;
} else {
    if (isset($expanded[$boxid])) {
        unset($expanded[$boxid]);
    }
}
$expanded = implode(',', array_keys($expanded));

set_user_preference($prefname, $expanded);

if ($boxtype == 'c' && $courseajax) {
    $cid = $boxid;


    $courses = $DB->get_records('course', array('id' => $cid));
    if (count($courses) > 0) {

        $PAGE->set_context(\context_course::instance($cid));
        $output = $PAGE->get_renderer('block_semester_sortierung');

        $expandedevents = block_semester_sortierung_get_courses_events($courses, $output);

        echo $output->header();
        echo $output->render_course_info($cid, $expandedevents);

    }

}