<?php

class TicketsFirstResponse extends Universal {

   public function upsertFirstResponseTime($data) {

      	$query = "  INSERT INTO 
                    	tickets_first_response
                  	(
                     	ticket_id, 
                     	date_customer_response, 
                     	date_agent_response, hostops_id
                  	)
                  	VALUES
                  	(
                     	:ticket_id, 
                     	:date_customer_response, 
                     	:date_agent_response, 
                     	:hostops_id
                  	)
                  	ON 
                     	DUPLICATE KEY
                  	UPDATE 
                     	date_agent_response = :date_agent_response, 
                     	hostops_id = :hostops_id
               ";

		$statement = $this->db->prepare($query);
		$statement->execute(
		array(
			'ticket_id' => $data->ticket_id,
			'date_customer_response' => $data->date_customer_response,
			'date_agent_response' => $data->date_agent_response,
			'hostops_id' => $data->hostops_id
		));
   	}
}