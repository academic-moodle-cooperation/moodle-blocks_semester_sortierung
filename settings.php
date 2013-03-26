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
 * Semester sortierung settings page
 *
 * @package blocks_semester_sortierung
 * @author Simeon Naydenov
 * @copyright 2012 Vienna University of Technology
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $configs = array();

    $configs[] = new admin_setting_configcheckbox('sortcourses',
        get_string('sortcourse', 'block_semester_sortierung'), get_string('sortcoursedesc', 'block_semester_sortierung'), '1');

    // setting for configuring which months belong to the winter semester..
    // 12 checkboxes for each month; jan, july-dec should be checked by default
    $monthsarray = array();
    $selected = array();
    for ($i = 0; $i < 12; $i++) {
        $monthsarray['mon' . (($i+1) < 10?'0':'') . strval($i+1)] = strftime('%B', ($i*3600*24*31));
        if ($i < 1 || $i > 5) {
            $selected['mon' . (($i+1) < 10?'0':'') .  strval($i+1)] = 1;
        }
    }

    $configs[] = new admin_setting_configmulticheckbox('wintermonths',
        get_string('wintermonths', 'block_semester_sortierung'), get_string('monthsdesc', 'block_semester_sortierung'),
        $selected, $monthsarray);

    foreach ($configs as $config) {
        $config->plugin = 'blocks/semester_sortierung';
        $settings->add($config);
    }

}

