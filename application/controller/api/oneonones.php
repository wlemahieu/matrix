<?php

// retrieve post data from AngularJS
$data = json_decode(file_get_contents("php://input"));

// if the data is empty, don't proceed
if(!empty($data)) {

	// load models
	require APP . 'model/db-model.php';
	require APP . 'model/universal.php';
	require APP . 'model/ENPS.php';
	require APP . 'model/OneOnOnes.php';
	require APP . 'model/Users.php';

	// instantiate classes
	$ENPS = new ENPS($this->db);
	$OneOnOnes = new OneOnOnes($this->db);
	$Users = new Users($this->db);

	// store route / action for this API
	$route = $data->route;
	$action = $data->action;

	// restrict parameters based on user type
	if($_SESSION['userinfo']->type === 'Agent') {
		$data->leadership_username = -1;
		$data->agent_username = $_SESSION['userinfo']->username;
		$data->team = -1;
		$data->dept = -1;
		$data->active = 1;
		$edit_ability = false;
	}
	elseif($_SESSION['userinfo']->type === 'Supervisor') {
		$data->leadership_username = $_SESSION['userinfo']->username;
		$data->team = -1;
		if(!isset($data->agent_username) || empty($data->agent_username)) {
			$data->agent_username = -1;
		}
		$data->dept = -1;
		$data->active = -1;
		$edit_ability = true;
	}
	elseif($_SESSION['userinfo']->type === 'Manager') {
		$data->leadership_username = -1;
		$data->team = -1;
		if(!isset($data->agent_username) || empty($data->agent_username)) {
			$data->agent_username = -1;
		}
		$data->dept = $_SESSION['userinfo']->dept;
		$data->active = -1;
		$edit_ability = true;
	}

	// create a return payload
	$return = new stdClass();

	// questions-related
	if($route == 'questions') {

		if($action == 'read') {
			echo json_encode($OneOnOnes->readQuestions($_SESSION['userinfo']->dept), JSON_NUMERIC_CHECK);
		}

		elseif($action == 'save' && $edit_ability) {

			$payload = new stdClass();
			$payload->position = $data->payload->position;
			$payload->question = $data->payload->question;
			$payload->username = $_SESSION['userinfo']->username;
			$payload->dept = $_SESSION['userinfo']->dept;
			echo json_encode($OneOnOnes->saveQuestion($payload), JSON_NUMERIC_CHECK);
		}

		elseif($action == 'deactivate' && $edit_ability) {
			$payload = new stdClass();
			$payload->question_id = $data->question_id;
			$payload->username = $_SESSION['userinfo']->username;
			$OneOnOnes->deactivateQuestion($payload);
		}

		elseif($action == 'activate' && $edit_ability) {
			$payload = new stdClass();
			$payload->question_id = $data->question_id;
			$payload->username = $_SESSION['userinfo']->username;
			$OneOnOnes->activateQuestion($payload);
		}
	}
	// entire reviews
	elseif($route == 'reviews') {

		if($action == 'read') {

			$payload = new stdClass();
			$payload->agent_username = $data->agent_username;
			$payload->leadership_username = $data->leadership_username;
			$payload->team = $data->team;
			$payload->dept = $data->dept;
			$payload->active = $data->active;

			$data->startRange = date("Y-m-d", strtotime($data->dateRange->start));
			$data->endRange = date("Y-m-d", strtotime($data->dateRange->end));
			$payload->dateRange = $data->dateRange;

			// get one-on-ones for the date range
			$oneonones = $OneOnOnes->fetch($payload);

			// iterate through questions and transform key to be the id of the row
			$questions = array();
			foreach($OneOnOnes->readQuestions($_SESSION['userinfo']->dept) as $key => $obj) {
				$questions[$obj->question_id] = $obj;
			}

			// if we have oneonones...
			if(!empty($oneonones)) {

				// create object to store details for the one-on-one
				$return->details = array();

				// iterate through the one-on-ones and build our details object using the object id as the key for the details array
				foreach($oneonones as $key => $obj) {

					// create a new property called 'answers' which we'll use to store the answers for this object in.
					$obj->answers = $OneOnOnes->readAnswers($obj->id);
					// assume the 1on1 is complete until we prove otherwise below
					$obj->complete = true;

					// iterate through each answer
					foreach($obj->answers as $subkey => $subobj) {
						// store the question this answer is answering
						$subobj->question = $questions[$subobj->question_id]->question;
						// store the position of the question
						$subobj->position = $questions[$subobj->question_id]->position;
						// if there are any answers incomplete, mark it here
						if(is_null($subobj->answer) && $_SESSION['userinfo']->type === 'Agent'){
							$obj->complete = false;
						}
					}

					// store the question and answers in our details property if it's complete, or if they are a lead or manager
					if(($obj->complete && !is_null($obj->finished)) || $_SESSION['userinfo']->type === 'Supervisor' || $_SESSION['userinfo']->type === 'Manager') {
						array_push($return->details, $obj);
					}
				}

			}

			echo json_encode($return, JSON_NUMERIC_CHECK);
		}

		elseif($action == 'save' && $edit_ability) {

			// build payload for when we save the one on one answers for the newly created one on one.
			$payload = new stdClass();
			$payload->leadership_username = $_SESSION['userinfo']->username;
			$payload->agent_username = $data->agent_username;

			$results = $OneOnOnes->fetchLast($payload->agent_username);

			// if the last one on one was NPS_able, this one should not be
			if($results->enps_able) {
				$payload->enps_able = 0;
			} else {
				$payload->enps_able = 1;
			}

			// if there is an id sent, it's an update (save), not a new one on one
			if(isset($data->id)) {
				$payload->id = $data->id;
			}

			// must be a manager saving the one-on-one because -1 was passed through.
			// fetch the team based on the user.
			if($data->team === -1) {

				// grab this user's details
				$data = $Users->fetchUserProfile($payload->agent_username);
				$payload->team = $data->team;
			} else {
				$payload->team = $data->team;
			}

			// is this an update or a new one?
			if(isset($payload->id)) {
				$OneOnOnes->update($payload);
				$one_on_one_id = $payload->id;
			} else {
				$one_on_one_id = $OneOnOnes->create($payload);
			}

			// build return response of the one on one ID
			$return = new stdClass();
			$return->one_on_one_id = $one_on_one_id;

			// return one-on-one unique id for use with saving answers
			echo json_encode($return, JSON_NUMERIC_CHECK);
		}

		elseif($action == 'finish' && $edit_ability) {

			$OneOnOnes->finish($data->id);
		}

		elseif($action == 'remove' && $edit_ability) {

			$OneOnOnes->remove($data->id);
		}

		elseif($action == 'accept' && !$edit_ability) {

			// find the agent this 1on1 is for
			$owner = $OneOnOnes->findAgent($data->id);

			// verify the agent accepting this is the one who should be accepting it.
			// also prevent a user from providing ENPS for pre-acceptance 1on1s.
			// any rows with 0000-00-00 00:00:00 for accepted_datetime are pre-acceptance.
			// any rows with null, are post-acceptance and acceptable.
			if($owner->agent_username === $_SESSION['userinfo']->username && is_null($owner->accepted)) {

				// fetch this 1on1 so we can determine who the team/leader
				$single = $OneOnOnes->fetchSingle($data->id);

				// if they passed a rating and reason through, save it
				if(isset($data->rating) && isset($data->reason)) {

					// store ENPS rating, reason, team name, and leadership name
					$payload = new stdClass();
					$payload->rating = $data->rating;
					$payload->reason = $data->reason;
					$payload->team = $single->team;
					$payload->leadership_username = $single->leadership_username;

					$ENPS->saveResponse($payload);
				}

				// verify the agent accepting this id owns this 1on1
				$OneOnOnes->accept($data->id);
			}
		}
	}
	// answers-related
	elseif($route == 'answers') {

		if($action == 'read') {

			// build return payload
			$return = new stdClass();
			$return->answers = $OneOnOnes->readAnswers($data->id);
			echo json_encode($return, JSON_NUMERIC_CHECK);
		}

		elseif($action == 'save' && $edit_ability) {

			// iterate through each answer and obtain the question_id that's being answered then insert the data
			foreach($data->answers as $key => $obj) {

				// create payload for saving the answer
				$payload = new stdClass();
				$payload->question_id = $obj->question_id;
				$payload->one_on_one_id = $data->one_on_one_id;

				// if there is an answer for this question, add it to the payload
				if(isset($obj->answer)) {
					$payload->answer = $obj->answer;
				} else {
					$payload->answer = NULL;
				}
				
				// save the answer and set the return id using the new row id, or 0 if it's being updated...
				$return = new stdClass();
				$return->id = $OneOnOnes->saveAnswer($payload);
			}
		}
	}
}