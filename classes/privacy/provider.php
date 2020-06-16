<?php

namespace block_semester_sortierung\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;

class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\user_preference_provider,
    \core_privacy\local\request\core_user_data_provider,
    \core_privacy\local\request\core_userlist_provider {

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
        $contextlist = new contextlist();

        $sql = '
SELECT
  c.id
FROM
  {context} c 
WHERE
  (c.instanceid = :userid AND
   c.contextlevel = :contextlevel)';
        $params = array(
            'userid' => $userid,
            'contextlevel' => CONTEXT_USER
        );
        $contextlist->add_from_sql($sql, $params);

        $sql = '
SELECT
  c.id
FROM
  {context} c
JOIN
  {block_instances} bi ON c.instanceid = bi.id
JOIN
  {context} c2 ON bi.parentcontextid = c2.id
WHERE
  (c2.instanceid = :userid AND
   c2.contextlevel = :contextleveluser AND
   c.contextlevel = :contextlevelblock AND
   bi.blockname = :blockname)';
        $params = array(
            'userid' => $userid,
            'contextleveluser' => CONTEXT_USER,
            'contextlevelblock' => CONTEXT_BLOCK,
            'blockname' => 'semester_sortierung'
        );
        $contextlist->add_from_sql($sql, $params);

        $sql = '
SELECT
  c.id
FROM
  {context} c
JOIN
  {block_instances} bi ON c.instanceid = bi.id
JOIN 
  {context} c2 ON bi.parentcontextid = c2.id
WHERE
  (c2.contextlevel = :contextlevelsystem AND
   c.contextlevel = :contextlevelblock AND
   bi.blockname = :blockname)';
        $params = array(
            'contextlevelsystem' => CONTEXT_SYSTEM,
            'contextlevelblock' => CONTEXT_BLOCK,
            'blockname' => 'semester_sortierung'
        );
        $contextlist->add_from_sql($sql, $params);

            
        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts, using the supplied exporter instance.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $parentcontext = null;
        $subcontext = null;
        $userid = null;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_BLOCK) {
                $parent = $context->get_parent_context();

                if ($parent->contextlevel == CONTEXT_SYSTEM) {
                    $parentcontext = $parent;
                    $subcontext = $context;
                } else if ($parent->contextlevel == CONTEXT_USER) {
                    if (is_null($parentcontext)) {
                        $parentcontext = $parent;
                        $subcontext = $context;
                    }
                }
            } else if ($context->contextlevel == CONTEXT_USER) {
                $userid = $context->instanceid;
            }
        }
        if (!is_null($parentcontext) && !is_null($userid)) {

            $usersorts = $DB->get_records('block_semester_sortierung_us', array('userid' => $userid));
            foreach ($usersorts as $us) {
                unset($us->id);
                unset($us->userid);
                $us->lastmodified = transform::datetime($us->lastmodified);
            }
            writer::with_context($subcontext)
                ->export_data([get_string('privacy:exportdata:usersort', 'block_semester_sortierung')], (object) ['usersorts' => $usersorts]);
        }
    }


    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $parentcontext = null;
        $subcontext = null;
        $userid = null;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_BLOCK) {
                $parent = $context->get_parent_context();

                if ($parent->contextlevel == CONTEXT_SYSTEM) {
                    $parentcontext = $parent;
                    $subcontext = $context;
                } else if ($parent->contextlevel == CONTEXT_USER) {
                    if (is_null($parentcontext)) {
                        $parentcontext = $parent;
                        $subcontext = $context;
                    }
                }
            } else if ($context->contextlevel == CONTEXT_USER) {
                $userid = $context->instanceid;
            }
        }
        if (!is_null($parentcontext) && !is_null($userid)) {
            $DB->delete_records('block_semester_sortierung_us', array('userid' => $userid));
        }

    }

    /**
     * Delete all personal data for all users in the specified context.
     *
     * @param context $context Context to delete data from.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        if ($context->contextlevel == CONTEXT_SYSTEM) {
            $DB->delete_records('block_semester_sortierung_us', array());
        }
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
            writer::export_user_preference('block_semester_sortierung', 'semester_sortierung_courses', $courses, $semestersdescription);
        }
        if ($favorites !== null) {
            $semestersdescription = get_string('privacy:exportdata:preference:semester_sortierung_favorites', 'block_semester_sortierung');
            writer::export_user_preference('block_semester_sortierung', 'semester_sortierung_favorites', $favorites, $semestersdescription);
        }
    }


    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {

        $context = $userlist->get_context();

        if ($context instanceof \context_user) {
            $params = [
                'contextid'    => $context->id
            ];

            $sql = 'SELECT c.instanceid AS userid
              FROM {context} c 
             WHERE c.id = :contextid';
            $userlist->add_from_sql('userid', $sql, $params);
        }

        if ($context instanceof \context_block || $context instanceof \context_system) {
            $sql = 'SELECT bsmus.userid 
              FROM {block_semester_sortierung_us}
              GROUP BY bsmus.userid';
            $params = [];
            $userlist->add_from_sql('userid', $sql, $params);
        }

    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist       $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        list($userinsql, $userinparams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        $sql = "userid {$userinsql}";

        if (empty($userinparams)) {
            return;
        }

        $DB->delete_records_select('block_semester_sortierung_us', $sql, $userinparams);

    }

}