<?php

// retrieve post data from AngularJS
$data = json_decode(file_get_contents("php://input"));

// if the data is empty, don't proceed
if(!empty($data)) {

	require APP . 'model/db-model.php';
	require APP . 'model/universal.php';
	require APP . 'model/Exceptions.php';

	$Exceptions = new Exceptions($this->db);

	// ensure agents can only see their own exceptions
	if($_SESSION['userinfo']->type == 'Agent') {
		$data->username = $_SESSION['userinfo']->username;
	}

	// this if-else can go away in a few days (today is Feb 4th, 2016), because it's meant to appease anyone who has not hard-refreshed
	// we were passing in via angular start/end as individuals, but now they are a single object.
	if(isset($data->dateRange)) {
		$data->startRange = date("Y-m-d", strtotime($data->dateRange->start));
		$data->endRange = date("Y-m-d", strtotime($data->dateRange->end));
	} else {
		$data->startRange = date("Y-m-d", strtotime($data->startRange));
		$data->endRange = date("Y-m-d", strtotime($data->endRange));
	}

	echo json_encode($Exceptions->getAttendanceExceptions($data), JSON_NUMERIC_CHECK);
}