<?php

/**
 * Gather any PHP Session data we have, as well as the past 12 pay periods, and selected date range.
 */
 
// connect to database
require APP . 'model/db-model.php';

// load universal model
require APP . 'model/universal.php';

$return = new stdClass();

// fetch user info or the authorization url for logging-in
if(!empty($_SESSION['userinfo'])){
    $return->userInfo = $_SESSION['userinfo'];
    if($_SESSION['userinfo']->dept === 'CS' && $_SESSION['userinfo']->type === 'Agent') {
    	$return->parameters = $_SESSION['parameters'];
    }
} else {
	if(!empty($_SESSION['authUrl'])) {
		$return->authUrl = $_SESSION['authUrl'];
	}
}

// return any PDOExceptions (broken DB connections)
$return->PDOExceptions = $_SESSION['PDOException'];

echo json_encode($return, JSON_NUMERIC_CHECK);