(function() {

    /*
    Exceptions
    
    This is used for taking and ending attendance exceptions. 
    Agents can also retrieve their past 10 exceptions.
    */

    angular

    .module('ExceptionsController', [])

    .controller('exceptionsController', function ($rootScope, $timeout, $http, APIModifiedTime) {

        this.takeException = function(exception) {

            // Marks the time in which this API call was last used. 
            // Used for preventing a jarring effect in the agent navbar.
            APIModifiedTime.save('navbar');
            $rootScope.agentActionComplete = false;
            
            // mark my current exception now so that navbar knows about it 
            $rootScope.api.agentStatuses.current_exception = exception;

            // mark last channel so when we come back we can re-join that channel
            $rootScope.api.agentStatuses.last_channel = $rootScope.api.agentStatuses.live_channel;

            // mark the channel we are manipulating as offline
            if($rootScope.api.agentStatuses.live_channel == 'all') {
                $rootScope.api.agentStatuses.chats_status = 'offline';
                $rootScope.api.agentStatuses.phones_status = 'offline';
                $rootScope.api.agentStatuses.tickets_status = 'offline';
            } else {
                var channel_status = $rootScope.api.agentStatuses.live_channel + '_status';
                $rootScope.api.agentStatuses[channel_status] = 'offline';
            }

            payload = {
                scope: 'exceptions',
                command: 'logout',
                live_channel: $rootScope.api.agentStatuses.live_channel
            };

            // visually, we should no longer be in a live channel
            $rootScope.api.agentStatuses.live_channel = 'none';

            $http.post( '/api/channelControl', payload ).then(function() {
                $http.post( '/api/exceptionsControl', { command: 'start', mark: exception } ).then(function() {
                    $rootScope.agentActionComplete = true;
                });
            });
        }

        this.endException = function() {

            APIModifiedTime.save('navbar');
            $rootScope.agentActionComplete = false;

            payload = {
                command: 'stop',
                mark: $rootScope.api.agentStatuses.current_exception
            }
            
            $http.post( '/api/exceptionsControl', payload ).then(function(response) {

                // mark the channel we are manipulating as ready
                if($rootScope.api.agentStatuses.last_channel == 'all') {
                    $rootScope.api.agentStatuses.chats_status = 'ready';
                    $rootScope.api.agentStatuses.phones_status = 'ready';
                    $rootScope.api.agentStatuses.tickets_status = 'ready';
                } else {
                    var channel_status = $rootScope.api.agentStatuses.last_channel + '_status';
                    $rootScope.api.agentStatuses[channel_status] = 'ready';
                }

                // my last exception is my current one which is about to cease
                myLastException = $rootScope.api.agentStatuses.current_exception;

                // we are no longer on an exception
                $rootScope.api.agentStatuses.current_exception = 'none';

                // return them to their last channel if they have one
                if($rootScope.api.agentStatuses.last_channel != 'none') {

                    // our live channel is what it was prior to the exception, our last channel.
                    $rootScope.api.agentStatuses.live_channel = $rootScope.api.agentStatuses.last_channel;

                    // agents in all channels log back in to all channels.
                    if($rootScope.api.agentStatuses.last_channel == 'all') {

                        $http.post( '/api/channelControl', {
                            scope: 'exceptions',
                            command: 'login',
                            live_channel: 'phones'
                        }).then(function() {
                            $rootScope.agentActionComplete = true;
                        });

                        $http.post( '/api/channelControl', {
                            scope: 'exceptions',
                            command: 'login',
                            live_channel: 'chats'
                        }).then(function() {
                            // popup livechat window if they are returning to livechat
                            var popupWindow = window.open('https://my.livechatinc.com/');
                            $rootScope.agentActionComplete = true;
                        });

                    } else {

                        $http.post( '/api/channelControl', {
                            scope:'exceptions',
                            command: 'login',
                            live_channel: $rootScope.api.agentStatuses.last_channel
                        }).then(function() {
                            // popup livechat window if they are returning to livechat
                            if($rootScope.api.agentStatuses.last_channel == 'chats') {
                                var popupWindow = window.open('https://my.livechatinc.com/');
                            }
                            $rootScope.agentActionComplete = true;
                        });

                    }
                }

                // otherwise, they did not go on break from their live channel, but after they already logged out of their live channel
                else {
                    $rootScope.api.agentStatuses.live_channel = 'none';
                }
            });
        }
    })

})();