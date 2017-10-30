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
require_once($CFG->dirroot . "/my/lib.php");
require_once($CFG->libdir . "/completionlib.php");
require_once($CFG->libdir . '/grouplib.php');
		require_once($CFG->libdir . '/enrollib.php');
		require_once($CFG->dirroot . "/user/lib.php");
		
		
class local_warwickws_external extends external_api {

	/** Custom **/
	
	/** Return Course completion status **/
	public static function binary_get_course_completion_status_parameters() {
          return new external_function_parameters(
                array(
                    'completionchecks' => new external_multiple_structure(
                            new external_single_structure(
                                    array(
                                        'universityid' => new external_value(PARAM_RAW, 'The WARWICK UNIVERSITY ID of the user that is going to be enrolled'),
                                        'courseidnumber' => new external_value(PARAM_RAW, 'The WARWICK COURSE ID to enrol the user role in')
                                    )
                            )
                    )
                )
        );
    }

    public static function binary_get_course_completion_status_returns() {
      return new external_value(PARAM_BOOL, 'Success');
	  //return new external_value(PARAM_RAW, 'Success'); 
    }

    public static function binary_get_course_completion_status($completionchecks) {
        
		global $DB, $CFG, $USER;
		

		  $params = self::validate_parameters(self::binary_get_course_completion_status_parameters(),
                   array('completionchecks' => $completionchecks)); 

     
      $enrol = enrol_get_plugin('manual');
      if (empty($completionchecks)) {
        throw new moodle_exception('manualpluginnotinstalled', 'enrol_manual');
      }

	    // Loop through all the enrolments
	   	foreach ($params['completionchecks'] as $enrolment) {

					// Find this course
					$course = $DB->get_record('course', array('idnumber' => $enrolment['courseidnumber']));
										
					if(empty($course)) {
					  $errorparams = new stdClass();
					  $errorparams->courseidnumber = $enrolment['courseidnumber'];
					  $context = context_system::instance();
					  
							//Write event to log
							$event =\local_warwickws\event\wsevent_error::create(array(
								'context'=>$context,
								'other'=>'Course: '.$enrolment['courseidnumber']
									));
							$event->trigger();
					  
					  throw new moodle_exception('nocourse', 'local_warwickws', '', $errorparams);
					}

											
					// Get userid from universityid
					$users = $DB->get_records_list('user', 'idnumber',array($enrolment['universityid']), 'id');
					
										
					//check user returned
					if (empty($users)) {
					  $errorparams = new stdClass();
					  $errorparams->universityid = $enrolment['universityid'];
					  $context = context_system::instance();
					  
					  //Write event to log
							$event =\local_warwickws\event\wsevent_error::create(array(
								'context'=>$context,
								'other'=>'User: '.$enrolment['universityid']
									));
							$event->trigger();
					  
					  throw new moodle_exception('nouser', 'local_warwickws', '', $errorparams);
					}
					
					
											
											$user=reset($users);
											
											$context = context_course::instance($course->id);
											self::validate_context($context);
																										 
											$info = new completion_info($course);

											// Check this user is enroled.
											if (!$info->is_tracked_user($user->id)) {
												if ($USER->id == $user->id) {
													throw new moodle_exception('notenroled', 'completion');
												} else {
													//Write event to log
													$event =\local_warwickws\event\wsevent_error::create(array(
														'context'=>$context,
														'other'=>'User: '.$enrolment['universityid'].' not enrolled on course '.$enrolment['courseidnumber']
															));
													$event->trigger();
													
																
													
													throw new moodle_exception('usernotenroled', 'completion');
												}
											}

										
											return $info->is_course_complete($user->id);
							
					
      }
	       
    }
	
	
	
	/** Custom Enrol plugin **/
	public static function enrol_student_parameters() {
          return new external_function_parameters(
                array(
                    'enrolments' => new external_multiple_structure(
                            new external_single_structure(
                                    array(
                                        'universityid' => new external_value(PARAM_RAW, 'The WARWICK UNIVERSITY ID of the user that is going to be enrolled'),
                                        'courseidnumber' => new external_value(PARAM_RAW, 'The WARWICK COURSE ID to enrol the user role in'),
                                        'timestart' => new external_value(PARAM_INT, 'Timestamp when the enrolment start', VALUE_OPTIONAL),
                                        'timeend' => new external_value(PARAM_INT, 'Timestamp when the enrolment end', VALUE_OPTIONAL),
                                        'suspend' => new external_value(PARAM_INT, 'set to 1 to suspend the enrolment', VALUE_OPTIONAL)
                                    )
                            )
                    )
                )
        );
    }

