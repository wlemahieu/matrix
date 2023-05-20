<?php

class Conversocial extends Universal {

    public function updateUnreadMessages($unread_messages) {

        $query = "  UPDATE
                        conversocial_data
                    SET
                        unread_messages = :unread_messages
                    WHERE
                        id = 1
                ";
                
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'unread_messages' => $unread_messages
            ));
    }

    public function upsertUsers($data) {

        $query = "  INSERT INTO
                        conversocial_users
                        (cs_id, is_active, first_name, last_name)
                    VALUES
                        (:cs_id, :is_active, :first_name, :last_name)
                    ON
                        DUPLICATE KEY
                    UPDATE
                        is_active = :is_active,
                        first_name = :first_name,
                        last_name = :last_name
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'is_active' => $data->is_active,
                'cs_id' => $data->id,
                'first_name' => $data->first_name,
                'last_name' => $data->last_name
            ));
    }

    public function fetchUsers() {

        $query = "  SELECT
                        *
                    FROM
                        conversocial_users
                ";

        $statement = $this->db->query($query);
        return $statement->fetchAll();
    }
}