<?php

class Exceptions extends Universal {

    public function endException($username) {

        $query = "  UPDATE
                        attendance_marks
                    SET
                        ended = :ended
                    WHERE
                        ended IS NULL &&
                        offending_username = :username
        ";
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'username' => $username,
                'ended' => date('Y-m-d H:i:s')
            )
        );
    }

    public function selfInsertException($mark) {

        $query = "  INSERT IGNORE INTO
                        attendance_marks
                        (mark, reporting_username, offending_username)
                    VALUES
                        (:mark, :reporting_username, :offending_username)
        ";
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'mark' => $mark, 
                'reporting_username' => $_SESSION['userinfo']->username, 
                'offending_username' => $_SESSION['userinfo']->username
                )
        );
    }

    public function updateException($data) {

        $query = "  UPDATE 
                        attendance_marks
                    SET 
                        mark = :mark,
                        started = :start, 
                        ended = :end, 
                        active = :active, 
                        reporting_username = :lead_username
                    WHERE 
                        id = :id
                        ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'id' => $data->id,
                'mark' => $data->type,
                'active' => $data->active,
                'start' => $data->start,
                'end' => $data->end,
                'lead_username' => $data->lead_username
                )
            );
    }

    /**
     * Add an exception
     * @return [type] [description]
     */
    public function addException($data) {

        $query = "  INSERT INTO 
                        attendance_marks 
                        (mark, reporting_username, offending_username, started, ended, active)
                    VALUES 
                        (:mark, :reporting_username, :offending_username, :started, :ended, :active)
                    ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'mark' => $data->type,
                'reporting_username' => $data->lead_username,
                'offending_username' => $data->username,
                'started' => $data->start,
                'ended' => $data->end,
                'active' => $data->active
                )
            );

        /* Only update their availability row if it's not an attendance mark. */
        $exclude = array('Absence', 'Late Arrival', 'Early Departure', 'VPN');

        if(!in_array($data->type, $exclude)) {

          $query2 = "
              UPDATE availability
              LEFT JOIN
              (SELECT offending_username, SUM(duration) as break FROM attendance_marks WHERE offending_username = :offending_username && mark = 'Break' && date(started) = date(:started)) as break
              ON agent_username = break.offending_username
              LEFT JOIN
              (SELECT offending_username, SUM(duration) as lunch FROM attendance_marks WHERE offending_username = :offending_username && mark = 'Lunch' && date(started) = date(:started)) as lunch
              ON agent_username = lunch.offending_username
              LEFT JOIN
              (SELECT offending_username, SUM(duration) as dept_meeting FROM attendance_marks WHERE offending_username = :offending_username && mark = 'Dept Meeting' && date(started) = date(:started)) as dept_meeting
              ON agent_username = dept_meeting.offending_username
              LEFT JOIN
              (SELECT offending_username, SUM(duration) as team_meeting FROM attendance_marks WHERE offending_username = :offending_username && mark = 'Team Meeting' && date(started) = date(:started)) as team_meeting
              ON agent_username = team_meeting.offending_username
              LEFT JOIN
              (SELECT offending_username, SUM(duration) as self_directed_time FROM attendance_marks WHERE offending_username = :offending_username && mark = 'Self-Directed Time' && date(started) = date(:started)) as self_directed_time
              ON agent_username = self_directed_time.offending_username
              LEFT JOIN
              (SELECT offending_username, SUM(duration) as 1on1 FROM attendance_marks WHERE offending_username = :offending_username && mark = '1on1' && date(started) = date(:started)) as 1on1
              ON agent_username = 1on1.offending_username
              LEFT JOIN
              (SELECT offending_username, SUM(duration) as other FROM attendance_marks WHERE offending_username = :offending_username && mark = 'Other' && date(started) = date(:started)) as other
              ON agent_username = other.offending_username
              SET break_duration = break, lunch_duration = lunch, one_on_one_duration = 1on1, team_meeting_duration = team_meeting, self_directed_duration = self_directed_time, dept_meeting_duration = dept_meeting, other_duration = other
              WHERE availability_date = date(:started)
              && agent_username = :offending_username
            ";

            $statement2 = $this->db->prepare($query2);
            $statement2->execute(
                array(
                    'offending_username' => $data->offending_username,
                    'started' => $data->start
                    )
                );
        }
    }

    /**
     * Get attendance exceptions for a user for a date range
     * @return [type] [description]
     */
    public function getAttendanceExceptions($data) {

        $query = "  SELECT 
                        id, 
                        mark as type, 
                        offending_username as username, 
                        UNIX_TIMESTAMP(started) * 1000 as start,
                        UNIX_TIMESTAMP(ended) * 1000 as end,
                        active, 
                        duration
                    FROM 
                        attendance_marks
                    WHERE 
                        DATE(started) BETWEEN :startRange and :endRange && 
                        offending_username = :username
                    ORDER BY 
                        started 
                    DESC
                        ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'username' => $data->username,
                'startRange' => $data->startRange,
                'endRange' => $data->endRange
                )
            );
        return $statement->fetchAll();
    }
    
    /**
     * Check which current exception a user is on.
     * @param  [type] $username [description]
     * @return [type]           [description]
     */
    public function currentException($username) {

        $query = "  SELECT
                        mark
                    FROM 
                        attendance_marks
                    WHERE 
                        date(started) = curdate() && 
                        ended IS NULL && 
                        active = 1 && 
                        offending_username = '$username'
        ";

        $result = $this->db->query($query)->fetch();

        if(!empty($result->mark))
        {
            return $result->mark;
        }
    }
}