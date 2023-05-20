<?php

class Alerts extends Universal {

    public function fetch($data) {

        // create a unique key based on the unix timestamps added together for the start and end ranges.
        $key = 'alertMessages_'.strtotime($data->startRange)+strtotime($data->endRange);
        
        // check if key is set in memcache
        $memcache = Universal::memcacheRetrieve($key, 60);

        // if there are no results in memcache, or if the results are expired...
        if(empty($memcache->data) || $memcache->expired === true) {

            $query = "CALL alert_messages2(-1, -1, :startRange, :endRange);";

            $statement = $this->db->prepare($query);
            $statement->execute(
                array(
                    'startRange' => $data->startRange,
                    'endRange' => $data->endRange
                ));

            $data = $statement->fetchAll();

            // store in memcache
            Universal::memcacheStore($key, $data);

            $response = $data;
        }

        // we have valid results in memcache, so let's use it
        else {
            $response = $memcache->data;
        }

        return $response;
    }
}