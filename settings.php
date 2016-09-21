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
 * Settings page
 *
 * @package       block_semester_sortierung
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Simeon Naydenov (moniNaydenov@gmail.com)
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $configs = array();

    $configs[] = new admin_setting_configcheckbox('sortcourses',
        get_string('sortcourse', 'block_semester_sortierung'),
        get_string('sortcoursedesc', 'block_semester_sortierung'), '1');

    // Setting for configuring which months belong to the winter semester..
    // 12 checkboxes for each month; jan, july-dec should be checked by default.
    $monthsarray = array();
    $selected = array();
    for ($i = 1; $i <= 12; $i++) {
        $monthsarray['mon' . ($i < 10 ? '0' : '') . strval($i)] = userdate(mktime(1, 0, 0, $i, 1, 2016), '%B');//strftime('%B', ($i * 3600 * 24 * 31));
        if ($i < 2 || $i > 6) {
            $selected['mon' . ($i < 10 ? '0' : '') .  strval($i)] = 1;
        }
    }

    $configs[] = new admin_setting_configmulticheckbox('wintermonths',
        get_string('wintermonths', 'block_semester_sortierung'),
        get_string('monthsdesc', 'block_semester_sortierung'),
        $selected,
        $monthsarray);

    $configs[] = new admin_setting_configcheckbox('enablefavorites',
        get_string('enablefavorites', 'block_semester_sortierung'),
        get_string('enablefavoritesdesc', 'block_semester_sortierung'),
        '1');

    $configs[] = new admin_setting_configcheckbox('enablepersonalsort',
        get_string('enablepersonalsort', 'block_semester_sortierung'),
        get_string('enablepersonalsortdesc', 'block_semester_sortierung'),
        '1');

    foreach ($configs as $config) {
        $config->plugin = 'block_semester_sortierung';
        $settings->add($config);
    }

}

