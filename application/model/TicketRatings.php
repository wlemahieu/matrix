<?php

class TicketRatings extends Universal {

   public function updateTicketRating($data) {

      $query = "  UPDATE 
                     tickets
                  SET
                     rating_timeliness = 
                     CASE
                         WHEN 
                           :rating_timeliness != -1
                     THEN
                           :rating_timeliness
                     ELSE
                           rating_timeliness
                     END,

                     rating_quality = 
                     CASE
                         WHEN 
                           :rating_quality != -1
                     THEN
                           :rating_quality
                     ELSE
                           rating_quality
                     END
                  WHERE 
                     ticket_history_id = :ticket_history_id
               ";

      $statement = $this->db->prepare($query);
      $statement->execute(
         array(
            'rating_quality' => $data->rating_quality,
            'rating_timeliness' => $data->rating_timeliness,
            'ticket_history_id' => $data->ticket_history_id
         ));
   }
}