(function() {

	angular

	.module('ENPS', [])

    .config(function($routeProvider) {

        $routeProvider.when('/enps', {
            templateUrl : 'angular/templates/leadership/enps.html',
            controller: 'enpsCtrl'
        })
    })

    // view controller
    .controller('enpsCtrl', function($q, $scope, $rootScope, $interval, $http, $filter, API, NPS) {
        
        var intervalPromise, // store the enps api call in a promise
        refreshInterval = 60000; // refresh enps every 60s
        $scope.apiCallComplete = true; // prevent http calls from stacking

        $scope.fetchEnps = function() {

            // enable loaders
            $scope.loading = true;

            // create our payload delivery to fetch ENPS
            this.payload = {
                action: 'read',
                dateRange: $rootScope.selectedDateRange
            };

            // as long as the prior api call has completed, continue making a new one
            if($scope.apiCallComplete) {

                // mark this call as incomplete until the end
                $scope.apiCallComplete = false;

                // fetch enps surveys
                API.call('enps', this.payload).then(function(response) {

                    // recalculate nps and update the summary numbers
                    // surveys and team params
                    $scope.updateNumbers(response.data, $scope.selectedTeam);
                });
            } else {
                $scope.loading = false;
            }
        }

        $scope.filterByTeam = function(enps, team) {

            return $q(function(resolve, reject) {

                // if a team is defined...
                if(team) {

                    // we're filtering for a specific team, so drop all surveys not from this team
                    angular.forEach(enps.surveys, function(object, key) {
                        if(object.team !== team && team !== 'Filter by team') {
                            delete enps.surveys[key];
                        }
                    });
                }
                resolve(enps);
            });

            return promise;
        }

        // recalculate nps and update the summary numbers
        $scope.updateNumbers = function(surveys, team) {
            
            // clone the enps survey object
            enps = {
                surveys: JSON.parse(JSON.stringify(surveys))
            };

            // filter a specific team if set
            $scope.filterByTeam(enps, team).then(function(enps) {

                // calculate NPS scores
                NPS.calculate(enps.surveys, 'rating').then(function(nps) {

                    enps.nps = nps;

                    $scope.updateSummary(enps.surveys).then(function(response) {
                    
                        enps.avgRating = response.avgRating;
                        enps.count = response.count;
                        enps.score = nps.score;

                        $scope.enps = enps;
                        $scope.setLineItemsFilter('nps_type', 'All');
                        $scope.apiCallComplete = true;
                        $scope.loading = false;
                        
                    });
                });
            });
        }

        $scope.startInterval = function(){
            $scope.fetchEnps();
            intervalPromise = $interval($scope.fetchEnps, refreshInterval);
        }

        $scope.startInterval();

        $scope.filterTeam = function(team) {

            $scope.selectedTeam = team;
            $scope.setLineItemsFilter('team', team);
            $scope.updateNumbers($rootScope.api.enps, team);
        }

        // update the counters & average rating
        $scope.updateSummary = function(surveys) {
            
            return $q(function(resolve, reject) {

                // if there are responses, continue with calculations
                if(surveys) {

                    var ratingSum = 0,
                    count = 0,
                    avgRating = 'N/A',
                    score = 'N/A/';

                    // iterate through them and count the total sum of rating numbers, and update the total counter
                    angular.forEach(enps.surveys, function(object, key) {

                        ratingSum += object.rating;
                        count++;
                    });

                    // only divide if we have a count, otherwise stay 'N/A'
                    if(count > 0) {
                        avgRating = $filter('number')(ratingSum / count, 2);
                    };

                    payload = {
                        avgRating: avgRating,
                        count: count 
                    };

                    resolve(payload);
                }
            });

            return promise;
        }

        // destroy interval if route changes
        $scope.$on('$destroy',function(){
            if(intervalPromise) {
                $interval.cancel(intervalPromise);   
            }
        });

        $scope.filterOptions = [
            'All',
            'Detractor',
            'Promoter',
            'Passive'
        ];

        // sets the filter on team or nps_type
        $scope.setLineItemsFilter = function(field, param) {

            // no filter yet? create it
            if(!$scope.filter) {
                $scope.filter = {};
            }

            // clearing filter
            if(param === 'All' || param === 'Filter by team') {
                param = '';
            }

            $scope.filter[field] = param;
        }

        // open and view a specific ENPS rating's reason in a modal
        $scope.open = function(reasonId) {

            angular.forEach($scope.enps.surveys, function(obj, key) {
                if(obj.id === reasonId) {
                    $scope.reason = obj.reason;
                    $scope.rating = obj.rating;
                    
                    $('#enps-reason').modal('show');
                    $('#enps-reason').modal({ observeChanges: true });
                }
            });
        }
    })

    // view a single reason for an enps response
    .directive('enpsReasonModal', function() {
        return {
            templateUrl: '/angular/templates/leadership/modals/enps-reason.html',
            controller: function($rootScope, $scope) {},
            restrict: 'E'
        }
    })

})()