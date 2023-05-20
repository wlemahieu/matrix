<?php

class CustomerComments extends Universal {

    public function upsertChatComment($data) {

        $query = "  INSERT INTO 
                        chat_comments
                    (
                        chat_id,
                        username,
                        comment
                    )
                    VALUES 
                    (
                        :chat_id,
                        :username,
                        :comment
                    )
                    ON DUPLICATE KEY 
                    UPDATE 
                        comment = :comment
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'chat_id' => $data->chat_id,
                'username' => $data->username,
                'comment' => $data->comment
            ));
    }
    
    public function getAfterCallSurveys() {

        $query = "  SELECT 
                        *, DATE_FORMAT(FROM_UNIXTIME(timestamp), '%Y-%m-%d %H:%i:%s') as datetime
                    FROM 
                        post_call_survey
                    ORDER BY
                        datetime
                    DESC
                ";

       return $this->db->query($query)->fetchAll();
    }
    
    public function getComments($startRange, $endRange, $username) {

        $query = "  SELECT 
                        'calls' as type, 
                        comment, 
                        DATE_FORMAT(from_unixtime(timestamp), '%W %m/%d %l:%i%p') as datetime
                    FROM 
                        post_call_survey
                    WHERE 
                        DATE(FROM_UNIXTIME(timestamp)) between :startRange AND :endRange && 
                        comment != '' && 
                        agent = :username


                    UNION


                    SELECT 
                        'chats' as type, 
                        a.comment, 
                        DATE_FORMAT(from_unixtime(b.started), '%W %m/%d %l:%i%p') as datetime
                    FROM 
                        chat_comments a
                    LEFT JOIN
                        chats b
                    ON
                        a.chat_id = b.chat_id
                    WHERE 
                        DATE(FROM_UNIXTIME(b.started)) BETWEEN :startRange AND :endRange && 
                        agentUsername = :username


                    ORDER BY datetime
                    DESC
                ";

        $preparray = array(
            ':username'=>$username,
            ':startRange'=>$startRange,
            ':endRange'=>$endRange
            );
        $statement = $this->db->prepare($query);
        $statement->execute($preparray);

        return $statement->fetchAll(PDO::FETCH_OBJ);
    }

}