<?php
// This is Semester overview block for Moodle.
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
 * This file returns the required mod information for a given course for a given user
 *
 * @package blocks_semester_sortierung
 * @author Simeon Naydenov
 * @copyright 2012 Vienna University of Technology
 */

$course = $DB->get_record('course', array('id' => $cid), '*', MUST_EXIST);
require_login($course);
$context = context_user::instance($USER->id);
$PAGE->set_context($context);

print $OUTPUT->header();
$mods = get_fast_modinfo($course);
$mods_array = array();
foreach ($mods->cms as $modinfo) {
    if (!in_array($modinfo->modname, $mods_array)) {
        array_push($mods_array, $modinfo->modname);
    }
}
sort($mods_array);
//code copied from course_overview block, that's why $USER is used, although discouraged
if (isset($USER->lastcourseaccess[$course->id])) {
    $course->lastaccess = $USER->lastcourseaccess[$course->id];
} else {
    $course->lastaccess = 0;
}

$sortedcourses = array($course->id => $course);
$htmlarray = array();

foreach ($mods_array as $mod) {
    if (file_exists($CFG->dirroot.'/mod/'.$mod.'/lib.php')) {
        include_once($CFG->dirroot.'/mod/'.$mod.'/lib.php');
        $fname = $mod.'_print_overview';
        if (function_exists($fname)) {
            @$fname($sortedcourses, $htmlarray);
        }
    }
}
if (isset($htmlarray[$course->id])) {
    $content = '';
    foreach ($htmlarray[$course->id] as $modname => $modinfo) {
        $content .= $modinfo;
    }
    print html_writer::tag('div', $content, array('class' => 'coursebox'));
}
