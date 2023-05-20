<?php

/**
 * (╯°□°)╯︵ ┻━┻
 * @author Wesley LeMahieu <wlemahieu@mediatemple.net>
 */

class Universal extends DB {

    /*
     * $key is a unique memcache key. 
     * $threshold is for expiration and is in seconds.
     * This function checks if a certain unique key exists in memcache which means there is a value stored.
     * If there is a value, check if it's expired already by comparing the elapsed time since retrieval the data is.
     * If the elapsed timed is greater than the acceptable amount of time passed in as a parameter, it's expired.
     *
    */
    public static function memcacheRetrieve($key, $threshold) {

        $cacheObject = $_SESSION['memcache']->get($key);

        $response = new stdClass();
       
        if(!empty($cacheObject->data)) {
            if(strtotime('now') - $cacheObject->stored_unixtime >= $threshold) {
                $response->data = false;
                $response->expired = true;
            } else {
                $response->data = $cacheObject->data;
                $response->expired = false;
            }
        }

        return $response;
    }

    /*
     * Store the key and it's data in memcache
     */
    public static function memcacheStore($key, $data) {

        $cacheObject = new stdClass();
        $cacheObject->data = $data;
        $cacheObject->stored_unixtime = strtotime('now');

        $_SESSION['memcache']->set($key, $cacheObject);
    }
}