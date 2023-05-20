<?php
// ensure only management sees this information
if($_SESSION['userinfo']->type == 'Manager' || $_SESSION['userinfo']->type == 'Supervisor') {

	// retrieve post data from AngularJS
	$postdata = json_decode(file_get_contents("php://input"));

	// if the postdata is empty, don't proceed
	if(!empty($postdata)) {

		require APP . 'model/db-model.php';
		require APP . 'model/universal.php';
		require APP . 'model/Alerts.php';
		$Alerts = new Alerts($this->db);

		// READ
		if($postdata->action == 'read') {

			// create an sql payload
			$payload = new stdClass();
			$payload->dept = $_SESSION['userinfo']->dept;
			$payload->startRange = date("Y-m-d", strtotime($postdata->dateRange->start));
		    $payload->endRange = date("Y-m-d", strtotime($postdata->dateRange->end));

		    // build return payload
		    $results = new stdClass();

			// retrieve results (sql or memcache)
			$results->line_items = $Alerts->fetch($payload);

			// only show teammates to leads - managers see all
			if($_SESSION['userinfo']->type == 'Supervisor') {

				// create a new results object
				$newResults = array();

				// iterate through results to find who's on my team
				foreach($results as $key => $array) {

					foreach($array as $nullKey => $object) {

						// is this user on my team?
						if($object->team == $_SESSION['userinfo']->team) {
							array_push($newResults, $object);
						}
					}
				}

				// overwrite results with our teammates
				$results->line_items = $newResults;
			}

			// counts
			$results->counts = array();
			$results->counts['total'] = 0;

			// per team
			foreach($results->line_items as $key => $obj) {

				if(!isset($results->counts[$obj->team])) {
					$results->counts[$obj->team] = 1;
				} else {
					$results->counts[$obj->team]++;
				}
			}

			// total
			foreach($results->counts as $key => $count) {
				$results->counts['total'] += $count;
			}

			// return data
			echo json_encode($results);
		}
	}
}