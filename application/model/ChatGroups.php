<?php

class ChatGroups extends Universal {

    public function upsertChatGroups($data) {

        $query = "  INSERT INTO 
                        chat_groups
                    (
                        group_id,
                        group_name
                    )
                    VALUES
                    (
                        :group_id,
                        :group_name
                    )
                    ON 
                        DUPLICATE KEY 
                    UPDATE 
                        group_id = :group_id, 
                        group_name = :group_name
                ";
                
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'group_name' => $data->group_name,
                'group_id' => $data->group_id
            ));
    }

    public function getChatGroups() {

        $query = "  SELECT 
                        group_id
                    FROM 
                        chat_groups
                ";
        return $this->db->query($query)->fetchAll();
    }
}