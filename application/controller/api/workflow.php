<?
/**
 * Workflow Data API
 */
require APP . 'model/db-model.php';
require APP . 'model/universal.php';
require APP . 'model/Auxiliary.php';
require APP . 'model/Workflow.php';

/* instantiate two classes we need to use */
$Auxiliary = new Auxiliary($this->db);
$Workflow = new Workflow($this->db);

/* the object our data will all be contained within */
$workflow = new stdClass();

/* unread conversocial messages */
$workflow->conversocialUnread = $Workflow->conversocialUnread();

/* queue data by channel */
$workflow->chatsQueued = $Workflow->currentQueued('chats', $_SESSION['userinfo']->dept);
$workflow->callsQueued = $Workflow->currentQueued('calls', $_SESSION['userinfo']->dept);
$workflow->ticketsQueued = $Workflow->queuedTickets('All', $_SESSION['userinfo']->dept);
$workflow->ticketsQueuedOver20Hours = $Workflow->queuedTickets('Over20', $_SESSION['userinfo']->dept);
$workflow->ticketsQueuedOver12Hours = $Workflow->queuedTickets('Over12', $_SESSION['userinfo']->dept);

/* live-channel wait times */
$workflow->chatWaitTime = $Workflow->currentWaitTime('chats', $_SESSION['userinfo']->dept);
$workflow->callWaitTime = $Workflow->currentWaitTime('calls', $_SESSION['userinfo']->dept);

/* fetch agents and their channel statuses / data */
$agentsClockedIn = $Workflow->agentsClockedIn($_SESSION['userinfo']->dept);

/* fetch all currently active interactions */
$activeInteractions = $Workflow->activeInteractions($_SESSION['userinfo']->dept);

/* fetch all of our agent's channel statuses */
$agentChannelStatuses = $Workflow->agentChannelStatuses($_SESSION['userinfo']->dept);

/* organize our data into a multi-dimensional array */
$agentsClockedInObject = new stdClass();

/* we will put our agents into one of these three objects, depending on their PRIMARY CHANNEL */
$agentsClockedInObject->chats = new stdClass();
$agentsClockedInObject->calls = new stdClass();
$agentsClockedInObject->tickets = new stdClass();

/* iterate through agents that are clocked in and begin data organization */
foreach($agentsClockedIn as $key => $object) {

	$primary_channel = $object->primary_channel;
	$username = $object->username;
	$name = $object->name;
	$asterisk_id = $object->asterisk_id;
	$workingEarly = $object->workingEarly;

	/* create an object for each user */
	if(!isset($agentsClockedInObject->$primary_channel->$username)) {

		$agentsClockedInObject->$primary_channel->$username = new stdClass();
		$agentsClockedInObject->$primary_channel->$username->touches = new stdClass();
		$agentsClockedInObject->$primary_channel->$username->channels_available = new stdClass();

		/* set their defaults to 0 for now, below in a different loop, we will update these values */
		$agentsClockedInObject->$primary_channel->$username->channels_available->chats = 0;
		$agentsClockedInObject->$primary_channel->$username->channels_available->calls = 0;
		$agentsClockedInObject->$primary_channel->$username->channels_available->tickets = 0;

		/* store some user-info as well for use in the view */
		$agentsClockedInObject->$primary_channel->$username->name = $name;
		$agentsClockedInObject->$primary_channel->$username->asterisk_id = $asterisk_id;
		$agentsClockedInObject->$primary_channel->$username->workingEarly = $workingEarly;
	}
}

/* iterate through all active interactions, and pair them with their user's respective array key. */
foreach($activeInteractions as $key => $object) {

	$primary_channel = $object->primary_channel;
	$username = $object->username;
	$touch_channel = $object->touch_channel;

	/* push the chat/call duration or ticket ID */
	if(!empty($object->data)) {

		/* only add the touches to their object if they exist. 
		For example, if someone leaves for the day and has a ticket locked, don't add their touches to their non-existent object.
		If it's added, there may be a ticket touch floating around the Workflow with no username attached to it, because we are only using agents who are SIGNED IN FOR THE DAY*/
		if(isset($agentsClockedInObject->$primary_channel->$username)) {

			// if this array is not existent, create it
			if(!isset($agentsClockedInObject->$primary_channel->$username->touches->$touch_channel)) {
				$agentsClockedInObject->$primary_channel->$username->touches->$touch_channel = array();
			}

			$obj = new stdClass();
			$obj->duration = $object->data;

			if(isset($object->customer_name)) {
				$obj->customer_name = $object->customer_name;
			}

			// add touch to channel array
			array_push($agentsClockedInObject->$primary_channel->$username->touches->$touch_channel, $obj);
		}
	}
}

/* iterate through all channel statuses, and pair them with their user's respective array key. */

foreach($agentChannelStatuses as $key => $object) {

	$primary_channel = $object->primary_channel;
	$username = $object->username;
	$channel = $object->channel;

	//print_R($agentsClockedInObject);

	/* Same as above, only set information within if the user is actually being defined in our first query, the query that grabs all the users. */
	if(isset($agentsClockedInObject->$primary_channel->$username)) {
		
		/* Active = 1 */
		if($object->status == 'active') {
			/* define wrap-up */
			if($channel == 'calls' && $workflow->callsQueued > 0 && empty($agentsClockedInObject->$primary_channel->$username->touches->calls)) {
				$agentsClockedInObject->$primary_channel->$username->wrap_up = 1;
			}
			else {
				$agentsClockedInObject->$primary_channel->$username->wrap_up = 0;
			}

			$agentsClockedInObject->$primary_channel->$username->channels_available->$channel = 1;
		}

		/* Paused = 2 */
		elseif($object->status == 'paused') {
			$agentsClockedInObject->$primary_channel->$username->channels_available->$channel = 2;
		}
		/* Offline = 0 */
		elseif($object->status == 'offline') {
			$agentsClockedInObject->$primary_channel->$username->channels_available->$channel = 0;
		}
		else {
			$agentsClockedInObject->$primary_channel->$username->channels_available->$channel = 0;
		}
	}
}

$workflow->agents = $agentsClockedInObject;

$workflow->notLoggedIntoChannel = $Workflow->notLoggedIntoChannel($_SESSION['userinfo']->dept);

/*
Right-hand portion Workflow Upcoming Schedule & Active Exceptions
 */
$workflow->workflowScheduleItemsUpcoming = $Workflow->workflowScheduleItems('Upcoming', $_SESSION['userinfo']->dept);
$workflow->workflowScheduleItemsNow = $Workflow->workflowScheduleItems('Now', $_SESSION['userinfo']->dept);

echo json_encode($workflow, JSON_NUMERIC_CHECK);