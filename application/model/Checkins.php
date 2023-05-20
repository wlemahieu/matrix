<?php

class Checkins extends Universal {

    public function acceptCheckin($id) {

        $query = "  UPDATE
                        check_in
                    SET 
                        accepted_timestamp = NOW(),
                        accepted = 1
                    WHERE 
                        id = :id
        ";
        $statement = $this->db->prepare($query);
        $statement->execute(array(':id' => $id));
    }

    public function getCheckins($payload) {

        $query = "  SELECT 
                        id,
                        CONCAT(UNIX_TIMESTAMP(timestamp), '000') as creation_date,
                        CONCAT(UNIX_TIMESTAMP(interaction_date), '000') as interaction_date,
                        lead_username,
                        agent_username,
                        account_number,
                        contact_type,
                        support_number,
                        proper_greeting,
                        correct_auth,
                        unlisted_info_given,
                        identified_issue,
                        educate_customer,
                        proper_solutions,
                        correctly_closed,
                        proper_notes,
                        unnecessary_sr,
                        maintain_control,
                        tone_attitude_assurance,
                        missed_procedures,
                        comment,
                        presented,
                        accepted
                    FROM 
                        check_in
                    WHERE
                        ( agent_username = :username || :username = -1 ) &&
                        ( lead_username = :lead_username || :lead_username = -1 ) &&
                        ( id = :id || :id = -1 ) &&
                        (
                            ( :start != -1 && :end != -1 && timestamp BETWEEN :start AND :end ) || 
                            ( :start = -1 && :end = -1 )
                        )
                    ORDER BY 
                        timestamp
                    DESC
        ";

        $statement = $this->db->prepare($query);
        $statement->execute(array(
                    'id' => $payload->id,
                    'username' => $payload->username,
                    'lead_username' => $payload->lead_username,
                    'start' => $payload->start,
                    'end' => $payload->end
                )
        );

        return $statement->fetchAll();
    }

    public function setPresentedStatus($id, $status) {

        $query = "  UPDATE
                        check_in
                    SET 
                        presented_timestamp = NOW(),
                        presented = :status
                    WHERE 
                        id = :id
        ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'id' => $id,
                'status' => $status
                )
            );
    }

    public function upsertCheckin($data) {

        $query = "  INSERT INTO 
                        check_in
                    (
                        id,
                        interaction_date,
                        lead_username,
                        agent_username,
                        account_number,
                        contact_type,
                        support_number,
                        proper_greeting,
                        correct_auth,
                        unlisted_info_given,
                        identified_issue,
                        educate_customer,
                        proper_solutions,
                        correctly_closed,
                        proper_notes,
                        unnecessary_sr,
                        maintain_control,
                        tone_attitude_assurance,
                        missed_procedures,
                        comment
                    )
                    VALUES
                    ( 
                        :id,
                        :interaction_date,
                        :lead_username,
                        :agent_username,
                        :account_number,
                        :contact_type,
                        :support_number,
                        :proper_greeting,
                        :correct_auth,
                        :unlisted_info_given,
                        :identified_issue,
                        :educate_customer,
                        :proper_solutions,
                        :correctly_closed,
                        :proper_notes,
                        :unnecessary_sr,
                        :maintain_control,
                        :tone_attitude_assurance,
                        :missed_procedures,
                        :comment
                    )
                    ON 
                        DUPLICATE KEY 
                    UPDATE 
                        interaction_date = :interaction_date,
                        lead_username = :lead_username,
                        agent_username = :agent_username,
                        account_number = :account_number,
                        contact_type = :contact_type,
                        support_number = :support_number,
                        proper_greeting = :proper_greeting,
                        correct_auth = :correct_auth,
                        unlisted_info_given = :unlisted_info_given,
                        identified_issue = :identified_issue,
                        educate_customer = :educate_customer,
                        proper_solutions = :proper_solutions,
                        correctly_closed = :correctly_closed,
                        proper_notes = :proper_notes,
                        unnecessary_sr = :unnecessary_sr,
                        maintain_control = :maintain_control,
                        tone_attitude_assurance = :tone_attitude_assurance,
                        missed_procedures = :missed_procedures,
                        comment = :comment
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'id' => @$data->id,
                'interaction_date' => @$data->interaction_date,
                'lead_username' => @$data->lead_username,
                'agent_username' => @$data->agent_username,
                'account_number' => @$data->account_number,
                'contact_type' => @$data->contact_type,
                'support_number' => @$data->support_number,
                'proper_greeting' => @$data->proper_greeting,
                'correct_auth' => @$data->correct_auth,
                'unlisted_info_given' => @$data->unlisted_info_given,
                'identified_issue' => @$data->identified_issue,
                'educate_customer' => @$data->educate_customer,
                'proper_solutions' => @$data->proper_solutions,
                'correctly_closed' => @$data->correctly_closed,
                'proper_notes' => @$data->proper_notes,
                'unnecessary_sr' => @$data->unnecessary_sr,
                'maintain_control' => @$data->maintain_control,
                'tone_attitude_assurance' => @$data->tone_attitude_assurance,
                'missed_procedures' => @$data->missed_procedures,
                'comment' => @$data->comment
            ));

        return $this->db->lastInsertId();
    } 
}