<?php

/**
 * External Web Services
 *
 * @package    localwarwickws
 * @copyright  2019 University of Warwick
 */
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . "/my/lib.php");

require_once($CFG->libdir . "/completionlib.php");
require_once($CFG->libdir . '/grouplib.php');
require_once($CFG->libdir . '/enrollib.php');
require_once($CFG->dirroot . "/user/lib.php");
require_once($CFG->dirroot . "/course/lib.php");

		
class local_warwickws_external extends external_api {

	/** Custom **/
	/** Return Course completion status **/
	
    public static function timestamp_get_course_completion_status_parameters() {
      return new external_function_parameters(
        array(
          'courseidnumber' => new external_value(PARAM_RAW, 'The WARWICK COURSE ID to verify completion for'),
          'timestamp' => new external_value(PARAM_RAW, VALUE_OPTIONAL, 'Optional unix timestamp')
        )
      );

    }

  	public static function timestamp_get_course_completion_status_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'userid' => new external_value(PARAM_INT, 'userid'),
                    'timecompleted' => new external_value(PARAM_INT, 'timecompleted'),
                    'idnumber' => new external_value(PARAM_RAW, 'idnumber'),
                )
            ), 'list of completions'
        );
    }
	
    public static function timestamp_get_course_completion_status($courseidnumber, $timestamp) {
        
		global $DB, $CFG, $USER;
		
    $params = self::validate_parameters(self::timestamp_get_course_completion_status_parameters(),
                   array('courseidnumber' => $courseidnumber, 'timestamp' => $timestamp));

     
    $courseuid=$courseidnumber;
	  $inttimestamp=(int)$timestamp;
	
	// If param not set, set timestamp to 0 (1/1/1970). **Actually not required as defaults to 0 if not set anyway.
	/*
			if (!isset($completionchecks[0]['timestamp'])) 
				{
					echo "Not set";
					$inttimestamp=0;}
			else{
					echo "Set";
					$inttimestamp=(int)$completionchecks[0]['timestamp'];
			}
		*/	
			

    // Loop through all the enrolments
	   	//foreach ($params['completionchecks'] as $enrolment) {
					
					
					//echo "In loop";
					// Find this course
					$course = $DB->get_record('course', array('idnumber' => $courseuid));
					
					$courseidint=(int)$course->id;
					//print_r($course);
					//echo "Courseiding : ".$courseidint;
					
					
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
					//$users = $DB->get_records_list('user', 'idnumber',array($enrolment['universityid']), 'id');
					
					//echo "Course is: ".$course;
					//print_r($course);
					//$sql='select userid, timecompleted from mdl_course_completions where course='.$courseidint.' and timecompleted>='.$inttimestamp;
					$sql='select mdl_course_completions.userid, mdl_course_completions.timecompleted, mdl_user.idnumber from mdl_course_completions inner join mdl_user on mdl_course_completions.userid=mdl_user.id
 where course='.$courseidint.' and timecompleted>='.$inttimestamp;

						
						//echo $sql;
						$p=$DB->get_records_sql($sql);
						//print_r($p);

						//return $info->is_course_complete($user->id);
						//return json_encode((array)$p);
						return $p;
									
      
	}       
    
	

	
	//
	// Binary return
	//
	
	
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

	public static function warwick_enrol_student_parameters() {
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

    public static function warwick_enrol_student_returns() {
      return new external_value(PARAM_BOOL, 'Success');
    }

    public static function warwick_enrol_student($enrolments) {
        
		  global $DB, $CFG, $USER;

      require_once($CFG->libdir . '/enrollib.php');
		  require_once($CFG->dirroot . "/user/lib.php");


		
		  $params = self::validate_parameters(self::warwick_enrol_student_parameters(),
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
	public static function warwick_unenrol_student_parameters() {
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

    public static function warwick_unenrol_student_returns() {
      return new external_value(PARAM_BOOL, 'Success');
    }

    public static function warwick_unenrol_student($enrolments) {
        
		  global $DB, $CFG, $USER;

      require_once($CFG->libdir . '/enrollib.php');
		  require_once($CFG->dirroot . "/user/lib.php");

			$params = self::validate_parameters(self::warwick_unenrol_student_parameters(),
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


		  $params = self::validate_parameters(self::warwick_unenrol_student_parameters(),
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
	}



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
  
  

  public static function reset_user_dashboard_returns() {
    return new external_single_structure(
        array(
          'status' => new external_value(PARAM_BOOL, 'Success')
        )
    );
  }

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
                    'id' => new external_value(PARAM_INT, 'Course ID'),
                    'name' => new external_value(PARAM_TEXT, 'Name of course'),
                    'shortname' => new external_value(PARAM_TEXT, 'Shortname of course'),
                    'idnumber' => new external_value(PARAM_TEXT, 'ID number of course'),
                    'assignments' => new external_multiple_structure(
                        new external_single_structure(
                          array(
                            'id' => new external_value(PARAM_INT, 'Assignment ID'),
                            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
                            'name' => new external_value(PARAM_TEXT, 'Name of assignment'),
                            'duedate' => new external_value(PARAM_TEXT, 'Assignment due date'),
                            'allowsubmissionsfromdate' => new external_value(PARAM_TEXT, 'Allow submissions to assignment from date'),
                            'cutoffdate' => new external_value(PARAM_TEXT, 'Assignment cut off date'),
                            'visible' => new external_value(PARAM_INT, 'Visibility'),
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

        $assignments = array();

        // Find user
        $user = $DB->get_record('user', array('idnumber' => $params['idnumber']));

        if($user) {

          $fields = 'shortname,fullname,idnumber';
          $courses = enrol_get_users_courses($user->id, true, $fields);

          foreach($courses as $course) {
            $a = new stdClass();

            $a->name = $course->fullname;
            $a->shortname = $course->shortname;
            $a->idnumber = $course->idnumber;
            $a->id = $course->id;
            $a->assignments = self::get_assignments_for_course($course->id);

            $assignments[] = $a;
          }
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
                    'visible' => $module->visible,
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

    /** Get courses */

  public static function get_list_courses_parameters() {
    return new external_function_parameters(
      array('idnumber' => new external_value(PARAM_TEXT, 'University ID number', VALUE_REQUIRED))
    );
  }

  public static function get_list_courses_returns() {
    return new external_multiple_structure(
      new external_single_structure(
        array(
          'id' => new external_value(PARAM_TEXT, 'ID course'),
          'fullname' => new external_value(PARAM_TEXT, 'Full name of course'),
          'shortname' => new external_value(PARAM_TEXT, 'Short name of course'),
          'idnumber' => new external_value(PARAM_TEXT, 'ID number of course'),
          'summary' => new external_value(PARAM_TEXT, 'Summary of the course'),
          'startdate' => new external_value(PARAM_TEXT, 'Start date'),
          'enddate' => new external_value(PARAM_TEXT, 'End date'),
          'timemodified' => new external_value(PARAM_TEXT, 'Time modified'),
          'visible' => new external_value(PARAM_TEXT, 'Visibility'),
        )
      )
    );

  }

  public static function get_list_courses($idnumber) {
    global $USER, $DB;

    //Parameter validation
    //REQUIRED
    $params = self::validate_parameters(self::get_list_courses_parameters(),
      array('idnumber' => $idnumber));

    $usercourses = array();

    // Get user
    $user = $DB->get_record('user', array('idnumber' => $params['idnumber']));

    if($user) {

      $fields = 'shortname, fullname, shortname, idnumber, summary, timemodified, startdate, enddate, visible';
      $courses = enrol_get_users_courses($user->id, true, $fields);

      // For all courses, construct a response
      foreach($courses as $course) {
        $c = new stdClass();

        $c->id = $course->id;
        $c->fullname = $course->fullname;
        $c->shortname = $course->shortname;
        $c->idnumber = $course->idnumber;
        $c->summary = $course->summary;
        $c->startdate = $course->startdate;
        $c->enddate = $course->enddate;
        $c->timemodified = $course->timemodified;
        $c->visible = $course->visible;

        $usercourses[] = $c;
      }

    }

    return $usercourses;
  }

    /** Add blocks */

    public static function course_add_block_parameters() {
         return new external_function_parameters(
           array(
             'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_REQUIRED),
             'blockname' => new external_value(PARAM_TEXT, 'Block name', VALUE_REQUIRED),
             'weight' => new external_value(PARAM_INT, 'Weight', VALUE_DEFAULT, 1)
           )
         );
    }

    public static function course_add_block_returns() {
        return new external_value(PARAM_BOOL, 'Success');
    }

    public static function course_add_block($courseid, $blockname, $weight) {
        //global $PAGE;

        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::course_add_block_parameters(),
            array('courseid' => $courseid, 'blockname' => $blockname, 'weight' => $weight));

        // Where are we going to put this block?
        $course = get_course($params['courseid']);
        $context = context_course::instance($course->id);

        // Establish page within this course
        $page = new moodle_page();
        $page->set_context($context);
        $page->set_pagelayout("course");

        // Add the block
        $defaultregion = $page->blocks->get_default_region();

        $page->blocks->add_block($blockname, $defaultregion, $params['weight'], FALSE, 'course-view-*');
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

    // Edit HTML in an existing HTML block
    public static function course_block_set_html_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_REQUIRED),
                'blockid' => new external_value(PARAM_INT, 'Block instance ID', VALUE_REQUIRED),
                'html' => new external_value(PARAM_RAW, 'Block HTML', VALUE_REQUIRED),
                'title' => new external_value(PARAM_TEXT, 'Block title', VALUE_DEFAULT, ''),
            )
        );
    }

    public static function course_block_set_html_returns() {
        return new external_value(PARAM_BOOL, 'Success');
    }

    public static function course_block_set_html($courseid, $blockid, $html, $title) {

        global $DB;

        //Parameter validation
        $params = self::validate_parameters(self::course_block_set_html_parameters(),
            array('courseid' => $courseid, 'blockid' => $blockid, 'title' => $title, 'html' => $html));

        // Find the course
        $course = get_course($params['courseid']);
        $context = context_course::instance($course->id);

        require_capability('local/warwickws:usewebservices', $context);

        // Check the block exists
        $block = $DB->get_record('block_instances', array('parentcontextid' => $context->id, 'id' => $params['blockid']));

        // If it does, then update HTML
        if($block) {
            // Build config
            $config = new stdClass();
            $config->title = $params['title'];
            $config->text = $params['html'];
            $config->format = 1;

            $DB->update_record('block_instances', ['id' => $params['blockid'],
                'configdata' => base64_encode(serialize($config)), 'timemodified' => time()]);
            return TRUE;
        }

        return FALSE;
    }


    // Context freezing

    // Courses

    public static function freeze_course_parameters() {
      return new external_function_parameters(
        array(
          'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_REQUIRED)
        )
      );
    }

    public static function freeze_course_returns() {
      return new external_single_structure(
        array(
          'status' => new external_value(PARAM_BOOL, 'Success')
        )
      );
    }

    public static function freeze_course($courseid) {
      global $DB;

      $n = new stdClass();
      $n->status = FALSE;

      // Parameter validation - check courseid
      $params = self::validate_parameters(self::freeze_course_parameters(),
        array('courseid' => $courseid));

      // Which context are we going to freeze?
      $course = get_course($params['courseid']);
      $context = context_course::instance($course->id);

      // Lock this course context
      $context->set_locked(TRUE);

      // If context is now locked, return true
      if ($context->locked) {
        $n->status = TRUE;
      }

      return $n;
    }

    public static function unfreeze_course_parameters() {
      return new external_function_parameters(
        array(
          'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_REQUIRED)
        )
      );
    }

    public static function unfreeze_course_returns() {
      return new external_single_structure(
        array(
          'status' => new external_value(PARAM_BOOL, 'Success')
        )
      );
    }

    public static function unfreeze_course($courseid) {
      global $DB;

      $n = new stdClass();
      $n->status = FALSE;

      // Parameter validation - check courseid
      $params = self::validate_parameters(self::unfreeze_course_parameters(),
        array('courseid' => $courseid));

      // Which context are we going to freeze?
      $course = get_course($params['courseid']);
      $context = context_course::instance($course->id);

      // Lock this course context
      $context->set_locked(FALSE);

      // If context is now unlocked, return true
      if (!$context->locked) {
        $n->status = TRUE;
      }

      return $n;
    }

    // Categories
    public static function freeze_category_parameters() {
      return new external_function_parameters(
        array(
          'categoryid' => new external_value(PARAM_INT, 'Category ID', VALUE_REQUIRED)
        )
      );
    }

    public static function freeze_category_returns() {
      return new external_single_structure(
        array(
          'status' => new external_value(PARAM_BOOL, 'Success')
        )
      );
    }

    public static function freeze_category($categoryid) {
      global $DB;

      $n = new stdClass();
      $n->status = FALSE;

      // Parameter validation - check courseid
      $params = self::validate_parameters(self::freeze_category_parameters(),
        array('categoryid' => $categoryid));

      // Which context are we going to freeze?
      $category = get_category_or_system_context($params['categoryid']);

      // If we've found the SITE context, do NOT proceed
      if($categoryid != 0 || $category->instanceid != 0) {
        $context = context_coursecat::instance($category->instanceid);

        // Lock this category context
        $context->set_locked(TRUE);

        // If context is now locked, return true
        if ($context->locked) {
          $n->status = TRUE;
        }
      }

      return $n;
    }

    public static function unfreeze_category_parameters() {
      return new external_function_parameters(
        array(
          'categoryid' => new external_value(PARAM_INT, 'Category ID', VALUE_REQUIRED)
        )
      );
    }

    public static function unfreeze_category_returns() {
      return new external_single_structure(
        array(
          'status' => new external_value(PARAM_BOOL, 'Success')
        )
      );
    }

    public static function unfreeze_category($categoryid) {
      global $DB;

      $n = new stdClass();
      $n->status = FALSE;

      // Parameter validation - check courseid
      $params = self::validate_parameters(self::freeze_category_parameters(),
        array('categoryid' => $categoryid));

      // Which context are we going to freeze?
      $category = get_category_or_system_context($params['categoryid']);

      // If we've found the SITE context, do NOT proceed
      if($categoryid != 0 || $category->instanceid != 0) {
        $context = context_coursecat::instance($category->instanceid);

        // Lock this category context
        $context->set_locked(FALSE);

        // If context is now locked, return true
        if (!$context->locked) {
          $n->status = TRUE;
        }
      }

      return $n;
    }


    // Suspended enrolments
    public static function remove_suspended_enrolments_parameters() {
      return new external_function_parameters(
        array(
          'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_REQUIRED),
          'userid' => new external_value(PARAM_INT, 'User ID', VALUE_DEFAULT, 0),
          'enrolname' => new external_value(PARAM_TEXT, 'Enrolment method name', VALUE_DEFAULT, 'databaseextended')
        )
      );
    }

    public static function remove_suspended_enrolments_returns() {
      return new external_single_structure(
        array(
          'unenrolments' => new external_value(PARAM_INT, 'Number of unenrolments')
        )
      );
    }

    public static function remove_suspended_enrolments($courseid, $userid, $enrolname) {
      global $DB;

      $n = new stdClass();
      $n->unenrolments = 0;

      // Parameter validation - check courseid
      $params = self::validate_parameters(self::remove_suspended_enrolments_parameters(),
        array('courseid' => $courseid, 'userid' => $userid, 'enrolname' => $enrolname));

      // Which context are we going to freeze?
      $course = get_course($params['courseid']);
      $context = context_course::instance($course->id);

      // Is extendedenrolment plugin in this course?  Get instance
      $enrol = enrol_get_plugin($params['enrolname']);

      // Get enrolment instances for this course, and select the manual enrolment...
      $instance = FALSE;
      $enrolinstances = enrol_get_instances($course->id, true);

      foreach ($enrolinstances as $courseenrolinstance) {
        if ($courseenrolinstance->enrol == $params['enrolname']) {
          $instance = $courseenrolinstance;
          break;
        }
      }

      // Delete all entries from user_enrolments and role_assignments
      if($instance) {

        // Get all suspended users
        if($params['userid'] != 0) {
          // Specific user
          $users = array();
          $users[] = core_user::get_user($params['userid']);

        } else {
          // All users
          $users = get_enrolled_users($context, '', 0, 'u.*', null, 0, 0, false);
        }

        // Loop through them...
        foreach($users as $u) {
          // If a user_enrolment entry exists for this user and instance, that is also suspended...
          if ($DB->record_exists('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$u->id, 'status'=>ENROL_USER_SUSPENDED))) {
            // ...then unenrol them...
            $enrol->unenrol_user($instance, $u->id);
            $n->unenrolments++;
          }
        }
      }

      return $n;
    }

    /** Query assignments */

    public static function query_assignments_parameters() {
       return new external_function_parameters(
           array(
               'from' => new external_value(PARAM_INT, 'From date', VALUE_REQUIRED),
               'duedate' => new external_value(PARAM_INT, 'Due date', VALUE_REQUIRED)
           )
       );
    }

    public static function query_assignments_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Assignment ID'),
                    'course' => new external_value(PARAM_INT, 'Course ID'),
                    'name' => new external_value(PARAM_TEXT, 'Assignment name'),
                    'duedate' => new external_value(PARAM_INT, 'Due date'),
                    'allowsubmissionsfromdate' => new external_value(PARAM_INT, 'Allow submission from date'),
                    'cutoffdate' => new external_value(PARAM_INT, 'Cut off date'),
                    'nosubmissions' => new external_value(PARAM_INT, 'Number of submissions'),
                )
            )
        );
    }

    public static function query_assignments($from, $duedate) {
        global $DB;

        // Parameter validation
        $params = self::validate_parameters(self::query_assignments_parameters(),
           array('from' => $from, 'duedate' => $duedate));

        // Get records and process
        $assignments = array();
        $rs = $DB->get_recordset_sql('SELECT * FROM {assign} WHERE duedate <= :duedate AND allowsubmissionsfromdate >= :from;', $params);

        // Build return data structure
        foreach($rs as $assignment) {
           $g = new stdClass();
           $g->id = $assignment->id;
           $g->name = $assignment->name;
           $g->course = $assignment->course;
           $g->duedate = $assignment->duedate;
           $g->allowsubmissionsfromdate = $assignment->allowsubmissionsfromdate;
           $g->cutoffdate = $assignment->cutoffdate;
           $g->nosubmissions = $assignment->nosubmissions;

           $assignments[] = $g;
        }

        return $assignments;
    }

    /** Query quizzes */

    public static function query_quizzes_parameters() {
       return new external_function_parameters(
           array(
               'timeopen' => new external_value(PARAM_INT, 'Time that quiz opens', VALUE_DEFAULT, 0),
               'timeclose' => new external_value(PARAM_INT, 'Time that quiz closes', VALUE_REQUIRED),
           )
       );
    }

    public static function query_quizzes_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Assignment ID'),
                    'course' => new external_value(PARAM_INT, 'Course ID'),
                    'name' => new external_value(PARAM_TEXT, 'Assignment name'),
                    'timeopen' => new external_value(PARAM_INT, 'Quiz time open'),
                    'timeclose' => new external_value(PARAM_INT, 'Quiz time close'),
                    'timelimit' => new external_value(PARAM_INT, 'Time limit'),
                    'overduehandling' => new external_value(PARAM_TEXT, 'Overdue handling'),
                    'graceperiod' => new external_value(PARAM_INT, 'Grace period'),
                    'timemodified' => new external_value(PARAM_INT, 'Time modified'),
                )
            )
        );
    }

    public static function query_quizzes($timeopen, $timeclose) {
       global $DB;

       //Parameter validation
       $params = self::validate_parameters(self::query_quizzes_parameters(),
           array('timeopen' => $timeopen, 'timeclose' => $timeclose));

       // Get records and process
       $quizzes = array();
       $rs = $DB->get_recordset_sql('SELECT * FROM {quiz} WHERE timeopen >= :timeopen AND timeclose <= :timeclose;', $params);

       // Build return data structure
       foreach($rs as $quiz) {
          $g = new stdClass();
          $g->id = $quiz->id;
          $g->name = $quiz->name;
          $g->course = $quiz->course;
          $g->timeopen = $quiz->timeopen;
          $g->timeclose = $quiz->timeclose;
          $g->timelimit = $quiz->timelimit;
          $g->overduehandling = $quiz->overduehandling;
          $g->graceperiod = $quiz->graceperiod;
          $g->timemodified = $quiz->timemodified;

          $quizzes[] = $g;
       }

       return $quizzes;
    }


    /** Query user enrolments */

    public static function query_user_enrolments_parameters() {
      return new external_function_parameters(
        array(
          'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_REQUIRED)
        )
      );
    }

    public static function query_user_enrolments_returns() {
      return new external_value(PARAM_INT, 'Last modified');
    }

    public static function query_user_enrolments($courseid) {
      global $DB;

      $lastmodified = 0;

      //Parameter validation
      $params = self::validate_parameters(self::query_user_enrolments_parameters(),
        array('courseid' => $courseid));

      // Where are we going to put this block?
      $course = get_course($params['courseid']);

      // Find the last modified date for user enrolments for this course
      $instances = $DB->get_records('enrol', array('courseid' => $course->id));
      foreach ($instances as $instance) {
        # Get latest lastmodified for this enrol plugin
        $lm = $DB->get_field('user_enrolments', 'MAX(timemodified)', array('enrolid' => $instance->id));
        if ($lm > $lastmodified) {
          $lastmodified = $lm;
        }
      }

      return $lastmodified;
    }

    /** Query staff user - comparing ID number to PRS codes */

    public static function query_staff_idnumber_parameters() {
      return new external_function_parameters(
        array(
          'universityid' => new external_value(PARAM_TEXT, 'University ID', VALUE_REQUIRED)
        )
      );
    }

    public static function query_staff_idnumber_returns() {
      return new external_single_structure(
        array(
          'prscode' => new external_value(PARAM_TEXT, 'Staff ID/PRS code'),
          'userid' => new external_value(PARAM_INT, 'Moodle user ID')
        )
      );
    }

    public static function query_staff_idnumber($universityid) {
      global $DB;

      $n = new stdClass();
      $n->prscode = '';
      $n->userid = 0;

      //Parameter validation
      $params = self::validate_parameters(self::query_staff_idnumber_parameters(),
        array('universityid' => $universityid));

      // Can we match this user?
      $sql = "SELECT id, idnumber FROM {user} WHERE idnumber LIKE :universityid";
      $params = array('universityid' => '__' . $params['universityid']);
      $user = $DB->get_record_sql($sql, $params);

      // If we have a matching user
      if($user) {
        $n->prscode = $user->idnumber;
        $n->userid = $user->id;
      }

      return $n;
    }


}

