<?php

class Volume extends Universal {

    public function getVolume($channel, $username, $start, $end) {

        switch($channel) {

            case 'chats':
                $query = "  SELECT 
                                COUNT(*) as count
                            FROM 
                                chats
                            WHERE
                                agentUsername = :username &&
                                started BETWEEN :start AND :end
                            ";
            break;

            case 'calls':
                $query = "  SELECT 
                                COUNT(*) as count
                            FROM 
                                calls
                            WHERE
                                username = :username &&
                                started BETWEEN :start AND :end
                            ";
            break;

            case 'tickets':
                $query = "  SELECT 
                                COUNT(*) as count
                            FROM 
                                tickets
                            WHERE
                                username = :username &&
                                agent_responded BETWEEN :start AND :end
                            ";
            break;
        }

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'username' => $username,
                'start' => $start,
                'end' => $end
            ));

        $results = $statement->fetch();
        return $results->count;
    }

    public function upsertDailyContactsByAgent($data) {

        $query = "  INSERT INTO 
                        daily_contacts_by_agent
                    (
                        username, 
                        team, 
                        dept, 
                        date, 
                        chats, 
                        calls, 
                        tickets, 
                        total
                    )
                    VALUES
                    (
                        :username, 
                        :team, 
                        :dept, 
                        :date, 
                        :chats, 
                        :calls, 
                        :tickets, 
                        :total
                    )
                    ON 
                        DUPLICATE KEY 
                    UPDATE 
                        chats = :chats,
                        calls = :calls,
                        tickets = :tickets,
                        total = :total
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'username' => $data->username,
                'team' => $data->team,
                'dept' => $data->dept,
                'date' => $data->date,
                'chats' => $data->chats,
                'calls' => $data->calls,
                'tickets' => $data->tickets,
                'total' => $data->total
            ));
    }
}