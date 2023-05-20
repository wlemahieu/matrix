(function() {

    /*
    The checkins module is used for fetching and displaying checkins.
    It's shared between Agents and Leadership
    */

    angular

    .module('Checkins', [])

    // checkins line items
    .directive('checkins', function() {

        return {
            templateUrl: '/angular/templates/shared/checkins.html',
            controller: function($q, $rootScope, $scope, $filter, $http) {

                $scope.filterCheckins = function(item) {
                    switch(item) {
                        case 'not_presented':
                            $scope.checkinFilter = { presented: 0, completed: 1 };
                        break;
                        case 'presented':
                            $scope.checkinFilter = { presented: 1, completed: 1 };
                        break;
                        case 'incomplete':
                            $scope.checkinFilter = { completed: 0 };
                        break;
                        case 'not_accepted':
                            $scope.checkinFilter = { presented: 1, accepted: 0 };
                        break;
                        case 'accepted':
                            $scope.checkinFilter = { presented: 1, accepted:  1 };
                        break;
                    }

                    $scope.checkinActiveTab = item;
                }

                // user-type based filters                
                if($rootScope.userinfo.type === 'Agent') {
                    $scope.filterCheckins('not_accepted');
                }
                else if($rootScope.userinfo.type === 'Supervisor') {

                    $scope.filterCheckins('not_presented');

                    $scope.usersFilter = {
                        type: 'Agent',
                        team: $rootScope.userinfo.team,
                        dept: $rootScope.userinfo.dept,
                        active: 1
                    };
                }

                else if($rootScope.userinfo.type === 'Manager') {

                    $scope.filterCheckins('not_presented');

                    $scope.usersFilter = {
                        type: 'Agent',
                        dept: $rootScope.userinfo.dept,
                        active: 1
                    };
                }

                // fields to ignore when checking for unanswered fields
                // this is also used in this file (the shared checkins.js file) in the recountStatuses() function
                $scope.ignoreFields = [
                    'id',
                    'unanswered_fields',
                    'presented_timestamp',
                    'accepted_timestamp',
                    'creation_date',
                    'lead_username',
                    'comment',
                    'presented',
                    'accepted',
                    'completed'
                ];

                // toggle a specific tab
                $scope.toggleTab = function(tab) {

                    // pass 'new' but no id since it's "new"
                    if(tab === 'new') {
                        $scope.resetForm();
                        $scope.toggleManipulate(tab);
                    }

                    // for leadership, when leaving certain views, perform some tasks.
                    if($rootScope.userinfo.type === 'Supervisor' || $rootScope.userinfo.type === 'Manager') {

                        // anytime we leave the view-single-checkin tab, clear the checkin from $scope
                        if(tab !==  'view') {
                            $scope.singleCheckin = undefined;
                        }

                        // anytime we leave the edit-single-checkin tab, clear the checkin from $scope
                        if(tab !==  'edit') {
                            $scope.resetForm();
                        }
                    }

                    // switch tab
                    $scope.tab = tab;
                }

                // re-count all status counters
                // form is optional
                $scope.reCountStatuses = function() {

                    return $q(function(resolve, reject) {

                        counters = {
                            accepted: 0,
                            incomplete: 0,
                            not_accepted: 0,
                            not_presented: 0,
                            presented: 0
                        };

                        // iterate through each checkin
                        angular.forEach($rootScope.api.checkins.line_items, function(obj, key) {

                            var unanswered_fields = 0;

                            // iterate through each checkin field
                            angular.forEach(obj, function(val, field) {

                                // only check non-ignored fields, basically all question-answers.
                                if($scope.ignoreFields.indexOf(field) === -1) {

                                    // if the field is null, empty, or undefined, it's unanswered.
                                    if(val === null || val === '' || val === undefined) {
                                        unanswered_fields++;
                                    }
                                }
                            });

                            // Increase Incomplete
                            if(unanswered_fields > 0) {
                                counters.incomplete++;
                            }

                            // Increase Presented
                            if(obj.presented === 0 && obj.completed === 1) {
                                counters.not_presented++;
                            }
                            // Increase Presented
                            else if(obj.presented === 1 && obj.completed === 1) {
                                counters.presented++;
                            }

                            // Increase Not Accepted
                            if(obj.presented === 1 && obj.accepted === 0) {
                                counters.not_accepted++;
                            }
                            // Increase Accepted
                            else if(obj.presented === 1 && obj.accepted === 1) {
                                counters.accepted++;
                            }
                        });

                        // store counters back in $rootScope
                        $rootScope.api.checkins.counters = counters;

                        resolve();
                    });

                    return promise;
                }

                // accept checkin (agents only use this)
                $scope.accept = function(id) {

                    // immediately remove from view by marking as accepted
                    angular.forEach($rootScope.api.checkins.line_items, function(obj, key) {
                        if(obj.id == id) {
                            $rootScope.api.checkins.line_items[key].accepted = 1;
                        }
                    });

                    payload = {
                        action: 'accept',
                        id: id
                    };

                    $http.post( '/api/checkins', payload ).then(function(response) {
                        $scope.reCountStatuses();
                    });
                }

                // view a checkin
                $scope.view = function(id) {

                    // find the checkin in master and clone it to $scope for view
                    angular.forEach($rootScope.api.checkins.line_items, function(obj, key) {
                        if(obj.id === id) {
                            $scope.singleCheckin = $rootScope.api.checkins.line_items[key];
                        }
                    });

                    $scope.toggleTab('view');

                    // agents use this within a modal
                    if($rootScope.userinfo.type === 'Agent') {
                        $('#view-checkin').modal('show');
                    }
                }

                // 1st set of Q&A properties
                $scope.qaProperties1Template = [

                    {
                        icon : 'child',
                        question : 'Proper Greeting Used?',
                        yesColor : 'green',
                        noColor : 'red',
                        dbField : 'proper_greeting'
                    },
                    {
                        icon : 'barcode',
                        question : 'Authentication protocol followed?',
                        yesColor : 'green',
                        noColor : 'red',
                        dbField : 'correct_auth'
                    },
                    {
                        icon : 'unlock',
                        question : 'Account info given to unlisted contact?',
                        yesColor : 'red',
                        noColor : 'green',
                        dbField : 'unlisted_info_given'
                    },
                    {
                        icon : 'target',
                        question : 'Issue successfully identified?',
                        yesColor : 'green',
                        noColor : 'red',
                        dbField : 'identified_issue'
                    },
                    {
                        icon : 'idea',
                        question : 'Educated customer on troubleshooting?',
                        yesColor : 'green',
                        noColor : 'red',
                        dbField : 'educate_customer'
                    },
                    {
                        icon : 'idea',
                        question : 'Proper solutions provided?',
                        yesColor : 'green',
                        noColor : 'red',
                        dbField : 'proper_solutions'
                    }
                ];

                // 2nd set of Q&A properties
                $scope.qaProperties2Template = [

                    {
                        icon : 'child',
                        question : 'Closed interaction properly?',
                        yesColor : 'green',
                        noColor : 'red',
                        dbField : 'correctly_closed'
                    },
                    {
                        icon : 'pencil',
                        question : 'Proper notes generated?',
                        yesColor : 'green',
                        noColor : 'red',
                        dbField : 'proper_notes'
                    },
                    {
                        icon : 'pencil',
                        question : 'Unnecessary support request created?',
                        yesColor : 'red',
                        noColor : 'green',
                        dbField : 'unnecessary_sr'
                    },
                    {
                        icon : 'target',
                        question : 'Control maintained throughout?',
                        yesColor : 'green',
                        noColor : 'red',
                        dbField : 'maintain_control'
                    },
                    {
                        icon : 'smile',
                        question : 'Proper tone, attitude & assurance?',
                        yesColor : 'green',
                        noColor : 'red',
                        dbField : 'tone_attitude_assurance'
                    },
                    {
                        icon : 'unlock',
                        question : 'Break or miss any other procedures?',
                        yesColor : 'red',
                        noColor : 'green',
                        dbField : 'missed_procedures'
                    }
                ];

            },
            restrict: 'E'
        };
    })

    // agent checkins view (simply a directive wrapper. directiveception)
    .directive('agentCheckins', function() {

        return {
            templateUrl: '/angular/templates/agents/checkins.html',
            restrict: 'E'
        };
    })

    // view a single checkin
    .directive('viewCheckin', function() {

        return {
            templateUrl: '/angular/templates/shared/view-checkin.html',
            controller: function($rootScope, $scope) {},
            restrict: 'E'
        };
    })

    // view a single checkin in a modal
    .directive('viewCheckinModal', function() {

        return {
            templateUrl: '/angular/templates/agents/modals/view-checkin.html',
            controller: function($rootScope, $scope) {},
            restrict: 'E'
        };
    })

})();