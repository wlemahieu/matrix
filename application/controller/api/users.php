<?php

// ensure only management sees this information
if($_SESSION['userinfo']->type == 'Manager' || $_SESSION['userinfo']->type == 'Supervisor') {

	// retrieve post data from AngularJS
	$postdata = json_decode(file_get_contents("php://input"));

	// if the postdata is empty, don't proceed
	if(!empty($postdata)) {

		// READ
		if($postdata->action == 'read') {

			// require models
			require APP . 'model/db-model.php';
			require APP . 'model/universal.php';
			require APP . 'model/Users.php';

			// instantiate classes
			$Users = new Users($this->db);

			// output
			echo json_encode($Users->fetch(), JSON_NUMERIC_CHECK);
		}
	}
}