    public static function enrol_student_returns() {
      return new external_value(PARAM_BOOL, 'Success');
    }

    public static function enrol_student($enrolments) {
        
		  global $DB, $CFG, $USER;

      require_once($CFG->libdir . '/enrollib.php');
		  require_once($CFG->dirroot . "/user/lib.php");

		
		  $params = self::validate_parameters(self::enrol_student_parameters(),
                   array('enrolments' => $enrolments));
					
  
					
														   
      // Retrieve the manual enrolment plugin.
      $enrol = enrol_get_plugin('manual');
      if (empty($enrol)) {
        throw new moodle_exception('manualpluginnotinstalled', 'enrol_manual');
      }

	  	  
	  
	  
      // Loop through all the enrolments
	   	foreach ($params['enrolments'] as $enrolment) {

        // Find this course
        $course = $DB->get_record('course', array('idnumber' => $enrolment['courseidnumber']));

        if(empty($course)) {
          $errorparams = new stdClass();
          $errorparams->courseidnumber = $enrolment['courseidnumber'];
          $context = context_system::instance();
		  
					  
							//Write event to log
							$event =\local_warwickws\event\wsevent_error::create(array(
								'context'=>$context,
								//'other'=>'Enrol failed. Course does not exist : '.$enrolment['courseidnumber']
								'other'=>'Enrol failed, Course does not exist. User: '.$enrolment['universityid'].' to course '.$enrolment['courseidnumber']
								));
							$event->trigger();
		  throw new moodle_exception('nocourse', 'local_warwickws', '', $errorparams);
        }

		  	// Ensure the current user is allowed to run this function in the enrolment context.
				$context = context_course::instance($course->id, IGNORE_MISSING);
				self::validate_context($context);

				// Check that the user has the permission to manual enrol.
			 	require_capability('enrol/manual:enrol', $context);

				$enrolname = 'manual';
				$enrol = enrol_get_plugin($enrolname);

        // Get enrolment instances for this course, and select the manual enrolment...
				$enrolinstances = enrol_get_instances($course->id, true);

				foreach ($enrolinstances as $courseenrolinstance) {
			  	if ($courseenrolinstance->enrol == $enrolname) {
				  	$instance = $courseenrolinstance;
						break;
					}
				}

				// If we don't have an instance, throw an error...
		  	if (empty($instance)) {
			    $errorparams = new stdClass();
				  $errorparams->courseid = $enrolment['courseidnumber'];
          $errorparms->userid = $enrolment['universityid'];
				  throw new moodle_exception('wsnoinstance', 'enrol_manual', $errorparams);
				}

        // Check that the plugin accept enrolment (it should always the case, it's hard coded in the plugin).
        if (!$enrol->allow_enrol($instance)) {
          $errorparams = new stdClass();
          $errorparams->courseid = $enrolment['courseidnumber'];
          throw new moodle_exception('wscannotenrol', 'enrol_manual', '', $errorparams);
        }

        //$context = context_system::instance();
        //	self::validate_context($context);

        // Throw an exception if user is not able to assign the role.
        $roles = get_assignable_roles($context);

        if (!array_key_exists(5, $roles)) {
          $errorparams = new stdClass();
          $errorparams->roleid = 'student';
          $errorparams->courseid = $enrolment['courseidnumber'];
          $errorparams->userid = $enrolment['universityid'];
          throw new moodle_exception('wsusercannotassign', 'enrol_manual', '', $errorparams);
        }
			
        //Start working enrolment config
        // Get userid from universityid
        $users = $DB->get_records_list('user', 'idnumber',array($enrolment['universityid']), 'id');

        //check user returned
        if (empty($users)) {
          $errorparams = new stdClass();
          $errorparams->universityid = $enrolment['universityid'];
          
		  $context = context_system::instance();
		  
		  //Write event to log
													$event =\local_warwickws\event\wsevent_error::create(array(
														'context'=>$context,
														'other'=>'Enrol failed, User does not exist. User: '.$enrolment['universityid'].' to course '.$enrolment['courseidnumber']
															));
													$event->trigger();
		  
		  
		  throw new moodle_exception('nouser', 'local_warwickws', '', $errorparams);
        }
		
		
									
		
 $transaction = $DB->start_delegated_transaction(); // Rollback all enrolment if an error occurs
                                                           // (except if the DB doesn't support it).
        //iterate array enrolling each id
        foreach ($users as $item) {
          $enrol->enrol_user($instance, $item->id, 5, 0, 0, 0, 1);
        }


        $transaction->allow_commit();
      }

      return TRUE;
    }

	
	
	
	/** Custom UNenrol plugin **/
	public static function unenrol_student_parameters() {
          return new external_function_parameters(
                array(
                    'enrolments' => new external_multiple_structure(
                            new external_single_structure(
                                    array(
                                        'universityid' => new external_value(PARAM_RAW, 'The WARWICK UNIVERSITY ID of the user that is going to be enrolled'),
                                        'courseidnumber' => new external_value(PARAM_RAW, 'The WARWICK COURSE ID to enrol the user role in')
                                    )
                            )
                    )
                )
        );
    }

