(function() {

    /*
    Caller Info

    This module is used for fetching the caller info
    */

    angular

    .module('CallerInfo', [])

    .run(function($rootScope, $interval, API) {

        // initialize dashboard refresh (real, non-emulating users only)
        if($rootScope.userinfo.emulating == 0) {

            // if we're in phones and not emulating, go ahead and fetch
            $rootScope.$watch('api.agentStatuses.live_channel', function(scope) {

                // cs agents on phones, or any agents (like sales) in all channels.
                if(scope == 'phones' || scope == 'all'){

                    // initial call
                    API.call( 'callerInfo', { action: 'read' } );

                    // 15s interval call
                    $rootScope.callerInfoInterval = $interval(function() {

                        // prevent the agent-status refresh from happening right around when the user interacts with the navbar.
                        var diff;
                        if($rootScope.modified['navbar']) {
                            diff = (Date.now() - $rootScope.modified['navbar']) / 1000;
                        }

                        if($rootScope.agentActionComplete && diff > 3) {
                            API.call( 'callerInfo', { action: 'read' } );
                        }

                    }, 15000);
                }
                // not in phones? cancel the caller info interval
                else {
                    $interval.cancel($rootScope.callerInfoInterval);
                }
            });
 
        }
    })

    .directive('callerInfo', function() {
        return {
            templateUrl: '/angular/templates/agents/caller-info.html',
            controller: function($rootScope) {},
            restrict: 'E'
        };
    })

})();