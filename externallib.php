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
 * @package    localwstemplate
 * @copyright  2011 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");

class local_wstemplate_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function hello_world_parameters() {
        return new external_function_parameters(
                array('welcomemessage' => new external_value(PARAM_TEXT, 'The welcome message. By default it is "Hello world,"', VALUE_DEFAULT, 'Hello world, '))
        );
    }

    public static function get_mock_data_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_TEXT, 'The course id"', VALUE_DEFAULT, 0),
                'userid' => new external_value(PARAM_TEXT, 'The user id"', VALUE_DEFAULT, 0), ));
    }


    public static function get_mock_data_returns() {
        return new external_value(PARAM_RAW, 'JSON mock data');
    }

    public static function generateMockData($age, $population){
        $foo = new StdClass();
        $foo->age = $age;
        $foo->population = $population;
        return $foo;
    }

/*    private static function getLogReader(){
        // Get the log manager.
        $logreader = get_log_manager()->get_readers();
        $logreader = reset($logreader);
        return $logreader;
    }*/

    public static function get_bubble_data_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_TEXT, 'The course id"', VALUE_DEFAULT, 0),
                'userid' => new external_value(PARAM_TEXT, 'The user id"', VALUE_DEFAULT, 0),));
    }

    public static function get_bubble_data_returns() {
        return new external_value(PARAM_RAW, 'Returns a JSON object of all user events for a particular course');
    }

    public static function get_bubble_data($courseid, $userid){
        global $DB;

        $sql = "SELECT l.eventname, l.component, COUNT(*) as count
                  FROM m_logstore_standard_log l
                  INNER JOIN m_user u ON u.id = l.userid
                 WHERE l.courseid = $courseid
                 AND l.userid = $userid
                 GROUP BY l.component, l.eventname
                 ORDER BY count DESC"; /*"SELECT DISTINCT count(component), component, eventname
                FROM m_logstore_standard_log
                WHERE userid = $userid and courseid = $courseid";*/

        $result = $DB->get_records_sql($sql);
        $outputArray = Array();
        $i = 0;
        foreach ($result as $record) {
            $spiltArray = explode("\\", $record->eventname);
            $record->eventname = $spiltArray[sizeof($spiltArray) - 1];
            $outputArray[$i] = $record;
            $i++;
        }
        return json_encode($outputArray);
        /*$test = Array(
            local_wstemplate_external::generateMockData('<5', 2704659),
            local_wstemplate_external::generateMockData('5-13', 4499890),
            local_wstemplate_external::generateMockData('14-17', 2159981),
            local_wstemplate_external::generateMockData('18-24', 3853788),
            local_wstemplate_external::generateMockData('25-44', 14106543),
       );
       $output = json_encode($test);
       return $output;*/
    }

    public static function get_mock_data($courseid, $userid){
        global $DB;
        /*print_object($userid);*/
        $sql = "SELECT l.eventname, COUNT(*) as quant
                  FROM m_logstore_standard_log l
                  INNER JOIN m_user u ON u.id = l.userid
                 WHERE l.courseid = $courseid
                 AND l.userid = $userid
                 GROUP BY l.eventname
                 ORDER BY quant DESC";

        $result = $DB->get_records_sql($sql);
        $outputArray = Array();
        $i = 0;
        foreach ($result as $record) {
            $outputArray[$i] = $record;
            $i++;
        }
        return json_encode($outputArray);
         /*$test = Array(
             local_wstemplate_external::generateMockData('<5', 2704659),
             local_wstemplate_external::generateMockData('5-13', 4499890),
             local_wstemplate_external::generateMockData('14-17', 2159981),
             local_wstemplate_external::generateMockData('18-24', 3853788),
             local_wstemplate_external::generateMockData('25-44', 14106543),
        );
        $output = json_encode($test);
        return $output;*/
    }

    /**
     * Returns welcome message
     * @param string $welcomemessage
     * @return string welcome message
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function hello_world($welcomemessage = 'Hello world, ') {
        global $USER;

        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::hello_world_parameters(),
                array('welcomemessage' => $welcomemessage));

        //Context validation
        //OPTIONAL but in most web service it should present
        $context = context_system::instance(CONTEXT_USER, $USER->id);
        self::validate_context($context);

        //Capability checking
        //OPTIONAL but in most web service it should present
        if (!has_capability('moodle/user:viewdetails', $context)) {
            throw new moodle_exception('cannotviewprofile');
        }

        return $params['welcomemessage'] . $USER->firstname ;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function hello_world_returns() {
        return new external_value(PARAM_TEXT, 'The welcome message + user first name');
    }
}