    public static function unenrol_student_returns() {
      return new external_value(PARAM_BOOL, 'Success');
    }

    public static function unenrol_student($enrolments) {
        
		  global $DB, $CFG, $USER;

      require_once($CFG->libdir . '/enrollib.php');
		  require_once($CFG->dirroot . "/user/lib.php");

			$params = self::validate_parameters(self::unenrol_student_parameters(),
                   array('enrolments' => $enrolments));

			

      // Retrieve the manual enrolment plugin.
      $enrol = enrol_get_plugin('manual');
      if (empty($enrol)) {
        throw new moodle_exception('manualpluginnotinstalled', 'enrol_manual');
      }

      // Loop through all the enrolments
	   	foreach ($params['enrolments'] as $enrolment) {

        // Find this course
        $course = $DB->get_record('course', array('idnumber' => $enrolment['courseidnumber']));

        if(empty($course)) {
          $errorparams = new stdClass();
          $errorparams->courseidnumber = $enrolment['courseidnumber'];
          $context = context_system::instance();
					  
					  //Write event to log
							$event =\local_warwickws\event\wsevent_error::create(array(
								'context'=>$context,
								'other'=>'Unenrol failed, Course does not exist. User: '.$enrolment['universityid'].' to course '.$enrolment['courseidnumber']
								//'other'=>'Unenrol no course '
									));
							$event->trigger();
		  
		  
		  throw new moodle_exception('nocourse', 'local_warwickws', '', $errorparams);
        }

		  	// Ensure the current user is allowed to run this function in the enrolment context.
				$context = context_course::instance($course->id, IGNORE_MISSING);
				self::validate_context($context);

				// Check that the user has the permission to manual enrol.
			 	require_capability('enrol/manual:unenrol', $context);

				$enrolname = 'manual';
				$enrol = enrol_get_plugin($enrolname);

        // Get enrolment instances for this course, and select the manual enrolment...
				$enrolinstances = enrol_get_instances($course->id, true);

				foreach ($enrolinstances as $courseenrolinstance) {
			  	if ($courseenrolinstance->enrol == $enrolname) {
				  	$instance = $courseenrolinstance;
						break;
					}
				}

				// If we don't have an instance, throw an error...
		  	if (empty($instance)) {
			    $errorparams = new stdClass();
				  $errorparams->courseid = $enrolment['courseidnumber'];
          $errorparms->userid = $enrolment['universityid'];
				  throw new moodle_exception('wsnoinstance', 'enrol_manual', $errorparams);
				}

        // Check that the plugin accept enrolment (it should always the case, it's hard coded in the plugin).
        if (!$enrol->allow_enrol($instance)) {
          $errorparams = new stdClass();
          $errorparams->courseid = $enrolment['courseidnumber'];
          throw new moodle_exception('wscannotenrol', 'enrol_manual', '', $errorparams);
        }

        //$context = context_system::instance();
        //	self::validate_context($context);

        // Throw an exception if user is not able to assign the role.
        $roles = get_assignable_roles($context);

        if (!array_key_exists(5, $roles)) {
          $errorparams = new stdClass();
          $errorparams->roleid = 'student';
          $errorparams->courseid = $enrolment['courseidnumber'];
          $errorparams->userid = $enrolment['universityid'];
          throw new moodle_exception('wsusercannotassign', 'enrol_manual', '', $errorparams);
        }
			
        //Start working enrolment config
        // Get userid from universityid
        $users = $DB->get_records_list('user', 'idnumber',array($enrolment['universityid']), 'id');

        //check user returned
        if (empty($users)) {
          $errorparams = new stdClass();
          $errorparams->universityid = $enrolment['universityid'];
           $context = context_system::instance();
					  
					  //Write event to log
							$event =\local_warwickws\event\wsevent_error::create(array(
								'context'=>$context,
								'other'=>'Unenrol failed, user does not exist. User: '.$enrolment['universityid'].' to course '.$enrolment['courseidnumber']
								//'other'=>'Unenrol no student '
									));
							$event->trigger();
		  
		  
		  
		  throw new moodle_exception('nouser', 'local_warwickws', '', $errorparams);
        }
		//check if user enrolled
											$user=reset($users);
											
											$context = context_course::instance($course->id);
											self::validate_context($context);
																										 
											$info = new completion_info($course);

											// Check this user is enroled.
											if (!$info->is_tracked_user($user->id)) {
												if ($USER->id != $user->id) {
													
													//Write event to log
													$event =\local_warwickws\event\wsevent_error::create(array(
														'context'=>$context,
														'other'=>'Cant unenrol as user not enrolled in course. User: '.$enrolment['universityid'].' not enrolled on course '.$enrolment['courseidnumber']
															));
													$event->trigger();
													
																
													
													throw new moodle_exception('usernotenroled', 'completion');
												}
											}
		
		
		$transaction = $DB->start_delegated_transaction(); // Rollback all enrolment if an error occurs
                                                           // (except if the DB doesn't support it).
		
        //iterate array enrolling each id
        foreach ($users as $item) {
          //$enrol->enrol_user($instance, $item->id, 5, 0, 0, 0, 1);
		  $enrol->unenrol_user($instance, $item->id);
        }

        $transaction->allow_commit();
        
      }

      return TRUE;
    }	
	
	
	
	
  /** Reset user dashboard */

