<?php

class Logger {

    public function logAPI($httpStatus) {

        // if we don't have a success code, log it
        if($httpStatus !== 200) {

            $log_file = 'api-fails.log';
            $log = '[' . date('Y-m-d H:i:s') .'][' . strtotime('now') .'] ' . $httpStatus . ' CURL response' . "\r\n";
            file_put_contents($log_file, $log, FILE_APPEND | LOCK_EX);
        }
    }
}