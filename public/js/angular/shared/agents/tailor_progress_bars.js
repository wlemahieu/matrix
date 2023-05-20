(function() {

    /*
    Tailor Progress Bars

    This module is used for setting the progress bars based on statistics in the api/statistics call
    */

    angular

    .module('TailorProgressBars', [])

    // set progress bars based on the statistics api
    .service('setProgressBars', function($q, $filter, $rootScope) {

        this.now = function() {

            return $q(function(resolve, reject) {

                var color,

                // shorthand variables
                stats = $rootScope.api.statistics,
                today = stats.today,
                period = stats.period,
                params = $rootScope.params;

                // convert decimals to percentages for comparisons below
                csatMin = params.customer_satisfaction_min * 100,
                csatBonus = params.customer_satisfaction_bonus * 100,
                availMin = params.availability_min * 100,
                availBonus = params.availability_bonus * 100,
                cpdMin = params.contacts_per_day_min,
                cpdBonus = params.contacts_per_day_bonus,
                attMin = params.attendance_min * 100;

                // PROGRESS BAR COLORS

                // period metrics
                if(period) {

                    // period csat
                    if(period.csat_overall >= csatMin && period.csat_overall < csatBonus) {
                        color = 'yellow';
                    } else if (period.csat_overall >= csatBonus) {
                        color = 'green';
                    } else if (period.csat_overall < csatMin) {
                        color = 'red';
                    }
                    $rootScope.csatPeriodStatus = color;

                    // period contacts-per-day
                    if(period.cpd >= cpdMin && period.cpd < cpdBonus) {
                        color = 'warning';
                    } 
                    else if(period.cpd >= cpdBonus) {
                        color = 'positive';
                    } 
                    else if(period.cpd < cpdMin) {
                        color = 'negative';
                    }
                    $rootScope.cpdPeriodStatus = color;

                    // period availability
                    if(period.availability >= availMin && period.availability < availBonus) {
                        color = 'yellow';
                    } else if (period.availability >= availBonus) {
                        color = 'green';
                    } else if (period.availability < availMin) {
                        color = 'red';
                    }
                    $rootScope.availPeriodStatus = color;
                } 

                // today metrics
                if(today) {

                    // today csat
                    if(today.csat_overall >= csatMin && today.csat_overall < csatBonus) {
                        color = 'yellow';
                    } else if (today.csat_overall >= csatBonus) {
                        color = 'green';
                    } else if (today.csat_overall < csatMin) {
                        color = 'red';
                    }
                    $rootScope.csatTodayStatus = color;

                    // today availability
                    if(today.availability >= availMin && today.availability < availBonus) {
                        color = 'yellow';
                    } 
                    else if (today.availability >= availBonus) {
                        color = 'green';
                    } 
                    else if (today.availability < $rootScope.availMin) {
                        color = 'red';
                    }
                    $rootScope.availTodayStatus = color;

                    // today contacts-per-day
                    if(today.cpd >= cpdMin && today.cpd < cpdBonus) {
                        color = 'warning';
                    } 
                    else if(today.cpd >= cpdBonus) {
                        color = 'positive';
                    } 
                    else if(today.cpd < cpdMin) {
                        color = 'negative';
                    }
                    $rootScope.cpdTodayStatus = color;
                }
                
                resolve();
            });

            return promise;
        }
    })

})();