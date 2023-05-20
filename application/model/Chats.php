<?php

class Chats extends Universal {

    public function updateEmptyAccounts($data) {

        $query = "  UPDATE
                        chats 
                    SET
                        account_number = 
                            CASE WHEN :account = 0 THEN NULL
                            ELSE :account
                        END,
                        contact_number = 
                            CASE WHEN :contact = 0 THEN NULL
                            ELSE :contact
                        END
                    WHERE
                        chat_id = :chat_id
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'chat_id' => $data->chat_id,
                'account' => $data->account,
                'contact' => $data->contact
                )
            );
    }

    public function emptyAccounts() {

        $query = "  SELECT 
                        * 
                    FROM 
                        chats
                    WHERE
                        account_number = 0
                ";

        $statement = $this->db->query($query);

        return $statement->fetchAll();
    }

    public function upsertChatVisitors($data) {

        $query = "  INSERT INTO 
                        chats_current_visitors 
                    (
                        chat_id,
                        queue_start_time, 
                        chat_start_time, 
                        chat_group, 
                        issue, 
                        question, 
                        state, 
                        name, 
                        agent, 
                        account,
                        contact
                    )
                    VALUES 
                    (
                        :chat_id,
                        :queue_start_time, 
                        :chat_start_time, 
                        :chat_group, 
                        :issue, 
                        :question, 
                        :state, 
                        :name, 
                        :agent, 
                        :account,
                        :contact
                    )
                    ON 
                        DUPLICATE KEY 
                    UPDATE
                        modified_time = NOW(),
                        chat_id = :chat_id,
                        queue_start_time = :queue_start_time, 
                        chat_start_time = :chat_start_time, 
                        chat_group = :chat_group, 
                        issue = :issue, 
                        question = :question, 
                        state = :state, 
                        agent = :agent,
                        account = :account,
                        contact = :contact
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'chat_id' => $data->chat_id,
                'queue_start_time' => $data->queue_start_time,
                'chat_start_time' => $data->chat_start_time,
                'chat_group' => $data->chat_group,
                'issue' => $data->issue,
                'question' => $data->question,
                'state' => $data->state,
                'agent' => $data->agent,
                'account' => @$data->account,
                'contact' => @$data->contact,
                'name' => $data->name
                )
            );
    }
    
    public function upsertChat($data) {

        $query = "  SELECT
                        @team := team,
                        @dept := dept
                    FROM
                        users
                    WHERE
                        username = :agent_username;
                    INSERT INTO 
                        chats 
                    (
                        chatter_id, 
                        chat_id, 
                        account_number, 
                        contact_number, 
                        skill, 
                        reason, 
                        rating, 
                        numrating, 
                        started, 
                        ended, 
                        wait_time, 
                        handle_time, 
                        agentUsername,
                        teamname,
                        dept
                    )
                    VALUES
                    (
                        :visitor_id, 
                        :chat_id, 
                        :account_id, 
                        :contact_id, 
                        :group_id, 
                        :chat_reason, 
                        :rating, 
                        :num_rating, 
                        :chat_start, 
                        :chat_end, 
                        :wait_time, 
                        :duration, 
                        :agent_username,
                        @team,
                        @dept
                    )
                    ON DUPLICATE KEY 
                    UPDATE
                        teamname = @team,
                        dept = @dept,
                        numrating = :num_rating, 
                        ended = :chat_end, 
                        handle_time = :duration, 
                        rating = :rating,
                        account_number = :account_id,
                        contact_number = :contact_id
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'visitor_id' => $data->visitor_id,
                'chat_id' => $data->chat_id,
                'account_id' => $data->account_id,
                'contact_id' => $data->contact_id,
                'group_id' => $data->group_id,
                'chat_reason' => $data->chat_reason,
                'rating' => $data->rating,
                'num_rating' => $data->num_rating,
                'chat_start' => $data->chat_start,
                'chat_end' => $data->chat_end,
                'wait_time' => $data->wait_time,
                'duration' => $data->duration,
                'agent_username' => $data->agent_username   
            ));
    }

    public function upsertChatsUnanswered($data) {

        $query = "  INSERT INTO 
                        chats_unanswered
                    (
                        date, 
                        group_id, 
                        hour, 
                        total
                    )
                    VALUES
                    (
                        :date, 
                        :group_id, 
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
                'group_id' => $data->group_id,
                'hour' => $data->hour,
                'total' => $data->total
            ));
    }

    public function saveChatTag($data) {

        $query = "  INSERT IGNORE INTO 
                        chat_tags
                    (
                        chat_id, 
                        chat_tag
                    )
                    VALUES
                    (
                        :chat_id, 
                        :tag
                    )
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'chat_id' => $data->chat_id,
                'tag' => $data->tag
            ));
    }
}