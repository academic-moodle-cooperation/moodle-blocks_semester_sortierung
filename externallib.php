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
 * External API functions
 *
 * @package       block_semester_sortierung
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Simeon Naydenov (moniNaydenov@gmail.com)
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


require_once("$CFG->libdir/externallib.php");

class block_semester_sortierung_external extends external_api {

    /**
     * Describes the parameters for get_modules.
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_modules_parameters() {
        return new external_function_parameters (
            array()
        );
    }

    /**
     * Returns a list of all active modules
     *
     * @return array the forum details
     * @since Moodle 3.0
     */
    public static function get_modules() {
        global $CFG, $DB, $OUTPUT;
        $modules = $DB->get_records('modules', array('visible' => 1));
        $result = array();
        $CFG->svgicons = false;
        foreach ($modules as $module) {
            $m = new stdClass;
            $m->name = $module->name;

            $m->title_en = (new lang_string('pluginname',
                                            'mod_' . $module->name,
                                            null,
                                            'en'))->out();
            $m->title_de = (new lang_string('pluginname',
                                            'mod_' . $module->name,
                                            null,
                                            'de'))->out();
            $m->image = base64_encode(file_get_contents($OUTPUT->image_url('icon', 'mod_' . $module->name)));
            if (empty($m->image)) {
                $imagepath = $CFG->dirroot . '/mod/' . $module->name . '/pix/icon.';
                if (file_exists($imagepath . 'png')) {
                    $m->image = base64_encode(file_get_contents($imagepath . 'png'));
                } else if (file_exists($imagepath . 'gif')) {
                    $m->image = base64_encode(file_get_contents($imagepath . 'gif'));
                }
            }
            $result[] = $m;
        }
        return $result;
    }

    /**
     * Describes the get_modules return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
    public static function get_modules_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'name' => new external_value(PARAM_TEXT, 'Short one-word name of module'),
                    'title_en' => new external_value(PARAM_TEXT, 'Module title in english'),
                    'title_de' => new external_value(PARAM_TEXT, 'Module title in german'),
                    'image' => new external_value(PARAM_RAW, 'Module image')
                ), 'module'
            )
        );
    }

    /**
     * Describes the parameters for get_courses.
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_courses_parameters() {
        return new external_function_parameters (
            array('userid' => new external_value(PARAM_TEXT, 'user id', VALUE_REQUIRED))
        );
    }

    /**
     * Returns a list of user courses
     *
     * @return array the forum details
     * @since Moodle 3.0
     */
    public static function get_courses($userid) {
        global $CFG, $DB, $OUTPUT;
        require_once(__DIR__ . '/locallib.php');

        $user = $DB->get_record('user', array('username' => $userid));

        // Get the information about the enrolled courses.
        $courses = enrol_get_all_users_courses($user->id, false , 'id, fullname, shortname, idnumber, summary');

        $config = get_config('block_semester_sortierung');
        // Add the semester to each course.
        $courses = block_semester_sortierung_fill_course_semester($courses, $config);

        $result = array();
        foreach ($courses as $semester => $seminfo) {
            if ($semester == 'fav') {
                continue;
            }
            foreach ($seminfo['courses'] as $semcourse) {
                $course = new stdClass;
                $course->coursenumber = $semcourse->idnumber;
                $course->coursename = $semcourse->fullname;
                $course->description = $semcourse->summary;
                $course->courseid = $semcourse->id;
                $course->semestercode = $semcourse->semester_short;
                $result[] = $course;
            }
        }
        return $result;
    }

    /**
     * Describes the get_courses return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
    public static function get_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'coursename' => new external_value(PARAM_TEXT, 'Name of course'),
                    'semestercode' => new external_value(PARAM_RAW, 'Semester code'),
                    'coursenumber' => new external_value(PARAM_RAW, 'Course number'),
                    'courseid' => new external_value(PARAM_INT, 'Database unique course id'),
                    'description' => new external_value(PARAM_RAW, 'Course description')
                ), 'course'
            )
        );
    }

    /**
     * Describes the parameters for get_coursedetails.
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_coursedetails_parameters() {
        return new external_function_parameters (
            array(
                'userid' => new external_value(PARAM_TEXT, 'user id', VALUE_REQUIRED),
                'courseid' => new external_value(PARAM_INT, 'course id', VALUE_REQUIRED)
            )
        );
    }

    /**
     * Returns a list of user courses
     *
     * @return array the forum details
     * @since Moodle 3.0
     */
    public static function get_coursedetails($userid, $courseid) {
        global $CFG, $DB, $OUTPUT, $USER, $PAGE;
        require_once(__DIR__ . '/locallib.php');
        $user = $DB->get_record('user', array('username' => $userid));
        $courses = $DB->get_records('course', array('id' => $courseid));
        if (!$user || empty($courses)) {
            return array();
        }
        $context = \context_course::instance($courseid);
        self::validate_context($context);

        $olduser = $USER;
        $GLOBALS['USER'] = $user; // Note for codechecker - GLOBALS is a php reserved variable, can't really change it.
        $result = array();
        $output = $PAGE->get_renderer('block_semester_sortierung');
        $expandedevents = block_semester_sortierung_get_courses_events($courses, $output);
        if (isset($expandedevents[$courseid]) && !empty($expandedevents[$courseid]->events)) {

            foreach ($expandedevents[$courseid]->events as $event) {
                $mod = new \stdClass;
                $mod->modname = $event->modulename;
                $mod->modinfo = $output->render_from_template('block_semester_sortierung/course-event-list-item-clean', $event);
                $result[] = $mod;
            }
        }

        $GLOBALS['USER'] = $olduser; // Note for codechecker - GLOBALS is a php reserved variable, can't really change it.

        return $result;
    }


    /**
     * Describes the get_coursedetails return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
    public static function get_coursedetails_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'modname' => new external_value(PARAM_TEXT, 'Name of module'),
                    'modinfo' => new external_value(PARAM_RAW, 'Module information')
                ), 'module'
            )
        );
    }
}