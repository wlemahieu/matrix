<?php

class SalesBonus extends Universal {
	
    public function getContributionTier($contribution_percentage) {

        $query = "  SELECT
                        multiplier
                    FROM
                        bi_sales_tiers a
                    WHERE 
                        :contribution_percentage >= start_range &&
                        :contribution_percentage < end_range";

        $preparray = array(':contribution_percentage' => $contribution_percentage);
        $statement = $this->db->prepare($query);
        $statement->execute($preparray);

        return $statement->fetch(PDO::FETCH_OBJ);
    }

    public function getDeptGoalExpected($dept) {

        $query = "  SELECT
                        SUM(a.contribution) as amount
                    FROM
                        bi_contribution a
                    WHERE 
                        a.day >= DATE_FORMAT(CURDATE(),'%Y-%m-01') &&
                        a.day < DATE_ADD(DATE_FORMAT(CURDATE(),'%Y-%m-01'), INTERVAL 1 MONTH) &&
                        a.dept = :dept
                ";

        $preparray = array('dept' => $dept);

        $statement = $this->db->prepare($query);
        $statement->execute($preparray);

        return $statement->fetch(PDO::FETCH_OBJ);
    }

    public function getDeptGoalAmount($dept) {

        $query = "  SELECT
                        SUM(a.bookings) as amount
                    FROM
                        bi_sales a
                    JOIN
                        users u
                    ON 
                        u.username = a.empl
                    WHERE 
                        a.entdate >= DATE_FORMAT(CURDATE(),'%Y-%m-01') &&
                        a.entdate < DATE_ADD(DATE_FORMAT(CURDATE(),'%Y-%m-01'), INTERVAL 1 MONTH) &&
                        u.dept = :dept
                ";

        $preparray = array('dept' => $dept);

        $statement = $this->db->prepare($query);
        $statement->execute($preparray);

        return $statement->fetch(PDO::FETCH_OBJ);
    }

    public function getBonusAmount($startRange, $endRange, $username) {

        $query = "  SELECT
                        FORMAT(SUM(a.bonus),2) as amount
                    FROM
                        bi_sales a
                    WHERE 
                        a.saledate BETWEEN :startRange AND :endRange &&
                        a.empl = :username";

        $preparray = array(':startRange'=>$startRange,
                           ':endRange'=>$endRange,
                           ':username'=>$username);

        $statement = $this->db->prepare($query);
        $statement->execute($preparray);

        return $statement->fetch(PDO::FETCH_OBJ);
    }

    public function getContributionAmount($startRange, $endRange, $username) {

        $query = "  SELECT
                        FORMAT(SUM(a.paid) + SUM(a.refunds),2) as amount
                    FROM
                        bi_sales a
                    WHERE 
                        a.saledate BETWEEN :startRange AND :endRange &&
                        a.empl = :username";

        $preparray = array(':startRange'=>$startRange,
                           ':endRange'=>$endRange,
                           ':username'=>$username);

        $statement = $this->db->prepare($query);
        $statement->execute($preparray);

        return $statement->fetch(PDO::FETCH_OBJ);
    }

    public function getContributionExpected($startRange, $endRange, $username) {

        $query = "  SELECT
                        FORMAT(SUM(a.contribution),2) as expected
                    FROM
                        bi_contribution a
                    WHERE 
                        a.day BETWEEN :startRange AND :endRange &&
                        a.username = :username";

        $preparray = array(':startRange'=>$startRange,
                           ':endRange'=>$endRange,
                           ':username'=>$username);

        $statement = $this->db->prepare($query);
        $statement->execute($preparray);

        return $statement->fetch(PDO::FETCH_OBJ);
    }

}