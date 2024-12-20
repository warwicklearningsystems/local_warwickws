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

$services = [
    'local_warwickws' => [
        'functions' => [
            'warwick_timestamp_get_course_completion_status',
            'warwick_binary_get_course_completion_status',
            'warwick_unenrol_student',
            'warwick_enrol_student',
            'warwick_check_cron_tasks',
            'warwick_get_student_assignments',
            'warwick_get_list_courses',
            'warwick_course_add_block',
            'warwick_course_list_blocks',
            'warwick_course_remove_block',
            'warwick_course_block_set_html',
            'warwick_reset_dashboard',
            'warwick_add_enrolment_method',
            'warwick_freeze_course',
            'warwick_unfreeze_course',
            'warwick_freeze_category',
            'warwick_unfreeze_category',
            'warwick_remove_suspended_enrolments',
            'warwick_query_assignments',
            'warwick_query_quizzes',
            'warwick_query_user_enrolments',
            'warwick_query_staff_idnumber'
        ],
        'requiredcapability' => '',
        'enabled' => 1
    ]
];


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
    'warwick_course_block_set_html' => array(
            'classname'   => 'local_warwickws_external',
            'methodname'  => 'course_block_set_html',
            'classpath'   => 'local/warwickws/externallib.php',
            'description' => 'Sets the HTML in an existing HTML block',
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
    ),
    'warwick_freeze_course' => array(
            'classname'   => 'local_warwickws_external',
            'methodname'  => 'freeze_course',
            'classpath'   => 'local/warwickws/externallib.php',
            'description' => 'Freezes a course',
            'type'        => 'write',
    ),
    'warwick_unfreeze_course' => array(
            'classname'   => 'local_warwickws_external',
            'methodname'  => 'unfreeze_course',
            'classpath'   => 'local/warwickws/externallib.php',
            'description' => 'Unfreezes a course',
            'type'        => 'write',
    ),
    'warwick_freeze_category' => array(
            'classname'   => 'local_warwickws_external',
            'methodname'  => 'freeze_category',
            'classpath'   => 'local/warwickws/externallib.php',
            'description' => 'Freezes an entire category',
            'type'        => 'write',
    ),
    'warwick_unfreeze_category' => array(
            'classname'   => 'local_warwickws_external',
            'methodname'  => 'unfreeze_category',
            'classpath'   => 'local/warwickws/externallib.php',
            'description' => 'Unfreezes an entire category',
            'type'        => 'write',
    ),
    'warwick_remove_suspended_enrolments' => array(
            'classname'   => 'local_warwickws_external',
            'methodname'  => 'remove_suspended_enrolments',
            'classpath'   => 'local/warwickws/externallib.php',
            'description' => 'Removes suspended enrolments',
            'type'        => 'write',
    ),
    'warwick_query_assignments' => array(
            'classname'   => 'local_warwickws_external',
            'methodname'  => 'query_assignments',
            'classpath'   => 'local/warwickws/externallib.php',
            'description' => 'Identifies assignments with upcoming duedate',
            'type'        => 'read',
    ),
    'warwick_query_quizzes' => array(
            'classname'   => 'local_warwickws_external',
            'methodname'  => 'query_quizzes',
            'classpath'   => 'local/warwickws/externallib.php',
            'description' => 'Identifies available quizzes',
            'type'        => 'read',
    ),
    'warwick_query_user_enrolments' => array(
            'classname'   => 'local_warwickws_external',
            'methodname'  => 'query_user_enrolments',
            'classpath'   => 'local/warwickws/externallib.php',
            'description' => 'Identifies recent changes to user enrolments in a course',
            'type'        => 'read',
    ),
    'warwick_query_staff_idnumber' => array(
            'classname'   => 'local_warwickws_external',
            'methodname'  => 'query_staff_idnumber',
            'classpath'   => 'local/warwickws/externallib.php',
            'description' => 'Identify staff user based on university ID number',
            'type'        => 'read',
    ),
);

