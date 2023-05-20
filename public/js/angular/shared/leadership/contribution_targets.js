(function() {
    
    // The contribution targets module is used by CS managers to define the sales targets for CS agents for the upcoming month.
    angular

    .module('ContributionTargets', [])

    .config(function($routeProvider) {

        $routeProvider.when('/contributiontargets', {
            templateUrl : 'angular/templates/leadership/contribution-targets.html',
            controller: 'targetsCtrl'
        })
    })

    // a service for building a new empty contribution targets form
    .service('ContributionTargets', function($q, $filter, $timeout) {

        this.newForm = function(date) {

            return $q(function(resolve, reject) {

                // build our days into an array of objects
                days = [
                    { day: 'Sunday', day_number: 1},
                    { day: 'Monday', day_number: 2},
                    { day: 'Tuesday', day_number: 3},
                    { day: 'Wednesday', day_number: 4},
                    { day: 'Thursday', day_number: 5},
                    { day: 'Friday', day_number: 6},
                    { day: 'Saturday', day_number: 7}
                ];

                // reserve our empty form data array-of-objects
                form = {};
                form.days = {};

                newDate = new Date();

                // -1 means brand new form, no data exists for the month selected
                date == -1 ? form.date = $filter('date')(newDate, 'yyyy-MM-01') : form.date = date;
                
                // each day of the week is an object in an array (form). For each full time or part time, default to 0.
                angular.forEach(days, function(obj, key) {

                    form.days[obj.day] = {};
                    form.days[obj.day].day_number = obj.day_number;
                    form.days[obj.day].ft = 0;
                    form.days[obj.day].pt = 0;
                });

                resolve(form);
            });

            return promise;
        }
    })

    .controller('targetsCtrl', function($rootScope, $scope, $http, $filter, $timeout, API, ContributionTargets) {   

        $scope.loading = 0;
        $scope.selectedDate = new Date();

        // when the selected date changes, grab the 1st of the month for the month selected, then try to fetch any existing contributions.
        // if there are no existing contributions, then simply create a new empty form
        $scope.$watch('selectedDate', function(date) {

            if(date) {

                $scope.loading = 1;

                // convert the selected date into the first of that month
                var firstOfMonth = $filter('date')(date, 'yyyy-MM-01');

                // store the selected Month, YYYY for letting the end-user know which month they are dealing with
                $scope.selectedMonthVerbose = $filter('date')(date, 'MMMM, yyyy');

                // fetch the current month's contribution targets
                ct_pl = {};
                ct_pl.date = firstOfMonth;
                ct_pl.action = 'fetch';

                // $scope.form is populated with data, or a brand new form
                API.call('contributionTargets', ct_pl).then(function(response) {

                    // no results, create a new form
                    if(response.data.days == 0) {

                        ContributionTargets.newForm(-1).then(function(form) {
                            $scope.form = form;
                            $scope.form.date = firstOfMonth;
                            $scope.loading = 0;
                        });
                    }
                    // results, load them into view
                    else {
                        $scope.form = response.data;
                        $scope.form.date = firstOfMonth;
                        $scope.loading = 0;
                    }
                });
            }
        });

        // initiate dynamic button sequence
        $scope.button = {};
        
        // define different button states
        $scope.button.default = {
            'title': 'Save Targets',
            'class': 'blue',
            'icon': 'save',
            'disabled': false,
        };
        $scope.button.saving = {
            'title': 'Saving',
            'class': 'grey',
            'icon': 'loading spinner',
            'disabled': true,
        };
        $scope.button.saved = {
            'title': 'Saved',
            'class': 'green',
            'icon': 'thumbs up',
            'disabled': true,
        };
        $scope.button.historical_error = {
            'title': 'You can\'t change historical data',
            'class': 'red',
            'icon': 'thumbs down',
            'disabled': true,
        };

        // set our currently active button as the default button
        $scope.button.active = $scope.button.default;

        $scope.submit = function() {

            $scope.loading = 1;
            $scope.button.active = $scope.button.saving;

            ct_pl = {};
            ct_pl.payload = $scope.form;
            ct_pl.action = 'manipulate';

            $http.post('/api/contributionTargets', ct_pl).then(function(response) {

                if(response.data == 0)  {
                    $scope.button.active = $scope.button.historical_error;
                }
                else {
                    $scope.button.active = $scope.button.saved;
                }

                $timeout(function() { 
                    $scope.button.active = $scope.button.default;
                    $scope.loading = 0;
                }, 3000);

            });
        }
    })

})();