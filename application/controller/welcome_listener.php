<?php

/**
 * Google OAuth2 Callback page
 * @var OAuth2
 */

/* Google sends us back with a code. Something like   4/AmRbwVKYPylNR5flPJsA6ees2D3XP_5GPFOLz9yzEHQ   */
if(isset($_GET['code'])) {

	/* Exchange our 1-time call-back code from Google with a token */
	$_SESSION['authObj'] = $OAuth->obtainToken($client, $_GET['code']);

	/* Grab our access token from our authorization object */
	$access_token = $_SESSION['authObj']->access_token;
	$refresh_token = @$_SESSION['authObj']->refresh_token;

	/* Pass our OAuth object and grab our user object. */
	$userObject = $OAuth->googleUserInfo($oauth2);

	/* We need to store the username in a session for onboarding purposes */
	$_SESSION['authObj']->username = $userObject->username;

	/* Fetch our Matrix user data and store in a session */
	$_SESSION['userinfo'] = $OAuth->userInfo($userObject->username);

	/* Check if we have a user in the Matrix or not (1 or 0 returned) */
	$userinfoEmpty = empty($_SESSION['userinfo']);

	/* Only Existing Matrix Users */
	if($userinfoEmpty == 0) {

		// only CS users need the parameters fetched
		if($_SESSION['userinfo']->dept === 'CS' && $_SESSION['userinfo']->type === 'Agent') {
			$_SESSION['parameters'] = $OAuth->parameters($userObject->username);
		}

		/* Upon sign-in, if the refresh token obtained by Google differs from the one stored in our session, over-write the old one */
		if(@$_SESSION['authObj']->refresh_token!="" && @$_SESSION['authObj']->refresh_token != $_SESSION['userinfo']->refresh_token) {

			$OAuth->updateRefreshToken($_SESSION['userinfo']->username, $_SESSION['authObj']);
			$_SESSION['userinfo']->refresh_token = $_SESSION['authObj']->refresh_token;
		}

		/* 
		User is signing back in later after already accepting the application. Their refresh token from Google will equal nothing.
	    Grab their existing refresh token and store that into the token to grab new access tokens hourly.
	    */
	    elseif(!isset($_SESSION['authObj']->refresh_token) && $_SESSION['userinfo']->refresh_token!="") {
	    	$_SESSION['authObj']->refresh_token = $_SESSION['userinfo']->refresh_token;
	    	$refresh_token = $_SESSION['authObj']->refresh_token;
	    }

		/*
	    User is signing back in after already accepting the application, but they have no refresh token stored. 
	    It was lost or the user's token was truncated.
	    */
	    elseif(!isset($_SESSION['authObj']->refresh_token) && $_SESSION['userinfo']->refresh_token == "") {
	    	unset($_SESSION['authObj'],$_SESSION['userinfo']);
	    	session_destroy();
	    }
	}

	/*
    If it's not an @mediatemple.net domain, destroy the session, revoke the application & redirect the user.
    */
    if($userObject->domain!="mediatemple.net") {
    	unset($_SESSION['authObj'],$_SESSION['userinfo']);
    	session_destroy();
    }

	/*
    The user is not added in Matrix yet, so let's add them (username & refresh_token)
    */
    elseif($userinfoEmpty == 1) {
    	/*
     	If they already accepted the app but lost their refresh token (user removed from table), then revoke their app and send them back /w error
      	*/
     	if(is_null($refresh_token)) {

     		/* Revoke the Matrix from their Google Application's list */
     		$OAuth->revokeApplication($access_token);

     		/* Destroy their session so they are logged-out */
     		session_unset();
     		session_destroy();

     		/* Redirect to our application URI /w error */
     		header('Location: ' . filter_var('http://'.$_SERVER['HTTP_HOST'].'?error=tokenFail', FILTER_SANITIZE_URL));
     	}

     	else {
     		//$_SESSION['onboarding'] = true;
	    	/* Redirect the new person to the onboarding screen with a special token */
     		header('Location: ' . filter_var('http://'.$_SERVER['HTTP_HOST'].'/#/onboard', FILTER_SANITIZE_URL));
		}
	}

    /**
     * Domain & user checks passed! Redirect them to the Matrix for further processing (this happens anytime they sign-in.)
     */
    elseif(isset($_SESSION['userinfo'])) {

    	$_SESSION['last_interaction'] = new DateTime('now');
    	header('Location: ' . filter_var('http://'.$_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL));
    }
}

/* No google code provided? go away! */
else {
	header('Location: ' . filter_var('http://'.$_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL));
}