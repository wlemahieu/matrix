(function() {

    angular

    .module('AgentSelector', [])

    .directive('agentSelector', function() {
        return {
            templateUrl: '/angular/templates/leadership/agent-selector.html',
            controller: function($rootScope, $scope) {
                $scope.selectedAgent = 'Select a user';
            	$rootScope.$watch('api.users', function(scope) {
            		$('#agent-selector').dropdown();
            	});
            },
            restrict: 'E'
        };
    })

})();