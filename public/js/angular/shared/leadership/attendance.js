(function() {

	angular

	.module('AgentClocks', [])

    .config(function($routeProvider) {

        $routeProvider.when('/attendance', {
            templateUrl : 'angular/templates/leadership/attendance.html',
            controller: 'attendanceCtrl'
        })
    })

    // view controller
    .controller('attendanceCtrl', function($q, $scope, $rootScope, $timeout, $filter, $http, ACE, API) {
        
        // by default, the edit clocks modal will be closed
        // the object for a specific clock we are editing or creating is empty, until 'edit' or 'New Clock' is clicked
        $scope.editClockModalToggled = false;
        $rootScope.loaders.inline.aaa = false;

        // the filter for the user select
        // "active" "agents" in my "department" only.
        $scope.usersFilter = {
            active: 1,
            type: 'Agent', //disabled for dev
            dept: $rootScope.userinfo.dept
        };

        // initiate semantic-ui menu tabs (Availability, Clocks, Exceptions)
        $('.menu .item').tab();

        // store buttons for different use-cases
        $scope.button = {

            default: {
                title: 'Save',
                class: 'blue',
                icon: 'save',
                disabled: false
            },

            saving: {
                title: 'Saving',
                class: 'grey',
                icon: 'loading spinner',
                disabled: true
            },
            
            saved: {
                title: 'Saved',
                class: 'green',
                icon: 'thumbs up',
                disabled: true
            }
        };

        $scope.button.state = $scope.button.default;

        // change the field and direction we're ordering by
        $scope.orderByField = function(usage, orderBy) {

            // create orderBy object if it doesn't exist.
            if(!$scope.orderBy) {
                $scope.orderBy = {};
            }

            // create revereSort object if it doesn't exist.
            if(!$scope.reverseSort) {
                $scope.reverseSort = {};
            }

            // define our orderBy field for this tab (clocks or exceptions)
            $scope.orderBy[usage] = orderBy;

            // reverse our sort if it exists, or sort greatest first (true)
            if($scope.reverseSort[usage]) {
                $scope.reverseSort[usage] = !$scope.reverseSort[usage];
            } else {
                $scope.reverseSort[usage] = true;
            }
        }
        
        // default sort by start field
        $scope.orderByField('clocks', 'start');
        $scope.orderByField('exceptions', 'start');
        $scope.orderByField('availability', 'entdate');

        $scope.exceptions = [
        'Absence', 
        'Late Arrival', 
        'Early Departure', 
        'VPN', 
        'Break', 
        'Lunch', 
        'Dept Meeting', 
        'Other', 
        '1on1', 
        'Self-Directed Time', 
        'Team Meeting', 
        'Shift Swap'
        ];

        // agent-selector.html's ng-change function.
        // on user change, update the big-3 attendance calls.
        $scope.selectAgent = function() {

            // only continue with refreshing attendance availability/clocks/exceptions
            if($scope.selectedAgent != 'Select a user') {
                $scope.refreshAll();
            }
        }

        // takes the parameters passed through to openModal() and creates a dynamic modal title.
        $scope.createModalTitle = function(adj, noun) {

            var title;

            // create modal title's describing word
            if(adj == 'edit') {
                title = 'Edit';
            } else if(adj == 'new') {
                title = 'New';
            }

            // add a space after the adjective
            title += ' ';

            // create modal title 2nd part    
            if(noun == 'attendanceExceptions') {
                title = title.concat('Exceptions');
            } else if(noun == 'attendanceClocks') {
                title = title.concat('Clocks');
            }

            $scope.modalTitle = title;
        }

        // edit a specific item by cloning it from master, then bringing it into a modal
        // mode = 'edit' or 'new', id is only defined for 'edit' when editing a specific item
        // apiCall = 'attendanceExceptions' or 'attendanceClocks'. one day they can be the same, unfortunately, they are classified as "different" for now.
        //      this is a parameter because we have clocks and exceptions and want to recycle the modal and form since they are basically identically things.
        $scope.openModal = function(mode, apiCall, id) {
            
            var newObj;

            // create the modal title based on our parameters
            $scope.createModalTitle(mode, apiCall);

            // store the parameters for use in template ng expressions
            $scope.mode = mode;
            $scope.apiCall = apiCall;

            // we are editing a specific item
            if(mode == 'edit') {

                //$scope.manipulatedItem.type = '';

                // clone the item we want to edit from master into a temporary local object for use in the edit item modal
                angular.forEach($rootScope.api[apiCall], function(obj, key) {

                    // once we find the matching item id, clone it's contents from master to local
                    if(id === obj.id) {

                        // clone the object instead of referencing it
                        newObj = JSON.parse(JSON.stringify($rootScope.api[apiCall][key]));
                        // turn milliseconds into readable timestamps
                        newObj.start = $filter('date')(newObj.start, 'yyyy-MM-dd HH:mm:ss');
                        newObj.end = $filter('date')(newObj.end, 'yyyy-MM-dd HH:mm:ss');

                        // store the manipulated object item in $scope
                        $scope.manipulatedItem = newObj;
                    }
                });
            }

            // we are creating a brand new item
            else if(mode == 'new') {

                // create a brand new item object
                newObj = {
                    start: $filter('date')(new Date(), 'yyyy-MM-dd HH:mm:ss'),
                    end: '',
                    active: 1,
                    username: $scope.selectedAgent,
                    index: $rootScope.api[apiCall].length
                };

                $scope.manipulatedItem = JSON.parse(JSON.stringify(newObj));
            }

            // define the route which is used in the API to determine what model-functions to use
            if(apiCall == 'attendanceExceptions') {
                $scope.manipulatedItem.route = 'exceptions';
            } 
            else if(apiCall == 'attendanceClocks') {
                $scope.manipulatedItem.route = 'clocks';
            }

            // translate 0/1 to inactive/active
            if($scope.manipulatedItem.active == 1) {
                $scope.manipulatedItem.activeVerbose = 'Active';
            } else {
                $scope.manipulatedItem.activeVerbose = 'Inactive';
            }

            // initiate dropdown menu
            $('#single-attendance-item .ui.dropdown').dropdown('refresh');
        }

        // status dropdown select
        $scope.activeItem = function(status) {
            if(status) {
                $scope.manipulatedItem.active = 1;
                $scope.manipulatedItem.activeVerbose = 'Active'
            } else {
                $scope.manipulatedItem.active = 0;
                $scope.manipulatedItem.activeVerbose = 'Inactive'
            }
        }

        // exceptions dropdown select
        $scope.selectException = function(exception) {

            $scope.manipulatedItem.type = exception;
        }

        // save the item we're editing or creating and close the modal
        $scope.manipulateItem = function(id) {
            
            // only length check type for exceptions
            if( ($scope.apiCall == 'attendanceExceptions' && $scope.manipulatedItem.type && $scope.manipulatedItem.type.length > 0) || $scope.apiCall == 'attendanceClocks') {

                // enable the saving loader
                $scope.saving = true;
                $scope.button.state = $scope.button.saving;

                $http.post( '/api/manipulateAgentAttendanceItem', $scope.manipulatedItem ).then(function() {

                    $scope.button.state = $scope.button.saved;

                    // after we update the duration, let's push the edited object back to master, then hide the modal
                    $scope.updateNewDuration($scope.manipulatedItem).then(function(duration) {

                        // update the duration
                        $scope.manipulatedItem.duration = duration;

                        // overwrite existing agent item in master with our finished edit
                        if($scope.mode == 'edit') {

                            // check the master object for the item we just edited...
                            angular.forEach($rootScope.api[$scope.apiCall], function(obj, key) {

                                // once we find the matching clock id, clone it's contents from local back into to master
                                if(id === obj.id) {
                                    $rootScope.api[$scope.apiCall][key] = JSON.parse(JSON.stringify($scope.manipulatedItem));
                                }
                            });
                        } 
                        // new clocks are pushed to the end of the master object
                        else {
                            $rootScope.api[$scope.apiCall].push($scope.manipulatedItem);
                        }
                        
                        delete $scope.manipulatedItem;

                        // fetch new availability
                        API.call('availabilityMarks', {
                            username: $scope.selectedAgent,
                            startRange: $rootScope.selectedDateRange.start,
                            endRange: $rootScope.selectedDateRange.end
                        }).then(function() {

                            // disable the saving loader
                            $scope.saving = false;
                            $scope.button.state = $scope.button.default;
                        });
                    });
                });
            }
        }

        // find the duration between the start and end time (if there is one)
        $scope.updateNewDuration = function(clock) {

            return $q(function(resolve, reject) {

                var date = {};
                date.start = new Date(clock.start);

                // no end time? use today, otherwise use the end time provided
                if(clock.end) {
                    date.end = new Date(clock.end);
                    // push the edited clock into master
                    var start = date.start.getTime()/1000;
                    var end = date.end.getTime()/1000;
                    var duration = end - start;
                } else {
                    date.end = new Date();
                }

                resolve(duration);
            });

            return promise;
        }

        // trigger ACE service's refresh function
        $scope.refreshAll = function() {

            delete $scope.manipulatedItem;

            $rootScope.loaders.inline.ace = true;
            ACE.refresh($scope.selectedAgent).then(function() {
                $rootScope.loaders.inline.ace = false;
            });
        }
    })

    // API calls for attendance Availability, Clocks & Exceptions
    .service('ACE', function($q, $rootScope, API) {

        this.refresh = function(username) {

            var defer = $q.defer();

            // chain all promises
            Promise.all(['attendanceClocks','availabilityMarks','attendanceExceptions'].map(function(path) { 
                return API.call(path, {
                    username: username,
                    startRange: $rootScope.selectedDateRange.start,
                    endRange: $rootScope.selectedDateRange.end
                });
            })).then(function() {
                defer.resolve();
            });

            return defer.promise
        }
    })
})()