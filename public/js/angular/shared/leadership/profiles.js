(function() {

    /**
     * user_profiles.js is used for managing user profiles.
     * User Profiles contain a variety of properties defined in the `users` table, as well as schedules from `schedules2` table
     */

	angular

	.module('UserProfiles', [])

    .config(function($routeProvider) {

        $routeProvider.when('/profiles', {
            templateUrl : 'angular/templates/leadership/profiles.html',
            controller : 'profilesCtrl'
        })
    })
    
    .controller('profilesCtrl', function($scope, $timeout, $rootScope, API, $http) {

        // initiate semantic-ui menu tabs (Details, Schedule, History)
        $('.menu .item').tab();

        $scope.loading = true;

        // create a filter for team leads on the users selection for just active agents
        $scope.usersFilter = {};
        if($rootScope.userinfo.type == 'Supervisor') {
            $scope.usersFilter.type = 'Agent';
            $scope.usersFilter.active = 1;
        }

        // create a filter for the teams, changes between sales/cs/other dept
        $scope.teamsFilter = {};

        // initiate dynamic button sequence
        $scope.button = {};
        
        // define different button states
        $scope.button.default = {
            'title': 'Save Profile',
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
            payload.data = $rootScope.api.userProfile.profile;
            
            $http.post('/api/userProfile', payload).then(function(response) {

                // change button state
                $scope.button.active = $scope.button.saved;

                // after 3 seconds, return form button to default
                $timeout(function() { 
                    $scope.button.active = $scope.button.default;
                }, 3000);
            });
        }

        $scope.locateConversocialId = function() {

            API.call('conversocialUsers', {
                action: 'read'
            }).then(function() {

                console.log(typeof $rootScope.api.conversocialUsers);

                $('#conversocial-users').modal('show');
                $('#conversocial-users').modal({ observeChanges: true });
            });
        }

        // on agent select
        $scope.selectAgent = function() {

            $scope.loading = true;

            API.call('userProfile', {
                action: 'read',
                username: $scope.selectedAgent
            }).then(function() {
                $scope.loading = false;
            });
        }
    })

    // view a single reason for an enps response
    .directive('conversocialUsersModal', function() {
        return {
            templateUrl: '/angular/templates/leadership/modals/conversocial-users.html',
            controller: function($rootScope, $scope) {},
            restrict: 'E'
        }
    })

})()