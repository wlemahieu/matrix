<?php

class Workflow extends Universal {

    // memcache interval in seconds
    public $memcacheInterval = 7;

    // fetch the total unread messages in conversocial
    public function conversocialUnread() {

        // create a unique key based on the unix timestamps added together for the start and end ranges.
        $key = 'conversocialUnread';
        
        // check if key is set in memcache
        $memcache = Universal::memcacheRetrieve($key, $this->memcacheInterval);

        // if there are no results in memcache, or if the results are expired...
        if(empty($memcache->data) || $memcache->expired === true) {

            $query = "  SELECT
                            unread_messages
                        FROM
                            conversocial_data
                    ";

            $statement = $this->db->query($query);
            $data = $statement->fetch();

            // store in memcache
            Universal::memcacheStore($key, $data);

            $response = $data;
        }

        // we have valid results in memcache, so let's use it
        else {
            $response = $memcache->data;
        }

        return $response->unread_messages;
    }

    // fetch the total unread messages in conversocial
    public function conversocialUsers() {

        // create a unique key based on the unix timestamps added together for the start and end ranges.
        $key = 'conversocialUsers';
        
        // check if key is set in memcache
        $memcache = Universal::memcacheRetrieve($key, $this->memcacheInterval);

        // if there are no results in memcache, or if the results are expired...
        if(empty($memcache->data) || $memcache->expired === true) {

            $query = "  SELECT
                            *
                        FROM
                            conversocial_users
                    ";

            $statement = $this->db->query($query);
            $data = $statement->fetch();

            // store in memcache
            Universal::memcacheStore($key, $data);

            $response = $data;
        }

        // we have valid results in memcache, so let's use it
        else {
            $response = $memcache->data;
        }

        return $response;
    }

