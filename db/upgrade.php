<?php

/**
 *
 * @package       moodle34
 * @author        Simeon Naydenov (moniNaydenov@gmail.com)
 * @copyright     2018
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


function xmldb_block_semester_sortierung_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();
    if ($oldversion < 2017111300) {
        require_once(__DIR__ . '/../locallib.php');

        // Define table block_semester_sortierung_us to be created.
        $table = new xmldb_table('block_semester_sortierung_us');

        // Adding fields to table block_semester_sortierung_us.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null, null);
        $table->add_field('semester', XMLDB_TYPE_CHAR, '6', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseorder', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('lastmodified', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table block_semester_sortierung_us.
        $table->add_key('idindex', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_semester_sortierung_us.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }


        $userprefs = $DB->get_records('user_preferences', array('name' => 'semester_sortierung_sorting'));
        $counters = new stdClass;
        $counters->updated = 0;
        $counters->inserted = 0;
        $counters->unchanged = 0;
        $counters->userprefs = 0;
        foreach ($userprefs as $userpref) {
            $userid = $userpref->userid;
            $value = json_decode($userpref->value, true);
            foreach ($value as $semester => $courses) {
                $courses = implode(',', $courses);
                $currentprefs = block_semester_sortierung_get_usersort($userid);
                if (isset($currentprefs[$semester])) {
                    if ($courses != $currentprefs[$semester]->courseorder) {
                        $currentprefs[$semester]->courseorder = $courses;
                        $currentprefs[$semester]->lastmodified = time();
                        $DB->update_record('block_semester_sortierung_us', $currentprefs[$semester]);
                        $counters->updated++;
                    } else {
                        $counters->unchanged++;
                    }
                } else {
                    $newpref = new stdClass;
                    $newpref->semester = $semester;
                    $newpref->courseorder = $courses;
                    $newpref->lastmodified = time();
                    $newpref->userid = $userid;
                    $DB->insert_record('block_semester_sortierung_us', $newpref);
                    $counters->inserted++;
                }
            }
        }

        // Semester_sortierung savepoint reached.
        upgrade_block_savepoint(true, 2017111300, 'semester_sortierung');
    }
    return true;
}