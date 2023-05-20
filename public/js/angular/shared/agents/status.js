(function() {

    /*
    Agent Statuses

    This module is used for fetching the agent's statuses
    */

    angular

    .module('AgentStatuses', [])

    .run(function($rootScope, $interval, API) {

        // initialize dashboard refresh (real, non-emulating users only)
        if($rootScope.userinfo.emulating == 0) {

            payload = {};
            payload.username = $rootScope.userinfo.username;
            payload.asterisk_id = $rootScope.userinfo.asterisk_id;

            // initial load
            API.call( 'agentStatuses', payload ).then(function() {
                //console.log($rootScope.api.agentStatuses.live_channel);
            });

            // 15s interval
            $rootScope.agentStatusesInterval = $interval(function() {

                // prevent the agent-status refresh from happening right around when the user interacts
                // with the navbar. this provides a seamless ux (but is gross because nodejs could handle this better)
                var diff;

                if($rootScope.modified['navbar']) {
                    diff = (Date.now() - $rootScope.modified['navbar']) / 1000;
                }

                // if there was a user-action in the navbar within the past 10 seconds, don't grab a new agent status.
                if($rootScope.agentActionComplete && diff > 10) {

                    // grab agent statuses
                    API.call( 'agentStatuses', payload ).then(function() {
                        //console.log($rootScope.api.agentStatuses.live_channel);
                    });
                }

            }, 15000);
        }
    });

})();