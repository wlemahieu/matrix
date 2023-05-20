(function() {

    /*
    Attendance
    
    This is what fuels the attendance modal that provides all clocks and exceptions for the selected period to agents.
    */

    angular

    .module('Attendance', [])

    .directive('attendanceModal', function() {

        return {
            templateUrl: '/angular/templates/agents/modals/attendance.html',
            controller: function($rootScope, $scope) {

                // there is a potential for the data to not be loaded yet in $rootScope.api, so the object will reference nothing.
                // usually there will always be data though and even if there is nothing, as soon as they click this filter again, it'll work.
                $scope.swapAttendanceView = function(view) {

                    switch(view) {
                        case 'clocks':
                            $scope.attendanceSwapObject = $rootScope.api.attendanceClocks;
                        break;
                        case 'exceptions':
                            $scope.attendanceSwapObject = $rootScope.api.attendanceExceptions;
                        break;
                    }

                    $scope.attendanceTab = view;
                }

                $scope.swapAttendanceView('exceptions');
            },
            restrict: 'E'
        };
    })

    .controller('attendance', function ($rootScope, $scope, $timeout, $http, APIModifiedTime) {

        $scope.viewAttendance = function() {

            // resizes the modal when the content changes
            $('#attendance').modal({ observeChanges: true });
            $('#attendance').modal('show');
        }
    })
})();