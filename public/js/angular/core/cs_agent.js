(function() {

    /**
     * cs_agent.js is the CS Agent's module.
     */

    angular

    .module('CSAgent', [
        'Agents',
        'TailorProgressBars', // used set the progress bar's colors for CS agents dependent on their parameters
        ])

    .config(function($routeProvider) {
        $routeProvider.when('/', {
            templateUrl : 'angular/templates/agents/dashboards/cs.html'
        });
    })

    .run(function($rootScope) {

        $rootScope.tab === 'checkins';
    })

    .directive('csContribution', function() {
        return {
            templateUrl: '/angular/templates/agents/panels/cs/contribution.html',
            controller: function($rootScope, $scope, $filter) {

                // watch for changes to csat overall total change to update progress bar
                $rootScope.$watch('api.statistics.period.contribution_percentage', function(scope) {
                    if(scope) {
                        if(scope > 100) {
                            scope = 100;
                        }
                        $('#sales_contribution_perc').progress({ percent: scope });
                    }
                });
            },
            restrict: 'E'
        };
    })
    
    .directive('csPayouts', function() {
        return {
            templateUrl: '/angular/templates/agents/panels/cs/payouts.html',
            restrict: 'E'
        };
    })

    .directive('csEligibility', function() {
        return {
            templateUrl: '/angular/templates/agents/panels/cs/eligibility.html',
            restrict: 'E'
        };
    })

    .directive('salesGoals', function() {
        return {
            templateUrl: '/angular/templates/agents/panels/cs/sales-goals.html',
            controller: function($rootScope, $scope, $filter) {

                // watch for changes to csat overall total change to update progress bar
                $rootScope.$watch('api.statistics.sales.dept_goal_completed', function(scope) {
                    if(scope) {
                        if(scope > 100) {
                            scope = 100;
                        }
                        $('#sales_goal_perc').progress({ percent: scope });
                    }
                });
            },
            restrict: 'E'
        };
    })
    
    // refreshes checkins, one on ones, and statistics every 60 seconds
    .service('Dashboard', function($q, $rootScope, API, setProgressBars) {

        this.refresh = function() {

            return $q(function(resolve, reject) {

                // grab data, then tailor
                Promise.all(['oneonones', 'checkins', 'attendanceClocks', 'attendanceExceptions', 'statistics'].map(function(path) { 
                    return API.call(path, {
                        action: 'read',
                        route: 'reviews', // only used for oneonones. shouldn't need routes for any other calls here, just actions. (routes are sub-actions)
                        dateRange: $rootScope.selectedDateRange
                    });
                })).then(function() {
                    setProgressBars.now().then(function() {
                        resolve();
                    });
                });

            });

            return promise;
        }
    })

})();