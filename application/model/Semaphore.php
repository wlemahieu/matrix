<?php

class Semaphore extends SemaphoreDB {

    public function getAgentResponseTime($ticket_id, $customer_response_time) {

        $query = "  SELECT
                        ticket_id,
                        date_entered, 
                        tech_id
                    FROM 
                        ticket_history
                    JOIN 
                        ticket
                    ON 
                        ticket.id = ticket_history.ticket_id
                    WHERE 
                        entry_type = 2 && 
                        ticket_history.ticket_id = :ticket_id  && 
                        ticket_history.date_entered > :customer_response_time
                    GROUP BY 
                        ticket_history.ticket_id
                    ORDER BY 
                        date_entered 
                    ASC
                  ";

        $statement = $this->semaphore_db->prepare($query);
        $statement->execute(
            array(
                'ticket_id' => $ticket_id,
                'customer_response_time' => $customer_response_time
            ));

        return $statement->fetch();
    }

    public function pastDaysTickets($days) {

        $query = "  SELECT 
                        ticket_id, 
                        date_entered
                    FROM 
                        ticket_history
                    JOIN 
                        ticket
                    ON 
                        ticket.id = ticket_history.ticket_id
                    WHERE 
                        entry_type = 1 && 
                        ticket.date_opened > UNIX_TIMESTAMP(NOW() - INTERVAL $days day)
                    GROUP BY 
                        ticket_history.ticket_id
                    ORDER BY 
                        date_entered 
                    ASC
                ";

        return $this->semaphore_db->query($query)->fetchAll();
    }

    public function fetchTicketRatings() {

        $query = "  SELECT 
                        question_id,
                        answer,
                        ticket_history_id
                    FROM
                        rating_answer
                    ORDER BY
                        ticket_history_id
                    DESC
                    LIMIT 10000
                  ";
        return $this->semaphore_db->query($query)->fetchAll();
    }

    // 7 = billing, 1 = CS, 5 = Sales ( also, see ticket_queue_counts table )
    public function fetchTickets() {

        $query = "  SELECT
                        *
                    FROM
                        ticket
                    WHERE 
                        ( status = 1 || status = 2 ) &&
                        response_status = 2 && 
                        type != 'callcenter' &&
                        (
                            assignment = 7 ||
                            assignment = 1 ||
                            assignment = 5
                        )
                ";
                
        return $this->semaphore_db->query($query)->fetchAll();
    }

    public function fetchTicketResponses() {

        $query = "  SELECT
                        a.ticket_id,
                        a.id as ticket_history_id,
                        a.tech_id as hostops_id,
                        a.date_entered as agent_responded,
                        b.type, b.account_id,
                        b.user_id,
                        b.service_id,
                        c.incident,
                        mt.id
                    FROM
                    (
                        SELECT
                            id,
                            ticket_id,
                            tech_id,
                           date_entered
                        FROM
                            ticket_history
                        WHERE
                            date_entered > UNIX_TIMESTAMP(NOW() - INTERVAL 60 MINUTE) &&
                            entry_type = 2 &&
                            tech_id <> 23
                    ) a
                    # Mass Update Definition: prior record to response is a note containing 'mass update'
                    LEFT JOIN
                        ticket_history mt
                    ON
                        mt.id = (a.id-1)
                        AND mt.entry_type = 4
                        AND mt.message LIKE '%mass update%'
                    LEFT JOIN
                        ticket b
                    ON
                        a.ticket_id = b.id
                    LEFT JOIN
                        incident_ticket c
                    ON
                        b.id = c.ticket
                    WHERE
                        mt.id IS NULL
                ";
        return $this->semaphore_db->query($query)->fetchAll();
    }
}