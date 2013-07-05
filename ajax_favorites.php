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
 * This file updates the user_preference for the block favorites courses
 *
 * @package blocks_semester_sortierung
 * @author Simeon Naydenov
 * @copyright 2012 Vienna University of Technology
 */

define('AJAX_SCRIPT', true);

require_once('../../config.php');
require_once(__DIR__ . '/locallib.php');

$cid = required_param('id', PARAM_ALPHANUM);
$status = required_param('status', PARAM_INT);


require_login();

block_semester_sortierung_toggle_fav($cid, $status);