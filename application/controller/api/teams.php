<?php

// ensure only management sees this information
if($_SESSION['userinfo']->type == 'Manager' || $_SESSION['userinfo']->type == 'Supervisor') {

	// retrieve post data from AngularJS
	$payload = json_decode(file_get_contents("php://input"));
	$payload->dept = $_SESSION['userinfo']->dept;

	// if the postdata is empty, don't proceed
	if(!empty($payload)) {

		$route = $payload->action;

		// require models
		require APP . 'model/db-model.php';
		require APP . 'model/universal.php';
		require APP . 'model/Teams.php';

		// instantiate classes
		$Teams = new Teams($this->db);

		// READ
		if($route == 'read') {
			$payload->sunrise = -1;
			$results = $Teams->read($payload);
			echo json_encode($results);
		}

		// UPSERT / INSERT
		elseif($route == 'upsert') {
			
			// only managers can change teams
			if($_SESSION['userinfo']->type == 'Manager') {
				$results = $Teams->upsert($payload);
				echo json_encode($results);
			}
		}
	}
}