    /**
     * Show the upcoming or current schedule items for everyone.
     * @param  [type] $when [description]
     * @return [type]       [description]
     */
    public function workflowScheduleItems($when, $dept) {

        // create a unique key based on the unix timestamps added together for the start and end ranges.
        $key = 'workflowScheduleItems_' . $when . $dept;
        
        // check if key is set in memcache
        $memcache = Universal::memcacheRetrieve($key, $this->memcacheInterval);

        // if there are no results in memcache, or if the results are expired...
        if(empty($memcache->data) || $memcache->expired === true) {

            switch($when) {

                case 'Now':
                    $query = "  SELECT
                                    CASE
                                        WHEN
                                            UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(d.started) >= 3600
                                        THEN
                                            TIME_FORMAT(SEC_TO_TIME(UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(d.started)),'%Hh %im %ss')
                                        ELSE
                                            TIME_FORMAT(SEC_TO_TIME(UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(d.started)),'%im %ss')
                                    END
                                    as time,
                                    d.mark as type,
                                    CASE
                                        WHEN d.mark = 'Absence' THEN 'Absence'
                                        WHEN d.mark = 'Break' THEN 'Break'
                                        WHEN d.mark = 'Lunch' THEN 'Lunch'
                                        WHEN d.mark = 'Other' THEN 'Other'
                                        WHEN d.mark = 'Self-Directed Time' THEN 'Other'
                                    END as class,
                                    CONCAT(LEFT(a.first_name,7), ' ', LEFT(a.last_name,1)) as name,
                                    c.channel
                                FROM
                                    users as a
                                LEFT JOIN
                                    schedules2 as b
                                ON
                                    a.username = b.username
                                LEFT JOIN
                                    parameters as c
                                ON
                                    a.parameter = c.id
                                LEFT JOIN
                                    attendance_marks as d
                                ON
                                    d.offending_username = a.username
                                INNER JOIN
                                    teams e
                                ON
                                    e.name = a.team
                                WHERE
                                    CAST(d.started as DATE) = CURDATE() &&
                                    b.day_of_week = CAST(weekday(NOW()) AS CHAR) &&
                                    a.type = 'Agent' &&
                                    a.dept = :dept &&
                                    a.active = 1 &&
                                    (
                                        ( d.mark = 'Absence' && d.ended IS NOT NULL ) || 
                                        ( d.mark != 'Absence' && d.ended IS NULL )
                                    ) &&
                                    d.mark IS NOT NULL
                                ORDER BY time DESC
                            ";
                break;

                case 'Upcoming':
                    $query = "  SELECT
                                    a.time,
                                    a.start_time_sort,
                                    a.type,
                                    a.class,
                                    CONCAT(LEFT(b.first_name,7), ' ', LEFT(b.last_name,1)) as name,
                                    c.channel
                                FROM
                                (
                                    (
                                        SELECT
                                            username,
                                            TIME_FORMAT(start_time, '%h:%i %p') as time,
                                            CONCAT(DATE_FORMAT(NOW(), '%Y-%m-%d'), ' ', start_time) as start_time_sort,
                                            'Start Shift' as type,
                                            'Start' as class
                                        FROM
                                            schedules2
                                        WHERE
                                            day_of_week = CAST(weekday(NOW()) AS CHAR) &&
                                            start_time IS NOT NULL &&
                                            start_time > DATE_FORMAT(NOW(),'%H:%i:%s')
                                        ORDER BY start_time_sort ASC
                                    )

                                    UNION ALL

                                    (
                                        SELECT
                                            username,
                                            TIME_FORMAT(lunch_start, '%h:%i %p') as time,
                                            CONCAT(DATE_FORMAT(NOW(), '%Y-%m-%d'), ' ', start_time) as start_time_sort,
                                            'Lunch' as type,
                                            'Lunch' as class
                                        FROM
                                            schedules2
                                        WHERE
                                            day_of_week = CAST(weekday(NOW()) AS CHAR) &&
                                            start_time IS NOT NULL &&
                                            lunch_start IS NOT NULL &&
                                            lunch_start > DATE_FORMAT(NOW(),'%H:%i:%s')
                                        ORDER BY start_time_sort ASC
                                    )

                                    UNION ALL

                                    (
                                        SELECT
                                            username,
                                            TIME_FORMAT(break1, '%h:%i %p') as time,
                                            CONCAT(DATE_FORMAT(NOW(), '%Y-%m-%d'), ' ', break1) as start_time_sort,
                                            'Break 1' as type,
                                            'Break' as class
                                        FROM
                                            schedules2 as b
                                        WHERE
                                            day_of_week = CAST(weekday(NOW()) AS CHAR) &&
                                            start_time IS NOT NULL &&
                                            break1 IS NOT NULL &&
                                            break1 > DATE_FORMAT(NOW(),'%H:%i:%s')
                                        ORDER BY start_time_sort ASC
                                    )

                                    UNION ALL

                                    (
                                        SELECT
                                            username,
                                            TIME_FORMAT(break2, '%h:%i %p') as time,
                                            CONCAT(DATE_FORMAT(NOW(), '%Y-%m-%d'), ' ', break2) as start_time_sort,
                                            'Break 2' as type,
                                            'Break' as class
                                        FROM
                                            schedules2 as b
                                        WHERE
                                            day_of_week = CAST(weekday(NOW()) AS CHAR) &&
                                            start_time IS NOT NULL &&
                                            break2 IS NOT NULL &&
                                            break2 > DATE_FORMAT(NOW(),'%H:%i:%s')
                                        ORDER BY start_time_sort ASC
                                    )

                                    UNION ALL

                                    (
                                        SELECT
                                            username,
                                            TIME_FORMAT(team_meeting, '%h:%i %p') as time,
                                            CONCAT(DATE_FORMAT(NOW(), '%Y-%m-%d'), ' ', team_meeting) as start_time_sort,
                                            'Team Meeting' as type,
                                            'Other' as class
                                        FROM
                                            schedules2
                                        WHERE
                                            day_of_week = CAST(weekday(NOW()) AS CHAR) &&
                                            start_time IS NOT NULL &&
                                            team_meeting IS NOT NULL &&
                                            team_meeting > DATE_FORMAT(NOW(),'%H:%i:%s')
                                        ORDER BY start_time_sort ASC
                                    )
                                    
                                    UNION ALL

                                    (
                                        SELECT
                                            username,
                                            TIME_FORMAT(one_on_one, '%h:%i %p') as time,
                                            CONCAT(DATE_FORMAT(NOW(), '%Y-%m-%d'), ' ', one_on_one) as start_time_sort,
                                            '1on1' as type,
                                            'Other' as class
                                        FROM
                                            schedules2
                                        WHERE
                                            day_of_week = CAST(weekday(NOW()) AS CHAR) &&
                                            start_time IS NOT NULL &&
                                            one_on_one IS NOT NULL &&
                                            one_on_one > DATE_FORMAT(NOW(),'%H:%i:%s')
                                        ORDER BY start_time_sort ASC
                                    )
                                ) a
                                LEFT JOIN
                                    users b
                                ON
                                    a.username = b.username
                                LEFT JOIN
                                    parameters as c
                                ON
                                    b.parameter = c.id
                                LEFT JOIN
                                    attendance_marks d
                                ON
                                    a.username = d.offending_username && 
                                    d.started >= CURDATE() && 
                                    d.started < DATE_ADD(CURDATE(), INTERVAL 1 DAY) && 
                                    d.mark = 'Absence' &&
                                    d.active = 1
                                WHERE
                                    b.active = 1 &&
                                    b.type = 'Agent' &&
                                    b.dept = :dept &&
                                    d.mark IS NULL &&
                                    (UNIX_TIMESTAMP(start_time_sort) -  UNIX_TIMESTAMP(NOW())) < 7200 /* only upcoming 2-hours-worth */
                                ORDER BY start_time_sort, name
                            ";
                break;
            }

            $preparray = array(
                'dept' => $dept
                );

            $statement = $this->db->prepare($query);
            $statement->execute($preparray);

            $data = $statement->fetchAll();

            // store in memcache
            Universal::memcacheStore($key, $data);

            $response = $data;
        }

        // we have valid results in memcache, so let's use it
        else {
            $response = $memcache->data;
        }

        return $response;
    }

