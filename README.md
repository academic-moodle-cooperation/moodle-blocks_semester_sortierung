semester_sortierung Block
=============

This file is part of the block_semester_sortierung plugin for Moodle - <http://moodle.org/>

*Author:*    Simeon Naydenov, Katarzyna Potocka

*Copyright:* 2019 [Academic Moodle Cooperation](http://www.academic-moodle-cooperation.org)

*License:*   [GNU GPL v3 or later](http://www.gnu.org/copyleft/gpl.html)


Description
-----------

In the semester_sortierung block courses are grouped by their starting dates in a semester view on the MyMoodle
page.

The semester_sortierung block also displays all MyMoodle information regarding the course activities of the
individual courses.

The module will also feature a "My favourites" area, for which courses from different semesters can
be selected and they will then be listed at the top of the favourites area.


Example
-------

Use the semester_sortierung module to display the winter and summer semesters with all your courses,
and the favourites area for quick access.


Requirements
------------

The plugin is available for Moodle 2.5+. This version is for Moodle 3.8.


Installation
------------

* Copy the module code directly to the blocks/semester_sortierung directory.

* Log into Moodle as administrator.

* Open the administration area (http://your-moodle-site/admin) to start the installation
  automatically.


Admin Settings
--------------

As an administrator you can set the default values instance-wide on the settings page for
administrators in the semester_sortierung block:

* Sort courses by semesters (checkbox) - controls whether courses are sorted at all
* Winter semester months (checkbox) - select which months should apply for the winter semester
* Show favourites (checkbox) - controls whether favourites on
* Archive (number) - group semesters that are older than the specified value 
* Autocollapse courses (number) - always show courses older than the specified number of months as collapse (only if there are performance issues) 
* Skip older events (number) - don't load events older than the specified number of months (only if there are performance issues)

Privacy API
-----------

The semester_sortierung block fully implements the Moodle Privacy API.

Web Service
-----------

The semester_sortierung block provides the following web service functions.

* Authentication - via token

  Token can be obtained in two ways:
  - manually from Moodle at `$CFG->wwwroot/admin/settings.php?section=webservicetokens`
  - by calling `$CFG->wwwroot/login/token.php?username=USERNAME&password=PASSWORD&service=SERVICENAME`
    (here `SERVICENAME` is semester_sortierung Web service)

* Requests

  Get courses - get all enrolled and active courses for a student
  - URL: `$CFG->wwwroot/webservice/rest/server.php?moodlewsrestformat=json&wsfunction=block_semester_sortierung_get_courses&wstoken={token}&userid={userid}`
  - Method: GET
  - Produces: application/json
  - Sample output: `[ {"coursename": "COURSE NAME", "semestercode": "2016S", "description": "COURSE DESCRIPTION", "coursenumber": "015709-2016S", "courseid": 1234567} ]`

  Get course details - get the course metadata
  - URL: `$CFG->wwwroot/webservice/rest/server.php?moodlewsrestformat=json&wsfunction=block_semester_sortierung_get_modules&wstoken={token}&userid={userid}&courseid={courseid}`
  - Method: GET
  - Produces: application/json
  - Sample output: `[ {"modname": "MODULE_NAME", "modinfo": "HTML_INFORMATION"} ]`

  Get modules - get all active modules
  - URL: `$CFG->wwwroot/webservice/rest/server.php?moodlewsrestformat=json&wsfunction=block_semester_sortierung_get_modules&wstoken={token}`
  - Method: GET
  - Produces: application/json
  - Image format: base64 png
  - Sample output: `[ {"modname": "MODULE NAME - UNIQUE ID", "title_en": "Module title in English", "title_de": "Module title in German", "image": "base64EncodedImage"} ]`



Bug Reports / Support
---------------------

We try our best to deliver bug-free plugins, but we can not test the plugin for every platform,
database, PHP and Moodle version. If you find any bug please report it on
[GitHub](https://github.com/academic-moodle-cooperation/moodle-blocks_semester_sortierung/issues).
Please provide a detailed bug description, including the plugin and Moodle version and, if
applicable, a screenshot.

You may also file a request for enhancement on GitHub. If we consider the request generally useful
and if it can be implemented with reasonable effort we might implement it in a future version.

You may also post general questions on the plugin on GitHub, but note that we do not have the
resources to provide detailed support.


License
-------

This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU
General Public License as published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

The plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
General Public License for more details.

You should have received a copy of the GNU General Public License with Moodle. If not, see
<http://www.gnu.org/licenses/>.


Good luck and have fun!
