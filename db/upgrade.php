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
 * Semester sortierung upgrade script
 *
 * @package blocks_semester_sortierung
 * @author Simeon Naydenov
 * @copyright 2012 Vienna University of Technology
 */

defined('MOODLE_INTERNAL') || die();


function xmldb_block_semester_sortierung_upgrade($oldversion, $block) {
    global $DB;

    if ($oldversion < 2013010904) {
        $settings = $DB->get_records_list('config', 'name', array('semester_sortierung_wintermonths',
            'semester_sortierung_sortcourses'));

        $dataobject = new stdClass;
        $dataobject->plugin = 'blocks/semester_sortierung';
        foreach ($settings as $id => $settingobj) {
            $dataobject->name = substr($settingobj->name, 20);
            $dataobject->value = $settingobj->value;
            if (!$DB->record_exists('config_plugins', array('plugin'=>'blocks/semester_sortierung', 'name' => $dataobject->name))) {
                $DB->insert_record('config_plugins', $dataobject);
                $DB->delete_records('config', array('name' => $settingobj->name));
            }
        }
        upgrade_block_savepoint(true, 2013010904, 'semester_sortierung');
    }


    return true;
}
