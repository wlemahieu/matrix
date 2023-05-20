<?php

class Users extends Universal {

    // fetch a user's info only from users table
  	public function fetch() {

  		$query = "	SELECT 
                        *
                    FROM 
                        users
                    ORDER BY 
                        first_name
              ";

        $statement = $this->db->query($query);
        return $statement->fetchAll();
  	}

    // fetch editable items for user profile
    public function fetchUserProfile($username) {

        $query = "  SELECT
                        a.id as usersId,
                        b.id as parametersId,
                        a.username,
                        a.type,
                        b.channel,
                        b.pt_or_ft,
                        b.level,
                        a.dept,
                        a.team,
                        a.chat_id,
                        a.hostops_id,
                        a.asterisk_id,
                        a.conversocial_id,
                        a.twitter_initials,
                        a.first_name,
                        a.last_name,
                        a.active
                    FROM
                        users a
                    LEFT JOIN
                        parameters b
                    ON
                        a.parameter = b.id
                    WHERE
                        username = :username
                    LIMIT
                        1
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(array(':username' => $username));
        return $statement->fetch();
    }

    // fetch parameter based on level, channel, and pt/ft status (used for editing users)
    public function fetchParameter($level, $channel, $pt_or_ft) {

        $query = "  SELECT 
                        id
                    FROM 
                        parameters
                    WHERE
                        level = :level &&
                        channel = :channel &&
                        pt_or_ft = :pt_or_ft
                    LIMIT
                        1
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'level' => $level,
                'channel' => $channel,
                'pt_or_ft' => $pt_or_ft
                )
        );

        $results = $statement->fetch();

        return $results->id;
    }

    // insert a new row into the users_history table
    public function updateUsersHistory($data) {

        $query = "  INSERT INTO
                        users_history
                    (username, type, parameter, channel, pt_or_ft, level, dept, team, chat_id, hostops_id, asterisk_id, twitter_initials, first_name, last_name, active)
                    VALUES
                    (:username, :type, :parameter, :channel, :pt_or_ft, :level, :dept, :team, :chat_id, :hostops_id, :asterisk_id, :twitter_initials, :first_name, :last_name, :active)
                ";

        $prepArray = array(
            'username' => $data->username,
            'type' => $data->type,
            'parameter' => $data->parametersId,
            'channel' => $data->channel,
            'pt_or_ft' => $data->pt_or_ft,
            'level' => $data->level,
            'dept' => $data->dept,
            'team' => $data->team,
            'chat_id' => $data->chat_id,
            'hostops_id' => $data->hostops_id,
            'asterisk_id' => $data->asterisk_id,
            'twitter_initials' => $data->twitter_initials,
            'first_name' => $data->first_name,
            'last_name' => $data->last_name,
            'active' => $data->active
            );

        $statement = $this->db->prepare($query);
        $statement->execute($prepArray);
    }

    // update a user's profile or schedule
    public function updateProfile($data) {

        $query = "  UPDATE
                        users
                    SET
                        type = :type,
                        parameter = :parameter,
                        dept = :dept,
                        team = :team,
                        chat_id = :chat_id,
                        hostops_id = :hostops_id,
                        asterisk_id = :asterisk_id,
                        conversocial_id = :conversocial_id,
                        twitter_initials = :twitter_initials,
                        first_name = :first_name,
                        last_name = :last_name,
                        active = :active
                    WHERE
                        id = :id &&
                        username = :username
                ";

        $prepArray = array(
            'id' => $data->usersId,
            'username' => $data->username,
            'type' => $data->type,
            'parameter' => $data->parameter,
            'dept' => $data->dept,
            'team' => $data->team,
            'chat_id' => $data->chat_id,
            'hostops_id' => $data->hostops_id,
            'asterisk_id' => $data->asterisk_id,
            'conversocial_id' => $data->conversocial_id,
            'twitter_initials' => $data->twitter_initials,
            'first_name' => $data->first_name,
            'last_name' => $data->last_name,
            'active' => $data->active
            );

        $statement = $this->db->prepare($query);
        $statement->execute($prepArray);
    }

    // fetch historical change data for users
    public function fetchUserHistory($username) {

        $query = "  SELECT
                        *
                    FROM
                        users_history a
                    WHERE 
                        a.username = :username";

        $preparray = array(
            'username' => $username
        );

        $statement = $this->db->prepare($query);
        $statement->execute($preparray);

        return $statement->fetchAll();
    }
}