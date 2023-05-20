(function() {

    /**
     * sales_agent.js is the Sales Agent's module.
     */

    angular

    .module('SalesAgent', [
        'Agents',
        ])

    .config(function($routeProvider) {
        $routeProvider.when('/', {
            templateUrl : 'angular/templates/agents/dashboards/sales.html'
        });
    })

    .directive('sales', function() {
        return {
            templateUrl: '/angular/templates/agents/panels/sales/sales.html',
            controller: function($rootScope) {},
            restrict: 'E'
        };
    })

    // refreshes checkins, one on ones, and statistics every 60 seconds
    .service('Dashboard', function($q, $rootScope, API) {

        this.refresh = function() {

            return $q(function(resolve, reject) {

                // grab data, then tailor
                Promise.all(['oneonones', 'attendanceClocks', 'attendanceExceptions', 'statistics'].map(function(path) { 
                    return API.call(path, {
                        action: 'read',
                        route: 'reviews', // only used for oneonones. shouldn't need routes for any other calls here, just actions. (routes are sub-actions)
                        dateRange: $rootScope.selectedDateRange
                    });
                })).then(function() {
                    $rootScope.loading = false;
                    resolve();
                });
            });

            return promise;
        }
    })

})();