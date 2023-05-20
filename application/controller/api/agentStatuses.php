<?php
require APP . 'model/db-model.php';
require APP . 'model/universal.php';
require APP . 'model/AgentNavbar.php';

$AgentNavbar = new AgentNavbar($this->db);

// fetch all agent navbar data, true = use memcache
$data = $AgentNavbar->fetchAgentNavbarData(true);

// iterate through results and return only my user's navbar data
foreach($data as $key => $obj) {
	if($obj->username === $_SESSION['userinfo']->username) {
		$_SESSION['userinfo']->navbar = $obj;
		echo json_encode($obj);
	}
}