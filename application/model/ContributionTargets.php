<?php

class ContributionTargets extends Universal {

    public function loadContributionTarget($data) {

        $query = "  INSERT INTO 
                        bi_contribution_value
                    (
                        entmonth, 
                        pt_or_ft, 
                        day_of_week, 
                        contribution
                    )
                    VALUES
                    (
                        :entmonth, 
                        :pt_or_ft, 
                        :day_of_week, 
                        :contribution
                    )
                  ON 
                      DUPLICATE KEY
                  UPDATE 
                      contribution = :contribution
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'entmonth' => $data->entmonth,
                'pt_or_ft' => $data->pt_or_ft,
                'day_of_week' => $data->day_of_week,
                'contribution' => $data->contribution
            ));
    }

    public function fetchContributionTargets($date) {

        $query = "  SELECT
                        *
                    FROM
                        bi_contribution_value
                    WHERE 
                        entmonth = :entmonth
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'entmonth' => $date
            ));

        return $statement->fetchAll();
    }
}