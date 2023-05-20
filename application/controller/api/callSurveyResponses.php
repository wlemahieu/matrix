<?php
/*
Call Survey Responses
*/

// capture any post data coming through command-line
$postdata = json_decode(file_get_contents("php://input"));

// if HTTP $_POST is being used...
if(!empty($_POST)) {

	// build data into an object for delivery if it's coming via HTTP $_POST
	$data = new stdClass();
	$data->account = $_POST['account'];
	$data->agent = $_POST['empl'];
	$data->friendliness = $_POST['friendliness'];
	$data->accuracy = $_POST['accuracy'];
	$data->resolution = $_POST['resolution'];
	$data->satisfaction = $_POST['satisfaction'];
	$data->comment = $_POST['comment'];
	$data->timestamp = $_POST['timestamp'];
	$data->totalaccounts = $_POST['totalaccounts'];
}
// if HTTP $_POST is not being used...
elseif(!empty($postdata)) {
	$data = $postdata;
}
// if no data is passed at all...
else {
	unset($data);
}

// if the postdata is empty, don't proceed
if(isset($data)){

	require APP . 'model/db-model.php';
	require APP . 'model/universal.php';
	require APP . 'model/CallSurveyResponses.php';

	$CallSurveyResponses = new CallSurveyResponses($this->db);

	$CallSurveyResponses->saveResponses($data);
	$CallSurveyResponses->saveTotalAccounts($data->totalaccounts);
} else {
	echo 'The payload appears to be empty?';
}