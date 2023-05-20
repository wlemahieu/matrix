(function() {

    /**
     * user_profiles.js is used for managing user profiles.
     * User Profiles contain a variety of properties defined in the `users` table, as well as schedules from `schedules2` table
     */

	angular

	.module('UserSchedules', [])

    .config(function($routeProvider) {

        $routeProvider.when('/schedules', {
            templateUrl : 'angular/templates/leadership/schedules.html',
            controller : 'schedulesCtrl'
        })
    })
    
    .controller('schedulesCtrl', function($scope, $timeout, $rootScope, API, $http) {

        // initiate semantic-ui menu tabs (Details, Schedule, History)
        $('.menu .item').tab();

        $scope.loading = true;

        // create a filter for team leads on the users selection for just active agents
        $scope.usersFilter = {};
        if($rootScope.userinfo.type == 'Supervisor') {
            $scope.usersFilter.type = 'Agent';
            $scope.usersFilter.active = 1;
        }

        // initiate dynamic button sequence
        $scope.button = {};
        
        // define different button states
        $scope.button.default = {
            'title': 'Save Schedule',
            'class': 'blue',
            'icon': 'save',
            'disabled': false,
        };
        $scope.button.saving = {
            'title': 'Saving',
            'class': 'grey',
            'icon': 'loading spinner',
            'disabled': true,
        };
        $scope.button.saved = {
            'title': 'Saved',
            'class': 'green',
            'icon': 'thumbs up',
            'disabled': true,
        };

        $scope.button.active = $scope.button.default;

        // save profile / schedule
        $scope.save = function() {

            $scope.button.active = $scope.button.saving;

            payload = {};
            payload.action = 'write';
            payload.data = $rootScope.api.userSchedule;
            
            $http.post('/api/userSchedule', payload).then(function(response) {

                // change button state
                $scope.button.active = $scope.button.saved;

                // after 3 seconds, return form button to default
                $timeout(function() { 
                    $scope.button.active = $scope.button.default;
                }, 3000);
            });
        }

        // on agent select
        $scope.selectAgent = function() {

            $scope.loading = true;

            payload = {};
            payload.action = 'read';
            payload.username = $scope.selectedAgent;

            API.call('userSchedule', payload).then(function() {
                $scope.loading = false;
            });
        }
    })

})()