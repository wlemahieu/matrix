<?php

class OneOnOnes extends Universal {

    public function activateQuestion($payload) {

        $query = "  UPDATE
                        one_on_ones_questions
                    SET 
                        active = 1,
                        activated = NOW(),
                        activated_username = :username
                    WHERE
                        question_id = :question_id
                ";
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'question_id' => $payload->question_id,
                'username' => $payload->username
                )
        );
    }

    public function deactivateQuestion($payload) {

        $query = "  UPDATE
                        one_on_ones_questions
                    SET 
                        active = 0,
                        deactivated = NOW(),
                        deactivated_username = :username
                    WHERE
                        question_id = :question_id
                ";
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'question_id' => $payload->question_id,
                'username' => $payload->username
                )
        );
    }

    public function finish($id) {

        $query = "  UPDATE
                        one_on_ones
                    SET
                        finished_datetime = NOW()
                    WHERE
                        id = :id
                ";
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'id' => $id
                )
        );
    }

    public function findAgent($id) {

        $query = "  SELECT
                        agent_username,
                        accepted_datetime as accepted
                    FROM
                        one_on_ones
                    WHERE
                        id = :id
                ";
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'id' => $id
                )
        );

        return $statement->fetch();
    }

    public function accept($id) {

        $query = "  UPDATE
                        one_on_ones
                    SET
                        accepted_datetime = NOW()
                    WHERE
                        id = :id
                ";
        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'id' => $id
                )
        );
    }

    public function create($payload) {

        $query = "  INSERT INTO
                        one_on_ones
                        (team, leadership_username, agent_username, created, last_saved, enps_able)
                    VALUES
                        (:team, :leadership_username, :agent_username, NOW(), NOW(), :enps_able)
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'leadership_username' => $payload->leadership_username,
                'agent_username' => $payload->agent_username,
                'team' => $payload->team,
                'enps_able' => $payload->enps_able
                )
        );

        return $this->db->lastInsertId();
    }

    public function update($payload) {

        $query = "  UPDATE
                        one_on_ones
                    SET
                        team = :team,
                        leadership_username = :leadership_username, 
                        agent_username = :agent_username, 
                        last_saved = NOW()
                    WHERE
                        id = :id
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'leadership_username' => $payload->leadership_username,
                'agent_username' => $payload->agent_username,
                'team' => $payload->team,
                'id' => $payload->id
                )
        );
    }

    public function remove($id) {

        $query = "  UPDATE
                        one_on_ones
                    SET
                        active = 0
                    WHERE
                        id = :id
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'id' => $id
                )
        );
    }

    public function saveAnswer($payload) {

        $query = "  INSERT INTO
                        one_on_ones_answers
                        (question_id, one_on_one_id, answer, answered)
                    VALUES
                        (:question_id, :one_on_one_id, :answer, NOW())
                    ON DUPLICATE KEY 
                    UPDATE
                        answer = :answer,
                        answered = NOW()
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'question_id' => $payload->question_id,
                'one_on_one_id' => $payload->one_on_one_id,
                'answer' => $payload->answer
                )
        );
    }

    public function saveQuestion($payload) {

        $query = "  INSERT IGNORE INTO
                        one_on_ones_questions
                    (question, position, created, created_username, dept)
                    VALUES
                        (:question, :position, NOW(), :username, :dept)
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'position' => $payload->position,
                'question' => $payload->question,
                'username' => $payload->username,
                'dept' => $payload->dept
                )
        );

        return $this->db->lastInsertId();
    }

    public function readQuestions($dept) {

        $query = "  SELECT
                        *
                    FROM
                        one_on_ones_questions
                    WHERE
                        dept = :dept
                ";

        $statement = $this->db->prepare($query);

        $statement->execute(
            array(
                'dept' => $dept
                )
        );
        return $statement->fetchAll();
    }

    public function readAnswers($id) {

        $query = "  SELECT
                        *
                    FROM
                        one_on_ones_answers
                    WHERE
                        one_on_one_id = :id
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'id' => $id
                )
        );

        return $statement->fetchAll();
    }

    public function fetch($payload) {

        $query = "  SELECT
                        *,
                        UNIX_TIMESTAMP(created)*1000 as created, /* convert S to MS */
                        UNIX_TIMESTAMP(finished_datetime)*1000 as finished, /* convert S to MS */
                        UNIX_TIMESTAMP(accepted_datetime)*1000 as accepted /* convert S to MS */
                    FROM
                        one_on_ones a
                    LEFT JOIN
                        teams b
                    ON
                        a.team = b.name
                    WHERE
                        ( a.active = :active || :active = -1 ) &&
                        ( a.agent_username = :agent_username || :agent_username = -1 ) &&
                        ( a.leadership_username = :leadership_username || :leadership_username = -1 ) &&
                        ( a.team = :team || :team = -1 ) && 
                        ( b.dept = :dept || :dept = -1 ) &&
                        UNIX_TIMESTAMP(created) >= UNIX_TIMESTAMP(STR_TO_DATE(:startRange, '%m/%d/%Y')) &&
                        UNIX_TIMESTAMP(created) < UNIX_TIMESTAMP(DATE_ADD(STR_TO_DATE(:endRange, '%m/%d/%Y'), INTERVAL 1 DAY))
                    ORDER BY
                        a.finished_datetime
                    DESC
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'active' => $payload->active,
                'agent_username' => $payload->agent_username,
                'leadership_username' => $payload->leadership_username,
                'team' => $payload->team,
                'dept' => $payload->dept,
                'startRange' => $payload->dateRange->start,
                'endRange' => $payload->dateRange->end
                )
        );

        return $statement->fetchAll();
    }

    public function fetchSingle($id) {

        $query = "  SELECT
                        *,
                        UNIX_TIMESTAMP(created)*1000 as created, /* convert S to MS */
                        UNIX_TIMESTAMP(finished_datetime)*1000 as finished, /* convert S to MS */
                        UNIX_TIMESTAMP(accepted_datetime)*1000 as accepted /* convert S to MS */
                    FROM
                        one_on_ones
                    WHERE
                        id = :id
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'id' => $id
                )
        );

        return $statement->fetch();
    }

    public function fetchLast($username) {

        $query = "  SELECT
                        enps_able
                    FROM
                        one_on_ones
                    WHERE
                        agent_username = :username
                    ORDER BY 
                        id 
                    DESC 
                    LIMIT 1
                ";

        $statement = $this->db->prepare($query);
        $statement->execute(
            array(
                'username' => $username
                )
        );

        return $statement->fetch();
    }
}