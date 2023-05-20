(function() {

    /**
     * Agents.js is the Agent's module, shared between all departments.
     */

	angular

	.module('Agents', [
        'Matrix', // master module
        '1on1s', // read 1on1s
        'AgentEmulator', // exit emulation as an agent
        'AgentExit', // exit matrix module for agents,
        'AgentStatuses', // fetches initially and on an interval the agent's status
        'Attendance', // the attendance panel for agents has an eyeball which provides all attendance details
        'CallerInfo', // fetches initially and on an interval any caller the agent may be interacting with
        'ChannelController', // for manipulating status in channels (chat, phone, ticket)
        'Checkins', // for leadership-to-agent check-ins (Quality-Assurance reporting)
        'ExceptionsController', // for taking an attendance exception (break, lunch, etc)
        'OneOnOnes', // for leadership-to-agent one-on-ones (scheduled meetings between the two parties)
        'PanelCookies', // this module handles the open/close Matrix dashboard panels through ngCookies
        ])

    .run(function($rootScope, APIModifiedTime) {
        
        // by default, we have no agent actions pending (navbar interactions, etc)
        $rootScope.agentActionComplete = true;
        APIModifiedTime.save('navbar');
    })

    // agent navbar
    .directive('navbar', function() {
        return {
            restrict: 'E',
            templateUrl: '/angular/templates/agents/navigation.html',
            controller: function($scope, $rootScope) {

                // prepare objects for storing statuses
                $scope.ngDisabled = {};
                $scope.ngHide = {};
                $scope.ngClass = {};

                var self = this;

                // refresh rules (happens on $watchers)
                this.refreshRules = function(dept) {

                    statuses = $rootScope.api.agentStatuses;

                    switch(dept) {

                        // CS RULES
                        case 'CS':

                            if($rootScope.userinfo.dept == 'CS') {
                                
                                /*
                                Enter Tickets
                                    ngDisabled rule: They must be online in a live channel already, OR are on an exception (like break or lunch)
                                    ngClass rule: paused shows yellow, ready shows green, offline shows default
                                    ngHide rule: hide 'Enter Tickets' button if live channel is not 'tickets' and not 'all'.
                                */

                                // ngDisabled
                                if(statuses.live_channel === 'tickets'){
                                    $scope.ngDisabled.enterTickets = true;
                                } else {
                                    $scope.ngDisabled.enterTickets = false;
                                }

                                // ngClass 
                                switch(statuses.tickets_status) {

                                    case 'paused':
                                        $scope.ngClass.enterTickets = 'orange';
                                    break;

                                    case 'ready':
                                        $scope.ngClass.enterTickets = 'green';
                                    break;

                                    case 'offline':
                                        $scope.ngClass.enterTickets = '';
                                    break;
                                }

                                // ENTER TICKETS (ngHide)
                                if(statuses.phones_status == 'ready' || statuses.phones_status == 'paused' || statuses.chats_status == 'ready' || statuses.chats_status == 'paused' || statuses.current_exception !== 'none') {
                                    $scope.ngHide.enterTickets = true;
                                } else {
                                    $scope.ngHide.enterTickets = false;
                                }


                                /*
                                Exit Chats
                                    ngDisabled rule: They are 'offline' and thus cannot exit, OR are on an exception (like break or lunch)
                                */
                                // ngDisabled
                                if(
                                    statuses.tickets_status == 'offline' || 
                                    statuses.current_exception != 'none'
                                    )
                                {
                                    $scope.ngDisabled.exitTickets = true;
                                } else {
                                    $scope.ngDisabled.exitTickets = false;
                                }

                                // ngHide
                                if(statuses.tickets_status == 'offline' || statuses.phones_status != 'offline' || statuses.chats_status != 'offline') {
                                    $scope.ngHide.exitTickets = true;
                                } else {
                                    $scope.ngHide.exitTickets = false;
                                }

                                /*
                                Enter Chats
                                    ngDisabled rule: They must be online in a live channel already, OR are on an exception (like break or lunch)
                                    ngClass rule: paused shows yellow, ready shows green, offline shows default
                                */

                                // ngDisabled
                                if(
                                    statuses.chats_status != 'offline' || 
                                    statuses.phones_status != 'offline' || 
                                    statuses.tickets_status != 'offline' || 
                                    statuses.current_exception != 'none'
                                    ) 
                                {
                                    $scope.ngDisabled.enterChats = true;
                                } else {
                                    $scope.ngDisabled.enterChats = false;
                                }

                                // ngClass 
                                switch(statuses.chats_status) {

                                    case 'paused':
                                        $scope.ngClass.enterChats = 'orange';
                                    break;

                                    case 'ready':
                                        $scope.ngClass.enterChats = 'green';
                                    break;

                                    case 'offline':
                                        $scope.ngClass.enterChats = '';
                                    break;
                                }

                                // ENTER CHATS (ngHide)
                                if(statuses.phones_status == 'ready' || statuses.phones_status == 'paused' || statuses.tickets_status == 'ready' || statuses.tickets_status == 'paused' || statuses.current_exception !== 'none') {
                                    $scope.ngHide.enterChats = true;
                                } else {
                                    $scope.ngHide.enterChats = false;
                                }

                                /*
                                Pause Chats
                                    ngDisabled rule: They are not 'ready' and thus cannot be paused, OR are on an exception (like break or lunch)
                                */
                                // ngDisabled
                                if(
                                    statuses.chats_status != 'ready' || 
                                    statuses.current_exception != 'none'
                                    ) 
                                {
                                    $scope.ngDisabled.pauseChats = true;
                                } else {
                                    $scope.ngDisabled.pauseChats = false;
                                }

                                // ngHide
                                if(statuses.chats_status == 'offline' || statuses.phones_status != 'offline' || statuses.tickets_status != 'offline') {
                                    $scope.ngHide.pauseChats = true;
                                } else {
                                    $scope.ngHide.pauseChats = false;
                                }

                                /*
                                Resume Chats
                                    ngDisabled rule: They are not 'paused' and thus cannot be resumed, OR are on an exception (like break or lunch)
                                */
                                // ngDisabled
                                if(
                                    statuses.live_channel != 'chats' ||
                                    statuses.chats_status != 'paused' || 
                                    statuses.current_exception != 'none'
                                    )
                                {
                                    $scope.ngDisabled.resumeChats = true;
                                } else {
                                    $scope.ngDisabled.resumeChats = false;
                                }

                                // ngHide
                                if(statuses.chats_status == 'offline' || statuses.phones_status != 'offline' || statuses.tickets_status != 'offline') {
                                    $scope.ngHide.resumeChats = true;
                                } else {
                                    $scope.ngHide.resumeChats = false;
                                }

                                /*
                                Exit Chats
                                    ngDisabled rule: They are 'offline' and thus cannot exit, OR are on an exception (like break or lunch)
                                */
                                // ngDisabled
                                if(
                                    statuses.chats_status == 'offline' || 
                                    statuses.current_exception != 'none'
                                    )
                                {
                                    $scope.ngDisabled.exitChats = true;
                                } else {
                                    $scope.ngDisabled.exitChats = false;
                                }

                                // ngHide
                                if(statuses.chats_status == 'offline' || statuses.phones_status != 'offline' || statuses.tickets_status != 'offline') {
                                    $scope.ngHide.exitChats = true;
                                } else {
                                    $scope.ngHide.exitChats = false;
                                }

                                /*
                                Enter Phones
                                    ngDisabled rule: They must be online in a live channel already, OR are on an exception (like break or lunch)
                                    ngClass rule: paused shows yellow, ready shows green, offline shows default
                                */

                                // ngDisabled
                                if(
                                    statuses.chats_status != 'offline' || 
                                    statuses.phones_status != 'offline' || 
                                    statuses.tickets_status != 'offline' || 
                                    statuses.current_exception != 'none'
                                    ) 
                                {
                                    $scope.ngDisabled.enterPhones = true;
                                } else {
                                    $scope.ngDisabled.enterPhones = false;
                                }

                                // ngClass 
                                switch(statuses.phones_status) {

                                    case 'paused':
                                        $scope.ngClass.enterPhones = 'orange';
                                    break;

                                    case 'ready':
                                        $scope.ngClass.enterPhones = 'green';
                                    break;

                                    case 'offline':
                                        $scope.ngClass.enterPhones = '';
                                    break;
                                }

                                // ENTER PHONES (ngHide)
                                if(statuses.chats_status == 'ready' || statuses.chats_status == 'paused' || statuses.tickets_status == 'ready' || statuses.tickets_status == 'paused' || statuses.current_exception !== 'none') {
                                    $scope.ngHide.enterPhones = true;
                                } else {
                                    $scope.ngHide.enterPhones = false;
                                }

                                /*
                                Pause Phones
                                    ngDisabled rule: They are not 'ready' and thus cannot be paused, OR are on an exception (like break or lunch)
                                */
                                // ngDisabled
                                if(
                                    statuses.tickets_status != 'offline' || 
                                    statuses.chats_status != 'offline' || 
                                    statuses.phones_status != 'ready' || 
                                    statuses.current_exception != 'none'
                                    ) 
                                {
                                    $scope.ngDisabled.pausePhones = true;
                                } else {
                                    $scope.ngDisabled.pausePhones = false;
                                }

                                // ngHide
                                if(statuses.phones_status == 'offline' || statuses.chats_status != 'offline' || statuses.tickets_status != 'offline') {
                                    $scope.ngHide.pausePhones = true;
                                } else {
                                    $scope.ngHide.pausePhones = false;
                                }


                                /*
                                Resume Phones
                                    ngDisabled rule: They are not 'paused' and thus cannot be resumed, OR are on an exception (like break or lunch)
                                */
                                // ngDisabled
                                if(
                                    statuses.live_channel != 'phones' ||
                                    statuses.phones_status != 'paused' || 
                                    statuses.current_exception != 'none'
                                    )
                                {
                                    $scope.ngDisabled.resumePhones = true;
                                } else {
                                    $scope.ngDisabled.resumePhones = false;
                                }

                                // ngHide
                                if(statuses.phones_status == 'offline' || statuses.chats_status != 'offline' || statuses.tickets_status != 'offline') {
                                    $scope.ngHide.resumePhones = true;
                                } else {
                                    $scope.ngHide.resumePhones = false;
                                }

                                /*
                                Exit Phones
                                    ngDisabled rule: They are 'offline' and thus cannot exit, OR are on an exception (like break or lunch)
                                */
                                // ngDisabled
                                if(
                                    statuses.phones_status == 'offline' || 
                                    statuses.current_exception != 'none'
                                    )
                                {
                                    $scope.ngDisabled.exitPhones = true;
                                } else {
                                    $scope.ngDisabled.exitPhones = false;
                                }

                                // ngHide
                                if(statuses.phones_status == 'offline' || statuses.chats_status != 'offline' || statuses.tickets_status != 'offline') {
                                    $scope.ngHide.exitPhones = true;
                                } else {
                                    $scope.ngHide.exitPhones = false;
                                }
                            }
                        break;

                        // SALES RULES
                        case 'Sales':

                            if($rootScope.userinfo.dept == 'Sales') {
                                
                                /*
                                Enter Chats
                                    ngDisabled rule: They must be online in chats already, OR are on an exception (like break or lunch)
                                    ngClass rule: paused shows yellow, ready shows green, offline shows default
                                */

                                // ngDisabled
                                if(
                                    statuses.chats_status != 'offline' || 
                                    statuses.current_exception != 'none'
                                    ) 
                                {
                                    $scope.ngDisabled.enterChats = true;
                                } else {
                                    $scope.ngDisabled.enterChats = false;
                                }

                                // ngClass 
                                switch(statuses.chats_status) {

                                    case 'paused':
                                        $scope.ngClass.enterChats = 'orange';
                                    break;

                                    case 'ready':
                                        $scope.ngClass.enterChats = 'green';
                                    break;

                                    case 'offline':
                                        $scope.ngClass.enterChats = '';
                                    break;
                                }

                                /*
                                Pause Chats
                                    ngDisabled rule: They are not 'ready' and thus cannot be paused, OR are on an exception (like break or lunch)
                                */
                                // ngDisabled
                                if(
                                    statuses.chats_status != 'ready' || 
                                    statuses.current_exception != 'none'
                                    ) 
                                {
                                    $scope.ngDisabled.pauseChats = true;
                                } else {
                                    $scope.ngDisabled.pauseChats = false;
                                }

                                /*
                                Resume Chats
                                    ngDisabled rule: They are not 'paused' and thus cannot be resumed, OR are on an exception (like break or lunch)
                                */
                                // ngDisabled
                                if(
                                    statuses.chats_status != 'paused' || 
                                    statuses.current_exception != 'none'
                                    )
                                {
                                    $scope.ngDisabled.resumeChats = true;
                                } else {
                                    $scope.ngDisabled.resumeChats = false;
                                }

                                /*
                                Exit Chats
                                    ngDisabled rule: They are 'offline' and thus cannot exit, OR are on an exception (like break or lunch)
                                */
                                // ngDisabled
                                if(
                                    statuses.chats_status == 'offline' || 
                                    statuses.current_exception != 'none'
                                    )
                                {
                                    $scope.ngDisabled.exitChats = true;
                                } else {
                                    $scope.ngDisabled.exitChats = false;
                                }

                                /*
                                Enter Phones
                                    ngDisabled rule: They must be online in phones already, OR are on an exception (like break or lunch)
                                    ngClass rule: paused shows yellow, ready shows green, offline shows default
                                */

                                // ngDisabled
                                if(
                                    statuses.phones_status != 'offline' || 
                                    statuses.current_exception != 'none'
                                    ) 
                                {
                                    $scope.ngDisabled.enterPhones = true;
                                } else {
                                    $scope.ngDisabled.enterPhones = false;
                                }

                                // ngClass 
                                switch(statuses.phones_status) {

                                    case 'paused':
                                        $scope.ngClass.enterPhones = 'orange';
                                    break;

                                    case 'ready':
                                        $scope.ngClass.enterPhones = 'green';
                                    break;

                                    case 'offline':
                                        $scope.ngClass.enterPhones = '';
                                    break;
                                }

                                /*
                                Pause Phones
                                    ngDisabled rule: They are not 'ready' and thus cannot be paused, OR are on an exception (like break or lunch)
                                */
                                // ngDisabled
                                if(
                                    statuses.phones_status != 'ready' || 
                                    statuses.current_exception != 'none'
                                    ) 
                                {
                                    $scope.ngDisabled.pausePhones = true;
                                } else {
                                    $scope.ngDisabled.pausePhones = false;
                                }

                                /*
                                Resume Phones
                                    ngDisabled rule: They are not 'paused' and thus cannot be resumed, OR are on an exception (like break or lunch)
                                */
                                // ngDisabled
                                if(
                                    statuses.phones_status != 'paused' || 
                                    statuses.current_exception != 'none'
                                    )
                                {
                                    $scope.ngDisabled.resumePhones = true;
                                } else {
                                    $scope.ngDisabled.resumePhones = false;
                                }

                                /*
                                Exit Phones
                                    ngDisabled rule: They are 'offline' and thus cannot exit, OR are on an exception (like break or lunch)
                                */
                                // ngDisabled
                                if(
                                    statuses.phones_status == 'offline' || 
                                    statuses.current_exception != 'none'
                                    )
                                {
                                    $scope.ngDisabled.exitPhones = true;
                                } else {
                                    $scope.ngDisabled.exitPhones = false;
                                }


                            }
                        break;
                    }
                }

                /*
                Channel Status Watchers

                These watchers are important for maintaining the clarity of code.
                It's also responsible for channel control button rules differences between Sales & CS
                */
                
                // watch chats status
                $rootScope.$watch('api.agentStatuses.chats_status', function(scope) {
                    if(scope !== undefined) {
                        self.refreshRules($rootScope.userinfo.dept);
                    }
                });

                // watch phones status
                $rootScope.$watch('api.agentStatuses.phones_status', function(scope) {
                    if(scope !== undefined) {
                        self.refreshRules($rootScope.userinfo.dept);
                    }
                });

                // watch tickets status
                $rootScope.$watch('api.agentStatuses.tickets_status', function(scope) {
                    if(scope !== undefined) {
                        self.refreshRules($rootScope.userinfo.dept);
                    }
                });

                // watch current exception
                $rootScope.$watch('api.agentStatuses.current_exception', function(scope) {
                    if(scope !== undefined) {
                        self.refreshRules($rootScope.userinfo.dept);
                    }
                });
            }
        }
    })

    .directive('lineItemSales', function() {
        return {
            templateUrl: '/angular/templates/agents/panels/shared/line-item-sales.html',
            restrict: 'E',
            controller: function ($scope, $rootScope) {

                $scope.calculateTotals = function() {

                    // default totals are 0
                    var totals = {
                        paid: 0,
                        credit: 0,
                        refund: 0,
                        bep: 0
                    };

                    // count totals
                    angular.forEach($rootScope.api.statistics.sales.line_items, function(obj, key) {

                        // only calculate totals for the class selected in the filter
                        if(obj.class == $scope.totalsFilter.class || $scope.totalsFilter.class === 'all') {
                            totals.paid += parseInt(obj.paid);
                            totals.credit += parseInt(obj.credit);
                            totals.refund += parseInt(obj.refund);
                            totals.bep += parseInt(obj.bep);
                        }
                    });

                    // store totals
                    $rootScope.api.statistics.sales.totals = totals;
                }

                // watch for changes to line items so we can re-calculate totals
                $rootScope.$watch('api.statistics.sales.line_items', function(scope) {
                    if(scope) {
                        $scope.calculateTotals();
                    }
                });
                $scope.$watch('totalsFilter.class', function(scope) {
                    if(scope && $rootScope.api.statistics) {
                        $scope.calculateTotals();
                    }
                });

                $scope.lineItemsFilter = {
                    class: '!hist_refund'
                }

                $scope.totalsFilter = {
                    class: 'all'
                }
            }
        };
    })

	.directive('csatBreakdown', function() {
        return {
            templateUrl: '/angular/templates/agents/panels/shared/csat-breakdown.html',
            controller: function($rootScope, $scope, $filter) {

                // watch for changes to csat overall total change to update progress bar
                $rootScope.$watch('api.statistics.period.csat_overall', function(scope) {
                    if(scope) {
                        if(scope > 100) {
                            scope = 100;
                        }
                        $('#csat_overall').progress({ percent: scope });
                    }
                });

                // watch for changes to csat today total change to update progress bar
                $rootScope.$watch('api.statistics.today.csat_overall', function(scope) {
                    if(scope) {
                        if(scope > 100) {
                            scope = 100;
                        }
                        $('#csat_today').progress({ percent: scope });
                    }
                });
            },
            restrict: 'E'
        };
    })

    .directive('customerComments', function() {
        return {
            templateUrl: '/angular/templates/agents/panels/shared/customer-comments.html',
            restrict: 'E'
        };
    })

    .directive('availability', function() {
        return {
            templateUrl: '/angular/templates/agents/panels/shared/availability.html',
            controller: function($rootScope, $scope, $filter) {

                // watch for changes to csat overall total change to update progress bar
                $rootScope.$watch('api.statistics.period.availability', function(scope) {
                    if(scope) {
                        if(scope > 100) {
                            scope = 100;
                        }
                        $('#availability_overall').progress({ percent: scope });
                    }
                });

                // watch for changes to csat today total change to update progress bar
                $rootScope.$watch('api.statistics.today.availability', function(scope) {
                    if(scope) {
                        if(scope > 100) {
                            scope = 100;
                        }
                        $('#availability_today').progress({ percent: scope });
                    }
                });
            },
            restrict: 'E'
        };
    })

    .directive('attendance', function() {
        return {
            templateUrl: '/angular/templates/agents/panels/shared/attendance.html',
            restrict: 'E'
        };
    })

    .directive('contacts', function() {
        return {
            templateUrl: '/angular/templates/agents/panels/shared/contacts.html',
            restrict: 'E'
        };
    })

})();