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
     * @param $remotecourses - array of the remote courses
     * @return string html to be displayed in course_overview block
     */
    public function semester_sortierung($sortedcourses, array $remotecourses=array(), $config = null) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/calendar/externallib.php');

        $html = '';

        $movecourse = optional_param('block_semester_sortierung_move_course', 0, PARAM_INT);
        $movecoursesemester = optional_param('block_semester_sortierung_move_semester', '', PARAM_ALPHANUM);
        $movecoursetarget = optional_param('block_semester_sortierung_move_target', -1, PARAM_INT);

        if (is_null($config)) {
            $config = get_config('block_semester_sortierung');
        }

        if ($semestersexpanded = get_user_preferences('semester_sortierung_semesters', '')) {
            $semestersexpanded = array_flip(explode(',', $semestersexpanded));
        } else {
            $semestersexpanded = array();
        }

        if ($coursesexpanded = get_user_preferences('semester_sortierung_courses', '')) {
            $coursesexpanded = array_flip(explode(',', $coursesexpanded));
        } else {
            $coursesexpanded = array();
        }

        if ($favorites = get_user_preferences('semester_sortierung_favorites', '')) {
            $favorites = array_flip(explode(',', $favorites));
        } else {
            $favorites = array();
        }

        // Whether the courses should be sorted.
        $sorted = (isset($config->sortcourses) && $config->sortcourses == '1');

        $showfavorites = (isset($config->enablefavorites) && $config->enablefavorites == '1');
        $personalsort = (isset($config->enablepersonalsort) && $config->enablepersonalsort == '1') && $sorted;
        // Prevent personal sort when not enabled in the setting.
        $userediting = $personalsort && $this->page->user_is_editing();

        $content = '';
        $first = true;

        $sortedcoursesexpanded = array();

        // Create an array with expanded courses to be filled up with info.
        foreach ($sortedcourses as $semester => $semesterinfo) {
            foreach ($semesterinfo['courses'] as $id => $courseinfo) {
                if (isset($coursesexpanded[strval($courseinfo->id)])) {
                    $sortedcoursesexpanded[$courseinfo->id] = $courseinfo;
                }
            }
        }

        // Fill in expanded courses with course info.
        $htmlarray = array();
        // Get all modules as objects.
        /*if ($modules = $DB->get_records('modules')) {
            foreach ($modules as $mod) {
                if (file_exists($CFG->dirroot.'/mod/'.$mod->name.'/lib.php')) {
                    include_once($CFG->dirroot.'/mod/'.$mod->name.'/lib.php');
                    $fname = $mod->name.'_print_overview';
                    if (function_exists($fname)) {
                       // $fname($sortedcoursesexpanded, $htmlarray);
                    }
                }
            }
        }*/

        $allevents = \core_calendar\local\api::get_action_events_by_courses(
            $sortedcoursesexpanded
        );


        $exportercache = new \core_calendar\external\events_related_objects_cache($allevents, $sortedcoursesexpanded);
        $exporter = new \core_calendar\external\events_grouped_by_course_exporter($allevents, ['cache' => $exportercache]);

        $exportedeventsraw = $exporter->export($this);
        $exportedevents = array();

        if (isset($exportedeventsraw->groupedbycourse)) {
            foreach ($exportedeventsraw->groupedbycourse as $courseevents) {
                $exportedevents[$courseevents->courseid] = $courseevents;
            }
        }


        foreach ($sortedcourses as $semester => $semesterinfo) {
            $isfavorites = $semester == 'fav';

            $printmovetargets = $userediting &&
                $movecoursesemester == $semester &&
                $movecoursetarget < 0 &&
                isset($semesterinfo['courses'][$movecourse]);

            if ($sorted || $isfavorites) {
                $empty = false;
                if ($isfavorites) {
                    $empty = count($semesterinfo['courses']) <= 0;
                }
                // All courses are divided by semester into openable divs
                // opens a new semester box.
                $content .= $this->start_semester($semester,
                    $semesterinfo['title'],
                    $semestersexpanded,
                    $first,
                    $empty);
            } else if ($first) {
                // When not sorted, only one div is opened.
                $content .= html_writer::start_tag('div', array( 'class' => 'semestersortierung nosemester semester'));
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
                $content .= $this->course_html($course,
                    $exportedevents,
                    isset($coursesexpanded[$courseid]),
                    isset($favorites[$courseid]),
                    $showfavorites,
                    $userediting);
                $moveindex++;
            }

            if ($printmovetargets) {
                $content .= $this->get_move_target($movecourse, $moveindex, $semester);
            }

            if ($sorted || $isfavorites) {
                // Close the semester box.
                $content .= $this->end_semester();
            }
        }

        // Closes the last semester box if the courses are not sorted.
        if (!$sorted) {
            $content .= html_writer::end_tag('div');
        }

        $html .= html_writer::nonempty_tag('div', $content, array(
            'id' => 'semesteroverviewcontainer',
            'class' => 'no_javascript'));

        // Prints remote courses.. standard behavior like in course overview block.
        if (!empty($remotecourses)) {
            $html .= $this->output->heading(get_string('remotecourses', 'mnet'));
        }
        foreach ($remotecourses as $course) {
            $html .= $this->output->box_start('coursebox');
            $attributes = array('title' => s($course->fullname));
            $html .= $this->output->heading(html_writer::link(
                new moodle_url('/auth/mnet/jump.php', array('hostid' => $course->hostid,
                    'wantsurl' => '/course/view.php?id='.$course->remoteid)),
                format_string($course->shortname),
                $attributes) . ' (' . format_string($course->hostname) . ')', 3);
            $html .= $this->output->box_end();
        }

        return $html;
    }

    private function start_semester($semestercode, $semestertitle, $expandedsemesters, $first, $empty) {
        $html = '';

        $classes = array('semester');
        if ($empty) {
            $classes[] = 'empty';
        }
        if (($first && count($expandedsemesters) == 0) || isset($expandedsemesters[$semestercode])) {
            $classes[] = 'expanded';
        }
        $classes[] = $semestercode;
        $html .= html_writer::start_tag('fieldset', array(
            'class' => implode(' ', $classes),
            'data-id' => $semestercode));
        $html .= html_writer::start_tag('legend');
        $html .= $this->get_expand_image_button_html();

        $html .= '&nbsp;' . $semestertitle;
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

    private function course_html($course, $exportedevents, $courseexpanded, $isfav, $showfavorites, $userediting) {
        // Prints course as in standard course overview block.
        $html = '';
        $classes = array('course');

        $attributes = array('title' => s($course->fullname));
        $attributes['class'] = 'courselink';
        if ($courseexpanded) {
            $classes[] = 'expanded';
        }
        if (empty($course->visible)) {
            $attributes['class'] .= ' dimmed';
            $classes[] = 'hidden';
        } else {
            $classes[] = 'nothidden';
        }
        $courseboxid = $course->id;
        $html .= html_writer::start_tag('fieldset', array(
            'class' => implode(' ', $classes),
            'data-id' => $courseboxid,
            'data-semester' => $course->semester_short,
            'data-fav' => ($isfav ? '1' : '0')));
        $html .= html_writer::start_tag('legend');

        if ($userediting) {
            $html .= $this->get_personalsort_icons($course->id, $course->semester_short);
        }

        $html .= $this->get_expand_image_button_html();

        $html .= '&nbsp;'. html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)),
            trim(format_string($course->fullname)), $attributes) . '&nbsp;';
        $html .= html_writer::end_tag('legend');
        if ($showfavorites) {
            $html .= $this->get_favorites_icon($course->id, true, $isfav == true);
            $html .= $this->get_favorites_icon($course->id, false, $isfav == false);
        } else {
            $html .= html_writer::tag('div', '');
            $html .= html_writer::tag('div', '');
        }
        $html .= html_writer::start_tag('div', array('class' => 'expandablebox'));
        /*if (isset($allevents[$course->id]) && !empty($allevents[$course->id])) {
            $html .= html_writer::start_tag('div', array('class' => 'coursebox'));

            foreach ($htmlarray[$course->id] as $modname => $modhtml) {
                $html .= $modhtml;
            }
        }*/
        $html .= $this->render_course_info($course->id, $exportedevents);
        $html .= html_writer::end_tag('div');

        $html .= html_writer::end_tag('fieldset');
        return $html;
    }

    private function get_personalsort_icons($courseid, $semester) {
        $html = html_writer::empty_tag('img', array(
            'src' => $this->output->image_url('i/dragdrop'),
            'class' => 'iconsmall move-drag-start hidden'
        ));
        $html .= html_writer::start_tag('a', array( // No-js icon.
            'href' => new moodle_url($this->page->url, array(
                'block_semester_sortierung_move_course' => $courseid,
                'block_semester_sortierung_move_semester' => $semester)),
            'class' => 'move-static'
        ));
        $html .= html_writer::empty_tag('img', array(
            'src' => $this->output->image_url('t/move'),
            'class' => 'iconsmall'
        ));
        $html .= html_writer::end_tag('a');


        return $html;
    }

    private function render_course_info($courseid, $exportedevents) {
        if (!isset($exportedevents[$courseid]) || empty($exportedevents[$courseid]->events)) {
            return '';
        }

        $output = $this->render_from_template('block_semester_sortierung/course-event-list', $exportedevents[$courseid]);
        return $output;
    }

    private function get_favorites_icon($courseid, $addorrem, $isfav) {
        $content = html_writer::start_tag('a', array(
            'href' => new moodle_url($this->page->url, array(
                'block_semester_sortierung_favorites' => $courseid,
                'status' => ($addorrem ? '0' : '1'))),
            'class' => 'togglefavorites ' . ($addorrem ? 'on' : 'off') . ($isfav ? '' : ' invisible'),
            'data-fav' => strval($addorrem)));
        $content .= html_writer::empty_tag('img', array(
            'src' => $this->output->image_url($addorrem ? 'fav_on' : 'fav_off', 'block_semester_sortierung'),
            'alt' => get_string($addorrem ? 'removefromfavorites' : 'addtofavorites', 'block_semester_sortierung'),
            'title' => get_string($addorrem ? 'removefromfavorites' : 'addtofavorites', 'block_semester_sortierung'),
            'class' => 'favoriteicon'));
        $content .= html_writer::end_tag('a');
        return $content;
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