    /**
     * Grab all active agents who are currently clocked in.
     * This is the basis for our list of agents showing in Workflow.
     * @param  [type] $channel [description]
     * @return [type]          [description]
     * ( Updated at 06-25-2015 )
     */
    public function agentsClockedIn($dept) {

        // create a unique key based on the unix timestamps added together for the start and end ranges.
        $key = 'agentsClockedIn_' . $dept;
        
        // check if key is set in memcache
        $memcache = Universal::memcacheRetrieve($key, $this->memcacheInterval);

        // if there are no results in memcache, or if the results are expired...
        if(empty($memcache->data) || $memcache->expired === true) {

            $query = "  SELECT
                            a.username,
                            a.asterisk_id,
                            CONCAT(LEFT(a.first_name,7), ' ', LEFT(a.last_name,1)) as name,
                            b.channel as primary_channel,
                            CASE
                                WHEN
                                    UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(CONCAT(DATE_FORMAT(NOW(), '%Y-%m-%d'), ' ', start_time)) > 0
                                THEN
                                    0
                                ELSE
                                    1
                            END as workingEarly
                        FROM
                            users a
                        INNER JOIN
                            parameters b
                        ON
                            a.parameter = b.id
                        INNER JOIN
                            attendance c
                        ON
                            c.agent_username = a.username
                        INNER JOIN
                            schedules2 d
                        ON
                            a.username = d.username
                        INNER JOIN
                            teams e
                        ON
                            e.name = a.team
                        LEFT JOIN
                            attendance_marks f
                        ON
                            f.offending_username = a.username
                            AND f.started >= DATE_FORMAT(NOW(), '%Y-%m-%d 00:00:00')
                            AND f.ended IS NULL
                        WHERE
                            f.offending_username IS NULL &&
                            d.day_of_week = CAST(weekday(NOW()) AS CHAR) &&
                            a.type = 'Agent' &&
                            a.dept = :dept &&
                            a.active = 1 &&
                            c.clocked_in >= DATE_FORMAT(NOW(), '%Y-%m-%d 00:00:00') &&
                            c.clocked_out IS NULL
                        GROUP BY
                            a.username
                        ORDER BY
                            a.username
                        ASC
            ";

            $preparray = array(
                'dept' => $dept
                );

            $statement = $this->db->prepare($query);
            $statement->execute($preparray);

            $data = $statement->fetchAll();

            // store in memcache
            Universal::memcacheStore($key, $data);

            $response = $data;
        }

        // we have valid results in memcache, so let's use it
        else {
            $response = $memcache->data;
        }

        return $response;
    }

