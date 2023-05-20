(function() {

    /*
    The agent exit module is used to open the modal itself, as well as allow for channel+matrix exiting within the modal.
    */

    angular

    .module('AgentExit', [])

    // confirmation modal
    .directive('exitConfirmationModal', function() {
        return {
            templateUrl: '/angular/templates/agents/modals/exit-confirmation.html',
            controller: function($rootScope, $scope, $interval, $http) {

                // exit live channels and/or matrix entirely
                $scope.exit = function() {

                    // enable loading icon
                    $rootScope.loading = true;

                    // cancels the intervaling data-pulls since the end-user clicked 'Exit' and we no longer need those promises to be fulfilled
                    $interval.cancel($rootScope.dashboardInterval);
                    $interval.cancel($rootScope.agentStatusesInterval);

                    $rootScope.agentActionComplete = false;
                    $rootScope.api.agentStatuses.currently_clocked_in = 0;

                    payload = {};
                    payload.scope = 'agent';
                    payload.command = 'logout';
                    payload.live_channel = 'all';

                    // after the logout payload is complete, log them out to clear their php session
                    $http.post( '/api/channelControl', payload ).then(function() {
                        window.location = "?logout";
                    });

                }
            },
            restrict: 'E'
        }
    })

})();