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
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new \core_privacy\local\request\contextlist();

        $sql = "SELECT c.id
                 FROM {context} c
           INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
           INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
           INNER JOIN {forum} f ON f.id = cm.instance
            LEFT JOIN {forum_discussions} d ON d.forum = f.id
                WHERE (
                d.userid        = :discussionuserid
                )
        ";

        $params = [
            'modname'           => 'forum',
            'contextlevel'      => CONTEXT_MODULE,
            'discussionuserid'  => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);
    }
}