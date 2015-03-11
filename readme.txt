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

v. 2013-01-09.07

Semester sortierung block
==================

OVERVIEW
================================================================================

An enhanced version of the Course overview block. Provides the ability to 
separate courses to semesters by their starting date. 
Features:
* Favorite courses - courses chosen by the user to be displayed on top of all 
courses

INSTALLAION
================================================================================
Requires Moodle version 2.2 - 2012112900


Just place the plugin contents inside moodle/blocks/semester_sortierung folder and go to 
Administration-> Notifications to install the new plugin.

CHANGE LOG
================================================================================

Taken from git history with bugs' ids taken from redmine log at
http://git.zserv.tuwien.ac.at/redmine/projects/block_semsort

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

