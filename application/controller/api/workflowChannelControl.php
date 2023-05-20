<?php

// store angular POST data
$postdata = json_decode(file_get_contents("php://input"));

// ensure post data is not empty
if(!empty($postdata)) {

	// require models
	require APP . 'model/db-model.php';
	require APP . 'model/universal.php';
	require APP . 'model/ChannelControl.php';
	require APP . 'model/Parameters.php';

	// instantiate classes
	$Parameters = new Parameters($this->db);
	$ChannelControl = new ChannelControl($this->db);

	// pause, resume, logout?
	$command = $postdata->command;

	/*
	Now that Workflow has a need to tap into the ability to pause/unpause agents in their channel, 
	we need to check if an agent's dashboard is using this, or if a Manager/Lead is adjusting their channel status, etc
	*/
	if($_SESSION['userinfo']->type == 'Supervisor' || $_SESSION['userinfo']->type == 'Manager') {
		$username = $postdata->username;
		$channel = $postdata->channel;
		$asterisk_id = $postdata->asterisk;
	}
	elseif($_SESSION['userinfo']->type == 'Agent') {
		$username = $_SESSION['userinfo']->username;
		$asterisk_id = $_SESSION['userinfo']->asterisk_id;
	}

	/* find the user's default channel */
	$defaultChannel = $Parameters->getAgentChannel($username);

	// if channel as not set above, set it now that we grabbed it for the user.
	if(empty($postdata->channel)) {
		$channel = $defaultChannel;
	} 
	else {
		$channel = $postdata->channel;
	}

	// send the channel command
	$ChannelControl->channelController($username, $asterisk_id, $channel, $command);
}