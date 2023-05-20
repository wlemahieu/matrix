<?php

class Cron extends Controller {

	// execute cron using name of php file sans .php since it's included here.
	public function execute($cron) {
		require './'.$cron;
	}

	// curl conversocial
	public function conversocialCurl($url) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, 'ainjd89ap:pe3y1g4kq275icpdo');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-type: application/json',
			'Accept: */*'));

		$payload = new stdClass();
		$payload->data = json_decode(curl_exec($ch));
		$payload->httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		return $payload;
	}

	// curl livechat
	public function livechatCurl($url) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, 'tstein@mediatemple.net:7bc118e4242a0eeb383a205e13cd6aa7');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-type: application/json',
			'Accept: */*',
			'X-API-Version:2'));
		$payload = new stdClass();
		$payload->data = json_decode(curl_exec($ch));
		$payload->httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		return $payload;
	}

	// curl astronomer
	public function astronomerCurl($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-type: application/json',
			'Accept: */*'
			));
		$payload = new stdClass();
		$payload->data = json_decode(curl_exec($ch));
		$payload->httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		return $payload;
	}

	// curl hostops
	public function hostopsCurl($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, "stats:fz974FYx");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$payload = new stdClass();
		$payload->data = json_decode(curl_exec($ch));
		$payload->httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		return $payload;
	}

	// insert query
	public function insertSQL($query, $prepArray) {
		$prep = $this->db->prepare($query);
		$prep->execute($prepArray);
		return $this->db->lastInsertId();
	}

	// run query
	public function runQuery($query) {

  		return $this->db->query($query);
  	}

  	// clean table
  	// removes records older than 1 minute.
  	public function cleanTable($table, $sleepInterval) {

  		// for existing crons, default to 1 minute since that's been the default and those won't have the $sleepInterval param
  		if(!isset($sleepInterval) || is_null($sleepInterval)) {
  			$sleepInterval = 60;
  		}

  		$query = "	DELETE
    				FROM 
    					$table
    				WHERE 
    					modified_time < now() - INTERVAL $sleepInterval SECOND ||
    					modified_time IS NULL
				";

		$statement = $this->db->prepare($query);
        $statement->execute();
  	}
}