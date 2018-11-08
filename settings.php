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

    $configs[] = new admin_setting_configcheckbox('block_semester_sortierung/sortcourses',
        get_string('sortcourse', 'block_semester_sortierung'),
        get_string('sortcoursedesc', 'block_semester_sortierung'), '1');

    // Setting for configuring which months belong to the winter semester..
    // 12 checkboxes for each month; jan, july-dec should be checked by default.
    $monthsarray = array();
    $selected = array();
    for ($i = 1; $i <= 12; $i++) {
        $monthsarray['mon' . ($i < 10 ? '0' : '') . strval($i)] = userdate(mktime(1, 0, 0, $i, 1, 2016),
                                                                          '%B');
        if ($i < 2 || $i > 6) {
            $selected['mon' . ($i < 10 ? '0' : '') .  strval($i)] = 1;
        }
    }

    $configs[] = new admin_setting_configmulticheckbox('block_semester_sortierung/wintermonths',
        get_string('wintermonths', 'block_semester_sortierung'),
        get_string('monthsdesc', 'block_semester_sortierung'),
        $selected,
        $monthsarray);

    $configs[] = new admin_setting_configcheckbox('block_semester_sortierung/enablefavorites',
        get_string('enablefavorites', 'block_semester_sortierung'),
        get_string('enablefavoritesdesc', 'block_semester_sortierung'),
        '1');

    $configs[] = new admin_setting_configcheckbox('block_semester_sortierung/enablepersonalsort',
        get_string('enablepersonalsort', 'block_semester_sortierung'),
        get_string('enablepersonalsortdesc', 'block_semester_sortierung'),
        '1');

    $values = array();
    for ($i = 0; $i < 16; $i++) {
        $values[$i] = strval($i);
    }
    $values[0] = get_string('no');
    $configs[] = new admin_setting_configselect('block_semester_sortierung/archive',
        get_string('setting:archive', 'block_semester_sortierung'),
        get_string('setting:archivedesc', 'block_semester_sortierung', '...'),
        0,
        $values
    );
    $values[0] = get_string('showall', 'moodle', '');
    $configs[] = new admin_setting_configselect('block_semester_sortierung/autoclose',
        get_string('setting:autoclose', 'block_semester_sortierung'),
        get_string('setting:autoclosedesc', 'block_semester_sortierung'),
        0,
        $values
    );
    $values = array();
    for ($i = 0; $i <= 48; $i++) {
        $values[$i] = strval($i);
    }
    $values[0] = get_string('showall', 'moodle', '');

    $configs[] = new admin_setting_configselect('block_semester_sortierung/skipevents',
        get_string('setting:skipevents', 'block_semester_sortierung'),
        get_string('setting:skipeventsdesc', 'block_semester_sortierung'),
        0,
        $values
    );


    $configs[] = new admin_setting_configcheckbox('block_semester_sortierung/showunreadforumposts',
        get_string('setting:showunreadforumposts', 'block_semester_sortierung'),
        get_string('setting:showunreadforumpostsdesc', 'block_semester_sortierung'),
        '1');

    foreach ($configs as $config) {
        $config->plugin = 'block_semester_sortierung';
        $settings->add($config);
    }

}
