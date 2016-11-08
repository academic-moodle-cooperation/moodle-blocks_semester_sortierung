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

WEB SERVICE
================================================================================


* Authentication - via token

    Token can be obtained in two ways:
     * manually from moodle at $CFG->wwwroot/admin/settings.php?section=webservicetokens
     * by calling $CFG->wwwroot/login/token.php?username=USERNAME&password=PASSWORD&service=SERVICENAME (here SERVICENAME is SemSort Web service)


* Requests

    Get courses - get all enrolled and active courses for a student
    url: $CFG->wwwroot/webservice/rest/server.php?moodlewsrestformat=json&wsfunction=block_semester_sortierung_get_courses&wstoken={token}&userid={userid}
    Method: GET
    Produces: application/json
    Sample output:
        [ {"coursename": "COURSE NAME", "semestercode": "2016S", "description": "COURSE DESCRIPTION", "coursenumber": "015709-2016S", "courseid": 1234567} ]


    Get course details - get the course metadata
    url: $CFG->wwwroot/webservice/rest/server.php?moodlewsrestformat=json&wsfunction=block_semester_sortierung_get_modules&wstoken={token}&userid={userid}&courseid={courseid}
    Method: GET
    Produces: application/json
    Sample output:
        [ {"modname": "MODULE_NAME", "modinfo": "HTML_INFORMATION"} ]


    Get modules - get all active modules
    url: $CFG->wwwroot/webservice/rest/server.php?moodlewsrestformat=json&wsfunction=block_semester_sortierung_get_modules&wstoken={token}
    Method: GET
    Produces: application/json
    image format: base64 png
    Sample output:
        [ {"modname": "MODULE NAME - UNIQUE ID", "title_en": "Module title in English", "title_de": "Module title in German", "image": "base64EncodedImage"} ]


CHANGE LOG
================================================================================

Taken from git history with bugs' ids taken from redmine log at
http://moodledev.zserv.tuwien.ac.at/redmine/projects/block_semsort

2016-11-02: Feature #3782 - Rename web service to SemSort Web service
2016-09-14: Update #3688 - remove $plugin->cron
2016-09-07: Bug #3694 - fix months in settings.php to work in all time zones
2016-07-28: Bug #3574 - fix long course/assignment names
2016-06-21: Update #2976 - update for moodle 3.1
2016-05-24: Add external web service
2016-05-19: AutoTesting #3281, #3280, #3279, #3284 - add first behat tests
2016-04-13: Feature #2729 - add first two behat tests
2016-03-28: Update #3125 - update and test on moodle 3.0.3
2016-03-18: Bug #3108: Replaced 'seperate' by 'separate' in english and german lang files.
2016-03-16: Bug #3108 - add missing empty space character
2015-10-14: Bug #2713 - fix bug with adding course to favorites - course can be added more than once
2015-09-24: Update #2491 - update for moodle 2.9.2
2015-09-23: increase version number
2015-09-19: HOTFIX #2647: fix star icon layout
2015-06-10: Feature #1970 - switch places of expand and move symbol
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
