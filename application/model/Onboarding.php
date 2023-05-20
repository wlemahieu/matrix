<?php

class Onboarding extends Universal {

	// insert a new user using the data they provided in the onboarding form
	// return the uid for schedule creation
    public function onboardUser($data) {

        $query = "  INSERT IGNORE INTO 
                        users
                    (
                        username,
                        type,
                        parameter,
                        dept,
                        team,
                        first_name, 
                        last_name,
                        refresh_token,
                        refresh_token_creation_date
                    )
                    VALUES
                    (
                        :username,
                        :type,
                        :parameter,
                        :dept,
                        :team,
                        :first_name, 
                        :last_name,
                        :refresh_token,
                        NOW()
                    )
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'username' => $data->username,
                'type' => $data->type,
                'parameter' => $data->parameter,
                'dept' => $data->dept,
                'team' => $data->team,
                'first_name' => $data->first_name,
                'last_name' => $data->last_name,
                'refresh_token' => $data->refresh_token
            ));

        return $this->db->lastInsertId();
    }

    // create the schedule framework for a user
    // inserts 7 rows, one for each day, into the table
    public function populateSchedule($uid, $username) {

        for($i=0; $i<7; $i++) {

            switch($i) {

                case 0:
                    $day = 'Monday';
                break;
                case 1:
                    $day = 'Tuesday';
                break;
                case 2:
                    $day = 'Wednesday';
                break;
                case 3:
                    $day = 'Thursday';
                break;
                case 4:
                    $day = 'Friday';
                break;
                case 5:
                    $day = 'Saturday';
                break;
                case 6:
                    $day = 'Sunday';
                break;
            }

            $query = "  INSERT INTO
                            schedules2 
                            (user_id, username, day_of_week, day)
                        VALUES
                            (:uid, :username, :day_of_week, :day) 
                    ";

            $statement = $this->db->prepare($query);
            $statement->execute(
                array(
                    'username' => $username,
                    'uid' => $uid,
                    'day_of_week' => $i,
                    'day' => $day
                    )
            );
        }
    }

}