    /**
     * Grab all of the currently active interactions for active agents on a team.
     * Polls chats, calls, and tickets.
     * @param  [type] $channel [description]
     * @return [type]          [description]
     * ( Updated at 06-25-2015 )
     */
    public function activeInteractions($dept) {

        // create a unique key based on the unix timestamps added together for the start and end ranges.
        $key = 'activeInteractions_workflow' . $dept;
        
        // check if key is set in memcache
        $memcache = Universal::memcacheRetrieve($key, $this->memcacheInterval);

        // if there are no results in memcache, or if the results are expired...
        // 'as data' is the duration for chats/calls or ticket # for tickets.
        if(empty($memcache->data) || $memcache->expired === true) {

            $query = "  SELECT
                            b.username,
                            CONCAT(LEFT(b.first_name,7), ' ', LEFT(b.last_name,1)) as name,
                            UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(a.answered_at) as data,
                            d.channel as primary_channel,
                            'calls' as touch_channel,
                            NULL as customer_name
                        FROM
                            astronomer_realtime_queues_callers a
                        LEFT JOIN
                            users b
                        ON
                            b.asterisk_id = a.device_number
                        INNER JOIN
                            teams c
                        ON
                            c.name = b.team
                        INNER JOIN
                            parameters d
                        ON
                            b.parameter = d.id
                        WHERE
                            a.answered_at IS NOT NULL &&
                            b.type = 'Agent' &&
                            b.dept = :dept &&
                            b.active = 1

                        UNION

                        SELECT
                            b.username,
                            CONCAT(LEFT(b.first_name,7), ' ', LEFT(b.last_name,1)) as name,
                            UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(a.chat_start_time) as data,
                            d.channel as primary_channel,
                            'chats' as touch_channel,
                            a.name as customer_name
                        FROM
                            chats_current_visitors a
                        LEFT JOIN
                            users b
                        ON
                            b.username = a.agent
                        INNER JOIN
                            teams c
                        ON
                            c.name = b.team
                        INNER JOIN
                            parameters d
                        ON
                            b.parameter = d.id
                        WHERE
                            a.chat_start_time IS NOT NULL &&
                            b.type = 'Agent' &&
                            b.dept = :dept &&
                            b.active = 1

                        UNION

                        SELECT
                            b.username,
                            CONCAT(LEFT(b.first_name,7), ' ', LEFT(b.last_name,1)) as name,
                            a.id as data,
                            d.channel as primary_channel,
                            'tickets' as touch_channel,
                            NULL as customer_name
                        FROM
                            tickets_currently_queued a
                        LEFT JOIN
                            users b
                        ON
                            a.agent_locked = b.hostops_id
                        INNER JOIN
                            teams c
                        ON
                            c.name = b.team
                        INNER JOIN
                            parameters d
                        ON
                            b.parameter = d.id
                        WHERE
                            a.date_locked > UNIX_TIMESTAMP(NOW()) - 60 &&
                            b.type = 'Agent' &&
                            b.dept = :dept &&
                            b.active = 1

                        ORDER BY
                            name
                        ASC
                    ";

            $preparray = array(
                'dept' => $dept
                );

            $statement = $this->db->prepare($query);
            $statement->execute($preparray);

            $data = $statement->fetchAll();

            // store in memcache
            Universal::memcacheStore($key, $data);

            $response = $data;
        }

        // we have valid results in memcache, so let's use it
        else {
            $response = $memcache->data;
        }

        return $response;
    }

