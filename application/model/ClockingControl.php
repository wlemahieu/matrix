<?php

class ClockingControl extends Universal {

	/**
     * Handles clock-out requests. Multi-channel different than single-channel users.
     * @param  [type] $username [description]
     * @return [type]           [description]
     */
    public function handleClockOut() {

    	// Sales department clock-out policy
    	if($_SESSION['userinfo']->dept == 'Sales') {
            
    		if($_SESSION['userinfo']->navbar->chats_status != 'offline' && $_SESSION['userinfo']->navbar->phones_status != 'offline') {
    			// don't clock them out if they are in both channels
    		} else {
    			// clock them out if they are in 1 channel
    			$this->clockOut($_SESSION['userinfo']->username);
    		}
    	}

    	// CS department clock-out policy
    	elseif($_SESSION['userinfo']->dept == 'CS') {
    		// just clock them out because we know they are only in one channel
    		$this->clockOut($_SESSION['userinfo']->username);
    	}
    }

    /**
     * Clock the agent out
     * @param  [type] $username [description]
     * @return [type]           [description]
     */
    public function clockOut($username) {

        $query = "  UPDATE
                        attendance
                    SET 
                        clocked_out = NOW()
                    WHERE
                        agent_username = :username && 
                        clocked_out IS NULL
                    ORDER BY 
                        id 
                    DESC
                    LIMIT 
                        1
        ";

        $statement = $this->db->prepare($query);
        $statement->execute( 
            array(
                'username' => $username
                )
        );
    }

    /**
     * Handles clock-in requests. Late arrivals, absences, etc.
     * @param  [type] $username [description]
     * @return [type]           [description]
     */
    public function handleClockIn() {

        // check if they are clocked in right now.
        $clockedIn = $this->checkClockedIn($_SESSION['userinfo']->username);

        // check if they have clocked in today at all.
        $clocksToday = $this->checkClocksToday($_SESSION['userinfo']->username);

        // not clocked-in
        if($clockedIn == 0) {

            // remove any early departure marks they may have since they are clocking in
            $this->removeLastEarlyDeparture($_SESSION['userinfo']->username);

            // only allow attendance marks for the first log-in of the day
            if($clocksToday->count >= 0 && is_null($clocksToday->sunrise) || ($clocksToday->count >= 1 && $clocksToday->sunrise == 1)) {

                // remove any absences they may have and get ready to check if they are late.
                // this should remove any lead-created absences for the day that the agent decides to log-in.
                $this->removeLastAbsence($_SESSION['userinfo']->username);

                // check if they are arriving late
                $late = $this->checkIfArrivingLate($_SESSION['userinfo']->username);

                // grab the agent's start time so we can check if they are late.
                $scheduleTimes = $this->agentScheduleStartEnd($_SESSION['userinfo']->username, date('m/d/Y'));

                // in unixtime, take the current exact time and subtract their exact start time.
                // if it's more than 900 seconds, they've surpassed their 15-minute buffer and are officially late.
                if(strtotime(date('Y-m-d H:i:s')) - strtotime($scheduleTimes->start_time) >= 900) {
                    $this->addAttendanceMark('Auto', $_SESSION['userinfo']->username, 'Late Arrival', 0, $scheduleTimes->start_time, date('Y-m-d H:i:s'));
                }
            }
            
            // clock the agent in
            $this->clockIn($_SESSION['userinfo']->username);
        }
    }

    /**
     * Check if they are already clocked in for the day and not clocked out yet.
     */
    public function checkClockedIn($username) {

        $query = "  SELECT 
                        count(*) as count
                    FROM
                        attendance
                    WHERE
                        agent_username = :agent_username && 
                        clocked_in >= CURDATE() && 
                        clocked_out IS NULL
                    LIMIT 
                        1
        ";

        $statement = $this->db->prepare($query);
        $statement->execute(array('agent_username' => $username));
        $results = $statement->fetch();

        return $results->count;
    }

