<?php

class Tickets extends Universal {

    public function upsertTicketsQueued($data) {

        $query = "  INSERT INTO 
                        tickets_currently_queued 
                    (
                        id,
                        modified_time,
                        queue_number,
                        subject,
                        expanded_subject,
                        account_id,
                        user_id, 
                        service_id,
                        date_last_correspond,
                        date_assigned_response,
                        date_customer_response,
                        date_locked,
                        agent_locked
                    )
                    VALUES 
                    (
                        :id,
                        NOW(),
                        :queue_number,
                        :subject,
                        :expanded_subject,
                        :account_id,
                        :user_id,
                        :service_id,
                        :date_last_correspond,
                        :date_assigned_response,
                        :date_customer_response,
                        :date_locked,
                        :agent_locked
                    )
                    ON 
                        DUPLICATE KEY 
                    UPDATE
                        modified_time = NOW(),
                        queue_number = :queue_number,
                        subject = :subject,
                        expanded_subject = :expanded_subject,
                        account_id = :account_id,
                        user_id = :user_id,
                        service_id = :service_id,
                        date_last_correspond = :date_last_correspond,
                        date_assigned_response = :date_assigned_response,
                        date_customer_response = :date_customer_response,
                        date_locked = :date_locked, 
                        agent_locked = :agent_locked
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'id' => $data->id,
                'queue_number' => $data->queue_number,
                'subject' => $data->subject,
                'expanded_subject' => $data->expanded_subject,
                'account_id' => $data->account_id,
                'user_id' => $data->user_id,
                'service_id' => $data->service_id,
                'date_last_correspond' => $data->date_last_correspond,
                'date_assigned_response' => $data->date_assigned_response,
                'date_customer_response' => $data->date_customer_response,
                'date_locked' => $data->date_locked,
                'agent_locked' => $data->agent_locked
            ));
    }
    
    public function upsertTicket($data) {

        $query = "  INSERT INTO
                        tickets
                    (
                        ticket_id,
                        ticket_history_id,
                        hostops_id,
                        agent_responded,
                        type,
                        account_id,
                        user_id,
                        service_id,
                        incident,
                        username,
                        team,
                        dept
                    )
                    SELECT
                        :ticket_id as ticket_id,
                        :ticket_history_id as ticket_history_id,
                        :hostops_id as hostops_id,
                        :agent_responded as agent_responded,
                        :type as type,
                        :account_id as account_id,
                        :user_id as user_id,
                        :service_id as service_id,
                        :incident as incident,
                        users.username as username,
                        users.team as team,
                        users.dept as dept
                    FROM
                        users
                    WHERE
                        hostops_id = :hostops_id &&
                        active = 1
                    ON
                        DUPLICATE KEY
                    UPDATE
                        ticket_history_id = :ticket_history_id
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'ticket_id' => $data->ticket_id,
                'ticket_history_id' => $data->ticket_history_id,
                'hostops_id' => $data->hostops_id,
                'agent_responded' => $data->agent_responded,
                'type' => $data->type,
                'account_id' => $data->account_id,
                'user_id' => $data->user_id,
                'service_id' => $data->service_id,
                'incident' => $data->incident
            ));
    }
}