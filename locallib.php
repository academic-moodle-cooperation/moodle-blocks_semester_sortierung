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
 * Local functions
 *
 * @package       block_semester_sortierung
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Simeon Naydenov (moniNaydenov@gmail.com)
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function block_sememster_sortierung_usort($a, $b) {
    return strcasecmp(trim($a->fullname), trim($b->fullname));
}

function block_semester_sortierung_toggle_fav($courseid, $status) {

    if ($favorites = get_user_preferences('semester_sortierung_favorites', '')) {
        $favorites = explode(',', $favorites);
        $favorites = array_flip($favorites);
    } else {
        $favorites = array();
    }

    if ($status) {
        $favorites[$courseid] = 1;
    } else {
        if (isset($favorites[$courseid])) {
            unset($favorites[$courseid]);
        }
    }
    $favorites = implode(',', array_keys($favorites));

    set_user_preference('semester_sortierung_favorites', $favorites);
}

function block_semester_sortierung_update_personal_sort($config = null) {

    // Get all possible parameters.
    $movecourse = optional_param('block_semester_sortierung_move_course', 0, PARAM_INT);
    $movecoursesemester = optional_param('block_semester_sortierung_move_semester', '', PARAM_ALPHANUM);
    $movecoursetarget = optional_param('block_semester_sortierung_move_target', -1, PARAM_INT);

    if ($movecourse <= 0 || $movecoursesemester == '' || $movecoursetarget < 0) {
        return; // Not needed.
    }

    if (is_null($config)) {
        $config = get_config('block_semester_sortierung');
    }

    if ((isset($config->enablepersonalsort) && $config->enablepersonalsort == '1') == false) {
        return; // Personal sorting disabled.
    }

    $courses = enrol_get_my_courses('id, fullname, shortname', 'visible DESC, fullname ASC');

    if (!isset($courses[$movecourse])) {
        return; // Invalid course id.
    }
    $context = context_course::instance($courses[$movecourse]->id);

    if (is_enrolled($context) != true) {
        return; // Invalid course id.
    }

    $courses = block_semester_sortierung_fill_course_semester($courses, $config, $movecoursesemester);
    $courses = block_semester_sortierung_sort_user_personal_sort($courses, $config, true);

    $userpref = get_user_preferences('semester_sortierung_sorting', '[]');
    $userpref = json_decode($userpref, true);

    if (!isset($userpref[$movecoursesemester])) {
        return; // Invalid semester.
    }

    $usersort = $userpref[$movecoursesemester];

    if (!in_array($movecourse, $usersort)) {
        return; // Invalid course/semester combination.
    }

    if (($key = array_search($movecourse, $usersort)) !== false) {
        unset($usersort[$key]); // Remove the course from the list.
    }

    if ($movecoursetarget >= count($userpref[$movecoursesemester])) {
        $movecoursetarget = count($userpref[$movecoursesemester]) - 1; // Prevent invalid input.
    }

    array_splice($usersort, $movecoursetarget, 0, $movecourse); // Add the course at the new index.

    $userpref[$movecoursesemester] = array_values($usersort); // Clear out indexes.

    set_user_preference('semester_sortierung_sorting', json_encode($userpref)); // Save setting.

}

/**
 * add the "semester" variable to each semester. The semester is calculated according to the settings and the course
 * start date. Thus, each course MUST have a course date
 *
 */
