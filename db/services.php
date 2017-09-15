<?php

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
 * Web service local plugin template external functions and service definitions.
 *
 * @package    localwarwickws
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = array(
       'enrol_universityid' => array(
                'classname'   => 'local_warwickws_external',
                'methodname'  => 'enrol_universityid',
                'classpath'   => 'local/warwickws/externallib.php',
                'description' => 'Allow enrolment via University ID.',
                'type'        => 'write',
        ),
	   'warwick_check_cron_tasks' => array(
                'classname'   => 'local_warwickws_external',
                'methodname'  => 'list_cron_tasks',
                'classpath'   => 'local/warwickws/externallib.php',
                'description' => 'Retrieves a list of cron tasks.',
                'type'        => 'read',
        ),
        'warwick_get_student_assignments' => array(
                'classname'   => 'local_warwickws_external',
                'methodname'  => 'get_student_assignment',
                'classpath'   => 'local/warwickws/externallib.php',
                'description' => 'Retrieves list of upcoming assignments for an individual student',
                'type'        => 'read',
        ),
        'warwick_course_add_block' => array(
                 'classname'   => 'local_warwickws_external',
                 'methodname'  => 'course_add_block',
                 'classpath'   => 'local/warwickws/externallib.php',
                 'description' => 'Adds a block to a course',
                 'type'        => 'write',
        ),
        'warwick_course_list_blocks' => array(
                'classname'   => 'local_warwickws_external',
                'methodname'  => 'course_list_blocks',
                'classpath'   => 'local/warwickws/externallib.php',
                'description' => 'Lists blocks in a course',
                'type'        => 'read',
        ),
        'warwick_course_remove_block' => array(
                'classname'   => 'local_warwickws_external',
                'methodname'  => 'course_remove_block',
                'classpath'   => 'local/warwickws/externallib.php',
                'description' => 'Deletes an instance of a block in a course',
                'type'        => 'write',
        )
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
//$services = array(
//        'My service' => array(
//                'functions' => array ('warwick_check_cron_tasks', 'warwick_get_student_assignments', 'warwick_course_add_block'),
//                'restrictedusers' => 0,
//                'enabled'=>1,
//        )
//);
