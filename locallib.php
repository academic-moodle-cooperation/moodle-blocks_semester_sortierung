<?php
// This file is part of blocks_semester_sortierung for Moodle - http://moodle.org/
//
// It is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// It is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// If not, see <http://www.gnu.org/licenses/>.

/**
 * locallib.php
 * local functions
 *
 * @package       blocks_semester_sortierung
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Simeon Naydenov
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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