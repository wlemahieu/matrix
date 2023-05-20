(function() {
	
	/**
	* The 'Bootstrap' module is the 1st AND absolutely most important module for The Matrix.
	* It identifies the end-user and bootstraps their respective AngularJS base-module.
	* Base-modules are located in the 'js/angular/core' folder and are directly related to the different states possible within The Matrix.
	* Possible states are 
	*	Onboardee, meaning you are not registered in The Matrix, therefor you should be onboarded. ( onboardee.js )
	*	Agent, CS or Sales ( cs_agent.js, sales_agent.js )
	*	Leads & Managers, CS or Sales ( cs_lead.js & cs_manager.js --> cs_leadership.js,  sales_lead.js & sales_manager.js --> sales_leadership.js )
	* 
	*/
	var Bootstrap = angular.module('Bootstrap', [
		'routeHelper' // a module for storing the current route in $rootScope.path and clearing template caching
		]);

	var initInjector = angular.injector(['ng']);
	var $http = initInjector.get('$http');
	var $rootScope = initInjector.get('$rootScope');

	return $http.get('/api/gatherPHP').then(function(response) {
		
		var data = response.data;

		angular.element(document).ready(function() {

			var app;

			// if they are no in the Matrix, they must need to get onboarded. otherwise, load their module (like CSAgent, or SalesManager)
			data.userInfo === undefined ? app = 'Onboardee' : app = data.userInfo.dept + data.userInfo.type;

			console.log($rootScope.path);

			this.payload = {
				authUrl: data.authUrl,
				userInfo: data.userInfo,
			};

			// cs-only
			if(data.parameters) {
				this.payload.parameters = data.parameters;
			}

			// pass the data to global.js module
			angular.module(app).value('BootstrapPayload', this.payload);

			//bootstrap module dependant on user type. 'Global' for Onboarded agents or anyone not-signed-in, or 'Agent, Lead, Manager, Community' for other users.
			angular.bootstrap(document, [app]);
		});
	});
})();