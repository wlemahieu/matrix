(function() {

    /*
    Channel Controller
    
    This module is used by agents for manipulating the channel(s) they're in.
    */

    angular

    .module('ChannelController', [])

    .controller('channelController', function ($rootScope, $scope, $http, $timeout, APIModifiedTime) {

        var that = this;

        this.sendCommand = function(channel, command) {

            // Marks the time in which this API call was last used. 
            // Used for preventing a jarring effect in the agent navbar.
            APIModifiedTime.save('navbar');
            $rootScope.agentActionComplete = false;

            // handle the aesthetics of the navbar right before a channel command is sent
            that.navbarAesthetics(channel, command);

            // create the payload and send the command
            payload = {
                scope: 'agent',
                live_channel: channel,
                command: command
            };

            $http.post( '/api/channelControl', payload ).then(function() {
                $rootScope.agentActionComplete = true;
            });
        }

        // this function handle's the look of the agent navbar while the user is interacting with it
        this.navbarAesthetics = function(channel, command) {

            obj = JSON.parse(JSON.stringify($rootScope.api.agentStatuses));

            // this channel is the last channel
            $rootScope.api.agentStatuses.last_channel = channel;

            // create the status field name
            var statusField = channel.concat('_status');

            switch(command) {

                case 'login':

                    obj.currently_clocked_in = 1;
                    obj[statusField] = 'ready';
                    if(obj.live_channel !== 'none' && channel !== obj.live_channel) {
                        obj.live_channel = 'all';
                    } else {
                        obj.live_channel = channel;
                    }

                    // open windows for chats / ticket queues
                    switch(channel) {
                        case 'chats':
                            var popupWindow = window.open('https://my.livechatinc.com/');
                        break;
                        case 'tickets':
                            var popupWindow = window.open('https://hostops.mediatemple.net/index.php?url=%2Fticketmanager');
                        break;
                    }

                break;

                case 'pause':
                    obj[statusField] = 'paused';
                break;

                case 'unpause':
                    obj[statusField] = 'ready';
                break;

                case 'logout':
                    if(obj.default_channel == 'all') {
                        if(obj.live_channel != 'all') {
                            obj.currently_clocked_in = 0;
                        }
                    }
                    obj[statusField] = 'offline';
                break;

            }
            
            // place object back into agentStatuses API response
            $rootScope.api.agentStatuses = JSON.parse(JSON.stringify(obj));
        }
    });

})();