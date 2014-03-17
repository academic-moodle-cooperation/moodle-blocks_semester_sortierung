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
 * Semester overview block.. partial copy from the course overview block. Displays a list of all the courses,
 * divided by semester. Uses ajax to update data.
 *
 * @package blocks_semester_sortierung
 * @author Simeon Naydenov
 * @copyright 2012 Vienna University of Technology
 */

defined('MOODLE_INTERNAL') || die;

class block_semester_sortierung extends block_base {

    /**
     * block initializations
     */
    public function init() {
        //set the title of the block
        $this->title   = get_string('pluginname', 'block_semester_sortierung');
    }

    /**
     * prevent addition of more than one block instance
     *
     * @return boolean
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * prevent user from hiding the block
     *
     * @return boolean
     */
    public function instance_can_be_hidden() {
        return false;
    }

    /**
     * block contents
     *
     * @return object
     */
    public function get_content() {
        require_once(__DIR__ . '/locallib.php');
        global $USER, $CFG;
        //code copied from course_overview block, that's why $USER is used, although discouraged
        //if content already present, spare time
        if ($this->content !== null) {
            return $this->content;
        }

        
        $config = get_config('blocks/semester_sortierung');
        $this->config = $config;

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $content = array();
        //get the information about the enrolled courses
        $courses = enrol_get_my_courses('id, fullname, shortname', 'visible DESC, fullname ASC');
        
        $cid = optional_param('block_semester_sortierung_favorites', null, PARAM_ALPHANUM);
        $status = optional_param('status', null, PARAM_ALPHANUM);
        
        if (!empty($cid)) {
            block_semester_sortierung_toggle_fav($cid, $status);
        }
        
        
        //some moodle hacks..
        $site = get_site();
        $course = $site; //just in case we need the old global $course hack

        //get remote courses.. not really needed but part of the Course overview block, so keep it
        if (is_enabled_auth('mnet')) {
            $remote_courses = get_my_remotecourses();
        }
        if (empty($remote_courses)) {
            $remote_courses = array();
        }
        
        $renderer = $this->page->get_renderer('block_semester_sortierung');

        //moodle hack - removes the site(main course with id usually 0 or 1) from the list of courses - there is no information in
        //that "course"
        if (array_key_exists($site->id, $courses)) {
            unset($courses[$site->id]);
        }

        foreach ($courses as $c) {
            if (isset($USER->lastcourseaccess[$c->id])) {
                $courses[$c->id]->lastaccess = $USER->lastcourseaccess[$c->id];
            } else {
                $courses[$c->id]->lastaccess = 0;
            }
        }
        //add the semester to each course
        $courses = $this->fill_course_semester($courses);

        //more remote courses stuff, directly copied from Course overview block
        //output buffering is used here
        if (empty($courses) && empty($remote_courses)) {
            $content[] = get_string('nocourses', 'my');
        } else {

            $content[] = $renderer->semester_sortierung($courses, $remote_courses, $config);
        }

        //"compile" the text from the array
        $this->content->text = implode($content);

        return $this->content;
    }

    /**
     * enable the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my-index'=>true);
    }

    /**
     * prevent user from editing
     *
     * @return boolean
     */
    public function user_can_edit() {
        //return false; not needed in 2.5 since block protection is working
        return true;
    }

    /**
     * Sets whether the block can be configured
     *
     * @return boolean false
     */
    public function instance_allow_config() {
        return false;
    }

    /**
     * Prints the block configuration. since the block is not configurable, nothing gets printed
     *
     * @return false
     */
    public function instance_config_print() {
        // Default behavior: print the config_instance.html file
        // You don't need to override this if you're satisfied with the above
        if (!$this->instance_allow_multiple() && !$this->instance_allow_config()) {
            return false;
        }
    }

    /**
     * add the "semester" variable to each semester. The semester is calculated according to the settings and the course
     * start date. Thus, each course MUST have a course date
     *
     */
    private function fill_course_semester($courses) {
        global $CFG;
        require_once(__DIR__ . '/locallib.php');
        $sortedcourses = array();
        
        
        if ($favorites = get_user_preferences('semester_sortierung_favorites', '')) {
            $favorites = array_flip(explode(',', $favorites));
        } else {
            $favorites = array();
        }
        
        foreach ($courses as $course) {
            $semester = $this->get_semester_from_date($course->startdate);
            $course->semester = $semester[0];
            $course->sortid = $semester[1];
            if (empty($sortedcourses[$semester[1]])) {
                $sortedcourses[$semester[1]] = array('hidden' => array(), 'visible'=>array());
            }
            if ($course->visible) {
                $sortedcourses[$semester[1]]['visible'][] = $course;
            } else {
                $sortedcourses[$semester[1]]['hidden'][] = $course;
            }
            //array_push($sortedcourses[$semester[1]], $course);
        }
        ksort($sortedcourses);
        $courses = array();
        foreach ($sortedcourses as $semestercourses) {
            usort($semestercourses['visible'], 'block_sememster_sortierung_usort');
            usort($semestercourses['hidden'], 'block_sememster_sortierung_usort');
            foreach ($semestercourses['visible'] as $course) {
                $courses[$course->id] = $course;
            }
            foreach ($semestercourses['hidden'] as $course) {
                $courses[$course->id] = $course;
            }
        }
        return $courses;
    }

    /**
     * loads the required javascript to run semsort
     *
     */
    public function get_required_javascript() {
        $arguments = array(
            'id'             => $this->instance->id
        );
        $this->page->requires->yui_module(array('core_dock', 'moodle-block_semester_sortierung-semester_sortierung'),
            'M.block_semester_sortierung.init_add_semsort', array($arguments));
    }


    /**
     * convert a date to a valid semester
     *
     * @return string
     */
    private function get_semester_from_date($startdate) {
        global $CFG;
        $month = userdate($startdate, '%m');
        $year = intval(userdate($startdate, '%Y'));
        $prevyear = strval(($year - 1));
        $semester = "";
        $sortid = 3000;
        if (strpos($this->config->wintermonths, 'mon'.$month) !== false) {
            if (intval($month) <= 6) {
                $year -= 1;
            }
            $sortid -= 3;
            $semester = get_string('wintersem', 'block_semester_sortierung') . '  ' . strval($year) .'/' . strval($year + 1);
        } else {
            $semester = get_string('summersem', 'block_semester_sortierung') . '  ' . $year;
            $sortid -= 2;
        }
        $sortid -= $year * 10;
        return array($semester, $sortid);

    }


}
