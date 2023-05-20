<?php

// retrieve post data from AngularJS
$postdata = json_decode(file_get_contents("php://input"));

// if the postdata is empty, don't proceed
if(!empty($postdata)) {

	require APP . 'model/db-model.php';
	require APP . 'model/universal.php';
	require APP . 'model/Parameters.php';

    $Parameters = new Parameters($this->db);

    // READ
	if($postdata->action == 'read') {
		echo json_encode($Parameters->getParameters());
	}

	// UPDATE
	elseif($postdata->action == 'update') {

		// managers-only
		if($_SESSION['userinfo']->type === 'Manager') {
			
			// iterate through our post data and run the function for each row
		    foreach($postdata->parameters as $parameter_row) {
		        $Parameters->updateParameter($parameter_row);
		    }
		}
	}
}