function block_semester_sortierung_fill_course_semester($courses, $config, $chosensemester = null) {
    $sortedcourses = array();

    $showfavorites = isset($config->enablefavorites) && $config->enablefavorites == '1';
    $favorites = array();

    if ($showfavorites) {
        // Favorites are not enabled, nothing to do here.
        if ($favorites = get_user_preferences('semester_sortierung_favorites', '')) {
            $favorites = array_flip(explode(',', $favorites));
        }
        $sortedcourses['fav'] = array(
            'visible' => array(),
            'hidden' => array(),
            'title' => get_string('favorites', 'block_semester_sortierung')
        );
    }

    foreach ($courses as $course) {
        $semester = block_semester_sortierung_get_semester($config, $course->startdate);
        $course->semester_short = $semester->semester_short;
        if (empty($sortedcourses[$course->semester_short])) {
            $sortedcourses[$course->semester_short] = array(
                'visible' => array(),
                'hidden' => array(),
                'title' => $semester->semester
            );
        }
        $visible = $course->visible ? 'visible' : 'hidden';

        $sortedcourses[$course->semester_short][$visible][$course->id] = $course;
        if (isset($favorites[$course->id])) {
            $course = clone $course;
            $course->semester_short = 'fav';
            $sortedcourses['fav'][$visible][$course->id] = $course;
        }
    }
    krsort($sortedcourses);
    foreach ($sortedcourses as $semester => $groups) {
        // Esed to spare some some work after that when only changing personal sorting.
        if (!is_null($chosensemester) && $semester != $chosensemester) {
            unset($sortedcourses[$semester]);
            continue;
        }
        uasort($groups['visible'], 'block_sememster_sortierung_usort');
        uasort($groups['hidden'], 'block_sememster_sortierung_usort');
        $sortedcourses[$semester]['courses'] = $groups['visible'] + $groups['hidden'];
        unset($sortedcourses[$semester]['visible']);
        unset($sortedcourses[$semester]['hidden']);
    }
    return $sortedcourses;
}

function block_semester_sortierung_sort_user_personal_sort($sortedcourses, $config, $forcecreate = false) {

    if ((isset($config->enablepersonalsort) && $config->enablepersonalsort == '1') == false) {
        // Nothing to do.
        return $sortedcourses;
    }

    $userpref = get_user_preferences('semester_sortierung_sorting', '[]');

    $userpref = json_decode($userpref, true);

    $userprefchanged = false; // Used to spare some db updates.
    // Go through all semesters.
    foreach ($sortedcourses as $semester => $semesterinfo) {
        if ($forcecreate && !isset($userpref[$semester])) {
            $userpref[$semester] = array();
        }
        // Check if existing in the user preference; if not, don't do anything and keep the current sort.
        if (isset($userpref[$semester])) {
            $usersorted = array();
            $sempref = $userpref[$semester]; // Short for $usersorted[$semester].
            $courses = $semesterinfo['courses']; // Short for $semesterinfo['courses'].
            // Go through all courses in the preference and add them to the new sorted list in the desired order.
            foreach ($sempref as $i => $sortedid) {
                // Check whether course exists in user courses.
                if (isset($courses[$sortedid])) {
                    $usersorted[$sortedid] = $courses[$sortedid];
                    unset($courses[$sortedid]);
                } else {
                    // Remove non-existent courses.
                    $userprefchanged = true;
                    unset($sempref[$i]);
                }
            }

            // Checks if any unsorted courses are left; if that is the case, add them to the sorted list in the
            // Default order.
            foreach ($courses as $id => $course) {
                $userprefchanged = true;
                $usersorted[$id] = $course;
                $sempref[] = $id;
            }

            $userpref[$semester] = array_values($sempref); // Extract the values only => shortens the json.
            $sortedcourses[$semester]['courses'] = $usersorted; // Replaces old sorted courses with user sorted courses.
        }
    }
    // In case there was a change, update the db.
    if ($userprefchanged) {
        set_user_preference('semester_sortierung_sorting', json_encode($userpref));
    }

    return $sortedcourses;

}

/**
 * convert a date to a valid semester
 *
 * @return string
 */
function block_semester_sortierung_get_semester($config, $timestamp) {
    global $CFG;
    $month = userdate($timestamp, '%m');
    $year = userdate($timestamp, '%Y');
    $prevyear = strval((intval($year) - 1));
    $nextyear = strval((intval($year) + 1));
    $semester = '';
    $short = '';

    if (isset($config->wintermonths) && strpos($config->wintermonths, 'mon'.$month) !== false) {
        if (intval($month) <= 6) {
            $short = $prevyear . 'W';
            $semester = $prevyear .'/' . $year;
        } else {
            $short = $year . 'W';
            $semester = $year .'/' . $nextyear;
        }
        $semester = get_string('wintersem', 'block_semester_sortierung') . '  ' . $semester;
    } else {
        $short = $year . 'S';
        $semester = get_string('summersem', 'block_semester_sortierung') . '  ' . $year;
    }

    $ret = new stdClass;
    $ret->semester = $semester;
    $ret->semester_short = $short;
    return $ret;

}