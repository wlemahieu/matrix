<?php

class Teams extends Universal {

    public function read($payload) {

  		$query = "	SELECT 
                        *
                    FROM 
                        teams_new
                    ORDER BY 
                        name
                ";

        $statement = $this->db->query($query);
        return $statement->fetchAll();
  	}

    public function upsert($data) {

        $query = "  INSERT IGNORE INTO
                        teams_new
                        ( id, name, sunrise, dept, active, timestamp)
                    VALUES
                        ( :id, :name, :sunrise, :dept, :active, NOW())
                    ON
                        DUPLICATE KEY
                    UPDATE
                        id = LAST_INSERT_ID(id),
                        name = :name,
                        sunrise = :sunrise,
                        dept = :dept,
                        active = :active
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'id' => $data->id,
                'name' => $data->name,
                'sunrise' => $data->sunrise,
                'dept' => $data->dept,
                'active' => $data->active
            ));

        return $this->db->lastInsertId();
    }
}