(function() {

    /**
     * Matrix.js is the 2nd most important module. It's essentially at the top of the module-pyramid and gathers our baseline dependencies
     */

    angular

    // replace readableTime with my own readableTime filter which works better than this one and is much lighter
    .module('Matrix', [
        'Global', // the global module
        'ngCookies', // allows us to store data inside of cookies which can be handy.
        'ReadableTime', // a library that converts a unix timestamp into a readable timestamp (i.e. 4min 58s)
        'ngOrderObjectBy', // allows for filtering ng-repeat on objects instead of only arrays.
        'MAPI', // Matrix API module / service
        'Time', // a module for loading pay periods
        ])

    .run(function($rootScope, PayPeriods, Interval) {

        $rootScope.showSidebar = function() {
            $('.ui.sidebar').sidebar('toggle');
        }

        // this disables the big initial loaders when API calls happen
        $rootScope.refreshing = false;

        // element loaders
        $rootScope.loaders = {};
        $rootScope.loaders.inline = {};
        $rootScope.loaders.initial = {};

        // api results
        $rootScope.api = {};

        // DEFINE & GENERATE PAY PERIODS
        // YYYY = Year
        // MI == Month (0 = January, 11 = December)
        // DAY == Day
        // DPP == Days per period
        // TP = Total amount of periods to generate
        // (YYYY, MI, DAY, DPP, TP)
        $rootScope.past_12_pay_periods = PayPeriods.initialize(2016, 0, 4, 14, 12);

        // selected date definitions
        // grab the first pay period as the selected range
        $rootScope.selectedDateRange = $rootScope.past_12_pay_periods[0];
        $rootScope.selectedDateRangeRaw = {};
        
        // create raw version of the selectedDateRange for md-datepicker's ng-model to work properly
        $rootScope.selectedDateRangeRaw.start = new Date($rootScope.selectedDateRange.start);
        $rootScope.selectedDateRangeRaw.end = new Date($rootScope.selectedDateRange.end);

        // watch for any changes to the date and refresh the dashboard if it does change
        $rootScope.$watchGroup(['selectedDateRange.start', 'selectedDateRange.end'], function(scope) {
            Interval.refresh();
        });
    })

    // This service has a refresh function which is a wrapper for every user-type-dept's unique 'Dashboard' service.
    // These unique dashboard services contain the specific API calls in which that particular user-type-dept needs for their dashboard.
    // Since not all user types need the same data sets, we define what data is brought in using the same service name, 'Dashboard.refresh()'.
    // This universal (yet unique) service named 'Dashboard', is then referenced below along with some additional logic such as checking 
    // whether or not the end user is looking at historical data in the dashboard or the current pay-period.
    // If they are looking at historical data, we won't refresh the dashboard anymore since it's just wasting resources.
    // Example:  core/cs_agent.js contains a Dashboard service with API calls specific to CS agents.
    .service('Interval', function($q, $rootScope, $interval, Dashboard) {

        var parent = this;

        // check whether or not the user is viewing historical data.
        this.checkHistorical = function() {

            /* 
            Get the current time in milliseconds, as well as the end date for the time range.
            Using these two dates, I can determine if the user is looking at historical data or not.
            If they are looking at historical data, we will stop the refreshing of data.
            */

            // now
            var now = new Date(), now = now.getTime();
            
            // end time
            var d = new Date($rootScope.selectedDateRange.end),
            end = d.getTime();

            // if historical data is being viewed...
            if(now > end) {
                $interval.cancel($rootScope.dashboardInterval);
                return true;
            } else {
                return false;
            }
        }

        this.refresh = function() {

            return $q(function(resolve, reject) {

                $rootScope.loading = true;

                // cancel any existing interval for the dashboard refresh
                $interval.cancel($rootScope.dashboardInterval);
                
                Dashboard.refresh().then(function(){
                    $rootScope.loading = false;
                });

                // make sure the user is not looking at historical data
                // we don't want to refresh their dashboard for this case
                if(!parent.checkHistorical()) {

                    // store the dashboard refresh in an interval promise
                    $rootScope.dashboardInterval = $interval(function() {

                        $rootScope.loading = true;

                        Dashboard.refresh().then(function(){
                            $rootScope.loading = false;
                        });
                    }, 150000); // 3 minutes
                }

                resolve();
            });

            return promise;
        }
    })

    // Date-range Pickers (Pay Periods, Quarters)
    .controller('DatePickersCtrl', function($rootScope, $scope, Interval) {

        // custom date picker hidden by default
        $scope.customVisible = false;

        $scope.createDateDefinitions = function() {

            // date components
            var
            current_date = new Date(),
            current_year = current_date.getFullYear().toString(),
            last_year = (current_year - 1).toString(),
            two_years_ago = (current_year - 2).toString();

            // years
            $scope.years = [current_year, last_year, two_years_ago];

            // template quarters object
            $scope.quarters = {
                'Q1':{ start: '01/01/', end: '03/31/' },
                "Q2":{ start: '04/01/', end: '06/30/' },
                "Q3":{ start: '07/01/', end: '09/31/' },
                "Q4":{ start: '10/01/', end: '12/31/' }
            };
        }

        $scope.generateQuartersByYear = function() {

            // create quarters-by-year
            $rootScope.quarters_by_year = [];
            var tempObj = {};

            // iterate through each year
            angular.forEach($scope.years, function(year, key) {

                // iterate through each quarter
                angular.forEach($scope.quarters, function(obj, quarter) {

                    // the key for each object is 'YYYY QQ' - '2016 Q1' for example
                    var title = year + ' ' + quarter;

                    tempObj[title] = {
                        title: title,
                        start: obj.start.concat(year),
                        end: obj.end.concat(year)
                    };
                });
            });

            // store temp object into $rootScope and destroy
            $rootScope.quarters_by_year = tempObj;
            delete tempObj;
        }

        // used by dropdowns ng-click
        $scope.capturePayPeriod = function(start, end) {

            $rootScope.selectedDateRange = { start: start, end: end };

            // using a full JS date() format for md-datepicker to work properly locally
            start = new Date($rootScope.selectedDateRange.start);
            end = new Date($rootScope.selectedDateRange.end);

            delete $rootScope.startDateWatched;

            // stored in $rootScope for app-wide usage
            $rootScope.selectedDateRangeRaw = { start: start, end: end };
        }

        $scope.createDateDefinitions();
        $scope.generateQuartersByYear();
    })
    
    // Custom date-range (in navigation bar)
    .controller('CustomDatePickerCtrl', function($q, $rootScope, $scope, $filter, Interval) {

        // push the date range from local scope to root
        $scope.pushToMaster = function () {

            // convert the long JS date back to regular YYYY/MM/DD
            //this is to make ngMaterial's datePicker happy, it only accepts full JS date(), but I only want to display a shorter version.
            start = $filter('date')($rootScope.selectedDateRangeRaw.start, 'MM/dd/yyyy');
            end = $filter('date')($rootScope.selectedDateRangeRaw.end, 'MM/dd/yyyy');
            $rootScope.selectedDateRange = { start: start, end: end };
        }

        // make sure we have an end date set, and that our end date is not before our start date.
        $scope.checkStartAndEndDates = function (range) {

            // we must have an end-date
            if(range.end) {
                // the end date must be greater than or equal to the start date
                if(range.end >= range.start) {
                    $scope.pushToMaster();
                } else {
                    delete $rootScope.selectedDateRangeRaw.end;
                }
            }
        }

        // if they change the beginning date, empty the end date
        $rootScope.$watch('selectedDateRangeRaw.start', function(scope) {

            // make sure we aren't doing anything on the first change (the defininition from the .run() block
            if($rootScope.startDateWatched && scope) {
                delete $rootScope.selectedDateRangeRaw.end;
                $rootScope.startDateWatched = true;
            } else {
                $rootScope.startDateWatched = true;
            }
        });

        // if they change the end date, check the dates then propagate changes
        $rootScope.$watch('selectedDateRangeRaw.end', function(scope) {

            // make sure we aren't doing anything on the first change (the defininition from the .run() block
            if($rootScope.endDateWatched && scope) {
                $scope.checkStartAndEndDates($rootScope.selectedDateRangeRaw);
                $rootScope.endDateWatched = true;
            } else {
                $rootScope.endDateWatched = true;
            }
        });
    })

})();