(function() {

	angular

	.module('PercentageFilter', [])

	// converts decimal-format to percentage
 	.filter('percentage', function($filter) {

 		return function (input, decimals) {
 			return $filter('number')(input * 100, decimals) + '%';
 		};
 	})

 })()