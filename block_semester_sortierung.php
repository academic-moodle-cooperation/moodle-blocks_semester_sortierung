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
        global $USER, $CFG;
        //if content already present, spare time
        if ($this->content !== null) {
            return $this->content;
        }
        /*
        if (isset($this->config)) {
            $config = $this->config;
            echo 'AAAA';
        } else{
            // TODO: Move these config settings to proper ones using component name
            echo 'BBBB';*/
            $config = get_config('blocks/semester_sortierung');
            $this->config = $config;
        //}


        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $content = array();
        //get the information about the enrolled courses
        $courses = enrol_get_my_courses('id, shortname, modinfo, sectioncache', 'visible DESC,sortorder ASC');

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

        //moodle hack
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
        if (empty($courses) && empty($remote_courses)) {
            $content[] = get_string('nocourses', 'my');
        } else {
            ob_start();

            $this->print_overview($courses, $remote_courses);

            $content[] = ob_get_contents();
            ob_end_clean();
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
        return false;
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
        $sortedcourses = array();
        foreach ($courses as $course) {
            $semester = $this->get_semester_from_date($course->startdate);
            $course->semester = $semester[0];
            $course->sortid = $semester[1];
            if (empty($sortedcourses[$semester[1]])) {
                $sortedcourses[$semester[1]] = array();
            }
            array_push($sortedcourses[$semester[1]], $course);
        }
        ksort($sortedcourses);
        $courses = array();
        foreach ($sortedcourses as $semestercourses) {
            foreach ($semestercourses as $course) {
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
            'id'             => $this->instance->id,
            'instance'       => $this->instance->id,
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
        //$month = date("m", $startdate);
        $month = userdate($startdate, '%m');
        //$year = date("Y", $startdate);
        $year = userdate($startdate, '%Y');
        $prevyear = strval((intval($year) - 1));
        $nextyear = strval((intval($year) + 1));
        $semester = "";
        $sortid = (3000 - intval($year))*10;
        if (strpos($this->config->wintermonths, 'mon'.$month) !== false) {
            if (intval($month) <= 6) {
                $semester = get_string('wintersem', 'block_semester_sortierung') . '  ' . $prevyear. '/' . $year;
                $sortid -= 1;
            } else {
                $semester = get_string('wintersem', 'block_semester_sortierung') . '  ' . $year .'/' . $nextyear;
                $sortid -= 3;

            }
        } else {
            $semester = get_string('summersem', 'block_semester_sortierung') . '  ' . $year;
            $sortid -= 2;
        }
        return array($semester, $sortid);

    }

    /**
     * convert semester string to a short code, e.g. 2012W or 2012S
     *
     * @return string
     */
    private function get_semester_code($semesterstring) {
        $temp = explode('  ', $semesterstring);
        $code = '';
        $year = $temp[1];
        if ($temp[0] === get_string('summersem', 'block_semester_sortierung')) {
            $code = 'S';
        } else {
            $code = 'W';
        }
        if ($code === 'W') {
            $years = explode('/', $year);
            $year = $years[0];
        }
        return $year . $code;

    }

    /**
     * Prints all courses' short description, including remote_courses. remote_courses are not implemented
     *
     * @param sortedcourses - array of the sorted courses
     * @param $remote_courses - array of the remote courses
     */

    private function print_overview($sortedcourses, array $remote_courses=array()) {
        global $CFG, $USER, $DB, $OUTPUT;
        $htmlarray = array();

        $real_sortedcourses = array();
        $boxes_data = get_user_preferences('semester_sortierung_boxes', 'a:0:{}');
        $boxes_data = @unserialize($boxes_data);
        if (!is_array($boxes_data)) {
            $boxes_data = array();
        }
        
        foreach ($sortedcourses as $id => $courseinfo) {
            if (isset($boxes_data['c'.$id]) && $boxes_data['c'.$id] == '1') {
                $real_sortedcourses[$id] = $courseinfo;
            }
        }

        // get all modules as objects
        if ($modules = $DB->get_records('modules')) {
            foreach ($modules as $mod) {
                if (file_exists($CFG->dirroot.'/mod/'.$mod->name.'/lib.php')) {
                    include_once($CFG->dirroot.'/mod/'.$mod->name.'/lib.php');
                    $fname = $mod->name.'_print_overview';
                    if (function_exists($fname)) {
                        $fname($real_sortedcourses, $htmlarray);
                    }
                }
            }
        }

        $currentsemester = null;

        //whether the courses should be sorted
        $sorted = (isset($this->config->sortcourses) && $this->config->sortcourses == '1');
        //initial box
        echo html_writer::start_tag('div', array('id' => 'semesteroverviewcontainer'));
        $first = true;
        foreach ($sortedcourses as $course) { //needs work
            if ($sorted) {
                //all courses are divided by semester into openable divs
                if ($currentsemester != $course->semester) {
                    // close the last semester box
                    if (!is_null($currentsemester)) {
                        $first = false;
                        echo html_writer::end_tag('div');
                        echo html_writer::end_tag('fieldset');
                    }
                    //start a new semester box
                    $currentsemester = $course->semester;
                    echo html_writer::start_tag('fieldset');
                    $semestercode = self::get_semester_code($currentsemester);
                    $boxid = 'sbox'. $semestercode;
                    echo html_writer::start_tag('legend', array('id'=>$semestercode));
                    //check whether cookies are set and the box should be opened
                    if ($first && !isset($boxes_data[$semestercode])) {
                        $boxes_data[$semestercode] = '1';
                    }
                    echo $this->get_expand_image_button_html($semestercode, (isset($boxes_data[$semestercode]) &&
                        $boxes_data[$semestercode] == '1'));
                    echo html_writer::start_tag('a', array('href' => 'javascript: ;',
                        'onclick' => 'javascript: togglesemesterbox("'.$semestercode .'")'));

                    echo '&nbsp;' . $course->semester;
                    echo html_writer::end_tag('a');
                    echo html_writer::end_tag('legend');

                    $style = 'overflow: ' . (isset($boxes_data[$semestercode]) && $boxes_data[$semestercode] == '1' ?
                        'visible;' : 'hidden; height: 1px;') . ' margin-left: 5px;';

                    echo html_writer::start_tag('div', array( 'id' => $boxid, 'style' => $style, 'class' => 'semestersortierung'));

                }
            } else if($first) {
                $first = false;
                print html_writer::start_tag('div', array( 'class' => 'semestersortierung'));;
            }

            //prints courses as in standard course overview block
            $courseboxid = $course->id;
            $boxid = 'sbox'. $courseboxid;

            $attributes = array('title' => s($course->fullname));
            echo html_writer::start_tag('fieldset', array('style' => ' border: 1px solid #DDD; padding-left: 5px;'));
            echo html_writer::start_tag('legend', array('id'=>'c' . $course->id));
            echo html_writer::start_tag('h3', array('class' => 'main'));
            if ($first && !isset($boxes_data['c'.$courseboxid])) {
                $boxes_data['c'.$courseboxid] = '0';
            }

            echo $this->get_expand_image_button_html($courseboxid,
                (isset($boxes_data['c'.$courseboxid]) && $boxes_data['c'.$courseboxid] == '1'), false);
            if (empty($course->visible)) {
                $attributes['class'] = 'dimmed';
            }
            echo '&nbsp;'. html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)),
                format_string($course->fullname), $attributes) . '&nbsp;';
            echo html_writer::end_tag('h3');
            echo html_writer::end_tag('legend');
            $style = 'overflow: ' . (isset($boxes_data['c'.$courseboxid]) &&
                $boxes_data['c'.$courseboxid] == '1' ? 'visible;' : 'hidden; height: 1px;') . ' padding-top: -10px;';
            echo html_writer::start_tag('div', array( 'id' => $boxid, 'style' => $style, 'class' => 'semestersortierung'));

            if (array_key_exists($course->id, $htmlarray)) {
                echo $OUTPUT->box_start('coursebox');

                foreach ($htmlarray[$course->id] as $modname => $html) {
                    echo $html;
                }
                echo $OUTPUT->box_end();
            } else if (isset($boxes_data['c'.$courseboxid]) && $boxes_data['c'.$courseboxid] == '1') {
                echo '<div></div>';
            }

            echo html_writer::end_tag('fieldset');
        }
        //closes the last semester box
        //if ($sorted) {
            echo html_writer::end_tag('div');
        //}
        echo html_writer::end_tag('div');
        
        print '<noscript>
        <style type="text/css">
            div.semestersortierung {
                height: auto !important;
                overflow: visible !important;
            }
            
            #semesteroverviewcontainer .expand_button {
                display: none !important;
            }
        </style>
        </noscript>';

        //prints remote courses.. standard behavior like in course overview block
        if (!empty($remote_courses)) {
            echo $OUTPUT->heading(get_string('remotecourses', 'mnet'));
        }
        foreach ($remote_courses as $course) {
            echo $OUTPUT->box_start('coursebox');
            $attributes = array('title' => s($course->fullname));
            echo $OUTPUT->heading(html_writer::link(
                new moodle_url('/auth/mnet/jump.php', array('hostid' => $course->hostid,
                    'wantsurl' => '/course/view.php?id='.$course->remoteid)),
                format_string($course->shortname),
                $attributes) . ' (' . format_string($course->hostname) . ')', 3);
            echo $OUTPUT->box_end();
        }
    }
    /**
     * gets the html for each +/- button next to the semester/course name
     *
     * @param $id id of the div
     * @param $shown - bool whether the box should be opened
     * @return string the html of the box
     */
    public function get_expand_image_button_html($id, $shown, $is_sem = true) {
        global $OUTPUT;
        $ret = html_writer::start_tag('a',
            array('href' => 'javascript: ;', 'onclick' => ($is_sem ? 'javascript: togglesemesterbox("'.
            $id .'")' : ''), 'class' => 'expand_button', 'id' => 'expand'.$id));
        $ret .=  html_writer::start_tag('div', array(
                    'class' => ($shown ? 'minus':'plus'),
                    'id' => 'imgbox' . $id,
                    ));
        $ret .= html_writer::end_tag('div');
        $ret .= html_writer::end_tag('a');
        return $ret;
    }
}
