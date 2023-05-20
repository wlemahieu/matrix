<?php

// make sure the cron is running the file from the directory of the file we are calling. 
// this prevents relative paths from breaking when running our absolute-path cron jobs.
chdir(dirname(__FILE__));

// db config
require '../application/config/db-config.php';

// mini core models
require '../application/core/controller.php';
require '../application/model/db-model.php';
require '../application/model/universal.php';
require '../application/model/cron.php';

//instantiate class
$Cron = new Cron();

// execute cron
$Cron->execute($argv[1]);