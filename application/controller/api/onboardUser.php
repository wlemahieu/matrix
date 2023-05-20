<?php

// retrieve post data from AngularJS
$postdata = json_decode(file_get_contents("php://input"));

// if the postdata is empty, don't proceed
if(!empty($postdata)) {

	require APP . 'model/db-model.php';
	require APP . 'model/universal.php';
	require APP . 'model/Onboarding.php';

	$Onboarding = new Onboarding($this->db);
	
	// if they are not an agent, force their department to be 'Other'
	if($postdata->type != 'Agent') {
		$postdata->dept = 'Other';
	}

	// only CS has parameters
	if($postdata->dept == 'CS') {

		// define parameter based on full-time or part-time
		if($postdata->status == 'ft') {
			$postdata->parameter = 5;
		} 
		else {
			$postdata->parameter = 17;
		}

		// default to training team
		$postdata->team = 'Training';

	} 
	else if($postdata->dept == 'CT') {
		$postdata->parameter = 0;
		$postdata->team = 'CloudTech';
	}
	else if($postdata->dept == 'Sales') {
		$postdata->parameter = 0;
		$postdata->team = 'Sales Team 1';
	}

	// fetch username and refresh token from OAuth
	$postdata->username = $_SESSION['authObj']->username;
	$postdata->refresh_token = $_SESSION['authObj']->refresh_token;

	// create the new user
	$uid = $Onboarding->onboardUser($postdata);

	// populate their schedule rows if their new user was added. otherwise, don't try.
	if($uid != 0) {	
		$Onboarding->populateSchedule($uid, $postdata->username);
	}

	// return the UID for building schedules
	echo $uid;
}