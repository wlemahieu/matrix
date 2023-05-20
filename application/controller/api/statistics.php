<?php

// retrieve post data from AngularJS
$data = json_decode(file_get_contents("php://input"));

// if the data is empty, don't proceed
if(!empty($data)) {

    require APP . 'model/db-model.php';
    require APP . 'model/universal.php';

    require APP . 'model/Attendance.php';
    require APP . 'model/CustomerComments.php';
    require APP . 'model/SalesBonus.php';
    require APP . 'model/StoredProcedures.php';

    $Attendance = new Attendance($this->db);
    $CustomerComments = new CustomerComments($this->db);
    $SalesBonus = new SalesBonus($this->db);
    $StoredProcedures = new StoredProcedures($this->db);

    if($data->action == 'read') {

        // format the start and end dates for the period to be mysql-friendly
        $dateObj = new DateTime($data->dateRange->start);
        $data->dateRange->start = $dateObj->format('Y-m-d');
        $dateObj = new DateTime($data->dateRange->end);
        $data->dateRange->end = $dateObj->format('Y-m-d');

        $day = new DateTime('today');
        $today = $day->format('Y-m-d');
        $thirtyDaysAgoToday = $day->sub(new DateInterval("P30D"))->format('Y-m-d');
        $dept = $_SESSION['userinfo']->dept;
        $stats = new stdClass();

        switch($_SESSION['userinfo']->type) {
            
            // agent is loading their dashboard
            case 'Agent':

                // pull data for today and the period
                $metrics_today = $StoredProcedures->displayMetrics('username', -1, -1, $dept, 1, $today, $today, 'fetchAll');
                $metrics_period = $StoredProcedures->displayMetrics('username', -1, -1, $dept, 1, $data->dateRange->start, $data->dateRange->end, 'fetchAll');

                // fetch my 'today' data from memcache results
                foreach($metrics_today as $key => $object) {
                    if($object->username == $_SESSION['userinfo']->username) {
                        $stats->today = $object;

                    }
                }
                // fetch my 'pay period' data from memcache results
                foreach($metrics_period as $key => $object) {
                    if($object->username == $_SESSION['userinfo']->username) {
                        $stats->period = $object;
                    }
                }

                // attendance
                $obj = new stdClass();
                $obj->scheduled = $Attendance->getScheduledAttendance($thirtyDaysAgoToday, $today, $_SESSION['userinfo']->username, -1, -1);
                $obj->unscheduled = $Attendance->getUnscheduledAttendance($thirtyDaysAgoToday, $today, $_SESSION['userinfo']->username, -1, -1);
                $obj->total = new stdClass();
                $obj->total->late = $obj->scheduled->late->total + $obj->unscheduled->late->total;
                $obj->total->absent = $obj->scheduled->absent->total + $obj->unscheduled->absent->total;
                $stats->attendance = $obj;

                // customer comments
                $stats->comments = $CustomerComments->getComments($data->dateRange->start, $data->dateRange->end, $_SESSION['userinfo']->username);
                
                // this isn't always set immediately. memcache may not return results for a newly created agent so to avoid an error...
                if(isset($stats->period)) {
                    // sales stuff
                    $obj = new stdClass();
                    $obj->tier_multiplier = $stats->period->tier_multiplier;
                }

                // sales line items
                $obj->line_items = $StoredProcedures->getSalesLineItems($data->dateRange->start, $data->dateRange->end, $_SESSION['userinfo']->username);

                // actual dept sales amount
                $obj->dept_goal_amount = $SalesBonus->getDeptGoalAmount($_SESSION['userinfo']->dept);

                // expected dept sales amount (cs-only)
                if($_SESSION['userinfo']->dept === 'CS') {
                    $obj->dept_goal_expected = $SalesBonus->getDeptGoalExpected($_SESSION['userinfo']->dept);
                    if($obj->dept_goal_expected->amount > 0) {
                        $obj->dept_goal_completed = $obj->dept_goal_amount->amount / $obj->dept_goal_expected->amount * 100;
                    }
                }

                $stats->sales = $obj;

            break;

            // a lead is loading their dashboard
            case 'Supervisor':

                // floor metrics
                $stats->floor = $StoredProcedures->displayMetrics('dept', -1, -1, $dept, 1, $data->dateRange->start, $data->dateRange->end, 'fetch');

                // team metrics
                $obj = $StoredProcedures->displayMetrics('team', -1, -1, $dept, 1, $data->dateRange->start, $data->dateRange->end, 'fetchAll');
                
                // fetch only my team from the results
                foreach($obj as $key => $object) {
                    if($object->groupby == $_SESSION['userinfo']->team) {
                        $stats->team = $object;
                    }
                }

                // team metrics grouped by username
                $obj = $StoredProcedures->displayMetrics('username', -1, -1, $dept, 1, $data->dateRange->start, $data->dateRange->end, 'fetchAll');
                
                $agentsObject = array();
                // fetch only my team's agents from the results
                foreach($obj as $key => $object) {
                    if($object->team == $_SESSION['userinfo']->team) {
                        array_push($agentsObject, $object);
                    }
                }
                $stats->agents = $agentsObject;

            break;

            // a manager is loading their dashboard
            case 'Manager':

                // floor metrics
                $stats->floor = $StoredProcedures->displayMetrics('dept', -1, -1, $dept, 1, $data->dateRange->start, $data->dateRange->end, 'fetch');

                // team metrics
                $stats->teams = $StoredProcedures->displayMetrics('team', -1, -1, $dept, 1, $data->dateRange->start, $data->dateRange->end, 'fetchAll');

                // user metrics by user
                $stats->agents = $StoredProcedures->displayMetrics('username', -1, -1, $dept, 1, $data->dateRange->start, $data->dateRange->end, 'fetchAll');
            break;
        }

        echo json_encode($stats, JSON_NUMERIC_CHECK);
    }
}