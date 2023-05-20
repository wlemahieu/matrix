<?php

class Auxiliary extends Universal {

    /**
     * Formats a given amount of seconds into minutes and seconds and puts them in an array so you can output them how you please.
     * @param  [type] $seconds [description]
     * @return [type]          [description]
     */
    public function formatSecondsToTime($seconds) {

        $hours = 0;
        $x = $seconds / 60;
        $x = explode('.', $x);
        $remainingMinutes = $x[0];
        $remainingSeconds = $seconds - ($remainingMinutes*60);

        /*
        if($remainingMinutes >= 60)
        {
            $x = $remainingMinutes / 60;
            $x = explode('.', $x);
            $hours = $x[0];
            $remainingMinutes = $remainingMinutes - ($hours*60);
        }
        */

        $array = array();
        //$array['hours'] = $hours;
        $array['minutes'] = $remainingMinutes;
        $array['seconds'] = $remainingSeconds;

        return $array;
    }
}