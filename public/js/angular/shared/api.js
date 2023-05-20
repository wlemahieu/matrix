(function() {

    angular

    // Matrix API Module
    .module('MAPI', [])

    // store the last modified time for a specific API call
    .service('APIModifiedTime', function($rootScope) {

        // this is used for the agent navbar to prevent a jarring effect that occurs when 
        // a user-interaction occurs while an API call is happening which then overwrites the
        // view with a delayed data-set for the agent navbar
        this.save = function(usecase) {

            if(!$rootScope.modified) {
                $rootScope.modified = {};
            }
            $rootScope.modified[usecase] = Date.now();       
        }
    })

    // Matrix API Service is a wrapper for $http which lets us...
    // Store API responses in $rootscope for use in views,
    // Dictate which page loaders are active, either the 'initial' loader or the 'inline' loaders in views
    .service('API', function($q, $http, $rootScope, $interval, APIModifiedTime) {
 
        // based on how this particular API call is being used, 
        // whether it's the 'initial' loador an intervaled 'inline' load), 
        // turn our view's loading icons on or off.
        this.toggleLoaders = function(usage, target, boolean) {
            $rootScope.loaders[usage][target] = boolean;
        }

        // hit an API call using the target of the call and the payload
        this.call = function(target, payload) {

            var that = this,
            usage;

            // start a promise
            var defer = $q.defer();

            // no apiLoading object? create one.
            if(!$rootScope.apiLoading) {
                $rootScope.apiLoading = {};
            }

            // mark this 'api' call as being loaded
            $rootScope.apiLoading[target] = true;

            // no apiCounter object? create one.
            if(!$rootScope.apiCounter) {
                $rootScope.apiCounter = {};
            }

            // no apiCounter object for this API call? start it at 0, or else increase it's existing count...
            if(!$rootScope.apiCounter[target]) {
                $rootScope.apiCounter[target] = 1;
            } else {
                $rootScope.apiCounter[target]++;
            }

            // is it the first time this API call is being hit?
            // toggle the 'initial' loaders. otherwise, toggle the 'inline' loaders.
            if($rootScope.apiCounter[target] === 1) {
                usage = 'initial';
            } else {
                usage = 'inline';
            }

            // Marks the time in which this API call was last used. 
            // Used for preventing a jarring effect in the agent navbar.
            APIModifiedTime.save(target);

            // turn on loaders
            that.toggleLoaders(usage, target, true);

            // if there is no payload object sent with our API call, create one.
            if(!payload) { 
                payload = {}; 
            }

            // by default, save data in rootScope. or choose not to by passing false
            if(payload.saveData === undefined) {
                payload.saveData = true;
            }

            // send api payload and obtain a response
            $http.post('/api/'+target, payload).then(function(response) {

                // mark this API call as no longer being loaded...
                $rootScope.apiLoading[target] = false;

                // if we are saving the response to $rootScope...
                if(payload.saveData === true) {

                    // if there is an action defined in the payload...
                    if(payload.action) {

                        // if we are performing a READ-only...
                        if(payload.action == 'read') {

                            // for api end-points with multiple 'read' objects, like the oneonones API (controller/api/oneonones.php)
                            if(payload.object) {

                                // if there is no API call object for this particular call, create one...
                                if(!$rootScope.api[target]) {
                                    $rootScope.api[target] = {};
                                }

                                // store the response for this particular READ into an array-array
                                $rootScope.api[target][payload.object] = response.data;
                            } 
                            // for api end-points with single 'read'
                            else {
                                $rootScope.api[target] = response.data;
                            }
                        }
                    } 
                    // no action is defined in the payload...
                    else {
                        // simply store the response in $rootScope for this API call.
                        $rootScope.api[target] = response.data;
                    }

                    // turn off loaders
                    that.toggleLoaders(usage, target, false);
                }

                // if an interval in seconds is passed through with the payload, put this api call on an interval.
                if(payload.interval) {
                    that.initiateInterval(target, payload.interval);
                }

                // why not return our results too in case we want to use it in a promise?
                defer.resolve(response);

            });

            return defer.promise;
        }

        // we should repeat this API call on the specified interval.
        this.initiateInterval = function(target, seconds) {

            // so we can reference this.call within our $interval function.
            var that = this;

            // convert the seconds to milliseconds for $interval
            var milliseconds = seconds * 1000;

            // kick off the interval for this api call
            $interval(function() {

                // as long as there isn't already a API call going, start a new one...
                if(!$rootScope.apiLoading[target]) {

                    // hit the api
                    that.call(target).then(function() {
                        // mark this api as no longer loading...
                        $rootScope.apiLoading[target] = false;
                    });
                }
                
            }, milliseconds);        
        }
    })

})();