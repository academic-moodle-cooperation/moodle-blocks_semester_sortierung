<?php
/**
 * Created by PhpStorm.
 * User: moni
 * Date: 02.05.18
 * Time: 12:16
 */

namespace block_semester_sortierung\privacy;

use core_privacy\local\metadata\collection;


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
}