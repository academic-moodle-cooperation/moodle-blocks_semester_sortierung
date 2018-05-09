<?php
/**
 * Created by PhpStorm.
 * User: moni
 * Date: 02.05.18
 * Time: 12:16
 */

namespace block_semester_sortierung\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;

class provider implements \core_privacy\local\metadata\provider {

    public static function get_metadata(collection $collection) : collection {

        // Here you will add more items into the collection.
        $collection->add_database_table(
            'block_semester_sortierung_us',
            [
                'userid' => 'privacy:metadata:usersorting:userid',
                'semester' => 'privacy:metadata:usersorting:semester',
                'courseorder' => 'privacy:metadata:usersorting:courseorder',
                'lastmodified' => 'privacy:metadata:usersorting:lastmodified',

            ],
            'privacy:metadata:usersorting'
        );

        $collection->add_user_preference('semester_sortierung_favorites',
            'privacy:metadata:preference:semester_sortierung_favorites');

        $collection->add_user_preference('semester_sortierung_courses',
            'privacy:metadata:preference:semester_sortierung_courses');

        $collection->add_user_preference('semester_sortierung_semesters',
            'privacy:metadata:preference:semester_sortierung_semesters');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int           $userid       The user to search.
     * @return  contextlist   $contextlist  The list of contexts used in this plugin.
     */
    //TODO
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist();

        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts, using the supplied exporter instance.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts to export information for.
     */
    //TODO;
    public static function export_user_data(approved_contextlist $contextlist) {

    }


    //TODO;
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

    }

    /**
     * Delete all personal data for all users in the specified context.
     *
     * @param context $context Context to delete data from.
     */
    //TODO;
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

    }

    /**
     * Export all user preferences for the plugin.
     *
     * @param   int         $userid The userid of the user whose data is to be exported.
     */
    public static function export_user_preferences(int $userid) {
        $semesters = get_user_preferences('semester_sortierung_semesters', null, $userid);
        $courses = get_user_preferences('semester_sortierung_courses', null, $userid);;
        $favorites = get_user_preferences('semester_sortierung_favorites', null, $userid);
        if ($semesters !== null) {
            $semestersdescription = get_string('privacy:exportdata:preference:semester_sortierung_semesters', 'block_semester_sortierung');
            writer::export_user_preference('block_semester_sortierung', 'semester_sortierung_semesters', $semesters, $semestersdescription);
        }
        if ($courses !== null) {
            $semestersdescription = get_string('privacy:exportdata:preference:semester_sortierung_courses', 'block_semester_sortierung');
            writer::export_user_preference('block_semester_sortierung', 'semester_sortierung_courses', $semesters, $semestersdescription);
        }
        if ($favorites !== null) {
            $semestersdescription = get_string('privacy:exportdata:preference:semester_sortierung_favorites', 'block_semester_sortierung');
            writer::export_user_preference('block_semester_sortierung', 'semester_sortierung_favorites', $semesters, $semestersdescription);
        }
    }

}