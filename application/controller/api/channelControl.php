<?php

// POST data from AngularJS
$data = json_decode(file_get_contents("php://input"));

// if data is passed...
if(!empty($data)) {

    // load models
    require APP . 'model/db-model.php';
    require APP . 'model/universal.php';
    require APP . 'model/AgentNavbar.php';
    require APP . 'model/ChannelControl.php';
    require APP . 'model/ClockingControl.php';

    // instantiate classes
    $AgentNavbar = new AgentNavbar($this->db);
    $ChannelControl = new ChannelControl($this->db);
    $ClockingControl = new ClockingControl($this->db);

    /**
     * username             required    The agent's username. We also prevent agents from passing another agent's username / asterisk_id.
     * asterisk_id          required    Unique phone id tied to asterisk server.
     * scope                required    This is how the script is being used and will dictate clockin/clockout. Can be be 'agent' or 'workflow'
     * command              required    Login, logout, pause, unpause.
     * live_channel         required    The live channel we're dealing with.
     * last_channel         automatic   The last channel (sometimes current channel) they are in. For ticket agents, forget last channel upon logout.
     */

    $scope = $data->scope;
    $command = $data->command;

    // prevent agent-hacking by getting their username and asterisk id from their $_SESSION
    // else leadership workflow usage
    if($_SESSION['userinfo']->type == 'Agent') {
        
        $username = $_SESSION['userinfo']->username;
        $asterisk_id = $_SESSION['userinfo']->asterisk_id;
    } else {
        $username = $data->username;
        $asterisk_id = $data->asterisk_id;
    }

    // live channel
    if(!empty($data->live_channel)) {
        $live_channel = $data->live_channel;
    } else {
        $live_channel = 'none';
    }

    // last channel
    // if $scope is 'exception', don't mark their last channel as none. 
    // reason being, we want to preserve it for when they come back from exception so they immediately log into that channel
    if($scope == 'agent' && $command == 'logout') {
        $last_channel = 'none';
    } else {
        $last_channel = $live_channel;
    }

    /*
    CLOCK IN/OUT

        Restrictions in effect:
            Manager / Lead manipulations of agent channels can't trigger a clock-in or clock-out.
                This is why there are no parameters passed through to these two functions, self-serve.
     */
    if($scope == 'agent' && $command == 'logout') {
        $ClockingControl->handleClockOut();
    }
    elseif($scope == 'agent' && $command == 'login') {
        $ClockingControl->handleClockIn();
    }

    /*
    CHANNEL COMMAND
        Depending on the live_channel being manipulated, let's pass the command we want to that channel's action controller.
        No live_channel set? No need to log anyone out.
        This dual-if also allows for logging out of both channels if the user is logged-in to both.
     */
    $ChannelControl->channelController($username, $asterisk_id, $live_channel, $command);

    // build payload
    $payload = new stdClass();
    $payload->username = $username;
    $payload->asterisk_id = $asterisk_id;
    $payload->last_channel = $last_channel;
    $payload->live_channel = $live_channel;

    // when the user performs an action, immediately update the other things
    if($live_channel == 'phones') {

        $payload->chats_status = 'offline';
        if($command == 'logout') {
            $payload->phones_status = 'offline';
        } else {
            $payload->phones_status = 'ready';
        }
        $payload->tickets_status = 'offline';
    } elseif($live_channel == 'chats') {

        if($command == 'logout') {
            $payload->chats_status = 'offline';
        } else {
            $payload->chats_status = 'paused';
        }
        $payload->phones_status = 'offline';
        $payload->tickets_status = 'offline';

    } elseif($live_channel == 'tickets') {

        $payload->chats_status = 'offline';
        $payload->phones_status = 'offline';
        if($command == 'logout') {
            $payload->tickets_status = 'offline';
        } else {
            $payload->tickets_status = 'ready';
        }
    }

    if($command == 'logout') {
        $payload->currently_clocked_in = 0;
    } else {
        $payload->currently_clocked_in = 1;
    }

    // save data in session now for chain-calls by end-user (this data is originally stored via cron, agent_navbar)
    if($_SESSION['userinfo']->type == 'Agent') {

        $channelStatus = $live_channel . '_status';
        
        // logging out
        if($command == 'logout') {
            $_SESSION['userinfo']->navbar->$channelStatus = 'offline';
        }else {
            $_SESSION['userinfo']->navbar->$channelStatus = 'ready';
        }

        $_SESSION['userinfo']->navbar->live_channel = $live_channel;
        $_SESSION['userinfo']->navbar->last_channel = $last_channel;
    }

    // deliver payload
    $AgentNavbar->upsertAgentNavbarData($payload);
}