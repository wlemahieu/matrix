<?php

/**
 * LISTENS FOR $_GET or $_POST REQUESTS
 * newToken is used for obtaining a new token when things go wrong.
 * authUrl is used to create the url for signing in to the Matrix.
 * Check if they are logged-in then delete the authUrl, and estimate roughly when their Google access token expires.
 * If it's expired, obtain a new access token using the refresh token saved in the `users` table
 */

/* detect user logout requests */
if(isset($_GET['logout'])){

	if(!empty($_SESSION['userinfo'])) {

		if($_SESSION['userinfo']->type == 'Agent' && $_SESSION['parameters']->channel == 'tickets') {

			// are they clocked in? if so, clock them out
		    $count = $ClockingControl->checkClockedIn($_SESSION['userinfo']->username);

		    if($count == 1) {
		        $ClockingControl->clockOut($_SESSION['userinfo']->username);
		    }
		}
	}

	session_unset();
	session_destroy();
	header('Location: http://'.$_SERVER['HTTP_HOST']);

}

/* expose session data upon request */
if(isset($_GET['session'])) {
	?><h2>Session Data</h2><pre><?print_R($_SESSION);?></pre><?
}

/* catch new token requests from oauth if needed */
if(isset($_GET['newToken'])) {	
	$OAuth->newtoken($client);
}

/* create authorization URL used in header.php IF NOT LOGGED IN */
if(!isset($_SESSION['userinfo'])) {
	$_SESSION['authUrl'] = $client->createAuthUrl();
}

/* refresh last agent browser ineraction timestamp and find when the oauth access token is expiring to get a new one */
else {

	/* anytime an agent refreshes, update this timestamp */
	$_SESSION['last_interaction'] = new DateTime('now');

	unset($_SESSION['authUrl']);
	$expirationUnix = ($_SESSION['authObj']->created)+($_SESSION['authObj']->expires_in);
	$_SESSION['expires_in'] = $expirationUnix-time();

	/* Check if our access token is going to expire within a minute (could be set to 0 too), if so, get a new one using our refresh token. **/
	if($_SESSION['expires_in']<=60) {
		$OAuth->newtoken($client);
	}
}