    /**
     * All channel statuses per agent.
     * 1 agent could have 3 rows, 1 for each channel
     * @param  [type] $channel [description]
     * @return [type]          [description]
     * ( Updated at 06-25-2015 )
     */
    public function agentChannelStatuses($dept) {

        // create a unique key based on the unix timestamps added together for the start and end ranges.
        $key = 'agentChannelStatuses_workflow' . $dept;
        
        // check if key is set in memcache
        $memcache = Universal::memcacheRetrieve($key, $this->memcacheInterval);

        // if there are no results in memcache, or if the results are expired...
        if(empty($memcache->data) || $memcache->expired === true) {

            $query = "  SELECT
                            a.username,
                            CONCAT(LEFT(a.first_name,7), ' ', LEFT(a.last_name,1)) as name,
                            CASE
                                WHEN
                                    b.chats_status = 'ready'
                                THEN
                                    'active'
                                WHEN
                                    b.chats_status = 'paused'
                                THEN
                                    'paused'
                                WHEN
                                    b.chats_status = 'offline' ||
                                    b.chats_status IS NULL
                                THEN
                                    'offline'
                            END
                            as status,
                            'chats' as channel,
                            e.channel as primary_channel
                        FROM
                            users a
                        LEFT JOIN
                            agent_navbar_data b
                        ON
                            a.username = b.username
                        INNER JOIN
                            teams c
                        ON
                            c.name = a.team
                        INNER JOIN
                            attendance d
                        ON
                            d.agent_username = a.username
                        INNER JOIN
                            parameters e
                        ON
                            a.parameter = e.id
                        WHERE
                            a.type = 'Agent' &&
                            a.dept = :dept &&
                            a.active = 1 &&
                            d.clocked_in >= DATE_FORMAT(NOW(), '%Y-%m-%d 00:00:00') &&
                            d.clocked_out IS NULL


                        UNION


                        SELECT
                            a.username,
                            CONCAT(LEFT(a.first_name,7), ' ', LEFT(a.last_name,1)) as name,
                            CASE
                                WHEN
                                    b.phones_status = 'ready'
                                THEN
                                    'active'
                                WHEN
                                    b.phones_status = 'paused'
                                THEN
                                    'paused'
                                WHEN
                                    b.phones_status = 'offline' ||
                                    b.phones_status IS NULL
                                THEN
                                    'offline'
                            END
                            as status,
                            'calls' as channel,
                            e.channel as primary_channel
                        FROM
                            users a
                        LEFT JOIN
                            agent_navbar_data b
                        ON
                            a.asterisk_id = b.asterisk_id
                        INNER JOIN
                            teams c
                        ON
                            c.name = a.team
                        INNER JOIN
                            attendance d
                        ON
                            d.agent_username = a.username
                        INNER JOIN
                            parameters e
                        ON
                            a.parameter = e.id
                        WHERE
                            a.type = 'Agent' &&
                            a.dept = :dept &&
                            a.active = 1 &&
                            d.clocked_in >= DATE_FORMAT(NOW(), '%Y-%m-%d 00:00:00') &&
                            d.clocked_out IS NULL


                        UNION


                        SELECT
                            a.username,
                            CONCAT(LEFT(a.first_name,7), ' ', LEFT(a.last_name,1)) as name,
                            CASE
                                WHEN
                                    a.hostops_id IN
                                    (
                                        SELECT
                                            agent_locked
                                        FROM
                                            tickets_currently_queued
                                        WHERE
                                            date_locked > UNIX_TIMESTAMP(NOW()) - 60
                                    )
                                THEN
                                    'active'
                                ELSE
                                    'offline'
                            END
                            as status,
                            'tickets' as channel,
                            f.channel as primary_channel
                        FROM
                            users a
                        LEFT JOIN
                            tickets_currently_queued b
                        ON
                            a.hostops_id = b.agent_locked
                        INNER JOIN
                            teams c
                        ON
                            c.name = a.team
                        LEFT JOIN
                            schedules2 d
                        ON
                            a.username = d.username
                        INNER JOIN
                            attendance e
                        ON
                            e.agent_username = a.username
                        INNER JOIN
                            parameters f
                        ON
                            a.parameter = f.id
                        WHERE
                            d.day_of_week = CAST(weekday(NOW()) AS CHAR) &&
                            a.type = 'Agent' &&
                            a.dept = :dept &&
                            a.active = 1 &&
                            e.clocked_in >= DATE_FORMAT(NOW(), '%Y-%m-%d 00:00:00') &&
                            e.clocked_out IS NULL &&
                            b.date_locked > UNIX_TIMESTAMP(NOW()) - 60


                        ORDER BY
                            name
                        ASC
            ";

            $preparray = array(
                'dept' => $dept
                );

            $statement = $this->db->prepare($query);
            $statement->execute($preparray);

            $data = $statement->fetchAll();

            // store in memcache
            Universal::memcacheStore($key, $data);

            $response = $data;
        }

        // we have valid results in memcache, so let's use it
        else {
            $response = $memcache->data;
        }

        return $response;
    }

