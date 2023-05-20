<?php
/*
Clocking Control

This agents-only API call allows for clock-ins & clock-outs.

*/

// retrieve post data from AngularJS
$postdata = json_decode(file_get_contents("php://input"));

// if the postdata is empty, don't proceed
if(!empty($postdata)) {

	require APP . 'model/db-model.php';
	require APP . 'model/ClockingControl.php';

	$ClockingControl = new ClockingControl($this->db);

	if($postdata->command == "out") {

		// check if they are leaving early (15 min threshold) not used quite yet so commenting out
		//$results = $ClockingControl->checkIfLeavingEarly($_SESSION['userinfo']->username);

		// clock the agent out
		$ClockingControl->handleClockOut();
	}

	elseif($postdata->command == "in") {
		$ClockingControl->handleClockIn();
	}
}