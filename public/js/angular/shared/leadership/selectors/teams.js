(function() {

    angular

    .module('TeamSelector', [])

    .directive('teamSelector', function() {
        return {
            templateUrl: '/angular/templates/leadership/team-selector.html',
            controller: function($rootScope, $scope) {
                $scope.selectedTeam = 'Select a team';
            	$rootScope.$watch('api.teams', function(scope) {
            		$('#team-selector').dropdown();
            	});
            },
            restrict: 'E'
        };
    })

})();