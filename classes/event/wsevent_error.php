<?php
// This file is part of Moodle - http://moodle.org/
//
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
 * The EVENTNAME event.
 *
 * @package    FULLPLUGINNAME
 * @copyright  2014 YOUR NAME
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_warwickws\event;
defined('MOODLE_INTERNAL') || die();

//echo ($CFG->dirroot . "/config.php");

//require_once($CFG->dirroot . "/config.php");
//require_once(__DIR__ . '/../../config.php');
//require_once('/var/www/html/config.php');

/**
 * The EVENTNAME event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - PUT INFO HERE
 * }
 *
 * @since     Moodle MOODLEVERSION
 * @copyright 2014 YOUR NAME
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class wsevent_error extends \core\event\base {
    protected function init() {
        
		$this->data['crud'] = 'c'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        //$this->data['other'] = 'Other blah blah';
		//$this->data['other']
		//$this->data['objecttable'] = '...';
		//$this->data['contextid'] = 1;
		//$context = context_system::instance();
		//$context=get_context();
		//$context = context_system::instance();
		//$this->contextid=1;
		//$systemcontext = context_system::instance();
		//$context = context::instance_by_id(1);
		//print_r($this);
		//$systemcontext = context::instance();
    }
 
    public static function get_name() {
        return get_string('wsevent', 'local_warwickws');
		//return 'wsevent';
    }
 
    public function get_description() {
        //return "The user with id {$this->courseid} created ... ... ... with id {$this->objectid}.";
		return "Data supplied to webservice invalid : {$this->other}";
    }
 
    public function get_url() {
        //return new \moodle_url('....', array('parameter' => 'value', ...));
		return new \moodle_url('giraffe', array('parameter' => 'value'));
    }
 
    public function get_legacy_logdata() {
        // Override if you are migrating an add_to_log() call.
        return array($this->courseid, 'PLUGINNAME', 'LOGACTION',
            '...........',
            $this->objectid, $this->contextinstanceid);
    }
 
    public static function get_legacy_eventname() {
        // Override ONLY if you are migrating events_trigger() call.
        return 'MYPLUGIN_OLD_EVENT_NAME';
    }
 
    protected function get_legacy_eventdata() {
        // Override if you migrating events_trigger() call.
        $data = new \stdClass();
        $data->id = $this->objectid;
        $data->userid = $this->relateduserid;
        return $data;
    }
}