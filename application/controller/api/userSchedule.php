<?php

// retrieve post data from AngularJS
$data = json_decode(file_get_contents("php://input"));

// if the data is empty, don't proceed
if(!empty($data)) {

	require APP . 'model/db-model.php';
	require APP . 'model/universal.php';
	require APP . 'model/Schedules.php';

	$Schedules = new Schedules($this->db);

	// READ
	if($data->action == 'read') {
		echo json_encode($Schedules->fetch($data->username));
	}

	// WRITE
	elseif($data->action == 'write') {

		// for each day
		foreach($data->data as $obj) {

			// if empty value is passed, change to null
			foreach($obj as $key => $value) {
				if(empty($value)) {
					$obj->$key = NULL;
				}
			}

			// update the user schedule
			$Schedules->update($obj);
		}
	}
}