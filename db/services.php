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
 * External API functions
 *
 * @package       block_semester_sortierung
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Simeon Naydenov (moniNaydenov@gmail.com)
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$functions = array(

    'block_semester_sortierung_get_modules' => array(
        'classname' => 'block_semester_sortierung_external',
        'methodname' => 'get_modules',
        'classpath' => 'blocks/semester_sortierung/externallib.php',
        'description' => 'Returns a list of all enabled modules in Moodle.',
        'type' => 'read',
        'capabilities' => ''
    ),

    'block_semester_sortierung_get_courses' => array(
        'classname' => 'block_semester_sortierung_external',
        'methodname' => 'get_courses',
        'classpath' => 'blocks/semester_sortierung/externallib.php',
        'description' => 'Returns a list user current courses',
        'type' => 'read',
        'capabilities' => ''
    ),

    'block_semester_sortierung_get_coursedetails' => array(
        'classname' => 'block_semester_sortierung_external',
        'methodname' => 'get_coursedetails',
        'classpath' => 'blocks/semester_sortierung/externallib.php',
        'description' => 'Returns a list of course items for a course',
        'type' => 'read',
        'capabilities' => ''
    ),
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
    'Block semester_sortierung Web Service' => array(
        'functions' => array ('block_semester_sortierung_get_modules', 'block_semester_sortierung_get_courses', 'block_semester_sortierung_get_coursedetails' ),
        'restrictedusers' => 1,
        'enabled' => 1,
    )
);
