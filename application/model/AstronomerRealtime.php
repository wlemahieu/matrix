<?php

class AstronomerRealtime extends Universal {

    public function upsertRealtimeCaller2($data) {

        $query = "  UPDATE
                        astronomer_realtime_queues_callers
                    SET
                        joined_at = :joined_at,
                        answered_at = :answered_at,
                        call_id = :call_id,
                        caller_id_verbose = :caller_id_verbose
                    WHERE
                        callers_id = :callers_id
                ";
                
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'joined_at' => $data->joined_at,
                'answered_at' => $data->answered_at,
                'callers_id' => $data->callers_id,
                'call_id' => $data->call_id,
                'caller_id_verbose' => $data->caller_id_verbose
            ));
    }
    
    public function upsertRealtimeQueue($data) {

        $query = "  INSERT IGNORE INTO
                        astronomer_realtime_queues
                    (
                        number,
                        name,
                        longest_hold_time,
                        estimated_hold_time,
                        modified_time
                    )
                    VALUES 
                    (
                        :number,
                        :name,
                        :longest_hold_time,
                        :estimated_hold_time,
                        NOW()
                    )
                    ON
                        DUPLICATE KEY
                    UPDATE
                        number = :number,
                        name = :name,
                        longest_hold_time = :longest_hold_time,
                        estimated_hold_time = :estimated_hold_time,
                        modified_time = NOW()
                ";
                
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'number' => $data->number,
                'name' => $data->name,
                'longest_hold_time' => $data->longest_hold_time,
                'estimated_hold_time' => $data->estimated_hold_time
            ));
    }

    // update case prevents cron from over-writing user-input data.
    public function upsertRealtimeCaller($data) {

        $query = "  INSERT IGNORE INTO
                        astronomer_realtime_queues_callers
                    (
                        fk_queue_number,
                        device_number,
                        callers_id,
                        hold_time,
                        talk_time,
                        caller_id,
                        waiting,
                        account_id,
                        contact_id,
                        reason,
                        modified_time
                    )
                    VALUES 
                    (
                        :fk_queue_number,
                        :device_number,
                        :callers_id,
                        :hold_time,
                        :talk_time,
                        :caller_id,
                        :waiting,
                        :account_id,
                        :contact_id,
                        :reason,
                        NOW()
                    )
                    ON
                        DUPLICATE KEY
                    UPDATE
                        fk_queue_number = :fk_queue_number,
                        device_number = :device_number,
                        callers_id = :callers_id,
                        hold_time = :hold_time,
                        talk_time = :talk_time,
                        caller_id = :caller_id,
                        waiting = :waiting,
                        account_id = CASE WHEN :account_id IS NULL THEN account_id ELSE :account_id END,
                        contact_id = CASE WHEN :contact_id IS NULL THEN contact_id ELSE :contact_id END,
                        reason = :reason,
                        modified_time = NOW()
                ";
                
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'fk_queue_number' => $data->fk_queue_number,
                'device_number' => $data->device_number,
                'callers_id' => $data->callers_id,
                'hold_time' => $data->hold_time,
                'talk_time' => $data->talk_time,
                'caller_id' => $data->caller_id,
                'waiting' => $data->waiting,
                'account_id' => $data->account_id,
                'contact_id' => $data->contact_id,
                'reason' => $data->reason
            ));
    }

    public function upsertRealtimeMember($data) {

        $query = "  INSERT IGNORE INTO
                        astronomer_realtime_queues_members
                    (
                        fk_queue_number,
                        device_number,
                        name,
                        extension,
                        paused,
                        time_since_last_call,
                        modified_time
                    )
                    VALUES 
                    (
                        :fk_queue_number,
                        :device_number,
                        :name,
                        :extension,
                        :paused,
                        :time_since_last_call,
                        NOW()
                    )
                    ON
                        DUPLICATE KEY
                    UPDATE
                        fk_queue_number = :fk_queue_number,
                        device_number = :device_number,
                        name = :name,
                        extension = :extension,
                        paused = :paused,
                        time_since_last_call = :time_since_last_call,
                        modified_time = NOW()
                ";
                
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'fk_queue_number' => $data->fk_queue_number,
                'device_number' => $data->device_number,
                'name' => $data->name,
                'extension' => $data->extension,
                'paused' => $data->paused,
                'time_since_last_call' => $data->time_since_last_call
            ));
    }

    public function upsertPhoneDevice($data) {

        $query = "  INSERT IGNORE INTO
                        astronomer_realtime_devices
                    (
                        device_number,
                        extension,
                        name,
                        modified_time
                    )
                    VALUES 
                    (
                        :device_number,
                        :extension,
                        :name,
                        NOW()
                    )
                    ON
                        DUPLICATE KEY
                    UPDATE
                        modified_time = NOW()
                ";
                
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'device_number' => $data->device_number,
                'extension' => $data->extension,
                'name' => $data->name
            ));
    }
}