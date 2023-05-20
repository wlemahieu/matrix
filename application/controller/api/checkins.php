<?php

// retrieve post data from AngularJS
$data = json_decode(file_get_contents("php://input"));

// if the data is empty, don't proceed
if(!empty($data)) {

	require APP . 'model/db-model.php';
	require APP . 'model/universal.php';
	require APP . 'model/Checkins.php';
	
	$Checkins = new Checkins($this->db);

	// READ (all)
	if($data->action == 'read') {

		$payload = new stdClass();

		// only if grabbing a specific checkin
		if(isset($data->id)) {
			$payload->id = $data->id;
		} else {
			$payload->id = -1;
		}

		// restrict AGENTS to only their username
		if($_SESSION['userinfo']->type == 'Agent') {
			$payload->username = $_SESSION['userinfo']->username;
		}
		else {
			$payload->username = -1;
		}

		// restrict TEAM LEADS to only their username
		if($_SESSION['userinfo']->type == 'Supervisor') {
			$payload->lead_username = $_SESSION['userinfo']->username;
		}
		else {
			$payload->lead_username = -1;
		}

		// this if-else can go away in a few days (today is Feb 4th, 2016), because it's meant to appease anyone who has not hard-refreshed
        // we were passing in via angular start/end as individuals, but now they are a single object.
        if(isset($data->dateRange)) {
            $payload->start = date("Y-m-d", strtotime($data->dateRange->start));
            $payload->end = date("Y-m-d", strtotime($data->dateRange->end));
        } else {
            $payload->start = date("Y-m-d", strtotime($data->startRange));
            $payload->end = date("Y-m-d", strtotime($data->endRange));
        }

		// create the return results object
        $results = new stdClass();
        $results->counters = new stdClass();
        
        // line items from query above (Array of Objects)
        $results->line_items = $Checkins->getCheckins($payload);

        // build counters for checkin statuses
        $results->counters->not_presented = 0;
        $results->counters->presented = 0;
        $results->counters->accepted = 0;
        $results->counters->not_accepted = 0;
        $results->counters->incomplete = 0;

        // fields to ignore when checking for unanswered fields
        $ignoreFields = array(
            'id',
            'unanswered_fields',
            'presented_timestamp',
            'accepted_timestamp',
            'timestamp',
            'lead_username',
            'comment',
            'presented',
            'accepted',
            'completed'
        );

        // determine if there are incomplete fields
        foreach($results->line_items as $key => $obj) {

            //we are assuming the checkin is unanswered until we prove otherwise.
            $unanswered_fields = 0;

            // iterate through all fields and their values to fine any unanswered fields
            foreach($obj as $field => $value) {
                if(!in_array($field, $ignoreFields)) {
                    if($value == NULL || $value == '')  {
                        $unanswered_fields++;
                    }
                }
            }

            // no unanswered fields? mark complete.
            if($unanswered_fields === 0) {
                $completed = 1;
            }
            // mark checkin incomplete
            else  {
                $completed = 0;
                $results->counters->incomplete++;
            }

            // store completed & unanswered_fields counter
            $obj->completed = $completed;
            $obj->unanswered_fields = $unanswered_fields;
            
            // Increase Presented
            if($obj->presented == 0 && $obj->completed == 1) {
                $results->counters->not_presented++;
            }
            // Increase Presented
            elseif($obj->presented == 1 && $obj->completed == 1) {
                $results->counters->presented++;
            }
            // Increase Not Accepted
            if($obj->presented == 1 && $obj->accepted == 0) {
                $results->counters->not_accepted++;
            }
            // Increase Accepted
            elseif($obj->presented == 1 && $obj->accepted == 1) {
                $results->counters->accepted++;
            }
        }

		echo json_encode($results, JSON_NUMERIC_CHECK);
	}

	// ACCEPT (agents)
	elseif($data->action == 'accept' && $_SESSION['userinfo']->type == 'Agent') {

		$Checkins->acceptCheckin($data->id);
	}

	// PRESENTED STATUS (leadership)
	elseif($data->action == 'modify') {

		$route = $data->route; 
		$id = $data->id;

		// present check-in (leadership)
		if($route == 'present' && ( $_SESSION['userinfo']->type == 'Supervisor' || $_SESSION['userinfo']->type == 'Manager' )) {
			$Checkins->setPresentedStatus($id, 1);
		}

		// un-present check-in (leadership)
		elseif($route == 'unpresent' && ( $_SESSION['userinfo']->type == 'Supervisor' || $_SESSION['userinfo']->type == 'Manager' )) {
			$Checkins->setPresentedStatus($id, 0);
		}
	}

	// SAVE (leadership)
	elseif($data->action == 'save' && ( $_SESSION['userinfo']->type == 'Supervisor' || $_SESSION['userinfo']->type == 'Manager' )) {
		$data->payload->lead_username = $_SESSION['userinfo']->username;
		// return the checkin ID for use in the app
		echo $Checkins->upsertCheckin($data->payload);
	}
}