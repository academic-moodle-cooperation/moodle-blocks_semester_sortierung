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
 * local functions
 *
 * @package blocks_semester_sortierung
 * @author Simeon Naydenov
 * @copyright 2012 Vienna University of Technology
 */

defined('MOODLE_INTERNAL') || die;

function block_sememster_sortierung_usort($a, $b) {
    return strcasecmp(trim($a->fullname), trim($b->fullname));
}

function block_semester_sortierung_toggle_fav($courseid, $status) {
    
    if ($favorites = get_user_preferences('semester_sortierung_favorites', '')) {
        $favorites = explode(',', $favorites);
        $favorites = array_flip($favorites);
    } else {
        $favorites = array();
    }

    if ($status) {
        $favorites[$courseid] = 1;
    } else {
        if (isset($favorites[$courseid])) {
            unset($favorites[$courseid]);
        }
    }
    $favorites = implode(',', array_keys($favorites));

    set_user_preference('semester_sortierung_favorites', $favorites);
}