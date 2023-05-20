(function() {

    angular

    .module('TeamFilter', [])

    .directive('teamFilter', function() {
        return {
            templateUrl: '/angular/templates/leadership/team-filter.html',
            controller: function($scope) {
                $scope.selectedTeam = 'Filter by team';
            },
            restrict: 'E'
        };
    })

})();