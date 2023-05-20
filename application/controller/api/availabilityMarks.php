<?php

// retrieve post data from AngularJS
$postdata = json_decode(file_get_contents("php://input"));

// if the postdata is empty, don't proceed
if(!empty($postdata)) {

	require APP . 'model/db-model.php';
	require APP . 'model/universal.php';
	require APP . 'model/Availability.php';

	$Availability = new Availability($this->db);

	$username = $postdata->username;
	$startRange = date("Y-m-d", strtotime($postdata->startRange));
	$endRange = date("Y-m-d", strtotime($postdata->endRange));

	echo json_encode($Availability->getAvailabilityByDay($username, $startRange, $endRange), JSON_NUMERIC_CHECK);
}