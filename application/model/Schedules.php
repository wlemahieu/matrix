<?php

class Schedules extends Universal {

    // update a user's schedule
    public function update($data) {

        $query = "  UPDATE
                        schedules2
                    SET
                        start_time = :start_time,
                        vpn = :vpn,
                        lunch_start = :lunch_start,
                        lunch_end = :lunch_end,
                        break1 = :break1,
                        break2 = :break2,
                        team_meeting = :team_meeting,
                        one_on_one = :one_on_one,
                        self_directed_time = :self_directed_time
                    WHERE
                        id = :id &&
                        username = :username

                ";

        $prepArray = array(
            'id' => $data->id,
            'username' => $data->username,
            'start_time' => $data->start_time,
            'vpn' => $data->vpn,
            'lunch_start' => $data->lunch_start,
            'lunch_end' => $data->lunch_end,
            'break1' => $data->break1,
            'break2' => $data->break2,
            'team_meeting' => $data->team_meeting,
            'one_on_one' => $data->one_on_one,
            'self_directed_time' => $data->self_directed_time
            );

        $statement = $this->db->prepare($query);
        $statement->execute($prepArray);
    }


    // fetch a user's schedule
    public function fetch($username) {

        $query = "  SELECT
                        *
                    FROM
                        schedules2
                    WHERE
                        username = :username
                    LIMIT
                        7
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(array(':username' => $username));
        return $statement->fetchAll();
    }
}