<?php

/**
 * Returns Conversocial Users
 */
require APP . 'model/db-model.php';
require APP . 'model/universal.php';
require APP . 'model/Conversocial.php';

$Conversocial = new Conversocial($this->db);

echo json_encode($Conversocial->fetchUsers(), JSON_NUMERIC_CHECK);