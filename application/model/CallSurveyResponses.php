<?php

class CallSurveyResponses extends Universal {

    public function saveResponses($data) {

        $query = "  INSERT INTO 
                        post_call_survey 
                    (
                        account,
                        agent,
                        friendliness,
                        accuracy,
                        resolution,
                        satisfaction,
                        comment,
                        timestamp
                    )
                    VALUES
                    ( 
                        :account,
                        :agent,
                        :friendliness,
                        :accuracy,
                        :resolution,
                        :satisfaction,
                        :comment,
                        :timestamp
                    )
                    ON 
                        DUPLICATE KEY 
                    UPDATE 
                        account = :account,
                        agent = :agent,
                        friendliness = :friendliness,
                        accuracy = :accuracy,
                        resolution = :resolution,
                        satisfaction = :satisfaction,
                        timestamp = :timestamp
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'account' => @$data->account,
                'agent' => @$data->agent,
                'friendliness' => @$data->friendliness,
                'accuracy' => @$data->accuracy,
                'resolution' => @$data->resolution,
                'satisfaction' => @$data->satisfaction,
                'comment' => @$data->comment,
                'timestamp' => @$data->timestamp
            ));

        return $this->db->lastInsertId();
    } 

    public function saveTotalAccounts($totalaccounts) {

        $query = "  INSERT IGNORE INTO 
                        post_call_survey_accounts
                        ( accounts )
                    VALUES
                        ( :totalaccounts )
                    ON
                        DUPLICATE KEY
                    UPDATE
                        accounts = :totalaccounts
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'totalaccounts' => $totalaccounts
            ));
    }
}