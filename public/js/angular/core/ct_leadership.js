(function() {

    /**
     * Ct_leadership.js is the CT Leadership's module (Leads and Managers)
     */

    angular

    .module('CTLeadership', [
        'Leadership', // a global module for all Leadership
        ])

    .run(function($rootScope, API) {  

        // define our panels for panel filter's ng-repeat
        $rootScope.panels = [
            'All',
            'Statistics',
            'Workflow'
        ];
    })

    // refreshes alerts & statistics
    .service('Dashboard', function($q, $rootScope, $interval, API) {

        this.refresh = function() {

            return $q(function(resolve, reject) {

                // create a payload
                payload = Object.create(null);
                payload.action = 'read';
                payload.dateRange = $rootScope.selectedRangeMaster;

                API.call( 'alerts', payload ).then(function(response){

                    var count = 0;
                    angular.forEach(response.data, function(obj, key) {
                        count++;
                    });
                    $rootScope.alertsCount = count;

                    // create a payload
                    payload = Object.create(null);
                    payload.action = 'read';
                    payload.dateRange = $rootScope.selectedRangeMaster;

                    API.call( 'statistics', payload );
                });

                resolve();
            });
            
            return promise;
        }
    })

})();