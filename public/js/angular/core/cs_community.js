(function() {

    /*
    Community module dependancies

    Community members only have a few things on their dashboard, making this module very light-weight.
    Survey responses from customers after chats / phone interactions.
    NPS scores which are based on the surveys
    

    'Matrix'
    The chain of dependancies are brought in through this module.


    'AfterCallSurvey'
    The module for fetching after call survey data and deriving a net-promoter-score from that data.

    */

    angular

    .module('CSCommunity', [
        'Matrix',
        'AfterCallSurvey',
        'NPS',
        ])

    .config(function($routeProvider) {

        $routeProvider.when('/', {
            templateUrl : 'angular/templates/leadership/dashboards/community.html'
        });
    })

    .run(function($http, $rootScope, NPS, API) {

        // grab surveys
        API.call( 'afterCallSurveys', { saveData: false } ).then(function(response) {

            // take our surveys and calculate detractors, promoters, and neutrals
            NPS.attach(response.data, 'satisfaction', 'afterCallSurveys').then(function(response) {

                //response.nps is also available...
                $rootScope.api.afterCallSurveys = response.surveys;
                $rootScope.loaders.afterCallSurveys = false;
            });
        });
    });
})();