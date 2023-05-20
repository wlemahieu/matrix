(function(){

	angular

	.module('Leadership', [
        'Matrix', // master application module
        'AfterCallSurvey', // survey responses for after calls
        'AgentClocks', // fix or create clocks & attendance marks
        'AgentEmulator', // emulate any agent
        'AgentSelector', // a selector for agents
        'ENPS', // employee NPS
        'NPS', // services used for ENPS / NPS
        'TeamSelector', // a selector for teams
        'ManageTeams', // create new, edit or deactivate teams
        'OneOnOnes', // for leadership-to-agent one-on-ones (scheduled meetings between the two parties)
        'PanelCookies', // handles the open/close Matrix dashboard panels through ngCookies
        'LeadershipStatistics', // handles shared leadership statistics
        'TeamFilter', // a filter for teams
        'UserProfiles', // edit user profiles
        'UserSchedules', // edit user schedules
        'Workflow', // agent workflow manager
        ])

    // dept-agnostic leadership data
    .run(function($rootScope, $location, $interval, API, Interval) {

        // if we are on the home page, forward to workflow
        if($location.path() === '') {
            $location.path('flow');
        }

        API.call( 'users', { action: 'read' } );
        API.call( 'teams', { action: 'read' } );
    })

    // leadership navbar
    .directive('navbar', function() {
        return {
            restrict: 'E',
            templateUrl: '/angular/templates/leadership/navigation.html',
            controller: function($scope, $rootScope) {

                // initiate the status dropdown
                $('.ui.dropdown').dropdown();
            }
        }
    })

})()