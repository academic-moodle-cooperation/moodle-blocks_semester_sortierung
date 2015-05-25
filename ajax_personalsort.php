<?php
/**
 * Created by PhpStorm.
 * User: moni
 * Date: 5/25/15
 * Time: 9:46 PM
 */

define('AJAX_SCRIPT', true);

require_once('../../config.php');
require_once(__DIR__ . '/locallib.php');

require_login();

block_semester_sortierung_update_personal_sort();
