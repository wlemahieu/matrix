(function() {

    /**
    * This module is used for editing, adding, or removing new teams
    */

    angular

    .module('ManageTeams', [])

    .config(function($routeProvider) {

        $routeProvider.when('/teams', {
            templateUrl : 'angular/templates/leadership/teams.html',
            controller: 'teamsCtrl'
        })
    })

    .controller('teamsCtrl', function($timeout, $scope, $rootScope, API, $http) {

        // team-selector.html's ng-change function.
        // on user change, update the big-3 attendance calls.
        $scope.selectTeam = function() {

            // only continue with refreshing attendance availability/clocks/exceptions
            if($scope.selectedTeam != 'Select a team') {

                // clear the selected team object
                $scope.selectedTeamObj = {};

                // change button to "edit default"
                $scope.button.active = $scope.button.default_edit;

                // clone the selected team's info from the root teams into $scope
                angular.copy($rootScope.api.teams[$scope.selectedTeam], $scope.selectedTeamObj);
            }
        }

        $scope.newTeam = function() {
            // change button to "new default"
            $scope.button.active = $scope.button.default_new;

            // empty form for new team
            $scope.newTeamForm();
        }

        $scope.initializeTeamForm = function() {
            $scope.selectedTeamObj = undefined;
            $scope.selectedTeam = undefined;
        }

        $scope.newTeamForm = function() {
            $scope.selectedTeamObj = {};
            $scope.selectedTeamObj.id = undefined;
            $scope.selectedTeamObj.index = undefined;
            $scope.selectedTeamObj.name = '';
            $scope.selectedTeamObj.active = '1';
            $scope.selectedTeamObj.sunrise = '0';
        }

        $scope.initializeTeamForm();

        // initiate dynamic button sequence
        $scope.button = {};
        
        // define different button states
        $scope.button.default_edit = {
            'title': 'Save Edits',
            'class': 'blue',
            'icon': 'save',
            'disabled': false,
        };
        $scope.button.default_new = {
            'title': 'Create New',
            'class': 'blue',
            'icon': 'save',
            'disabled': false,
        };
        $scope.button.empty_team = {
            'title': 'Please select a team',
            'class': 'red',
            'icon': 'exclamation triangle',
            'disabled': true,
        };
        $scope.button.team_exists = {
            'title': 'That team exists',
            'class': 'red',
            'icon': 'exclamation triangle',
            'disabled': true,
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

        // set our currently active button as the default button
        $scope.button.active = $scope.button.default;

        // save team
        $scope.submit = function() {

            $scope.button.active = $scope.button.saving;

            // build the payload
            payload = {};
            payload.action = 'upsert';
            payload.name = $scope.selectedTeamObj.name;
            payload.active = $scope.selectedTeamObj.active;
            payload.sunrise = $scope.selectedTeamObj.sunrise;

            // no id? it's a new team then!
            if($scope.selectedTeamObj.id !== undefined) {
                payload.id = $scope.selectedTeamObj.id;
            }

            // send the payload to our API
            $http.post( '/api/teams', payload ).then(function(response) {

                // get rid of this now that the payload has been sent
                // because we are going to re-use the payload
                payload.action = undefined;

                // button marked as saved, then goes back to default
                $timeout(function() {

                    $scope.button.active = $scope.button.saved;

                    // update view depending on new or edit
                    // if this is a new team...
                    if($scope.selectedTeamObj.id === undefined) {

                        // the id of the team
                        payload.id = response.id;
                        // push the new team into the teams list
                        $rootScope.api.teams.push(payload);
                        // save the index as the selected team
                        $scope.selectedTeam = $rootScope.api.teams.length - 1;
                    }
                    // if this is an existing team...
                    else {

                        // move the updated team back into master
                        angular.copy(payload, $rootScope.api.teams[$scope.selectedTeam]);
                    }

                    // after 2 seconds, place the button back to default for editing an existing team
                    $timeout(function() {
                        $scope.button.active = $scope.button.default_edit;
                    }, 2000);

                }, 2000);
            });
        }
    })

})()