This file is part of Moodle - http://moodle.org/

Moodle is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Moodle is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

copyright 2009 Petr Skoda (http://skodak.org)
license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

v. 2015-05-26.00

Semester sortierung block
================================================================================

OVERVIEW
================================================================================

An enhanced version of the Course overview block. Provides the ability to 
separate courses to semesters by their starting date. 
Features:
* Favorite courses - courses chosen by the user to be displayed on top of all 
courses
* Personal sorting inside semesters

INSTALLAION
================================================================================
Requires Moodle version 2.2 - 2012112900


Just place the plugin contents inside moodle/blocks/semester_sortierung folder and go to 
Administration-> Notifications to install the new plugin.

AUTHORS
================================================================================
 * Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * Simeon Naydenov (moniNaydenov@gmail.com)

CHANGE LOG
================================================================================

Taken from git history with bugs' ids taken from redmine log at
http://git.zserv.tuwien.ac.at/redmine/projects/block_semsort


2015-05-26: Feature #2354 - fix code to pass code checker
2015-05-26: Feature #1970 - add personal sorting capability:
                            add admin setting
                            improve sorting courses - few loops less, some clean up
                            implement user sorting
                            finish no-javascript sorting
                            add ajax_personalsort for user sorting to be updated by ajax
                            added yui for drag and drop
2015-05-26: Bug #2337 - enable user to delete the block in his own my moodle page
2015-05-26: Update #2250 - Update and fix problems for moodle 2.8.5
2015-05-26: Bug #2072 - Fix settings plugin name to block_semester_sortierung, fix bug with config
2014-10-13: Update #2017 - Update version.php
2014-10-13: Update #2016 - update headers of all files
2014-04-14: Update #1487 - make it work on 2.6.2
2013-12-04: Bug #1145 - fix roles' strings
2013-11-13: Update #1124 - fix favorites "star" when no sort is set to true
2013-07-15: Bug #902 - No alt tags in IE
2013-07-15: Feature #883 - add an admin setting for favorites
2013-07-09: Bug #812 - fix "Zeilenumbruch"
2013-07-05: Bug #809 - fix block not deletable
2013-07-05: Update #769 - add a favorites box at the top
2013-06-27: Bug #756 - fix course sorting to fullname
2013-05-18: Rewritten so that it uses renderer.php - in preparation for 2.5
2013-03-26: code cleanup
2013-03-14: Feature #61 - make the block usable without javascript
2013-01-29: Bug #329 - fix mouseover and dimmed courses links
2013-01-25: codebase for 2.4
