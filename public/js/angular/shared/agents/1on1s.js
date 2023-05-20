(function() {

    /**
     * 1on1s.js is the an agent module for viewing 1on1s
     */

    angular

    .module('1on1s', [])

    .directive('oneOnOnes', function() {
        return {
            templateUrl: '/angular/templates/agents/panels/shared/1on1s.html',
            controller: function($rootScope, $scope, $http, $timeout) {

                $scope.viewOneOnOne = function(id) {

                    angular.forEach($rootScope.api.oneonones.details, function(obj, key) {
                        if(obj.id === id) {
                            $scope.oneonone = JSON.parse(JSON.stringify($rootScope.api.oneonones.details[key]));
                            $('#view-1on1').modal({ observeChanges: true });
                            $('#view-1on1').modal('show');
                            $('#enps_rating').rating({ maxRating: 10 });
                        }
                    });
                }

                $scope.button = {

                    accept: {
                        default: {
                            title: 'Accept',
                            class: 'teal',
                            icon: 'save',
                            disabled: false
                        },
                        accepting: {
                            title: 'Accepting',
                            class: 'grey',
                            icon: 'loading spinner',
                            disabled: true
                        },
                        accepted: {
                            title: 'Accepted',
                            class: 'green',
                            icon: 'thumbs up',
                            disabled: true
                        },
                        no_rating: {
                            title: 'Please provide a rating',
                            class: 'red',
                            icon: 'warning',
                            disabled: true
                        },
                        no_reason: {
                            title: 'Please provide a reason',
                            class: 'red',
                            icon: 'warning',
                            disabled: true
                        }
                    }
                }

                $scope.button.accept.state = $scope.button.accept.default;

                $scope.acceptOneOnOne = function() {

                    this.payload = {
                        action: 'accept',
                        route: 'reviews',
                        id: $scope.oneonone.id
                    };
                    
                    if($scope.oneonone.enps_able) {

                        // returns 0 if nothing selected.
                        // the requested range is 1-10, making 0 a good flag
                        var rating = $('.ui.rating').rating('get rating'),
                        reason = $scope.oneonone.reason;

                        // rating
                        if(rating == 0) {
                            $scope.button.accept.state = $scope.button.accept.no_rating;
                            $timeout(function() {
                                $scope.button.accept.state = $scope.button.accept.default;
                            }, 2000);
                            return false;
                        } else {
                            this.payload.rating = rating;
                        }

                        // reason
                        if(!reason) {
                            $scope.button.accept.state = $scope.button.accept.no_reason;
                            $timeout(function() {
                                $scope.button.accept.state = $scope.button.accept.default;
                            }, 2000);
                            return false;
                        } else {
                            this.payload.reason = reason;
                        }

                    } else {
                        $scope.button.accept.state = $scope.button.accept.accepting;
                    }

                    $http.post('/api/oneonones', this.payload ).then(function(response) {
                        
                        $scope.button.accept.state = $scope.button.accept.accepted;

                        // clone back to mster
                        angular.forEach($rootScope.api.oneonones.details, function(obj, key) {

                            if(obj.id === $scope.oneonone.id) {

                                $timeout(function() {

                                    // mark an accepted time for ux
                                    date = new Date();
                                    $scope.oneonone.accepted = date.getTime();

                                    // push updates to master
                                    $rootScope.api.oneonones.details[key] = JSON.parse(JSON.stringify($scope.oneonone));
                                    $scope.button.accept.state = $scope.button.accept.default;
                                }, 2000);
                            }
                        });
                    });
                }
            },
            restrict: 'E'
        };
    })

    // view a single checkin in a modal
    .directive('viewOneononeModal', function() {

        return {
            templateUrl: '/angular/templates/agents/modals/view-1on1.html',
            controller: function($rootScope, $scope) {},
            restrict: 'E'
        };
    })

})();