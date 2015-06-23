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
 * Semester overview block.. partial copy from the course overview block. Displays a list of all the courses,
 * divided by semester. Uses ajax to update data.
 *
 * @package       block_semester_sortierung
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Simeon Naydenov (moniNaydenov@gmail.com)
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

class block_semester_sortierung extends block_base {

    /**
     * block initializations
     */
    public function init() {
        // Set the title of the block.
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
        global $USER, $CFG, $PAGE;
        // If content already present, spare time.
        if ($this->content !== null) {
            return $this->content;
        }
        $config = get_config('block_semester_sortierung');
        $this->config = $config;

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $content = array();
        // Get the information about the enrolled courses.
        $courses = enrol_get_my_courses('id, fullname, shortname', 'visible DESC, fullname ASC');
        $cid = optional_param('block_semester_sortierung_favorites', null, PARAM_ALPHANUM);
        $status = optional_param('status', null, PARAM_ALPHANUM);
        if (!empty($cid)) {
            block_semester_sortierung_toggle_fav($cid, $status);
        }

        block_semester_sortierung_update_personal_sort($this->config);
        // Some moodle hacks..
        $site = get_site();
        $course = $site; // Just in case we need the old global $course hack.

        // Get remote courses.. not really needed but part of the Course overview block, so keep it.
        if (is_enabled_auth('mnet')) {
            $remotecourses = get_my_remotecourses();
        }
        if (empty($remotecourses)) {
            $remotecourses = array();
        }
        $renderer = $this->page->get_renderer('block_semester_sortierung');

        // Moodle hack - removes the site(main course with id usually 0 or 1) from the list of courses
        // There is no information in that "course".
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
        // Add the semester to each course.
        $courses = block_semester_sortierung_fill_course_semester($courses, $config);
        $courses = block_semester_sortierung_sort_user_personal_sort($courses, $config);

        // More remote courses stuff, directly copied from Course overview block
        // Output buffering is used here.
        if (empty($courses) && empty($remotecourses)) {
            $content[] = get_string('nocourses', 'my');
        } else {

            $content[] = $renderer->semester_sortierung($courses, $remotecourses, $config);
        }

        $this->content->text = implode($content); // Compile the text from the array.

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
        return array('my-index' => true, 'my' => true);
    }

    /**
     * prevent user from editing
     *
     * @return boolean
     */
    public function user_can_edit() {
        // Return false; not needed in 2.5 since block protection is working.
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
        // You don't need to override this if you're satisfied with the above.
        if (!$this->instance_allow_multiple() && !$this->instance_allow_config()) {
            return false;
        }
        return true;
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


}