  public static function reset_user_dashboard_parameters() {
    return new external_function_parameters(
      array(
       'users' => new external_multiple_structure(
          new external_single_structure(
            array(
              'userid' => new external_value(PARAM_INT, 'User ID to reset dashboard.'),
            )
          )
        )
      )
    );
  }

  public static function reset_user_dashboard_returns() {
    return new external_single_structure(
        array(
          'status' => new external_value(PARAM_BOOL, 'Success')
        )
    );
  }

  public static function reset_user_dashboard($users) {
    global $DB;

    $n = new stdClass;
    $n->status = TRUE;

    //Parameter validation
    //REQUIRED
    $params = self::validate_parameters(self::reset_user_dashboard_parameters(),
      array('users' => $users));

    //Capability checking
    //OPTIONAL but in most web service it should present
    //if (!has_capability('moodle/user:viewdetails', $context)) {
    //    throw new moodle_exception('cannotviewprofile');
    //}
    foreach($params['users'] as $user) {

      $u = $DB->get_record('user', array('id' => $user['userid']));

      my_reset_page($u->id);
    }

    return $n;
  }

  /** Add enrolment method */

  public static function add_enrolment_method_parameters() {
    return new external_function_parameters(
          array(
            'courseid' => new external_value(PARAM_INT, "Course ID"),
            'methodname' => new external_value(PARAM_ALPHANUM, 'Method name')
          )

      );
  }

  public static function add_enrolment_method_returns() {
    return new external_single_structure(
      array(
        'status' => new external_value(PARAM_BOOL, 'Success')
      )
    );
  }

  public static function add_enrolment_method($courseid, $methodname)
  {
    global $DB;

    $n = new stdClass();
    $n->status = TRUE;

    $params = self::validate_parameters(self::add_enrolment_method_parameters(),
      array('courseid' => $courseid, 'methodname' => $methodname));

    // Find the course
    $course = get_course($params['courseid']);

    // Find the plugin
    $plugin = enrol_get_plugin($params['methodname']);

    // If both course and plugin are valid, then let's add the default
    // instance of this enrolment method
    if($plugin && $course) {
      $instanceid = $plugin->add_default_instance($course);

      // If we managed to add an instance, let's enable it
      if( $instanceid ) {
        $instance = $DB->get_record('enrol', array('id' => $instanceid));
        if ( $instance ) {
          $plugin->update_status($instance, ENROL_INSTANCE_ENABLED);
        } else {
          $n->status = FALSE;
        }
      }

    } else {
      $n->status = FALSE;
    }

    return $n;
  }

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
