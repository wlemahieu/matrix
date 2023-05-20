1<?php

// retrieve post data from AngularJS
$data = json_decode(file_get_contents("php://input"));

// if the data is empty, don't proceed
if(!empty($data)) {

	require APP . 'model/db-model.php';
	require APP . 'model/universal.php';
	require APP . 'model/OAuth.php';
	
	$model = new OAuth2($this->db);
	
	// exit emulation
	if($data->username == 'exit') {

		// used for angular and php triggers
		$_SESSION['userinfo']->emulating = 0;
		// copy our original userinfo back to it's normal spot
		$_SESSION['userinfo'] = $_SESSION['userinfoOriginal'];
		// remove our extra info
		unset($_SESSION['userinfoOriginal']);
	}

	// start emulation
	else {

		// only management can do this
		if($_SESSION['userinfo']->type == 'Manager' || $_SESSION['userinfo']->type == 'Supervisor') {

			// already emulating and re-emulating without exiting? don't overwrite our original userinfo though
			if(!isset($_SESSION['userinfoOriginal'])) {
				
				// copy our original userinfo object into userinfoOriginal
				$_SESSION['userinfoOriginal'] = $_SESSION['userinfo'];
			}

			// get our new userinfo for the given user
			$_SESSION['userinfo'] = $model->userInfo($data->username);

			// CS-only parameters
			if($_SESSION['userinfo']->dept === 'CS') {
				$_SESSION['parameters'] = $model->parameters($data->username);
			}
			
			// used for angular and php triggers
			$_SESSION['userinfo']->emulating = 1;
		}
	}
}