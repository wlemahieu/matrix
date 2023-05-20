<?php

class ENPS extends Universal {

    public function saveResponse($payload) {

        $query = "  INSERT IGNORE INTO
                        enps_responses
                        (submitted_date, team, leadership_username, rating, reason)
                    VALUES
                        (NOW(), :team, :leadership_username, :rating, :reason)
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
            	'team' => $payload->team,
            	'leadership_username' => $payload->leadership_username,
                'rating' => $payload->rating,
                'reason' => $payload->reason
                )
        );
    }

    public function fetchResponses($payload) {

        $query = "  SELECT
                        *
                    FROM
                        enps_responses
                    WHERE
                        UNIX_TIMESTAMP(submitted_date) >= UNIX_TIMESTAMP(STR_TO_DATE(:start, '%m/%d/%Y')) AND
                        UNIX_TIMESTAMP(submitted_date) < UNIX_TIMESTAMP(STR_TO_DATE(:end, '%m/%d/%Y'))
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'start' => $payload->start,
                'end' => $payload->end
                )
        );

        return $statement->fetchAll();
    }
}