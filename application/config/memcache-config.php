<?php

// Connection constants
define('MEMCACHED_HOST', 'XXXXXXXXXX');
define('MEMCACHED_PORT', 'XXXXXXXXXX');
 
// Connection creation
$_SESSION['memcache'] = new Memcache;
$_SESSION['cacheAvailable'] = $_SESSION['memcache']->connect(MEMCACHED_HOST, MEMCACHED_PORT);