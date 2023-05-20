<?php

class StoredProcedures extends Universal {

    public function displayMetrics($groupby, $username, $teamname, $department, $active, $startrange, $endrange, $fetchType) {

        // create a unique key based on all parameters
        $key = 'displayMetrics_'.$groupby.$username.$teamname.$department.$active.$startrange.$endrange.$fetchType;
        
        //echo $key;

        // store the response from our memcache function on a 30-second expiration timer.
        $memcache = Universal::memcacheRetrieve($key, 30);
        
        // if there are no results in memcache, or if the results are expired.
        if(empty($memcache->data) || $memcache->expired === true) {

            //echo 'NEW CALL';
            $query = "CALL metrics(:groupby, :username, :teamname, :department, :active, :startrange, :endrange);";

            $preparray = 
                array(
                    ':groupby' => $groupby,
                    ':username'=> $username,
                    ':teamname' => $teamname,
                    ':department' => $department,
                    ':active' => $active,
                    ':startrange' => $startrange,
                    ':endrange' => $endrange
                );

            $statement = $this->db->prepare($query);
            $statement->execute($preparray);

            /**
             * Fetch Type
             * We don't need to fetchAll for when we are only expecting a single row of results.
             * So to keep our code clean and free from pointless iterative loops for single records, we instantiate our fetch type in the metrics call.
             */
            switch($fetchType) {
                case 'fetch':
                    $data = $statement->fetch();
                break;
                case 'fetchAll':
                    $data = $statement->fetchAll();
                break;
            }

            // store in memcache
            Universal::memcacheStore($key, $data);

            // build response
            $response = $data;
        }

        // we have valid results in memcache, so let's use it
        else {
            $response = $memcache->data;
            //print_R($response);
        }

        return $response;
    }

    public function getSalesLineItems($startrange, $endrange, $username) {

        $query = "CALL sales_detail(:username, :startrange, :endrange);";

        $preparray = array(':startrange' => $startrange,
                           ':endrange' => $endrange,
                           ':username' => $username);

        $statement = $this->db->prepare($query);
        $statement->execute($preparray);

        return $statement->fetchAll(PDO::FETCH_OBJ);
    }
}