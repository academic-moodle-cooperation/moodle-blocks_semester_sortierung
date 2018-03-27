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
 * Migration script from deprecated block
 *
 * @package   block_semester_sortierung
 * @author    Simeon Naydenov (moniNaydenov@gmail.com)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../locallib.php');
require_once($CFG->libdir . '/formslib.php');

require_login();

if (!is_siteadmin()) {
    redirect(new moodle_url('/my'), get_string('adminonly', 'badges'));
}

class migrateform extends moodleform {
    protected function definition() {
        $mform = &$this->_form;
        $mform->addElement('checkbox', 'defaultdashboard', get_string('migrate:defaultdashboard', 'block_semester_sortierung'));
        $mform->addHelpButton('defaultdashboard', 'migrate:defaultdashboard', 'block_semester_sortierung');
        $mform->addElement('checkbox', 'alldashboards', get_string('migrate:alldashboards', 'block_semester_sortierung'));
        $mform->addHelpButton('alldashboards', 'migrate:alldashboards', 'block_semester_sortierung');
        $mform->addElement('checkbox', 'usersettings', get_string('migrate:usersettings', 'block_semester_sortierung') );
        $mform->addHelpButton('usersettings', 'migrate:usersettings', 'block_semester_sortierung');
        $mform->addElement('checkbox', 'adminsettings', get_string('migrate:adminsettings', 'block_semester_sortierung'));
        $mform->addHelpButton('adminsettings', 'migrate:adminsettings', 'block_semester_sortierung');

        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'migrateone', get_string('migrateone', 'block_semester_sortierung'));
        $buttonarray[] = $mform->createElement('submit', 'migrateall', get_string('migrateall', 'block_semester_sortierung'));
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

    }
}

$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_url(new moodle_url('/blocks/semester_sortierung/db/migrate.php'));

$PAGE->set_pagelayout('admin');
$PAGE->set_heading(get_string('migrate_title', 'block_semester_sortierung'));
$PAGE->set_title(get_string('migrate_title', 'block_semester_sortierung'));

$migrateform = new migrateform();

$counts = false;
if ($migrateform->is_submitted() && $data = $migrateform->get_data()) {
    $migratemessages = '';
    $migrateall = isset($data->migrateall);

    if (isset($data->defaultdashboard)) {
        block_semester_sortierung_migrate_default_dashboard();
        $migratemessages .= get_string('migrate:defaultdashboard:success',  'block_semester_sortierung') . '<br />';
    }
    if (isset($data->alldashboards)) {
        if ($migrateall) {
            block_semester_sortierung_migrate_all_dashboards();
            $migratemessages .= get_string('migrate:alldashboards:success',  'block_semester_sortierung') . '<br />';
        } else {
            block_semester_sortierung_migrate_currentuser_dashboard();
            $migratemessages .= get_string('migrate:alldashboards_currentuser:success',  'block_semester_sortierung') . '<br />';
        }
    }
    if (isset($data->usersettings)) {
        $counts = block_semester_sortierung_migrate_user_preferences($migrateall);
        $migratemessages .= get_string('migrate:usersettings:success',  'block_semester_sortierung', $counts) . '<br />';
    }
    if (isset($data->adminsettings)) {
         block_semester_sortierung_migrate_admin_settings();
         $migratemessages .= get_string('migrate:adminsettings:success',  'block_semester_sortierung') . '<br />';
    }

    $migratemessages = get_string('migrate:results', 'block_semester_sortierung') . $migratemessages;

    redirect($PAGE->url, $migratemessages);
    die;
}

echo $OUTPUT->header();

echo $migrateform->render();
echo $OUTPUT->footer();