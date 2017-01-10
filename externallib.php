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

}
