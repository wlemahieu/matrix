(function() {

    /**
     * Global.js is the most important module. It's at the top of the module-pyramid and gathers our baseline dependencies
     */

    angular

    .module('Global', [
        'Bootstrap', // the most important dependancy for the entire application.
        'ngMaterial', // neat UI elements
        'ngRoute', // handles routing in the application
        ])

    .run(function(BootstrapPayload, $rootScope) {
        
        // show the sidebar if they are not logged in
        if(!BootstrapPayload.userInfo) {
            $('.ui.sidebar').sidebar({ closable: false }).sidebar('toggle');
        }

        // turn off the app loader by default
        $rootScope.loading = false;

        // retrieve and store data from bootstrap.js
        $rootScope.userinfo = BootstrapPayload.userInfo;

        // only CS department has parameters
        $rootScope.params = BootstrapPayload.parameters;

        // the google oauth URL if they are not logged-in
        $rootScope.authUrl = BootstrapPayload.authUrl;
    })

    .controller('sidebarCtrl', function($rootScope, $scope) {

        // bind tooltips
        /*
        $('.tooltip').popup({
            position : 'bottom center'
        });
        */
        
        $scope.login = function() {
            $rootScope.loading = true;
            window.location = $rootScope.authUrl;
        }
        
        $scope.logout = function() {
            $rootScope.loading = true;
            window.location = '?logout';
        }

        // toggles exit confirmation modal
        // only used by agent's currently.
        // settings force user-choice instead of off-clicking closing modal.
        $scope.toggleExitConfirmModal = function(bool) {
            if(bool) {
                console.log('true');
                $('#exit-confirmation').modal('setting', 'closable', false).modal('show');
            } else {
                console.log('false');
                $('#exit-confirmation').modal('hide');
            }
        }
    });

})();