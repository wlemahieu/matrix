(function() {

  /*
  The checkins module is used for fetching and displaying checkins.
  It's shared between Agents and Leadership
  */

  angular

  .module('ManipulateCheckins', [])

  .run(function($rootScope) {
    $rootScope.loaders.initial.checkins = true;
  })

  .config(function($routeProvider) {
    $routeProvider.when('/checkins', {
      templateUrl : '/angular/templates/leadership/checkins/index.html',
      controller : 'checkinsCtrl'
    });
  })

  .controller('checkinsCtrl', function($http, $scope, $rootScope) {
    $scope.tab = 'checkins';
  })

  // create or edit a checkin
  .directive('manipulateCheckin', function() {
    return {
      templateUrl: '/angular/templates/leadership/checkins/manipulate.html',
      controller: function($q, $rootScope, $scope, $http, $timeout) {

        $scope.contact_type = 'Call';
        $scope.button = {};

        // store buttons for different use-cases
        var button = {

        	default: {
            title: 'Save',
            class: 'blue',
            icon: 'save',
            disabled: false
        	},

        	saving: {
            title: 'Saving',
            class: 'grey',
            icon: 'loading spinner',
            disabled: true
        	},
        	
        	saved: {
            title: 'Saved',
            class: 'green',
            icon: 'thumbs up',
            disabled: true
        	},

        	agentless: {
            title: 'Select user!',
            class: 'red',
            icon: 'warning',
            disabled: true
        	},

        	dateless: {
        		title: 'Select date!',
            class: 'red',
            icon: 'warning',
            disabled: true
        	}
        };
        
        $scope.button.state = button.default;

        // when an agent is selected, store the user in the form.
        $scope.selectAgent = function() {
          $scope.form.agent_username = $scope.selectedAgent;
        }

        $scope.selectContactType = function() {
          $scope.form.contact_type = $scope.contactType;
        }

        // takes all answers from the DB and pairs them with their respective questions (used for EDITING only)
        $scope.pairQuestionAnswers = function() {

          // make clones of the templates so we can manipulate without affecting view or templates original until we're done.
          qaProperties1Clone = JSON.parse(JSON.stringify($scope.qaProperties1Template));
          qaProperties2Clone = JSON.parse(JSON.stringify($scope.qaProperties2Template));

          // iterate through form fields
          angular.forEach($scope.form, function(val, field) {

            // iterate through the 1st set of questions
            angular.forEach(qaProperties1Clone, function(obj, nullKey) {
              if(field === obj.dbField) {
                obj.answer = val;
              }
            });

            // iterate through the 2nd set of questions
            angular.forEach(qaProperties2Clone, function(obj, nullKey) {
              if(field === obj.dbField) {
                obj.answer = val;
              }
            });
          });
          
          $scope.qaProperties1 = JSON.parse(JSON.stringify(qaProperties1Clone));
          $scope.qaProperties2 = JSON.parse(JSON.stringify(qaProperties2Clone));
        }

        // opens or closes the modal for a new/edit checkin
        $scope.toggleManipulate = function(route, id) {

          // editing
          if(route === 'edit') {

            // find the checkin in question for editing
            angular.forEach($rootScope.api.checkins.line_items, function(obj, key) {

              if(obj.id === id) {

                // convert the unix timestamp to JS Date object (datepicker panics otherwise)
                obj.interaction_date = new Date(obj.interaction_date);

                // store edited checkin in master
                $scope.form = obj;

                // pair the answers to their respective questions based on the db field name
                $scope.pairQuestionAnswers();

                // switch to 'edit' tab
                $scope.toggleTab('edit');
              }
            });
          }
          
          // new
          else if(route == 'new') {
            $scope.resetForm();
          }
        }

        // present the checkin to the agent
        $scope.modify = function(route, id) {
            
          // find the checkin in master and mark it as presented before sending the call (UX)
          angular.forEach($rootScope.api.checkins.line_items, function(obj,key) {
              if(obj.id === id) {
                  $rootScope.api.checkins.line_items[key].presented = 1;
              }
          });

          // present the checkin
          $http.post( '/api/checkins', { action: 'modify', route: route, id: id } ).then(function(response) {
              $scope.reCountStatuses();
          });
        }

        // makes a clone of the question templates for use with pair answers to their respective questions in edit/new situations
        $scope.cloneQuestionTemplates = function() {

          // clone the question templates for storing new answers
          $scope.qaProperties1 = JSON.parse(JSON.stringify($scope.qaProperties1Template));
          $scope.qaProperties2 = JSON.parse(JSON.stringify($scope.qaProperties2Template));
        }

        $scope.resetForm = function() {

          console.log('Resetting Form');
            
          // must not change or be different than the leadership/selectors/agents.js file. to prevent dropdown white space
          $scope.selectedAgent = 'Select a user';

          $scope.cloneQuestionTemplates();

          // define a few things which the form doesn't define which we'll want to define :P
          // this is quick and dirty and since checkins is being revamped to by totally dynamic by allowing any questions to be added/removed, this being nonscalable is fine. just need to fix a bug for incomplete counter.
					$scope.form = {
            accepted: 0,
            account_number: null,
          	agent_username: $scope.selectedAgent,
            comment: null,
            completed: 0,
          	contact_type: 'Call', // defining a default so the dropdown doesn't poop out a whitespace undefined row :|
            correct_auth: null,
            correctly_closed: null,
            creation_date: null,
            educate_customer: null,
            id: null,
            identified_issue: null,
            interaction_date: null,
            lead_username: null,
            maintain_control: null,
            missed_procedures: null,
            presented: 0,
            proper_greeting: null,
            proper_notes: null,
            proper_solutions: null,
            support_number: null,
            tone_attitude_assurance: null,
            unlisted_info_given: null,
            unnecessary_sr: null
          };
        }

        // save or edit checkin
        $scope.submit = function() {

          $scope.button.state = button.saving;

          // self needs referencing because 'this' changes when used within a 'this' (see lines 313-318)
          var self = this;

          // new, or edit?
          if($scope.form.id) {
            $scope.type = 'Edit';
          } else {
            $scope.type = 'New';
          }

          this.checkRequiredInputs = function() {

          	return $q(function(resolve, reject) {

            	if(!$scope.form.interaction_date || $scope.form.interaction_date === '') {
            		reject('dateless');
            	} else if($scope.form.agent_username === 'Select a user' || $scope.form.agent_username === '') {
            		reject('agentless');
            	} else {
            		resolve();
            	}
            });

            return promise;
          }

          this.addAnswersToPayload = function() {

            return $q(function(resolve, reject) {
              angular.forEach($scope.qaProperties1, function(obj, key) {
                  $scope.form[obj.dbField] = obj.answer;
              });
              angular.forEach($scope.qaProperties2, function(obj, key) {
                  $scope.form[obj.dbField] = obj.answer;
              });
              resolve();
            });

            return promise;
          };

          this.checkForUnansweredFields = function() {
              
            return $q(function(resolve, reject) {

              // reset the # of unanswered fields to 0 because we're about to re-count it.
              var unansweredFields = 0;

              // go through all the required fields in the form to see which ones are unanswered
              angular.forEach($scope.form, function(value, field) {

                // find the index of a value in an array. -1 means it was not found.
                // if the value is not found in the $scope.ignoreFields array, continue to check if it's null or not
                if($scope.ignoreFields.indexOf(field) === -1){
                  // exclude false because that's an actual answer being provided
                  // an unanaswered field is when the value is null, an empty string, or undefined
                  if(value === null || value === '' || value === undefined ) {
                    unansweredFields++;
                  }
                }
              }); 

              // once we get a total, return it (resolve it)
              resolve(unansweredFields);
            });

            return promise;
          };
                
          this.markCompletedOrNot = function(unansweredFields) {

            return $q(function(resolve, reject) {

              if(unansweredFields === 0) {
                $scope.form.completed = 1;
              } else {
                $scope.form.completed = 0;
              }

              resolve();
            });

            return promise;
          }

          this.pushCheckinToMaster = function(form) {

            return $q(function(resolve, reject) {

              // we assume we have not found the checkin until proven otherwise below
              var found = false;

              // existing checkin found. merge it's edits back to master
              angular.forEach($rootScope.api.checkins.line_items, function(obj, key) {
                if(obj.id === form.id) {
                  found = true;
                  $rootScope.api.checkins.line_items[key] = form;
                }
              });

              // no checkin found. push new checkin into master
              if(!found) {
                $rootScope.api.checkins.line_items.push(form);
              }

              resolve();
            });

            return promise;
          };

          this.doAllTheThings = function() {

            // as long as an agent is selected...
            self.checkRequiredInputs().then(function() {

              self.addAnswersToPayload().then(function() {

                self.checkForUnansweredFields().then(function(unansweredFields) {

                  self.markCompletedOrNot(unansweredFields).then(function() {

                    $http.post( '/api/checkins', { action: 'save', payload: $scope.form }).then(function(response) {

                      $scope.button.state = button.saved;

                      // clone form data at this point because we don't want to affect $scope
                      // we just want to pass the form data into _pushCheckinToMaster() then change tabs.
                      // $scope.form is just our temporary placeholder for this view/edit/new checkin
                      form = JSON.parse(JSON.stringify($scope.form));

                      // if this was not an update, but an insert, grab the id.
                      if($scope.type === 'New') {
                        form.id = response.data;
                      }

                      self.pushCheckinToMaster(form).then(function() {

                        $scope.reCountStatuses().then(function() {

                          // after 3s, return button to default and switch tab to checkins
                          $timeout(function() {
                            $scope.tab = 'checkins';
                            $scope.button.state = button.default;
                            // clear form after view changes
                            $scope.resetForm();
                          }, 3000);
                        });
                      });
                    });
                  });
                });
              });
						},

            // if checkRequiredInputs() fails, then let's trigger that button state / message, and then bring the button back to default.
            function(err) {

              // update button state to whatever error was returned
              $scope.button.state = button[err];

              $timeout(function() {
                $scope.button.state = button.default;
              }, 3000);
            });
          };

          this.doAllTheThings();
        }
      },
      restrict: 'E'
    };
  })

})();