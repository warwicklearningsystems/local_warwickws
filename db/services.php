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
 * Web services definition
 *
 * @package    localwarwickws
 * @copyright  2019 University of Warwick
 */

// We defined the web service functions to install.
$functions = array(

		'warwick_timestamp_get_course_completion_status' => array(
                'classname'   => 'local_warwickws_external',
                'methodname'  => 'timestamp_get_course_completion_status',
                'classpath'   => 'local/warwickws/externallib.php',
                'description' => 'Return array of completion statuses after timestamp.',
                'type'        => 'read',
        ),
    'warwick_binary_get_course_completion_status' => array(
                'classname'   => 'local_warwickws_external',
                'methodname'  => 'binary_get_course_completion_status',
                'classpath'   => 'local/warwickws/externallib.php',
                'description' => 'Return binary completion status.',
                'type'        => 'read',
        ),
		'warwick_unenrol_student' => array(
                'classname'   => 'local_warwickws_external',
                'methodname'  => 'warwick_unenrol_student',
                'classpath'   => 'local/warwickws/externallib.php',
                'description' => 'Allow student UNenrolment via University ID.',
                'type'        => 'write',
        ),
	   'warwick_enrol_student' => array(

                'classname'   => 'local_warwickws_external',
                'methodname'  => 'warwick_enrol_student',
                'classpath'   => 'local/warwickws/externallib.php',
                'description' => 'Allow student enrolment via University ID.',
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
        'warwick_get_list_courses' => array(
                'classname'   => 'local_warwickws_external',
                'methodname'  => 'get_list_courses',
                'classpath'   => 'local/warwickws/externallib.php',
                'description' => 'Retrieves list of courses for a user',
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
        ),
        'warwick_reset_dashboard' => array(
                'classname'   => 'local_warwickws_external',
                'methodname'  => 'reset_user_dashboard',
                'classpath'   => 'local/warwickws/externallib.php',
                'description' => 'Resets a users dashboard',
                'type'        => 'write',
        ),
        'warwick_add_enrolment_method' => array(
                'classname'   => 'local_warwickws_external',
                'methodname'  => 'add_enrolment_method',
                'classpath'   => 'local/warwickws/externallib.php',
                'description' => 'Adds an enrolment method to a course',
                'type'        => 'write',
        )

);

