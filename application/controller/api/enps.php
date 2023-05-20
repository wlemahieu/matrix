<?php
if($_SESSION['userinfo']->type === 'Manager') {

	// retrieve post data from AngularJS
	$data = json_decode(file_get_contents("php://input"));

	// if the data is empty, don't proceed
	if(!empty($data)) {

		// load models
		require APP . 'model/db-model.php';
		require APP . 'model/universal.php';
		require APP . 'model/ENPS.php';

		// instantiate classes
		$ENPS = new ENPS($this->db);
		$action = $data->action;

		if($action == 'read') {

			// grab all the ENPS responses
			$return = $ENPS->fetchResponses($data->dateRange);
		}

		echo json_encode($return, JSON_NUMERIC_CHECK);
	}
}