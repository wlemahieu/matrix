(function() {

	angular

    .module('Parameters', [])

    .config(function($routeProvider) {

        $routeProvider.when('/parameters', {
            templateUrl : 'angular/templates/leadership/parameters.html',
            controller: 'parametersCtrl'
        })
    })

    .controller('parametersCtrl', function($scope, $rootScope, $timeout, $http) {
        
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
                } else if(filter == 'pt_or_ft') {
                    title += 'Statuses';
                } else if(filter == 'channel') {
                    title += 'Channels';
                }

                $scope.filtersVerbose[filter] = title;
                $scope.filters[filter] = '';

            } else {
                $scope.filters[filter] = value;
                $scope.filtersVerbose[filter] = value;
            }
        }

        $scope.updateFilter('level');
        $scope.updateFilter('pt_or_ft');
        $scope.updateFilter('channel');

        // define different button states
        $scope.button = {
        
            default: {
                'title': 'Save Parameters',
                'class': 'blue',
                'icon': 'save',
                'disabled': false,
            },
            saving: {
                'title': 'Saving',
                'class': 'grey',
                'icon': 'loading spinner',
                'disabled': true,
            },
            saved: {
                'title': 'Saved',
                'class': 'green',
                'icon': 'thumbs up',
                'disabled': true,
            }
        };

        // set our currently active button as the default button
        $scope.button.active = $scope.button.default;

        $scope.submit = function() {

            $scope.button.active = $scope.button.saving;

            // build a payload before deliverying to the API
            param_payload = {
                parameters: $rootScope.api.parameters,
                action: 'update'
            };

            $http.post('/api/parameters', param_payload).then(function(response) {

                $scope.button.active = $scope.button.saved;

                $timeout(function(){
                    $scope.button.active = $scope.button.default;
                }, 3000);
            });
        }
    })

})();