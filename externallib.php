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
 * External Web Service Template
 *
 * @package    localwarwickws
 * @copyright  2011 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");

class local_warwickws_external extends external_api {

    /** Cron tasks */

    public static function list_cron_tasks_parameters() {
        return new external_function_parameters(
            array()
        );
    }

    public static function list_cron_tasks_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'name' => new external_value(PARAM_TEXT, 'Name of scheduled task'),
                    'timestamp' => new external_value(PARAM_TEXT, 'Timestamp of last run.'),
                    'disabled' => new external_value(PARAM_BOOL, 'Whether the task is disabled.'),
                    'frequency' => new external_value(PARAM_TEXT, 'Frequency of scheduled task.')
                )
            )
        );

    }

    public static function list_cron_tasks() {
        global $USER, $DB;

        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::list_cron_tasks_parameters(),
            array());

        //Context validation
        //OPTIONAL but in most web service it should present
        $context = get_context_instance(CONTEXT_USER, $USER->id);
        self::validate_context($context);

        //Capability checking
        //OPTIONAL but in most web service it should present
        //if (!has_capability('moodle/user:viewdetails', $context)) {
        //    throw new moodle_exception('cannotviewprofile');
        //}

        $scheduledtasks = array();

        $tasks = $DB->get_records('task_scheduled', null, 'component, classname', '*', IGNORE_MISSING);

        foreach($tasks as $t) {
            $g = new stdClass();
            $g->name = $t->classname;
            $g->timestamp = $t->lastruntime;
            $g->disabled = $t->disabled;

            // Calculate typical frequency
            if($t->month != '*') {
                $g->frequency = 'monthly';
            } else if ($t->week != '*') {
                $g->frequency = 'weekly';
            } else {
                $g->frequency = 'daily';
            }
            if($t->month != '*') {
                $g->frequency = 'monthly';
            } else if ($t->dayofweek != '*') {
                $g->frequency = 'weekly';
            } else {
                $g->frequency = 'daily';
            }



            $scheduledtasks[] = $g;
        }

        return $scheduledtasks;
    }

    /** Student assignments */

    public static function get_student_assignment_parameters() {
        return new external_function_parameters(
          array('idnumber' => new external_value(PARAM_TEXT, 'University ID number', VALUE_REQUIRED))
        );
    }

    public static function get_student_assignment_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'name' => new external_value(PARAM_TEXT, 'Name of course'),
                    'idnumber' => new external_value(PARAM_TEXT, 'ID number of course'),
                    'assignments' => new external_multiple_structure(
                        new external_single_structure(
                        array(
                          'id' => new external_value(PARAM_TEXT, 'Assignment ID'),
                          'name' => new external_value(PARAM_TEXT, 'Name of assignment'),
                          'duedate' => new external_value(PARAM_TEXT, 'Due date for assignment'),
                        )
                        )
                    )
                )
            )
        );

    }

    public static function get_student_assignment($idnumber) {
        global $USER, $DB;

        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::get_student_assignment_parameters(),
            array('idnumber' => $idnumber));

        //Context validation
        //OPTIONAL but in most web service it should present
        $context = get_context_instance(CONTEXT_USER, $USER->id);
        self::validate_context($context);

        //Capability checking
        //OPTIONAL but in most web service it should present
        //if (!has_capability('moodle/user:viewdetails', $context)) {
        //    throw new moodle_exception('cannotviewprofile');
        //}

        $assignments = array();

        $fields = 'sortorder,shortname,fullname,timemodified';
        $courses = enrol_get_users_courses($USER->id, true, $fields);

        foreach($courses as $id => $course) {
            $a = new stdClass();

            $a->name = $course->fullname;
            $a->idnumber = $course->idnumber;
            $a->id = $course->id;
            $a->assignments = self::get_assignments_for_course($course->id);

            $assignments[] = $a;
        }

        return $assignments;
    }

    private static function get_assignments_for_course($courseid)
    {
        $assignmentarray = array();

        $extrafields='m.id as assignmentid, ' .
            'm.course, ' .
            'm.duedate, ' .
            'm.allowsubmissionsfromdate, '.
            'm.grade, ' .
            'm.timemodified, '.
            'm.completionsubmit, ' .
            'm.cutoffdate, ' .
            'm.teamsubmission, ' .
            'm.requireallteammemberssubmit, '.
            'm.teamsubmissiongroupingid, ' .
            'm.maxattempts';

        // Get a list of assignments for the course.
        if ($modules = get_coursemodules_in_course('assign', $courseid, $extrafields)) {
            foreach ($modules as $module) {
                $context = context_module::instance($module->id);
                try {
                    self::validate_context($context);
                    require_capability('mod/assign:view', $context);
                } catch (Exception $e) {
                    $warnings[] = array(
                        'item' => 'module',
                        'itemid' => $module->id,
                        'warningcode' => '1',
                        'message' => 'No access rights in module context'
                    );
                    continue;
                }

                $assignment = array(
                    'id' => $module->assignmentid,
                    'cmid' => $module->id,
                    'course' => $module->course,
                    'name' => $module->name,
                    'duedate' => $module->duedate,
                    'allowsubmissionsfromdate' => $module->allowsubmissionsfromdate,
                    'grade' => $module->grade,
                    'completionsubmit' => $module->completionsubmit,
                    'cutoffdate' => $module->cutoffdate,
                    'teamsubmission' => $module->teamsubmission,
                    'requireallteammemberssubmit' => $module->requireallteammemberssubmit,
                    'teamsubmissiongroupingid' => $module->teamsubmissiongroupingid,
                    'maxattempts' => $module->maxattempts,
                );

                $assignmentarray[] = $assignment;
            }
        }

        return $assignmentarray;
    }

    /** Add blocks */

    public static function course_add_block_parameters() {
         return new external_function_parameters(
           array(
             'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_REQUIRED),
             'blockname' => new external_value(PARAM_TEXT, 'Block name', VALUE_REQUIRED)
           )
         );
    }

    public static function course_add_block_returns() {
        return new external_value(PARAM_BOOL, 'Success');
    }

    public static function course_add_block($courseid, $blockname) {
        //global $PAGE;

        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::course_add_block_parameters(),
            array('courseid' => $courseid, 'blockname' => $blockname));

        // Where are we going to put this block?
        $course = get_course($params['courseid']);
        $context = context_course::instance($course->id);

        // Establish page within this course
        $page = new moodle_page();
        $page->set_context($context);
        $page->set_pagelayout("course");
        
        // Add the block
        $defaultregion = $page->blocks->get_default_region();

        $page->blocks->add_block($blockname, $defaultregion, 1, FALSE, 'course-view-*');
   }

    /** Remove blocks */

    public static function course_remove_block_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_REQUIRED),
                'blockid' => new external_value(PARAM_INT, 'Block instance ID', VALUE_REQUIRED)
            )
        );
    }

    public static function course_remove_block_returns() {
        return new external_value(PARAM_BOOL, 'Success');
    }

    public static function course_remove_block($courseid, $blockid) {

        global $OUTPUT, $PAGE;

        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::course_remove_block_parameters(),
            array('courseid' => $courseid, 'blockid' => $blockid));

        // Where are we going to put this block?
        $course = get_course($params['courseid']);
        $context = context_course::instance($course->id);
        self::validate_context($context);

        $PAGE->set_pagelayout('course');
        $course->format = course_get_format($course)->get_format();
        $PAGE->set_pagetype('course-view-' . $course->format);

        $PAGE->blocks->load_blocks();
        $PAGE->blocks->create_all_block_instances();

        $PAGE->set_course($course);
        $PAGE->set_context($context);

 //       $PAGE->blocks->create_all_block_instances();

        // Find block
        $block = $PAGE->blocks->find_instance($blockid);

        // Remove the block
        blocks_delete_instance($block->instance);
    }


   /** List blocks */

   public static function course_list_blocks_parameters() {
       return new external_function_parameters(
           array(
               'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_REQUIRED)
           )
       );
   }

    public static function course_list_blocks_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'blockid' => new external_value(PARAM_INT, 'Block instance ID'),
                    'name' => new external_value(PARAM_TEXT, 'Block name.')
                )
            )
        );
    }

   public static function course_list_blocks($courseid) {
       global $DB;

       //Parameter validation
       //REQUIRED
       $params = self::validate_parameters(self::course_list_blocks_parameters(),
           array('courseid' => $courseid));

        // Where are we going to put this block?
       $course = get_course($params['courseid']);
       $context = context_course::instance($course->id);

       $courseblocks = array();
       $blocks = $DB->get_records('block_instances', array('parentcontextid' => $context->id));

       foreach($blocks as $b) {
           $g = new stdClass();
           $g->blockid = $b->id;
           $g->name = $b->blockname;

           $courseblocks[] = $g;
       }

       return $courseblocks;
    }

}
