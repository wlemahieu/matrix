<?php
require APP . 'model/db-model.php';
require APP . 'model/universal.php';
require APP . 'model/ClockingControl.php';
require APP . 'model/Exceptions.php';

$Exceptions = new Exceptions($this->db);
$ClockingControl = new ClockingControl($this->db);
$postdata = json_decode(file_get_contents("php://input"));

if(!empty($postdata)) {

	// set empty/undefined times as NULL for mysql
	if(empty($postdata->start)) {
		$postdata->start = NULL;
	}
	if(empty($postdata->end)) {
		$postdata->end = NULL;
	}

	// adding/editing exceptions
	if($postdata->route == 'exceptions') {

		// the lead or manager who's manipulating this exception
		$postdata->lead_username = $_SESSION['userinfo']->username;

		// is the id set? we must be updating an existing record
		if(isset($postdata->id)) {
			$Exceptions->updateException($postdata);
		}
		// brand new exception
		else {
			$Exceptions->addException($postdata);
		}
	}

	// adding/editing clocks
	elseif($postdata->route == 'clocks') {

		// is the id set? we must be updating an existing record
		if(isset($postdata->id)) {
			$ClockingControl->updateClock($postdata);
		} 
		// brand new clock
		else {
			$ClockingControl->addClock($postdata);
		}
	}
}