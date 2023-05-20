<?php

// retrieve post data from AngularJS
$postdata = json_decode(file_get_contents("php://input"));

// if the postdata is empty, don't proceed
if(!empty($postdata)) {

	require APP . 'model/db-model.php';
	require APP . 'model/universal.php';
	require APP . 'model/ContributionTargets.php';

	$ContributionTargets = new ContributionTargets($this->db);

	// FETCH
	if($postdata->action == 'fetch') {

		$daysArray = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

		// only the YYYY-MM-01 is stored as `entmonth` in bi_contribution_value
		$results = $ContributionTargets->fetchContributionTargets($postdata->date);
		
		$data = new stdClass();
		$days = new stdClass();

		foreach($results as $key => $object) {

			$pt_or_ft = $object->pt_or_ft;
			$day_number = $object->day_of_week;
			$day_name = $daysArray[($day_number-1)];

			if(!isset($days->$day_name)) {
				$days->$day_name = new stdClass();
				$days->$day_name->day_number = $day_number;
			}

			$days->$day_name->$pt_or_ft = $object->contribution;
		}

		$data->days = $days;
		$check = (array)$data->days;

		// don't let them alter historical months. they can edit the current month.
		if(strtotime($postdata->date) >= strtotime(date('Y-m-01'))) {
			$data->disabled = 0;
		}
		else {
			$data->disabled = 1;
		}

		if(empty($check)) {
			$data->days = 0;
		}

		echo json_encode($data);
	}

	// CREATE
	elseif($postdata->action == 'manipulate') {

		$statuses = array('ft','pt');
		$status = 0;

		foreach($postdata->payload->days as $key => $object) {

			foreach($statuses as $key => $value) {

				$payload = new stdClass();
				$payload->entmonth = $postdata->payload->date;
				$payload->pt_or_ft = $value;
				$payload->day_of_week = $object->day_number;
				$payload->contribution = $object->$value;
				
				// don't let them alter historical months. they can edit the current month.
				if(strtotime($postdata->payload->date) >= strtotime(date('Y-m-01'))) {
					$ContributionTargets->loadContributionTarget($payload);
					$status = 1;
				}
				else{
					$status = 0;
				}
			}
		}

		// 1 = success
		echo $status;
	}
}