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
 * This file updates the user_preference for the block boxes' status
 *
 * @package blocks_semester_sortierung
 * @author Simeon Naydenov
 * @copyright 2012 Vienna University of Technology
 */

define('AJAX_SCRIPT', true);
 
require_once('../../config.php');

$boxid = required_param('id', PARAM_ALPHANUM);
$state = required_param('state', PARAM_INT);

require_login();
 
//get the course expansion info from user preference
$boxes_data = get_user_preferences('semester_sortierung_boxes', 'a:0:{}');
$boxes_data = @unserialize($boxes_data);

if (!is_array($boxes_data)) {
    $boxes_data = array();
}
$boxes_data[$boxid] = $state;

$newvalue = serialize($boxes_data);

set_user_preference('semester_sortierung_boxes', $newvalue);
