<?php

// retrieve post data from AngularJS
$data = json_decode(file_get_contents("php://input"));

// if the data is empty, don't proceed
if(!empty($data)) {

	require APP . 'model/db-model.php';
	require APP . 'model/universal.php';
	require APP . 'model/Users.php';

	$Users = new Users($this->db);

	// READ
	if($data->action == 'read') {

		$return = new stdClass();
		$return->profile = $Users->fetchUserProfile($data->username);
		$return->profileHistory = $Users->fetchUserHistory($data->username);

		print_R(json_encode($return));
	}

	// WRITE
	elseif($data->action == 'write') {

		// channel is lowercase in DB, titlecase in front-end obviously
		$data->data->channel = strtolower($data->data->channel);

		// find the parameter based on level, channel and pt/ft status
		// only for CS department currently
		if($data->data->dept == 'CS') {
			$parameter = $Users->fetchParameter($data->data->level, $data->data->channel, $data->data->pt_or_ft);
			$data->data->parameter = $parameter;
		} else {
			$data->data->pt_or_ft = null;
			// everyone but CS can be in multiple live-channels at once due to availability metrics.
			$data->data->channel = 'all';
			$data->data->level = null;
			$data->data->parameter = 0;
		}

		// before we update, let's grab the user's existing data...
		$userDetails = $Users->fetchUserProfile($data->data->username);
		
		// then save it in history
		$Users->updateUsersHistory($userDetails);

		// update the user profile
		$Users->updateProfile($data->data);
	}
}