<?php
class OAuth2 extends Universal {
	/**
	 * Revokes the Matrix Application from the user's Google Account for them
	 * This is used in case we there are issues with the user's refresh_token.
	 * @param  [type] $access_token [description]
	 * @return [type]               [description]
	 */
	public function revokeApplication($access_token) {
     	$url = 'https://accounts.google.com/o/oauth2/revoke?token='.$access_token;
     	$curl = curl_init();
     	curl_setopt($curl, CURLOPT_URL, $url);
     	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
     	curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
     	curl_exec($curl);
     	curl_close($curl);
	}

	/**
	 * Obtains an access token when signing in using the code that google sends us back with 
	 * Specifically when we're sent back with $_GET['code'] when hitting the welcome_listener.php controller
	 * @param  [type] $client [description]
	 * @param  [type] $code   [description]
	 * @return [type]         [description]
	 */
	public function obtainToken($client, $code) {
		$client->authenticate($code);
    	return json_decode($client->getAccessToken());
	}

	/**
	 * [googleUserInfo description]
	 * @param  [type] $oauth2 [description]
	 * @return [type]         [description]
	 */
	public function googleUserInfo($oauth2) {

		$user = $oauth2->userinfo->get();

		$userData = new stdClass();
	    $userData->domain = $user->hd;
	    $userData->email = $user->email;
	    $x = explode("@",$userData->email);
	   	$userData->username = $x[0];

	   	return $userData;
	}

	// fetch a single user's info from `users`
	public function userInfo($username) {

        $query = "  SELECT 
                        id,
                        username,
                        type,
                        parameter,
                        dept,
                        team,
                        hostops_id,
                        chat_id,
                        asterisk_id,
                        first_name,
                        last_name,
                        active,
                        refresh_token,
                        0 as emulating
                    FROM 
                        users
                    WHERE 
                        username = :username 
                    LIMIT 
                        1
        		";

		$statement = $this->db->prepare($query);
		$statement->execute(array('username' => $username));

		return $statement->fetch();
	}

	// fetch a CS user's info from `parameters`
	public function parameters($username) {

        $query = "  SELECT 
                        level,
                        channel,
                        pt_or_ft,
                        wiggle,
                        contacts_per_day_min,
                        contacts_per_day_bonus,
                        customer_satisfaction_min,
                        customer_satisfaction_bonus,
                        attendance_min,
                        attendance_bonus,
                        availability_min,
                        availability_bonus,
                        program_adherance_min,
                        program_adherance_bonus
                    FROM 
                        parameters a
                    LEFT JOIN
                    	users b
                    ON
                    	a.id = b.parameter
                    WHERE 
                        b.username = :username 
                    LIMIT 
                        1
        		";

		$statement = $this->db->prepare($query);
		$statement->execute(array('username' => $username));

		return $statement->fetch();
	}

	/**
	 * Update the user's refresh token and creation date
	 * This is generally used if there are errors and the refresh token needs to be replaced.
	 * @param  [type] $username [description]
	 * @param  [type] $authObj  [description]
	 * @return [type]           [description]
	 *
     */
	public function updateRefreshToken($username, $authObj) {

		$query = "	UPDATE 
						matrix.users 
					SET 
						refresh_token = :refresh_token,
						refresh_token_creation_date = FROM_UNIXTIME(:refresh_token_creation_date)
					WHERE 
						username = :username 
					LIMIT 
						1
				";

		$statement = $this->db->prepare($query);
		$statement->execute(
			array(
				'username' => $username,
				'refresh_token' => $authObj->refresh_token,
				'refresh_token_creation_date' => $authObj->created
				)
		);
	}

	/**
	 * Obtains a new access token
	 * Generally used when the access token expires
	 * @param  [type] $client [description]
	 * @return [type]         [description]
	 *
     */
	public function newtoken($client) {

		/** Use the user's refresh token to get a new access token from Google **/
		$client->refreshToken($_SESSION['userinfo']->refresh_token);

		/** Store this new token data into a $_SESSION for manipulation. **/
		$_SESSION['token'] = $client->getAccessToken();
		$_SESSION['authObj'] = json_decode($_SESSION['token']);
        $_SESSION['authObj']->refresh_token = $_SESSION['userinfo']->refresh_token;
		$_SESSION['token'] = json_encode($_SESSION['authObj']);

		/** Send our token back to Google to verify it's validity **/
		$_SESSION['result'] = $client->setAccessToken($_SESSION['token']);
	}
}