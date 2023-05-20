<?php

class Parameters extends Universal {

    public function getParameters() {

        $query = "  SELECT 
                        *
                    FROM 
                        parameters
                    WHERE
                        id != 0
                ";
        return $this->db->query($query)->fetchAll();
    }

    public function updateParameter($parameter_row) {

        $query = "  UPDATE
                        parameters
                    SET 
                        contacts_per_day_min = :contacts_per_day_min,
                        contacts_per_day_bonus = :contacts_per_day_bonus,
                        customer_satisfaction_min = :customer_satisfaction_min,
                        customer_satisfaction_bonus = :customer_satisfaction_bonus,
                        availability_min = :availability_min,
                        availability_bonus = :availability_bonus,
                        program_adherance_min = :program_adherance_min,
                        program_adherance_bonus = :program_adherance_bonus,
                        attendance_min = :attendance_min
                    WHERE 
                        id = :id
                ";

        $preparray = 
        array(
            ':contacts_per_day_min' => $parameter_row->contacts_per_day_min,
            ':contacts_per_day_bonus' => $parameter_row->contacts_per_day_bonus,
            ':customer_satisfaction_min' => $parameter_row->customer_satisfaction_min,
            ':customer_satisfaction_bonus' => $parameter_row->customer_satisfaction_bonus,
            ':availability_min' => $parameter_row->availability_min,
            ':availability_bonus' => $parameter_row->availability_bonus,
            ':program_adherance_min' => $parameter_row->program_adherance_min,
            ':program_adherance_bonus' => $parameter_row->program_adherance_bonus,
            ':attendance_min' => $parameter_row->attendance_min,
            ':id' => $parameter_row->id
            );
        $statement = $this->db->prepare($query);
        $statement->execute($preparray);
    }

    public function getAgentChannel($username) {

        $query = "  SELECT
                        b.channel as channel
                    FROM
                        users a
                    LEFT JOIN
                        parameters b
                    ON
                        a.parameter = b.id
                    WHERE
                        a.username = :username
                        ";

        $statement = $this->db->prepare($query);
        $statement->execute(array('username' => $username));
        $results = $statement->fetch();
        return $results->channel;
    }
}