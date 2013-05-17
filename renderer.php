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
 * semester_sortierung block rendrer
 *
 * @package    block_semester_sortierung
 */
defined('MOODLE_INTERNAL') || die;

/**
 * semester_sortierung block rendrer
 *
 */
class block_semester_sortierung_renderer extends plugin_renderer_base {

    /**
     * Constructs a string with all courses' short description, including remote_courses. remote_courses are not implemented though
     * 
     * @param sortedcourses - array of the sorted courses
     * @param $remote_courses - array of the remote courses
     * @return string html to be displayed in course_overview block
     */
    public function semester_sortierung($sortedcourses, array $remote_courses=array(), $config = null) {
        global $CFG, $USER, $DB, $OUTPUT;
        
        $html = '';
        
        if (is_null($config)) {
            $config = get_config('blocks/semester_sortierung');
        }
        
        $sortedcourses_expanded = array();
        if ($semesters_expanded = get_user_preferences('semester_sortierung_semesters', '')) {
            $semesters_expanded = array_flip(explode(',', $semesters_expanded));
        } else {
            $semesters_expanded = array();
        }
        if ($courses_expanded = get_user_preferences('semester_sortierung_courses', '')) {
            $courses_expanded = array_flip(explode(',', $courses_expanded));
        } else {
            $courses_expanded = array();
        }

        foreach ($sortedcourses as $id => $courseinfo) {
            if (isset($courses_expanded[strval($id)])) {
                $sortedcourses_expanded[$id] = $courseinfo;
            }
        }
        
        
        $htmlarray = array();
        // get all modules as objects
        if ($modules = $DB->get_records('modules')) {
            foreach ($modules as $mod) {
                if (file_exists($CFG->dirroot.'/mod/'.$mod->name.'/lib.php')) {
                    include_once($CFG->dirroot.'/mod/'.$mod->name.'/lib.php');
                    $fname = $mod->name.'_print_overview';
                    if (function_exists($fname)) {
                        $fname($sortedcourses_expanded, $htmlarray);
                    }
                }
            }
        }

        $currentsemester = null;

        //whether the courses should be sorted
        $sorted = (isset($config->sortcourses) && $config->sortcourses == '1');
        
        //initial box
        
        $content = '';
        $first = true;
        foreach ($sortedcourses as $course) { //needs work
            if ($sorted) {
                //all courses are divided by semester into openable divs
                if ($currentsemester != $course->semester) {
                    // close the last semester box
                    if (!is_null($currentsemester)) {
                        $content .= $this->end_semester();
        
                    }
                    //start a new semester box
                    $currentsemester = $course->semester;
                    
                    $content .= $this->start_semester($currentsemester, $course, $semesters_expanded, $first);
        
                    $first = false;
                }
            } else if ($first) {
                $first = false;
                $content .= html_writer::start_tag('div', array( 'class' => 'semestersortierung'));;
        
            }
            $content .= $this->course_html($course, $htmlarray, isset($courses_expanded[strval($course->id)]));
        

        }
        //closes the last semester box
        if ($sorted) {
            $content .= $this->end_semester();
        } else {
            $content .= html_writer::end_tag('div');
        }
        
        $html .= html_writer::nonempty_tag('div', $content, array('id' => 'semesteroverviewcontainer', 'class' => 'no_javascript'));
        
        

        //prints remote courses.. standard behavior like in course overview block
        if (!empty($remote_courses)) {
            $html .= $OUTPUT->heading(get_string('remotecourses', 'mnet'));
        }
        foreach ($remote_courses as $course) {
            $html .= $OUTPUT->box_start('coursebox');
            $attributes = array('title' => s($course->fullname));
            $html .= $OUTPUT->heading(html_writer::link(
                new moodle_url('/auth/mnet/jump.php', array('hostid' => $course->hostid,
                    'wantsurl' => '/course/view.php?id='.$course->remoteid)),
                format_string($course->shortname),
                $attributes) . ' (' . format_string($course->hostname) . ')', 3);
            $html .= $OUTPUT->box_end();
        }

        return $html;
    }
    
    private function start_semester($currentsemester, $course, $expanded_semesters, $first) {
        $html = '';
        $semestercode = $this->get_semester_code($currentsemester);
        $classes = array('semester');
        if (($first && count($expanded_semesters) == 0) || isset($expanded_semesters[$semestercode])) {
            $classes[] = 'expanded';
        }
        $html .= html_writer::start_tag('fieldset', array('class' => implode(' ', $classes), 'data-id' => $semestercode));
        $html .= html_writer::start_tag('legend');
        $html .= $this->get_expand_image_button_html();
        
        $html .= '&nbsp;' . $course->semester;
        $html .= html_writer::end_tag('legend');
        $html .= html_writer::start_tag('div', array('class' => 'expandablebox'));
        return $html;
    }
    
    private function end_semester() {
        $html = '';
        $html .= html_writer::end_tag('div');
        $html .= html_writer::end_tag('fieldset');
        return $html;
    }
    
    private function course_html($course, $htmlarray, $courseexpanded) {
        //prints course as in standard course overview block
        $html = '';
        $classes = array('course');
        
        if ($courseexpanded) {
            $classes[] = 'expanded';
        }
        $courseboxid = $course->id;
        $html .= html_writer::start_tag('fieldset', array('class' => implode(' ', $classes), 'data-id' => $courseboxid));
        $html .= html_writer::start_tag('legend');
        $html .= $this->get_expand_image_button_html();
        $attributes = array('title' => s($course->fullname));
        $attributes['class'] = 'courselink';
        if (empty($course->visible)) {
            $attributes['class'] .= ' dimmed';
        }
        $html .= '&nbsp;'. html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)),
            format_string($course->fullname), $attributes) . '&nbsp;';
        $html .= html_writer::end_tag('legend');
        $html .= html_writer::start_tag('div', array('class' => 'expandablebox'));
        if (isset($htmlarray[$course->id])) {
            $html .= html_writer::start_tag('div', array('class' => 'coursebox'));

            foreach ($htmlarray[$course->id] as $modname => $modhtml) {
                $html .= $modhtml;
            }
            $html .= html_writer::end_tag('div');
        }
        $html .= html_writer::end_tag('div');

        $html .= html_writer::end_tag('fieldset');
        return $html;
    }


    /**
     * gets the html for each +/- button next to the semester/course name
     *
     * @param $shown - bool whether the box should be opened
     * @return string the html of the box
     */
    public function get_expand_image_button_html() {
        $classes = array('imgbox');
        $ret = html_writer::tag('div', '', array('class' => implode(' ', $classes)));
        return $ret;
    }
    
    /**
     * convert semester string to a short code, e.g. 2012W or 2012S
     *
     * @return string
     */
    public function get_semester_code($semesterstring) {
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
}
