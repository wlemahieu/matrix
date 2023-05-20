<?php

class Availability extends Universal {

    public function getAvailabilityByDay($username, $startRange, $endRange) {
        
        $query = "  SELECT
                        entdate,
                        SUM(in_queue_time) as in_queue_time,
                        SUM(IFNULL(expected_time,0)) - SUM(IFNULL(prorated_wiggle,0)) - SUM(IFNULL(total_exceptions,0)) as expected_time
                    FROM 
                        dailyavailability_vw
                    WHERE 
                        entdate BETWEEN :startRange AND :endRange && 
                        username = :username
                    GROUP BY
                        availability_date
                    DESC
                    ";
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'username' => $username,
                'startRange' => $startRange,
                'endRange' => $endRange
                )
            );
        return $statement->fetchAll();
    }

    public function updateChatsAvailability($data) {

        $query = "  UPDATE 
                        availability
                    SET 
                        chat_in_queue_time = :chat_in_queue_time
                    WHERE
                        agent_username = :username && 
                        availability_date = :availability_date
                  ";
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'username' => $data->username,
                'chat_in_queue_time' => $data->chat_in_queue_time,
                'availability_date' => $data->availability_date
            ));
    }

    public function updatePhonesAvailability($data) {

        $query = "  UPDATE 
                        availability a
                    JOIN
                        users u
                    ON 
                        u.username = a.agent_username
                    SET 
                        a.call_in_queue_time = :call_in_queue_time
                    WHERE 
                        a.availability_date = :availability_date AND (
                            CONCAT(u.first_name, ' ', u.last_name) = :name OR
                            u.asterisk_id = :device_number
                        )
                  ";
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'name' => $data->name,
                'call_in_queue_time' => $data->call_in_queue_time,
                'availability_date' => $data->availability_date,
                'device_number' => $data->device_number
            ));
    }

    public function fetchAgentTicketAvailability($data) {

        $query = "  SELECT
                        SUM(duration) as ticket_in_queue_time
                    FROM
                        ticket_queue_clocks
                    WHERE
                        agent_username = :username AND
                        date(clocked_in) = :availability_date
                    ";
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'username' => $data->username,
                'availability_date' => $data->availability_date
                )
            );
        return $statement->fetch();
    }

    public function updateTicketsAvailability($data) {

        $query = "  UPDATE 
                        availability
                    SET 
                        ticket_in_queue_time = :ticket_in_queue_time
                    WHERE
                        agent_username = :username && 
                        availability_date = :availability_date
                  ";
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'username' => $data->username,
                'ticket_in_queue_time' => $data->ticket_in_queue_time,
                'availability_date' => $data->availability_date
            ));
    }
}