(function() {
	
    /*
    This module has no dependancies.

    The agent emulator is used emulating an agent's dashboard view

    */

    angular

    .module('AgentEmulator', [])

    .controller('agentEmulatorController', function ($http, $rootScope, $window, $location) {

        this.emulate = function(username) {
            $http.post( '/api/emulateAgent', { 'username': username } ).then(function(response) {
                $location.path('/');
                $rootScope.userinfo.emulating = 1;
                $window.location.reload();
            });
        };
    })
    
})();