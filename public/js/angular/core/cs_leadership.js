(function() {

    /*
    cs_leadership.js is the CS Leadership's module for Leads and Managers in Customer Support
    */

    angular

    .module('CSLeadership', [
        'Leadership', // a global module for all Leadership
        'Alerts', // responsible for displaying alert messages that let leadership know of any actionable items that must be "fixed"
        'Checkins', // for leadership-to-agent check-ins (Quality-Assurance reporting) (cs-only at the moment)
        'ContributionTargets', // monthly contribution targets (CS-only)
        'ManipulateCheckins', // create, edit and present checkins to agents (CS-only at the moment)
        'Parameters', // manage parameters for CS-only
        ])

    .config(function($routeProvider) {
        $routeProvider.when('/', {
            templateUrl : 'angular/templates/leadership/dashboards/cs.html'
        });
    })

    // initiate some department-specific items
    .run(function($rootScope, API) {  

        // cs-parameters
        API.call( 'parameters', { action: 'read' } );
    })  

    // cs-leadership data (one-on-ones, checkins, alerts & statistics)
    .service('Dashboard', function($q, $rootScope, $interval, API, NPS) {

        this.refresh = function() {

            return $q(function(resolve, reject) {

                // grab data, then tailor
                Promise.all(['oneonones', 'checkins', 'alerts', 'statistics'].map(function(path) { 
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