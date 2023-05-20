(function() {
    
    /*
    The alerts module is responsible for fetching alerts for leadership.
    Alerts are action items that help leaders identify problems which need fixing before the pay-period ends.
    */

    angular.module('Alerts', [])

    .config(function($routeProvider) {

        $routeProvider.when('/alerts', {
            templateUrl : '/angular/templates/leadership/alerts.html',
            controller : 'alertsCtrl'
        });
    })
    
    .controller('alertsCtrl', function($rootScope, $scope) {
        
        // default order settings
        $scope.alertsOrderBy = 'agent_username';
        $scope.alertsReverseSort = true;

        // update the display count for the selected team, or all alerts
        $scope.updateCount = function(team) {

            if(team) {

                // if there is a count for the team, they have alerts, so save those in $scope.count
                if($rootScope.api.alerts.counts[team]) {
                    $scope.count = $rootScope.api.alerts.counts[team];
                } else {
                    $scope.count = 0;
                }
                
            } 

            // false was sent in order to trigger showing the total count
            else {
                $scope.count = $rootScope.api.alerts.counts.total;
            }   
        }

        $scope.filterTeam = function(team) {

            // set filter to be the selected team
            if(team != 'Filter by team') {

                // alerts filter is the team name
                $scope.filter = team;

                // if alerts api call has completed...
                if($rootScope.api.alerts) {
                    $scope.updateCount(team);
                }
            } 
            // clear the filter (default load)
            else {

                // alerts filter is all teams
                $scope.filter = '';

                // if alerts api call has completed...
                if($rootScope.api.alerts) {
                    $scope.updateCount(false);
                }
            }
        };

        // when the api alerts call updates, redefine the displayed count based on selected team
        $rootScope.$watch('api.alerts', function(data) {
            if(data) {
                if($scope.selectedTeam !== 'Filter by team') {
                    $scope.updateCount($scope.selectedTeam);
                } else {
                    $scope.updateCount(false);
                }
            }
        });   
    })

})();