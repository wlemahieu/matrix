(function() {

    angular

    .module('Onboardee', ['Global'])

    .config(function($routeProvider) {

        $routeProvider.when('/onboard', {
            templateUrl : 'angular/templates/onboard.html',
            controller: 'onboardCtrl'
        })
    })

    .controller('onboardCtrl', function($scope, $timeout, $http) {
        
    	// initialize form
		$scope.form = {
            first_name: '',
            last_name: '',
            dept: '',
            type: '',
            status: ''
        };

        // create button states
        $scope.button = {

            default: {
                title: 'Submit Info',
                class: 'teal',
                icon: 'save',
                disabled: false
            },
            loading: {
                title: 'Submitting Info',
                class: 'grey',
                icon: 'loading spinner',
                disabled: true
            },
            loaded: {
                title: 'Info Submitted',
                class: 'green',
                icon: 'thumbs up',
                disabled: true
            },
            empty: {
                title: 'Please fill out the entire form',
                class: 'red',
                icon: 'warning',
                disabled: true
            },
            exists: {
                title: 'You already exist in the Matrix!',
                class: 'red',
                icon: 'warning',
                disabled: true
            }
        };

        $scope.button.state = $scope.button.default;

		// form submission
		$scope.onboard = function() {

			// form is empty, unless proves otherwise
			var emptyForm = false;
			
			// iterate over form elements
			angular.forEach($scope.form, function(val, key) {
				if(val == '') {
					emptyForm = true;
				}
			});

			// if any of the elements is empty, trigger error
			if(emptyForm === true) {

				$scope.button.state = $scope.button.empty;

				$timeout(function() { 
					$scope.button.state = $scope.button.default;
				}, 3000);
			}

			// no empty elements, continue submission
			else {

				$scope.button.state = $scope.button.loading;

				$timeout(function() {

					$http.post('/api/onboardUser', $scope.form)
					.then(function(response) {

						response.data == 0 ? $scope.button.state = $scope.button.exists : $scope.button.state = $scope.button.loaded;

						$timeout(function() { 
							window.location.href = '/?logout';
						}, 3000);
					});
				}, 1500);
			}
    	}
    })

})();