(function() {

    /*
    This module has no dependancies.

    The panel cookies module sets the defaults for the panels when a user enters the page.
    Eventually, this module could be dynamic and load specifically saved-panel cookies perhaps in Redis.
    */

    angular

    .module('PanelCookies', [])

    .run(function($rootScope) {

        // initialize panel cookies (1 means showing, 0 means hidden by end-user)
        // agent only unless marked otherwise
        $rootScope.cookies = {};
        $rootScope.cookies.panels = {};
        $rootScope.cookies.panels.customer_satisfaction = 1;
        $rootScope.cookies.panels.period_csat = 1;
        $rootScope.cookies.panels.customer_comments = 1;
        $rootScope.cookies.panels.availability = 1;
        $rootScope.cookies.panels.attendance = 1;
        $rootScope.cookies.panels.contacts = 1;
        $rootScope.cookies.panels.cs_sales = 1;
        $rootScope.cookies.panels.my_contribution = 1;
        $rootScope.cookies.panels.payouts = 1;
        $rootScope.cookies.panels.eligibility_formula = 1;
        $rootScope.cookies.panels.line_items = 1;
        $rootScope.cookies.panels.checkins = 1; // shared between leadership / agents
        $rootScope.cookies.panels.one_on_ones = 1; // shared between leadership / agents
    });

})();