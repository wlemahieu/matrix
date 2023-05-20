<?php
// only leads, managers and community members can reach this data
if($_SESSION['userinfo']->type == 'Supervisor' || $_SESSION['userinfo']->type == 'Manager' || $_SESSION['userinfo']->type == 'Community') {

	require APP . 'model/db-model.php';
	require APP . 'model/universal.php';
	require APP . 'model/CustomerComments.php';
	
	$CustomerComments = new CustomerComments($this->db);

	print_R(json_encode($CustomerComments->getAfterCallSurveys()));
}