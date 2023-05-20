(function() {

    /*
    This module has no dependancies.

    The after call survey module is responsible for displaying survey results derived from the parent module (Leadership or Community most likely).
    This module also serves as a net-promoter-score calculator for the survey results.
    Included is a date picker directive which should be researched if we can do more efficiently than place one in this module entirely.

    */

    angular.module('AfterCallSurvey', [])

    .directive('afterCallSurveys', function() {

        return {
            restrict: 'E',
            templateUrl: '/angular/templates/after-call-surveys.html',
            controller: ['$scope', '$rootScope', function ($scope, $rootScope) {

                $scope.limit = 100;
                $scope.filter = {};

                $scope.updateFilter = function(key, value) {
                    $scope.filter[key] = value
                }

                $scope.updateFilter('nps_type', 'Detractor');
                
            }],
            controllerAs: 'surveysCtrl'
        }
    })

})();