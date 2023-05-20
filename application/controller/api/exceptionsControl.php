<?php

// retrieve post data from AngularJS
$postdata = json_decode(file_get_contents("php://input"));

// if the postdata is empty, don't proceed
if(!empty($postdata)) {

	require APP . 'model/db-model.php';
	require APP . 'model/universal.php';
	require APP . 'model/AgentNavbar.php';
	require APP . 'model/Exceptions.php';

	$AgentNavbar = new AgentNavbar($this->db);
	$Exceptions = new Exceptions($this->db);

	$command = $postdata->command;
	$mark = $postdata->mark;
	$username = $_SESSION['userinfo']->username;

	if($command == "start") {
		$Exceptions->selfInsertException($mark);
		$AgentNavbar->startNavbarException($username, $mark);
	}
	elseif($command == 'stop') {
		$Exceptions->endException($username);
		$AgentNavbar->endNavbarException($username);
	}
}