    /**
     * Not to be confused with the function below, check if there are any prior open and closed clocks for today.
     * If there are, we wont allow anymore attendance marks for being late, etc.
     */
    public function checkClocksToday($username) {

        $query = "  SELECT 
                        count(*) as count, 
                        teams.sunrise as sunrise
                    FROM 
                        attendance
                    JOIN 
                        users
                    ON 
                        users.username = attendance.agent_username
                    JOIN 
                        teams
                    ON 
                        teams.name = users.team
                    WHERE 
                        agent_username = :agent_username && 
                        clocked_in >= CURDATE() && 
                        clocked_out IS NOT NULL
                    LIMIT 
                        1
        ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'agent_username' => $username
                )
        );

        return $statement->fetch();
    }

    public function removeLastEarlyDeparture() {

        $query = "  DELETE FROM 
                        attendance_marks
                    WHERE 
                        offending_username = :username && 
                        mark = :mark && started >= CURDATE()
                    LIMIT 
                        1
        ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'username' => $_SESSION['userinfo']->username, 
                'mark' => 'Early Departure'
            )
        );
    }

    /**
     * Not to be confused with the function below, check if there are any prior open and closed clocks for today.
     * If there are, we wont allow anymore attendance marks for being late, etc.
     */
    public function checkIfArrivingLate($username) {

        $query = "  SELECT 
                        count(*) as count
                    FROM 
                        attendance_marks
                    WHERE
                        offending_username = :username && 
                        mark = :mark && 
                        started BETWEEN CURDATE() AND CURDATE() + INTERVAL 1 DAY
        ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'username' => $username,
                'mark' => 'Late Arrival'
                )
        );

        return $statement->fetch();
    }

    public function removeLastAbsence($username) {

        $query = "  UPDATE
                        attendance_marks
                    SET
                        active = 0
                    WHERE 
                        offending_username = :username && 
                        mark = :mark && started >= CURDATE()
        ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'username' => $username, 
                'mark' => 'Absence'
            )
        );
    }

    /**
     * Clock the agent in
     * @param  [type] $username [description]
     * @return [type]           [description]
     */
    public function clockIn($username) {

        $query = "  INSERT IGNORE INTO 
                        attendance
                        (agent_username)
                    VALUES
                        (:agent_username)
                    ";

        $statement = $this->db->prepare($query);
        $statement->execute( 
            array(
                'agent_username' => $username
                )
        );
    }

    /**
     * Add a clock (after the fact)
     * @return [type] [description]
     */
    public function addClock($payload) {

        $query = "  INSERT INTO 
                        attendance 
                    (
                        agent_username, 
                        clocked_in, 
                        clocked_out, 
                        active
                        ) 
                    VALUES 
                    (
                        :username, 
                        :start, 
                        :end,
                        :active
                    )
                    ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'username' => $payload->username,
                'start' => $payload->start,
                'end' => $payload->end,
                'active' => $payload->active
                )
            );
    }

    public function checkIfLeavingEarly($username) {

        $query = "  SELECT
                        ADDTIME(a.start_timestamp, SEC_TO_TIME(b.shift_duration*60 + COALESCE(c.lunch_duration, 0))) as expected_clock_out
                    FROM
                    (
                        SELECT 
                            ADDTIME(TIMESTAMP(curdate()), start_time) as start_timestamp
                        FROM 
                            schedules2
                        WHERE 
                            user_id = :uid && 
                            day_of_week = weekday(curdate())+1
                    ) a,
                    (
                        SELECT
                            shift_duration
                        FROM 
                            users_shift_duration
                        WHERE 
                            id = :uid 
                    ) b,
                    (
                        SELECT if(count(*)>0, duration, count(*)) as lunch_duration
                        FROM 
                            attendance_marks
                        WHERE 
                            mark = 'Lunch' && offending_username = :username && 
                            active = 1 && 
                            started > SUBTIME(now(), '08:00:00')
                    ) c
        ";
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'uid' => $_SESSION['userinfo']->id, 
                'username' => $_SESSION['userinfo']->username
            )
        );

        return $statement->fetch();
    }

    /**
     * Grab an agent's start and end time for a specific date.
     * @param  [type] $userName [description]
     * @param  [type] $date     [description]
     * @return [type]           [description]
     */
    public function agentScheduleStartEnd($username, $date) {

        $query = "  SELECT
                        a.start_time as start_time,
                        FROM_UNIXTIME(UNIX_TIMESTAMP(a.start_time) + b.shift_duration) AS end_time
                    FROM
                    (
                        SELECT 
                            CONCAT(DATE_FORMAT(STR_TO_DATE(:date, '%m/%d/%Y'), '%Y-%m-%d'),' ', start_time) as start_time
                        FROM 
                            schedules2
                        WHERE 
                            username = :username && 
                            day_of_week = cast(weekday(DATE_FORMAT(STR_TO_DATE(:date, '%m/%d/%Y'), '%Y-%m-%d')) as CHAR(100))
                    ) a,
                    (
                        SELECT 
                            shift_duration
                        FROM 
                            users_shift_duration
                        WHERE 
                            username = :username
                    ) b
        ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'username' => $username,
                'date' => $date
                )
            );

        return $statement->fetch();
    }

    /**
     * Add an Attendance Mark for an agent
     * @param [type] $userName   [description]
     * @param [type] $mark       [description]
     * @param [type] $startRange [description]
     * @param [type] $endRange   [description]
     * ( Updated at 06-30-2015 )
     */
    public function addAttendanceMark($reporting_username, $offending_username, $mark, $scheduled, $started, $ended) {

        $query = "  INSERT INTO 
                        attendance_marks
                        (mark, reporting_username, offending_username, scheduled, started, ended)
                    VALUES
                        (:mark, :reporting_username, :offending_username, :scheduled, :started, :ended)
                        ";
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'mark'=> $mark,
                'reporting_username'=>$reporting_username,
                'offending_username'=>$offending_username,
                'scheduled'=>$scheduled,
                'started'=>$started,
                'ended'=>$ended
                )
            );
    }
   
    /**
     * Update a specific attendance clock
     * @param  [int] $id          [the id of the attendance clock in the database]
     * @param  [int] $active      [a flag for whether or not this clock is "deleted" or not. = 1 or 0]
     * @param  [datetime] $clocked_in  [YYYY-MM-DD HH:MM:SS]
     * @param  [datetime] $clocked_out [YYYY-MM-DD HH:MM:SS]
     * @return [None]
     */
    public function updateClock($payload) {

        $query = "  UPDATE 
                        attendance 
                    SET 
                        clocked_in = 
                        CASE
                            WHEN
                                :clocked_in != -1
                            THEN
                                :clocked_in
                            ELSE
                                clocked_in
                        END,
                        clocked_out = 
                        CASE
                            WHEN
                                :clocked_out != -1
                            THEN
                                :clocked_out
                            ELSE
                                clocked_out
                        END,
                        active = 
                        CASE
                            WHEN
                                :active != -1
                            THEN
                                :active
                            ELSE
                                active
                        END
                    WHERE
                        id = :id
                  ";

        $preparray = array(
                'id' => $payload->id,
                'active' => $payload->active,
                'clocked_in' => $payload->start,
                'clocked_out' => $payload->end
                );

        $statement = $this->db->prepare($query);
        $statement->execute($preparray);    
    }     
}