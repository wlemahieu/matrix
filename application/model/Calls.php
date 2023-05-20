<?php

class Calls extends Universal {

    public function updateCallAccountContactID($data) {

        $query = "  UPDATE
                        calls
                    SET
                        contact_id = :contact_id,
                        account_id = :account_id
                    WHERE
                        call_id = :call_id
                ";
                
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'contact_id' => $data->contact_id,
                'account_id' => $data->account_id,
                'call_id' => $data->call_id
            ));
    }
    
    public function upsertCallsUnanswered($data) {

        $query = "  INSERT INTO 
                        calls_unanswered
                    (
                        date, 
                        queue, 
                        hour, 
                        total
                    )
                    VALUES
                    (
                        :date, 
                        :queue, 
                        :hour, 
                        :total
                    )
                    ON 
                        DUPLICATE KEY 
                    UPDATE 
                        total = :total
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'date' => $data->date,
                'queue' => $data->queue,
                'hour' => $data->hour,
                'total' => $data->total
            ));
    }
    
    public function upsertPhoneCall($data) {

        $query = "  INSERT INTO
                        calls
                    (
                        call_id,
                        asterisk_id,
                        username,
                        team,
                        dept,
                        queue_number,
                        started,
                        ended,
                        hold_time,
                        handle_time
                    )
                    SELECT
                        :call_id as call_id,
                        :asterisk_id as asterisk_id,
                        users.username as username,
                        users.team as team,
                        users.dept as dept,
                        :queue_number as queue_number,
                        :started as started,
                        :ended as ended,
                        :hold_time as hold_time,
                        :handle_time as handle_time
                    FROM
                        users
                    WHERE
                        asterisk_id = :asterisk_id
                    ON
                        DUPLICATE KEY
                    UPDATE
                        username = users.username,
                        team = users.team,
                        dept = users.dept,
                        queue_number = :queue_number,
                        hold_time = :hold_time,
                        handle_time = :handle_time,
                        ended = :ended
                ";
                
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'call_id' => $data->call_id,
                'asterisk_id' => $data->asterisk_id,
                'queue_number' => $data->queue_number,
                'started' => $data->started,
                'ended' => $data->ended,
                'hold_time' => $data->hold_time,
                'handle_time' => $data->handle_time,
            ));
    }
}