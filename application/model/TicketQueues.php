<?php

class TicketQueues extends Universal {

    public function upsertTicketQueueCount($key, $value) {

        $query = "  INSERT INTO 
                        ticket_queue_counts
                    (
                        queue,
                        count
                    )
                    VALUES
                    (
                        :key, 
                        :value
                    )
                    ON 
                        DUPLICATE KEY 
                    UPDATE 
                        queue = :key,
                        count = :value;
        ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                ':key' => $key,
                ':value' => $value
            ));
    }
}