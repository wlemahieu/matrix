(function() {

    /**
     * sales_leadership.js is the Sales Leadership's module (Leads and Managers)
     */

    angular

    .module('SalesLeadership', [
        'Leadership' // a global module for all Leadership
        ])

    .config(function($routeProvider) {

        $routeProvider.when('/', {
            templateUrl : 'angular/templates/leadership/dashboards/sales.html'
        });
    })

    .run(function($rootScope, API) {  

        // define our panels for panel filter's ng-repeat
        $rootScope.panels = [
            '1on1s',
            'Stats',
            'Flow'
        ];
    })
    
    // sales-leadership data (one-on-ones & statistics)
    .service('Dashboard', function($q, $rootScope, $interval, API, NPS) {

        this.refresh = function() {

            return $q(function(resolve, reject) {

                // grab data, then tailor
                Promise.all(['oneonones', 'enps', 'statistics'].map(function(path) { 
                    return API.call(path, {
                        action: 'read',
                        route: 'reviews',
                        dateRange: $rootScope.selectedDateRange
                    });
                })).then(function() {
                    resolve();
                });
            });

            return promise;
        }
    })
})();