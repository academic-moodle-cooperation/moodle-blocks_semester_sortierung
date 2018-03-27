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
 * Render class
 *
 * @package       block_semester_sortierung
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Simeon Naydenov (moniNaydenov@gmail.com)
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_semester_sortierung\output;

use html_writer;
use moodle_url;
use stdClass;


defined('MOODLE_INTERNAL') || die;

/**
 * semester_sortierung block rendrer
 *
 */
class renderer extends \plugin_renderer_base {


    public function render_block(stdClass $context) {
        $output = $this->get_mustache()->render('block_semester_sortierung/block', $context);
        return $output;
    }

    /**
     * Constructs a string with all courses' short description, including remote_courses. remote_courses are not implemented though
     *
     * @param sortedcourses - array of the sorted courses
     * @param $remotecourses - array of the remote courses
     * @return string html to be displayed in course_overview block
     */
    public function semester_sortierung(\block_semester_sortierung $block) {
        global $CFG, $DB, $PAGE;

        $html = '';
        $context = $block->export_for_template($this);

        $movecourse = optional_param('block_semester_sortierung_move_course', 0, PARAM_INT);
        $movecoursesemester = optional_param('block_semester_sortierung_move_semester', '', PARAM_ALPHANUM);
        $movecoursetarget = optional_param('block_semester_sortierung_move_target', -1, PARAM_INT);

        if (is_null($context->config)) {
            die; // Just to make sure it doesn't really fire!
        }
        $config = $context->config;
        $content = '';
        $first = true;
        $archivestarted = false;

        foreach ($context->courses as $semester => $semesterinfo) {
            $isfavorites = $semester == 'fav';
            $printmovetargets = $context->userediting &&
                $movecoursesemester == $semester &&
                $movecoursetarget < 0 &&
                isset($semesterinfo['courses'][$movecourse]);

            if ($context->sorted || $isfavorites) {
                $empty = false;
                if ($isfavorites) {
                    $empty = count($semesterinfo['courses']) <= 0;
                }
                // All courses are divided by semester into openable divs
                // opens a new semester box.
                $scontext = new stdClass;
                $scontext->first = $first;
                $scontext->semestertitle = $semesterinfo['title'];
                $scontext->expanded = ($first && count($context->semestersexpanded) == 0)
                                      || isset($context->semestersexpanded[$semester]);
                $scontext->empty = $empty;
                $scontext->semestercode = $semester;

                if (!$isfavorites &&
                    $context->archive &&
                    strcmp($context->archive, $semester) >= 0 &&
                    !$archivestarted) {
                    $archivestarted = true;
                    $acontext = new stdClass;
                    $acontext->semestertitle = get_string('setting:archivedesc', 'block_semester_sortierung', $context->config->archive);
                    $acontext->semestercode = 'arch';
                    $acontext->expanded = isset($context->semestersexpanded['arch']);
                    $content .= $this->get_mustache()->render('block_semester_sortierung/semester_start', $acontext);
                }

                $content .= $this->get_mustache()->render('block_semester_sortierung/semester_start', $scontext);
            } else if ($first) {
                // When not sorted, only one div is opened.
                $content .= html_writer::start_tag('div', array( 'class' => 'semester_sortierung nosemester semester'));
            }

            $first = $isfavorites;

            $moveindex = 0;
            foreach ($semesterinfo['courses'] as $id => $course) {
                $courseid = strval($course->id);

                if ($printmovetargets) {
                    if ($movecourse == $id) {
                        continue;
                    } else {
                        $content .= $this->get_move_target($movecourse, $moveindex, $semester);
                    }
                }

                $ccontext = new stdClass;
                $ccontext->course = $course;
                $ccontext->expanded = isset($context->coursesexpanded[$courseid]);
                $ccontext->favorite = intval(isset($context->favorites[$courseid]));
                $ccontext->showfavorites = $context->showfavorites;
                $ccontext->userediting = $context->userediting;
                $ccontext->course_info = $this->render_course_info($courseid, $context->exportedevents);
                $ccontext->courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
                $ccontext->staticmoveurl = new moodle_url($this->page->url, array(
                    'block_semester_sortierung_move_course' => $courseid,
                    'block_semester_sortierung_move_semester' => $semester));
                $ccontext->favoritesaddurl = new moodle_url($this->page->url, array(
                    'block_semester_sortierung_favorites' => $courseid,
                    'status' => '0'));
                $ccontext->favoritesremoveurl = new moodle_url($this->page->url, array(
                    'block_semester_sortierung_favorites' => $courseid,
                    'status' => '1'));

                $content .= $this->get_mustache()->render('block_semester_sortierung/course', $ccontext);

                $moveindex++;
            }

            if ($printmovetargets) {
                $content .= $this->get_move_target($movecourse, $moveindex, $semester);
            }

            if ($context->sorted || $isfavorites) {
                // Close the semester box.
                $content .= $this->get_mustache()->render('block_semester_sortierung/semester_end');
            }
        }
        if ($archivestarted) {
            $content .= $this->get_mustache()->render('block_semester_sortierung/semester_end');
        }

        // Closes the last semester box if the courses are not sorted.
        if (!$context->sorted) {
            $content .= html_writer::end_tag('div');
        }

        $html .= html_writer::nonempty_tag('div', $content, array(
            'id' => 'semester_sortierungcontainer',
            'class' => 'no_javascript'));

        // Prints remote courses.. standard behavior like in course overview block.
        if (!empty($context->remotecourses)) {
            $html .= $this->output->heading(get_string('remotecourses', 'mnet'));
        }
        foreach ($context->remotecourses as $course) {
            $html .= $this->output->box_start('coursebox');
            $attributes = array('title' => s($course->fullname));
            $html .= $this->output->heading(html_writer::link(
                                            new moodle_url('/auth/mnet/jump.php',
                                                         array('hostid' => $course->hostid,
                                                               'wantsurl' => '/course/view.php?id='.$course->remoteid)),
                                            format_string($course->shortname),
                                            $attributes) .
                                            ' (' .
                                            format_string($course->hostname) .
                                            ')',
                                            3
            );
            $html .= $this->output->box_end();
        }

        return $html;
    }

    public function render_course_info($courseid, $exportedevents) {
        if (!isset($exportedevents[$courseid]) || empty($exportedevents[$courseid]->events)) {
            return '';
        }

        $output = $this->render_from_template('block_semester_sortierung/course-event-list',
                                              $exportedevents[$courseid]);
        return $output;
    }


    private function get_move_target($courseid, $moveindex, $semester) {
        $html = html_writer::tag('a', '', array(
            'class' => 'blockmovetarget',
            'href' => new moodle_url($this->page->url, array(
                'block_semester_sortierung_move_course' => $courseid,
                'block_semester_sortierung_move_target' => $moveindex,
                'block_semester_sortierung_move_semester' => $semester)),
        ));
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
}
