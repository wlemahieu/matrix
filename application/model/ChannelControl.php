<?php

class ChannelControl extends Universal {

    // Decide which actionController to use below
    public function channelController($username, $asterisk_id, $channel, $command) {

        if($channel == 'chats' || $channel == 'all') {
            $this->chatActionController($username, $command);
        }
        
        if($channel == 'phones' || $channel == 'calls' || $channel == 'all') {
            $this->phoneActionController($asterisk_id, $command);
        }

        if($channel == 'tickets' || $channel == 'all') {
            $this->ticketActionController($username, $command);
        }
    }

    public function ticketActionController($username, $command) {
        switch($command) {

            case 'login':
                $this->enterTicketQueue();
            break;
            case 'logout':
                $this->exitTicketQueue();
            break;
        }
    }

    private function enterTicketQueue() {

        $query = "  INSERT INTO 
                        ticket_queue_clocks 
                    (
                        agent_username,
                        clocked_in
                    )
                    VALUES 
                    (
                        :agent_username,
                        NOW()
                    )
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'agent_username' => $_SESSION['userinfo']->username
                )
            );
    }

    private function exitTicketQueue() {

        $query = "  UPDATE
                        ticket_queue_clocks 
                    SET 
                        clocked_out = NOW()
                    WHERE
                        agent_username = :agent_username &&
                        clocked_out IS NULL
                    LIMIT 1
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'agent_username' => $_SESSION['userinfo']->username
                )
            );
    }

    // send a command to LiveChat Inc servers
    public function chatActionController($username, $command) {

        //Depending on the command, we need to hit the API url with a specific method
        switch($command) {

            case 'pause':
                $method = "PUT";
                $postFields = array('status'=>'not accepting chats');
            break;
            case 'unpause':
                $method = "PUT";
                $postFields = array('status'=>'accepting chats');
            break;
            case 'login':
                //do nothing since the API cannot log users in
                $method = "GET";
                $postFields = array();
            break;
            case 'logout':
                $method = "PUT";
                $postFields = array('status'=>'offline');
            break;
            case 'status':
                $method = "GET";
                $postFields = array();
            break;
            case 'changetoadmin':
                $method = "PUT";
                $postFields = array('permission'=>'administrator');
            break;
            default:
                $method = "GET";
                $postFields = array();
            break;
        }

        // call the API
        $url = "https://api.livechatinc.com/agents/" . $username . "@mediatemple.net";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, 'tstein@mediatemple.net:7bc118e4242a0eeb383a205e13cd6aa7');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: multipart/form-data', 'Accept: */*', 'X-API-Version:2'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        $object = json_decode(curl_exec($ch));

        echo ucwords(@$object->status);

        if(@$object->status=='accepting chats') {

            /**
             * If the user is accepting chats, end the exception mark they currently have open.
             * This prevents discrepancies in the Attendance_Marks tables for people who come back from a break by simply accepting chats, instead of hitting "Come Back".
             * @var string
             */

            $query = "
            UPDATE attendance_marks
            SET ended = :ended
            WHERE ended IS NULL
            && offending_username = :username
            LIMIT 1
            ";

            $prep = $this->db->prepare($query);
            $prep->execute(array(':username'=>$username,
                ':ended'=>date('Y-m-d H:i:s')));
        }
    }

    // send a command to Asterisk phone server
    public function phoneActionController($asterisk_id, $command) {

        switch($command) {

            case 'pause':
                $method = "POST";
                $dir = "{$asterisk_id}/pause";
            break;
            case 'unpause':
                $method = "DELETE";
                $dir = "{$asterisk_id}/pause";
            break;
            case 'login':
                $method = "POST";
                $dir = "{$asterisk_id}/queues";
            break;
            case 'logout':
                $method = "DELETE";
                $dir = "{$asterisk_id}/queues";
            break;
            case 'status':
                $method = "GET";
                $dir = "{$asterisk_id}/pause";
            break;
            default:
                $method = "GET";
                $dir = "{$asterisk_id}/pause";
            break;
        }

        $url = "https://astronomer.mtvoip.net/api/realtime/devices/{$dir}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        echo curl_exec($ch);
        $ch = null;
    }
}