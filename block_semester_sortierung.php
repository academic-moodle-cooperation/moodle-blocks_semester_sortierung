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
 * Course overview block
 *
 * Currently, just a copy-and-paste from the old My Moodle.
 *
 * @package   blocks
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/lib/weblib.php');
require_once($CFG->dirroot . '/lib/formslib.php');

class block_semester_sortierung extends block_base {
    /**
     * block initializations
     */
    public function init() {
        $this->title   = get_string('pluginname', 'block_semester_sortierung');
    }

    /**
     * block contents
     *
     * @return object
     */
    public function get_content() {
        global $USER, $CFG;
        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $content = array();

        // limits the number of courses showing up
        $courses_limit = 21;
        // FIXME: this should be a block setting, rather than a global setting
        if (isset($CFG->mycoursesperpage)) {
            $courses_limit = $CFG->mycoursesperpage;
        }

        $morecourses = false;
        if ($courses_limit > 0) {
            $courses_limit = $courses_limit + 1;
        }

        $courses = enrol_get_my_courses('id, shortname, modinfo', 'visible DESC,sortorder ASC', $courses_limit);
        
        $site = get_site();
        $course = $site; //just in case we need the old global $course hack

        if (is_enabled_auth('mnet')) {
            $remote_courses = get_my_remotecourses();
        }
        if (empty($remote_courses)) {
            $remote_courses = array();
        }

        if (($courses_limit > 0) && (count($courses)+count($remote_courses) >= $courses_limit)) {
            // get rid of any remote courses that are above the limit
            $remote_courses = array_slice($remote_courses, 0, $courses_limit - count($courses), true);
            if (count($courses) >= $courses_limit) {
                //remove the 'marker' course that we retrieve just to see if we have more than $courses_limit
                array_pop($courses);
            }
            $morecourses = true;
        }


        if (array_key_exists($site->id,$courses)) {
            unset($courses[$site->id]);
        }

        foreach ($courses as $c) {
            if (isset($USER->lastcourseaccess[$c->id])) {
                $courses[$c->id]->lastaccess = $USER->lastcourseaccess[$c->id];
            } else {
                $courses[$c->id]->lastaccess = 0;
            }
        }
        $courses = $this->fill_course_semester($courses);
        if (empty($courses) && empty($remote_courses)) {
            $content[] = get_string('nocourses','my');
        } else {
            ob_start();

            $this->print_overview($courses, $remote_courses);

            $content[] = ob_get_contents();
            ob_end_clean();
        }

        // if more than 20 courses
        if ($morecourses) {
            $content[] = '<br />...';
        }

        $this->content->text = implode($content);

        return $this->content;
    }

    /**
     * allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return false;
    }

    /**
     * locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my-index'=>true);
    }
    
    private function fill_course_semester($courses) {
        $sortedcourses = array();
        foreach($courses as $course) {
            $semester = $this->get_semester_from_date($course->startdate);
            
            $course->semester = $semester[0];
            $course->sortid = $semester[1];
            if(empty($sortedcourses[$semester[1]]))
                $sortedcourses[$semester[1]] = array();
            array_push($sortedcourses[$semester[1]], $course);
        }
        ksort($sortedcourses);
        $courses = array();
        foreach($sortedcourses as $semestercourses) {
            foreach($semestercourses as $course)
                $courses[$course->id] = $course;
        }
        return $courses;
    }
    
    private function get_semester_from_date($startdate) {
        $month = intval(date("n", $startdate));
        $year = date("Y", $startdate);
        $nextyear = strval((intval($year) + 1));
        $semester = "";
        $sortid = (3000 - intval($year))*10;
        if($month >= 2 && $month <= 6) {
            $semester = "Sommersemester $year";
            $sortid -= 1;
        }
        else {
            $semester = "Wintersemester $year/$nextyear";
            $sortid -= 2;
        }
        return array($semester, $sortid);
        
    }
    
    private function print_overview($sortedcourses, array $remote_courses=array()) {
        global $CFG, $USER, $DB, $OUTPUT;
        $htmlarray = array();
        
       // echo str_replace( array("\n", " "), array("<br/>", "&nbsp;"), var_export($sortedcourses, true));
        
        
        if ($modules = $DB->get_records('modules')) {
            foreach ($modules as $mod) {
                if (file_exists($CFG->dirroot.'/mod/'.$mod->name.'/lib.php')) {
                    include_once($CFG->dirroot.'/mod/'.$mod->name.'/lib.php');
                    $fname = $mod->name.'_print_overview';
                    if (function_exists($fname)) {
                        $fname($sortedcourses,$htmlarray);
                    }
                }
            }
        }
        $currentsemester = null;
        foreach ($sortedcourses as $course) { //needs work
            if($currentsemester != $course->semester) {                
                if(!is_null($currentsemester))
                    echo html_writer::end_tag('fieldset');
                $currentsemester = $course->semester;
                echo html_writer::start_tag('fieldset', array( 'class' => 'mform clearfix' ));
                echo html_writer::start_tag('legend', array( 'class' => 'ftoggler' ));
                echo $course->semester;
                echo html_writer::end_tag('legend');
                
            }
                
            echo $OUTPUT->box_start('coursebox');
            $attributes = array('title' => s($course->fullname));
            if (empty($course->visible)) {
                $attributes['class'] = 'dimmed';
            }
            echo $OUTPUT->heading(html_writer::link(
                new moodle_url('/course/view.php', array('id' => $course->id)), format_string($course->fullname), $attributes), 3);
            if (array_key_exists($course->id,$htmlarray)) {
                foreach ($htmlarray[$course->id] as $modname => $html) {
                    echo $html;
                }
            }
            echo $OUTPUT->box_end();
            
        }
        if(!is_null($currentsemester))
            echo html_writer::end_tag('fieldset');
        
        if (!empty($remote_courses)) {
            echo $OUTPUT->heading(get_string('remotecourses', 'mnet'));
        }
        foreach ($remote_courses as $course) {
            echo $OUTPUT->box_start('coursebox');
            $attributes = array('title' => s($course->fullname));
            echo $OUTPUT->heading(html_writer::link(
                new moodle_url('/auth/mnet/jump.php', array('hostid' => $course->hostid, 'wantsurl' => '/course/view.php?id='.$course->remoteid)),
                format_string($course->shortname),
                $attributes) . ' (' . format_string($course->hostname) . ')', 3);
            echo $OUTPUT->box_end();
        }
    }
}
?>
