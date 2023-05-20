<?php
// test-emulate a user from live table. alternatively, I should use a fake data-set which would be easier.
//$_SESSION['userinfo']->asterisk_id = 2570;
//$_SESSION['userinfo']->asterisk_id = 2182;

// retrieve post data from AngularJS
$postdata = json_decode(file_get_contents("php://input"));

// if the postdata is empty, don't proceed
if(!empty($postdata)) {

    // requirements
    require APP . 'model/db-model.php';
    require APP . 'model/universal.php';
    require APP . 'model/CallerInfo.php';

    $CallerInfo = new CallerInfo($this->db);

    // pull the active call for my user
    $data = $CallerInfo->fetchCallerInfo($_SESSION['userinfo']->asterisk_id);

    // create a return object
    $return = new stdClass();

    // READ caller info
    if($postdata->action === 'read') {

        // make sure there is data returned for the caller
        if(!empty($data)) {

            // AUTHED caller
            if($data->account_id != null && $data->contact_id != null) {
                $data->hostops_url = "https://hostops.mediatemple.net/account/?account=" . $data->account_id . "&contact=" . $data->contact_id . "&reason=" . htmlentities(urlencode($data->reason)) . "&view=takecall";
                $return->authed = true;
            } 
            // UNAUTHED caller
            else {
                $data->hostops_url = null;
                $return->authed = false;
            }

            // call is active and return data from fetchCallerInfo
            $return->active = true;
            $return->data = $data;
        }

        // no data returned for the caller
        else {
            $return->data = null;
            $return->authed = false;
            $data = new stdClass();
            $data->active = false;
        }
    }

    echo json_encode($return, JSON_NUMERIC_CHECK);
}