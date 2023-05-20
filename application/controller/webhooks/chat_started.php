<?php

$data = 'Webhook hit!';
$myfile = fopen("/var/www/development/neo2/application/controller/webhooks/chat_started.log", "w") or die("Unable to open file!");
fwrite($myfile, $data);

// read the webhook sent by LiveChat
$data = file_get_contents('php://input');
$data = json_decode($data);

$myfile = fopen("/var/www/development/neo2/application/controller/webhooks/chat_started.log", "w") or die("Unable to open file!");
fwrite($myfile, $data);