    /**
     * See who is not logged in by looking at whether or not they are on an exception.
     * Also make sure that they are not logged into any channels.
     * This is essentially a double-check so that if for example an agent went on lunch in the Matrix but came back in Bria, they would still appear normally in Workflow if they started working.
     * @return [type] [description]
     */
    public function notLoggedIntoChannel($dept) {

        // create a unique key based on the unix timestamps added together for the start and end ranges.
        $key = 'notLoggedIntoChannel_workflow' . $dept;
        
        // check if key is set in memcache
        $memcache = Universal::memcacheRetrieve($key, $this->memcacheInterval);

        // if there are no results in memcache, or if the results are expired...
        if(empty($memcache->data) || $memcache->expired === true) {

            $query = "  SELECT
                            CONCAT(LEFT(users.first_name,7), ' ', LEFT(users.last_name,1)) as name
                        FROM
                            users
                        INNER JOIN
                            parameters
                        ON
                            users.parameter = parameters.id
                        INNER JOIN
                            schedules2
                        ON
                            schedules2.username = users.username
                        INNER JOIN
                            attendance_marks
                        ON
                            attendance_marks.offending_username = users.username
                        LEFT JOIN
                            agent_navbar_data
                        ON
                            agent_navbar_data.username = users.username
                        LEFT JOIN
                            tickets_currently_queued
                        ON
                            tickets_currently_queued.agent_locked = users.hostops_id
                        WHERE
                            users.dept = :dept &&
                            users.type = 'Agent' &&
                            users.active = 1 &&
                            schedules2.day_of_week = CAST(weekday(NOW()) AS CHAR) &&
                            attendance_marks.ended IS NULL &&
                            CASE
                                parameters.channel
                                WHEN 'calls'
                                THEN
                                    agent_navbar_data.live_channel = 'none'
                                WHEN 'chats'
                                THEN
                                    agent_navbar_data.live_channel = 'none'
                                WHEN 'tickets'
                                THEN
                                (
                                    tickets_currently_queued.date_locked IS NULL ||
                                    tickets_currently_queued.date_locked = 'offline' >= UNIX_TIMESTAMP(NOW()) - 120
                                )
                                WHEN 'all'
                                THEN
                                    agent_navbar_data.live_channel = 'none'
                            END
                        GROUP BY
                            schedules2.username
            ";

            $preparray = array(
                'dept' => $dept
                );

            $statement = $this->db->prepare($query);
            $statement->execute($preparray);

            $data = $statement->fetchAll();

            // store in memcache
            Universal::memcacheStore($key, $data);

            $response = $data;
        }

        // we have valid results in memcache, so let's use it
        else {
            $response = $memcache->data;
        }

        return $response;
    }

    /**
     * The total # of queued tickets
     * @param  [type] $modifier [description]
     * @return [type]           [description]
     * ( Updated at 06-25-2015 )
     */
    public function queuedTickets($modifier, $dept) {

        // create a unique key based on the unix timestamps added together for the start and end ranges.
        $key = 'queuedTickets_' . $modifier . $dept;
        
        // check if key is set in memcache
        $memcache = Universal::memcacheRetrieve($key, $this->memcacheInterval);

        // if there are no results in memcache, or if the results are expired...
        if(empty($memcache->data) || $memcache->expired === true) {

            switch($modifier) {

                case 'All':
                    $query = "  SELECT
                                    count(*) as count
                                FROM
                                    tickets_currently_queued a
                                LEFT JOIN
                                    ticket_queue_counts b
                                ON
                                    a.queue_number = b.queue_number
                                WHERE
                                    b.dept = :dept
                            ";
                break;

                case 'Over20':
                    $query = "  SELECT
                                    count(*) as count
                                FROM
                                    tickets_currently_queued a
                                LEFT JOIN
                                    ticket_queue_counts b
                                ON
                                    a.queue_number = b.queue_number
                                WHERE
                                    b.dept = :dept &&
                                    CASE
                                        WHEN 
                                            a.date_last_correspond = 0 OR a.date_last_correspond < a.date_customer_response
                                        THEN 
                                            UNIX_TIMESTAMP(NOW()) - a.date_customer_response >= 72000
                                        ELSE 
                                            UNIX_TIMESTAMP(NOW()) - a.date_last_correspond >= 72000
                                    END
                            ";
                break;

                case 'Over12':
                    $query = "  SELECT
                                    count(*) as count
                                FROM
                                    tickets_currently_queued a
                                LEFT JOIN
                                    ticket_queue_counts b
                                ON
                                    a.queue_number = b.queue_number
                                WHERE
                                    b.dept = :dept &&
                                    CASE
                                        WHEN 
                                            a.date_last_correspond = 0 OR a.date_last_correspond < a.date_customer_response
                                    THEN 
                                        UNIX_TIMESTAMP(NOW()) - a.date_customer_response >= 43200
                                    ELSE 
                                        UNIX_TIMESTAMP(NOW()) - a.date_last_correspond >= 43200
                                    END
                            ";
                break;
            }

            $preparray = array(
                'dept' => $dept
                );

            $statement = $this->db->prepare($query);
            $statement->execute($preparray);

            $results = $statement->fetch();
            $data = $results->count;

            // store in memcache
            Universal::memcacheStore($key, $data);

            $response = $data;
        }

        // we have valid results in memcache, so let's use it
        else {
            $response = $memcache->data;
        }

        return $response;
    }

