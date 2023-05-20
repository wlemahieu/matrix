<?php

class AgentNavbar extends Universal {

    public function fetchAgentNavbarData($use_cache) {

        // should we utilize memcache? crons dont, everything else should.
        if($use_cache) {

            // create a unique key
            $key = 'fetchAgentNavbarData';
            
            // check if key is set in memcache within the last 3 seconds.
            $memcache = Universal::memcacheRetrieve($key, 3);
        }
        // not using cache, emulate expired memcache key
        else {
            $memcache = new stdClass();
            $memcache->expired = true;
        }

        // if there are no results in memcache, or if the results are expired...
        if(empty($memcache->data) || $memcache->expired === true) {

            $query = "  SELECT 
                            *
                        FROM 
                            agent_navbar_data";

            $data = $this->db->query($query)->fetchAll();

            if($use_cache) {
                // store in memcache
                Universal::memcacheStore($key, $data);
            }

            $response = $data;
        }

        // we have valid results in memcache, so let's use it
        else {
            $response = $memcache->data;
        }

        return $response;
    }

    public function upsertAgentNavbarData($data) {

        $query = "  INSERT IGNORE INTO 
                        agent_navbar_data
                    (
                        modified_time,
                        username,
                        asterisk_id,
                        dept,
                        chats_status,
                        phones_status,
                        tickets_status,
                        default_channel,
                        live_channel,
                        currently_clocked_in,
                        current_exception
                    )
                    VALUES
                    (
                        NOW(),
                        :username,
                        :asterisk_id,
                        :dept,
                        :chats_status,
                        :phones_status,
                        :tickets_status,
                        :default_channel,
                        :live_channel,
                        :currently_clocked_in,
                        :current_exception
                    )
                    ON 
                        DUPLICATE KEY 
                    UPDATE
                        modified_time = NOW(),
                        username = CASE WHEN :username IS NULL THEN username ELSE :username END,
                        asterisk_id = CASE WHEN :asterisk_id IS NULL THEN asterisk_id ELSE :asterisk_id END,
                        dept = CASE WHEN :dept IS NULL THEN dept ELSE :dept END,
                        chats_status = CASE WHEN :chats_status IS NULL THEN chats_status ELSE :chats_status END,
                        phones_status = CASE WHEN :phones_status IS NULL THEN phones_status ELSE :phones_status END,
                        tickets_status = CASE WHEN :tickets_status IS NULL THEN tickets_status ELSE :tickets_status END,
                        default_channel = CASE WHEN :default_channel IS NULL THEN default_channel ELSE :default_channel END,
                        last_channel = CASE WHEN :last_channel IS NULL THEN last_channel ELSE :last_channel END,
                        live_channel = CASE WHEN :live_channel IS NULL THEN live_channel ELSE :live_channel END,
                        currently_clocked_in = CASE WHEN :currently_clocked_in IS NULL THEN currently_clocked_in ELSE :currently_clocked_in END,
                        current_exception = CASE WHEN :current_exception IS NULL THEN current_exception ELSE :current_exception END
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'username' => $data->username,
                'asterisk_id' => $data->asterisk_id,
                'dept' => @$data->dept,
                'chats_status' => @$data->chats_status,
                'phones_status' => @$data->phones_status,
                'tickets_status' => @$data->tickets_status,
                'default_channel' => @$data->default_channel,
                'live_channel' => @$data->live_channel,
                'last_channel' => @$data->last_channel,
                'currently_clocked_in' => @$data->currently_clocked_in,
                'current_exception' => @$data->current_exception
                )
            );
    }

    public function endNavbarException($username) {

        $query = "  UPDATE
                        agent_navbar_data
                    SET
                        current_exception = NULL
                    WHERE
                        username = :username
                ";
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'username' => $username
                )
            );
    }

    public function startNavbarException($username, $currentException) {

        $query = "  UPDATE
                        agent_navbar_data
                    SET
                        modified_time = NOW(),
                        current_exception = :currentException
                    WHERE
                        username = :username
                ";
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'username' => $username,
                'currentException'=> $currentException
                )
            );
    }

    // all ticket agents that are clocked-in to the ticket queue
    public function fetchActiveTicketAgents() {

        $query = "  SELECT
                        agent_username,
                        clocked_in,
                        clocked_out
                    FROM
                        ticket_queue_clocks
                    WHERE
                        clocked_out IS NULL
                ";

        return $this->db->query($query)->fetchAll();
    }
}