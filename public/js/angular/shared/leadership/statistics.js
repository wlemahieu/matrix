(function() {

    /**
	* leadership_statistics.js is the module for the CS-leadership 'Stats' view
	*/

	angular

	.module('LeadershipStatistics', [])

    .config(function($routeProvider) {
        $routeProvider.when('/stats', {
            templateUrl : '/angular/templates/leadership/statistics/cs.html',
            controller : 'statsCtrl'
        });
    })

    .controller('statsCtrl', function($rootScope, $scope) {

        // create filters object for storing the current filters going on
        $scope.filters = {};
        $scope.filtersVerbose = {};

        $('.filter').dropdown();

        // update a certain filter we are using. if value is undefined, we are fitering 'All'
        $scope.updateFilter = function(filter, value) {

            if(!value) {

                var title = 'All ';

                // Change the dropdown header depending on what filter we are using
                if(filter == 'level') {
                    title += 'CS Levels';
                } else if(filter == 'team') {
                    title += 'Teams';
                }

                $scope.filtersVerbose[filter] = title;
                $scope.filters[filter] = '';

            } else {
                $scope.filters[filter] = value;
                $scope.filtersVerbose[filter] = value;
            }
        }

        $scope.updateFilter('level');
        $scope.updateFilter('team');

        // bind tooltips
        $('.tooltip').popup();

        // default order by highest customer satisfaction
        $scope.orderByFieldName =  'csat_overall';
        $scope.reverseSort = true;

        // change field we're ordering by, and the direction
        $scope.orderByField = function(field) {
            $scope.orderByFieldName = field;
            $scope.reverseSort = !$scope.reverseSort;
        }

        // filter and switch between views (floor, team, agents)
        $scope.statisticsFilterChange = function(param) {
            $scope.statisticsFilter = param;
        }

        // filter by agents by default
        $scope.statisticsFilterChange('agents');
    })

})();