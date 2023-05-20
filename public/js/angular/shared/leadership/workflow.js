(function(){

    /**
     * Workflow.js is used for viewing the realtime status of agents and controlling their channel's
     */

	angular

	.module('Workflow', [])

    .config(function($routeProvider) {

        $routeProvider.when('/flow', {
            templateUrl : '/angular/templates/leadership/workflow/index.html',
            controller : 'flowCtrl'
        })
        
    })

    .run(function($rootScope, API) {
        API.call( 'workflow', { interval: 7 } );
    })

    // leadership popup modal control
    .controller('flowCtrl', function($scope, $http) {

        $scope.saving = 0;

        // default order by highest customer satisfaction
        $scope.order = {
            field: {},
            sort: {}
        };

        // change field we're ordering by, and the direction
        $scope.orderByField = function(field, usage, reverseSort) {
            $scope.order.field[usage] = field;
            if($scope.order.sort[usage] !== undefined) {
                $scope.order.sort[usage] = !$scope.order.sort[usage];
            } else {
                $scope.order.sort[usage] = reverseSort;
            }
            console.log($scope.order.sort[usage]);
        }

        $scope.orderByField('time', 'upcoming', false);
        $scope.orderByField('time', 'active', true);

        $scope.open = function(username, channel, status, asterisk_id) {

            $scope.username = username;
            $scope.asterisk = asterisk_id;
            $scope.channel = channel;
            $scope.status = status;

            $('#workflow-agent-control').modal('show');
        }

        $scope.upcomingScheduleFilter = function(param) {
            $scope.upcomingFilter = param;
        }
        $scope.activeExceptionsFilter = function(param) {
            $scope.activeFilter = param;
        }

        $scope.activeExceptionsFilter('all');
        $scope.upcomingScheduleFilter('all');

        // sending a command to the channel controller
        $scope.control = function(username, asterisk_id, live_channel, command) {

            $scope.saving = 1;

            // create a payload
            payload = {
                scope: 'workflow',
                command: command,
                username: username,
                asterisk_id: asterisk_id,
                live_channel: live_channel
            };

            // send payload
            $http.post( '/api/channelControl', payload ).then(function() {
                $('#workflow-agent-control').modal('hide');
                $scope.saving = 0;
            });
        }
    })

    // manipulate agent live channels modal
    .directive('agentControlModal', function() {
        return {
            templateUrl: '/angular/templates/leadership/modals/workflow-agent-control.html',
            controller: function($rootScope, $scope) {},
            restrict: 'E'
        }
    })

    .directive('csWorkflow', function() {
        return {
            templateUrl: '/angular/templates/leadership/workflow/cs.html',
            controller: function($rootScope, $scope) {
                // when the api alerts call updates, redefine the displayed count based on selected team
                $rootScope.$watch('api.workflow', function(data) {
                    if(data) {
                        $('.interaction').popup();
                    }
                });  
            },
            restrict: 'E'
        }
    })

    // manipulate agent live channels modal
    .directive('salesWorkflow', function() {
        return {
            templateUrl: '/angular/templates/leadership/workflow/sales.html',
            controller: function($rootScope, $scope) {
                $('.interaction').popup();
            },
            restrict: 'E'
        }
    })
})()