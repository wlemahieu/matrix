<?php

class MidnightAvailability extends Universal {

    public function fetchClockedInSunriseAgents() {

        $query = "    SELECT
                          a.id, 
                          a.agent_username
                      FROM
                          attendance a
                      JOIN
                          users b
                      ON
                          b.username = a.agent_username
                      JOIN
                          teams c
                      ON
                          b.team = c.name
                      WHERE
                          (
                          date(a.clocked_in) = subdate(curdate(), 1) && 
                          a.clocked_out IS NULL && 
                          c.sunrise = 1
                      )
                  ";

        return $this->db->query($query)->fetchAll();
    }
}