    /**
     * The total # of queued chats or calls by department
     * @param  [type] $channel [description]
     * @return [type]          [description]
     * ( Updated at 06-25-2015 )
     */
    public function currentQueued($channel, $dept) {

        // create a unique key based on the unix timestamps added together for the start and end ranges.
        $key = 'currentQueued_' . $channel . $dept;
        
        // check if key is set in memcache
        $memcache = Universal::memcacheRetrieve($key, $this->memcacheInterval);

        // if there are no results in memcache, or if the results are expired...
        if(empty($memcache->data) || $memcache->expired === true) {

            switch($channel) {

                case 'calls':
                    $query = "  SELECT
                                    count(*) as count
                                FROM
                                    astronomer_realtime_queues_callers a
                                LEFT JOIN
                                	astronomer_realtime_queues b
                                ON
                                	a.fk_queue_number = b.number
                                WHERE
                                    a.answered_at IS NULL &&
                                    b.dept = :dept
                            ";
                break;

                case 'chats':
                    $query = "  SELECT
                                    count(*) as count
                                FROM
                                    chats_current_visitors a
                                LEFT JOIN
                                    chat_groups b
                                ON
                                    b.group_id = a.chat_group
                                WHERE
                                    b.dept = :dept &&
                                    a.state = 'queued'
                            ";
                break;
            }

            $preparray = array(
	            'dept' => $dept
	        );

	        $statement = $this->db->prepare($query);
	        $statement->execute($preparray);

	        $results = $statement->fetch();
            $data = $results->count;

            // store in memcache
            Universal::memcacheStore($key, $data);

            $response = $data;
        }

        // we have valid results in memcache, so let's use it
        else {
            $response = $memcache->data;
        }

        return $response;
    }

    /**
     * Current Wait Time by Channel
     * @param  [type] $channel [description]
     * @return [type]          [description]
     * ( Updated at 06-25-2015 )
     */
    public function currentWaitTime($channel, $dept) {

        // create a unique key based on the unix timestamps added together for the start and end ranges.
        $key = 'currentWaitTime_' . $channel . $dept;
        
        // check if key is set in memcache
        $memcache = Universal::memcacheRetrieve($key, $this->memcacheInterval);

        // if there are no results in memcache, or if the results are expired...
        if(empty($memcache->data) || $memcache->expired === true) {

            switch($channel) {

                case 'calls':

                    $query = "  SELECT
                                    UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(MIN(joined_at)) as current_wait
                                FROM
                                    astronomer_realtime_queues_callers a
                                LEFT JOIN
                                    astronomer_realtime_queues b
                                ON
                                    a.fk_queue_number = b.number
                                WHERE
                                    a.answered_at IS NULL &&
                                    b.dept = :dept
                            ";
                break;

                case 'chats':

                    $query = "  SELECT
                                    UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(MIN(queue_start_time)) as current_wait
                                FROM
                                    chats_current_visitors a
                                LEFT JOIN
                                    chat_groups b
                                ON
                                    b.group_id = a.chat_group
                                WHERE
                                    a.chat_start_time IS NULL AND
                                    b.dept = :dept AND
                                    a.state = 'queued'
                            ";
                break;
            }

            $preparray = array(
                'dept' => $dept
            );

            $statement = $this->db->prepare($query);
            $statement->execute($preparray);

            $results = $statement->fetch();
            $data = $results->current_wait;

            // store in memcache
            Universal::memcacheStore($key, $data);

            $response = $data;
        }

        // we have valid results in memcache, so let's use it
        else {
            $response = $memcache->data;
        }

        if(is_null($response) || !isset($response)) {
            return 0;
        } else {
            return $response;
        }
    }
}