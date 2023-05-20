<?php

class Attendance extends Universal {

    public function agentsScheduledNow() {

        $query = "  SELECT
                        a.username
                    FROM
                    (
                        SELECT
                            a.username,
                            CONCAT(DATE_FORMAT(NOW(), '%Y-%m-%d'), ' ', a.start_time) as start,
                            FROM_UNIXTIME(UNIX_TIMESTAMP(CONCAT(DATE_FORMAT(NOW(), '%Y-%m-%d'), ' ', a.start_time)) + c.shift_duration) AS end
                        FROM
                            schedules2 a
                        LEFT JOIN 
                            users b
                        ON
                            b.username = a.username
                        LEFT JOIN
                            users_shift_duration c
                        ON
                            a.username = c.username
                        WHERE
                            a.day_of_week = CAST(weekday(now()) as CHAR(100)) &&
                            b.type = 'Agent' &&
                            b.active = 1
                    ) a
                    WHERE 
                        NOW() BETWEEN a.start AND a.end
                ";

        $statement = $this->db->query($query);

        return $statement->fetchAll();
    }

    public function checkForAbsences() {

        $query = "  SELECT 
                        a.username
                    FROM
                    (
                        SELECT  
                            a.username as username, 
                            a.parameter,
                            b.start_time
                        FROM    
                            users as a
                        JOIN    
                            schedules2 as b
                        ON
                            b.username = a.username
                        WHERE   
                            b.day_of_week = cast(weekday(curdate()) as CHAR(100)) AND
                            a.type = 'Agent' AND
                            ( a.dept = 'CS' || a.dept = 'Sales' ) AND
                            a.active = 1 AND
                            a.team != 'Internal Community' AND
                            a.team != 'External Community' AND
                            a.team != 'CloudTech' AND
                            TIME_FORMAT(TIME(now()-INTERVAL 15 MINUTE), '%H:%i') >= TIME_FORMAT(b.start_time, '%H:%i') AND
                            a.username NOT IN ( 
                                SELECT 
                                    agent_username
                                FROM 
                                    attendance
                                WHERE 
                                    DATE_FORMAT(now(), '%Y-%m-%d') = DATE_FORMAT(clocked_in, '%Y-%m-%d')
                                GROUP BY
                                    agent_username
                            ) AND a.username NOT IN (
                                SELECT
                                    offending_username
                                FROM
                                    attendance_marks
                                WHERE
                                    DATE_FORMAT(now(), '%Y-%m-%d') = DATE_FORMAT(started, '%Y-%m-%d') AND
                                    mark = 'Absence'
                            )
                        GROUP BY 
                            username
                    ) a
                        
                    JOIN 
                        parameters as d
                    ON 
                        d.id = a.parameter
                ";

        $statement = $this->db->query($query);

        return $statement->fetchAll();
    }

    public function agentsClockedIn($params) {

        $query = "  SELECT
                        agent_username as username
                    FROM
                        attendance
                    WHERE 
                        date(clocked_in) = :today
                    GROUP BY
                        agent_username
                ";
                
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'today' => $params->today
            ));
        return $statement->fetchAll();
    }

    public function agentMarkCheck($userName, $mark, $startRange, $endRange) {

        $query = "  SELECT 
                        count(*) as count
                    FROM 
                        attendance_marks
                    WHERE 
                        started >= :started && 
                        ended <= :ended && 
                        mark = :mark && 
                        offending_username = :userName && 
                        active = 1";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'userName'=>$userName,
                'mark'=>$mark,
                'started'=>$startRange,
                'ended'=>$endRange
                )
            );
        return $statement->fetch();
    }

    public function getClocks($data) {

        $query = "  SELECT 
                        id, 
                        agent_username as username, 
                        UNIX_TIMESTAMP(clocked_in) * 1000 as start,
                        UNIX_TIMESTAMP(clocked_out) * 1000 as end, 
                        active, 
                        duration as duration
                    FROM 
                        attendance
                    WHERE
                        DATE(clocked_in) BETWEEN :start AND :end &&
                        agent_username = :username
                    ORDER BY 
                        clocked_in
                    DESC ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'username' => $data->username,
                'start' => $data->startRange,
                'end' => $data->endRange
                )
            );

        // we use PDO::FETCH_OBJ to take advantage of indexes for angular to be able to update by-row certain clocks from leadership views
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAttendanceDates($startRange, $endRange, $username, $team, $dept, $mark, $scheduled) {

        $query = "  SELECT 
                        DATE(started) as date
                    FROM 
                        attendance_marks
                    WHERE 
                        DATE(started) BETWEEN :startRange AND :endRange &&
                        (dept = :dept OR :dept = -1) &&
                        (team = :team OR :team = -1) &&
                        (scheduled = :scheduled OR :scheduled = -1) &&
                        (offending_username = :username OR :username = -1) &&
                        mark = :mark &&
                        active = 1
                    GROUP BY 
                        date
                    ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                ':startRange' => $startRange,
                ':endRange' => $endRange,
                ':username' => $username,
                ':team' => $team,
                ':dept' => $dept,
                ':mark' => $mark,
                ':scheduled' => $scheduled
            ));

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getScheduledAttendance($startRange, $endRange, $username, $team, $dept) {

        $scheduled_late_arrivals = $this->getAttendanceDates($startRange, $endRange, $username, $team, $dept, "Late Arrival", 1);
        $scheduled_absences = $this->getAttendanceDates($startRange, $endRange, $username, $team, $dept, "Absence", 1);

        $lateObject = (object) array('total' => count($scheduled_late_arrivals), 'dates' => $scheduled_late_arrivals);
        $absentObject = (object) array('total' => count($scheduled_absences), 'dates' => $scheduled_absences);

        return (object) array('late' => $lateObject, 'absent' => $absentObject);
    }

    public function getUnscheduledAttendance($startRange, $endRange, $username, $team, $dept) {

        $unscheduled_late_arrivals = $this->getAttendanceDates($startRange, $endRange, $username, $team, $dept, "Late Arrival", 0);
        $unscheduled_absences = $this->getAttendanceDates($startRange, $endRange, $username, $team, $dept, "Absence", 0);

        $lateObject = (object) array('total' => count($unscheduled_late_arrivals), 'dates' => $unscheduled_late_arrivals);
        $absentObject = (object) array('total' => count($unscheduled_absences), 'dates' => $unscheduled_absences);

        return (object) array('late' => $lateObject, 'absent' => $absentObject);
    }
}