<?php

class CallerInfo extends Universal {

    public function fetchCallerInfo($asterisk_id) {

        $query = "  SELECT
                        *,
                        UNIX_TIMESTAMP(joined_at) as joined_at_unix,
                        UNIX_TIMESTAMP(answered_at) as answered_at_unix
                    FROM
                        astronomer_realtime_queues_callers
                    WHERE 
                        device_number = :device_number
                ";
                
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'device_number' => $asterisk_id
            ));

        return $statement->fetch();
    }

    public function updateAccountAndContact($data) {

        // update realtime calls table
        $query1 = "  UPDATE
                        astronomer_realtime_queues_callers
                    SET
                        account_id = :account_id,
                        contact_id = :contact_id
                    WHERE
                        device_number = :device_number
                ";
                
        $statement = $this->db->prepare($query1);
        $statement->execute(
            array(
                'account_id' => $data->account_id,
                'contact_id' => $data->contact_id,
                'device_number' => $data->device_number
            ));

        // update historical calls table
        $query2 = "  UPDATE
                        calls
                    SET
                        account_id = :account_id,
                        contact_id = :contact_id
                    WHERE
                        call_id = :call_id
                ";
                
        $statement = $this->db->prepare($query2);
        $statement->execute(
            array(
                'account_id' => $data->account_id,
                'contact_id' => $data->contact_id,
                'call_id' => $data->call_id
            ));
    }
}