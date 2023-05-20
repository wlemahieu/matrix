<?php
/**
 * Google OAuth 2.0 Configuration
**/

/* base oauth files that came from Google */
require_once(APP . 'config/oauth/src/Google/Client.php');
require_once(APP . 'config/oauth/src/Google/Service/Oauth2.php');

/* redirect uri must match what's set in our google developer's console */
$redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].'/welcome';

/* set our application properties */
$client = new Google_Client();
$client->setApplicationName("The Matrix");
$client->setDeveloperKey("XXXXXXXXXX");
$client->setClientId('XXXXXXXXXX');
$client->setClientSecret('XXXXXXXXXX');
$client->setRedirectUri($redirect_uri);
$client->setScopes("XXXXXXXXXX");
$client->setAccessType('XXXXXXXXXX');
$client->setApprovalPrompt('XXXXXXXXXX');

/* start oauth */
$oauth2 = new Google_Service_Oauth2($client);