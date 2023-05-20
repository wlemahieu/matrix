(function() {

    /*
    The one-on-ones module is responsible for all functions related to one-on-ones related to
    fetching, creating or displaying one-on-ones.
    */

    angular

    .module('OneOnOnes', [])

    .config(function($routeProvider) {

        $routeProvider.when('/1on1s', {
            templateUrl : 'angular/templates/leadership/oneonones.html',
            controller: 'oneOnOnesCtrl'
        })
    })

    .controller('oneOnOnesCtrl', function($http, $scope, $rootScope) {

        // assume we have at least 1 question unless we prove otherwise. purely to prevent jarring effect with red no-question warning panel
        $scope.activeQuestionsCount = 1;

        $scope.apiUrl = '/api/oneonones';
        $scope.oneOnOnesOrderBy = 'agent_username';
        $scope.oneOnOnesReverseSort = true;
        $scope.tab = 'Reviews';

        // fetches all questions along with an active questions count
        $scope.fetchQuestions = function() {

            // build a payload
            this.payload = {
                action: 'read',
                route: 'questions',
            };

            // fetch all one on one questions & total count of active questions
            $http.post( $scope.apiUrl, this.payload ).then(function(response) {
                $scope.questions = response.data;
                $scope.defineActiveQuestionsCount();
            });
        }

        // counts active questions
        $scope.defineActiveQuestionsCount = function() {

            var count = 0
            angular.forEach($scope.questions, function(obj, key) {
                if(obj.active === 1) {
                    count++;
                }
            });
            $scope.activeQuestionsCount = count;
        }

        $scope.fetchQuestions();

        // toggle a specific tab
        $scope.toggleTab = function(tab) {

            $scope.tab = tab;
        }
    })

    .controller('reviewsCtrl', function($q, $http, $filter, $rootScope, $scope, $timeout) {

        // filter for active users in my department
        $scope.usersFilter = {
            active: 1,
            type: 'Agent',
            dept: $rootScope.userinfo.dept
        };

        // as a lead, only show my team
        if($rootScope.userinfo.type == 'Supervisor')  {

            $scope.usersFilter.team = $rootScope.userinfo.team;
        }

        // loader used for when selecting agents
        $scope.loading = false;
        $scope.agentSelectLoading = false;

        // store buttons for different use-cases (saving, finishing, and removing 1on1s each have different states required)
        $scope.button = {

            save: {
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
                }
            },

            present: {
                default: {
                    title: 'Present',
                    class: 'green',
                    icon: 'checkmark',
                    disabled: false
                },
                presenting: {
                    title: 'Presenting',
                    class: 'grey',
                    icon: 'loading spinner',
                    disabled: true
                },
                presented: {
                    title: 'Presented',
                    class: 'orange',
                    icon: 'thumbs up',
                    disabled: true
                }
            },

            remove: {
                default: {
                    title: 'Remove',
                    class: 'red',
                    icon: 'close',
                    disabled: false
                },
                removing: {
                    title: 'Removing',
                    class: 'grey',
                    icon: 'loading spinner',
                    disabled: true
                },
                removed: {
                    title: 'Removed',
                    class: 'black',
                    icon: 'thumbs up',
                    disabled: true
                }
            }
        };

        $scope.button.save.state = $scope.button.save.default;
        $scope.button.present.state = $scope.button.present.default;
        $scope.button.remove.state = $scope.button.remove.default;

        // get today's date & format it
        $scope.regenerateDates = function() {
            
            date = new Date();

            $scope.today = $filter('date')(date, 'yyyy-MM-dd');
            $scope.today_datetime = $filter('date')(date, 'yyyy-MM-dd H:mm:ss');
            $scope.today_ms = date.getTime();
        }
        
        $scope.regenerateDates();

        // when an agent is slected, fetch agent reviews
        $scope.selectAgent = function() {
            $scope.agentSelectLoading = true;
            $scope.fetchAgentReviews().then(function() {
                $scope.agentSelectLoading = false;
            });
        }
        
        // fetch all reviews & respective answers for an agent for the selected date range
        // this simply copies any reviews that belong to this user which exist in $rootScope.api.oneonones.
        $scope.fetchAgentReviews = function() {

            return $q(function(resolve, reject) {

                // empty our selected agent's 1on1s array and selected 1on1 object
                $scope.agentOneOnOnes = [];
                // 'fetching' is a flag needed for the save, present, remove buttons
                $scope.active = { empty: true };

                $scope.defineActiveLength();

                $scope.cloneFromMaster().then(function(agent1on1s) {
                    $scope.checkAgentCompleteToday(agent1on1s).then(function(todayComplete) {
                        $scope.agentOneOnOnes = JSON.parse(JSON.stringify(agent1on1s));
                        $scope.todayComplete = todayComplete;
                        resolve();
                    });
                });
            });
        }

        // copies all of a particular agent's reviews from our master ($rootScope.api.oneonones) to local $scope.agentOneOnOnes
        $scope.cloneFromMaster = function() {

            //console.log('Cloning one on ones from master');

            return $q(function(resolve, reject) {
                
                agent1on1s = [];

                // iterate through master one on ones list and capture only ones for the selected user
                angular.forEach($rootScope.api.oneonones.details, function(obj, key) {

                    // go through all the one on ones and grab only this user's.
                    // push this users one on ones into $scope.agentOneOnOnes
                    if(obj.agent_username === $scope.selectedAgent) {
                        newObj = JSON.parse(JSON.stringify(obj));
                        agent1on1s.push(newObj);
                    }
                });

                // after iterating through all one on ones and pushing them into $scope.agentOneOnOnes, we can resolve this promise
                resolve(agent1on1s);

            });

            return promise;
        }

        // check if an agent has had a review completed today
        $scope.checkAgentCompleteToday = function(agent1on1s) {

            //console.log('Checking if agent has a one on one completed today');

            return $q(function(resolve, reject) {

                // check if this user has a completed one on one today or not, assume they have not had one today at first.
                var todayComplete = false;

                // iterate through each one on one for this user to determine if they have had a one on one today
                angular.forEach(agent1on1s, function(obj, key) {

                    // if the creation date is today and it's active, they've completed one today already so we can mark today as completed
                    if($filter('date')(obj.created, 'yyyy-MM-dd') === $scope.today && obj.active === 1) {
                        todayComplete = true;
                    }
                });

                // once we have gone through all of this agent's one on ones and determined if they have had one today, resole this promise
                resolve(todayComplete);
            });

            return promise;
        }

        // open a single review by copying it's object into $scope.active
        $scope.open = function(id) {

            //console.log('Opening single one on one');

            angular.forEach($scope.agentOneOnOnes, function(obj, key) {
                if(id === obj.id) {
                    // create a clone of 
                    $scope.active = JSON.parse(JSON.stringify(obj));
                    $scope.active.empty = false;
                    $scope.defineActiveLength();
                }
            });
        }

        // create a new review
        $scope.new = function() {

            //console.log('Creating empty one on one form');

            // create a new form, overwriting whatever is in active
            // there are 2 keys we are defining here. we reference this directly in our view for the Remove button
            // if we add any other properties here, we should reflect that in our activeLength count.
            $scope.active = {
                empty: false,
                finished: 0,
                answers: []
            };

            // iterate through the our active questions stored in $scope.questions, then store each question, it's id & position in the answer object
            angular.forEach($scope.questions, function(obj, key) {

                // only active questions are used for new one on ones
                if(obj.active === 1) {

                    this.payload = {
                        question_id: obj.question_id,
                        question: obj.question,
                        position: obj.position
                    };

                    $scope.active.answers.push(this.payload);
                }
            });

            // sets length to 0 which affects view (show or hide remove button)
            $scope.defineActiveLength();
        }

        // remove this review
        $scope.remove = function() {

            // disables save/finish buttons
            $scope.loading = true;
            $scope.button.remove.state = $scope.button.remove.removing;

            // create a payload to finish this one on one review
            this.payload = {
                route: 'reviews',
                action: 'remove',
                id: $scope.active.id
            };

            // finish the one on one...
            $http.post($scope.apiUrl, this.payload).then(function() {

                $scope.button.remove.state = $scope.button.remove.removed;

                $timeout(function() {

                    // find the 1on1 we're removing, and remove it from view immediately
                    angular.forEach($scope.agentOneOnOnes, function(obj, key) {
                        if(obj.id === $scope.active.id) {
                            $scope.agentOneOnOnes[key].active = 0;
                        }
                    });

                    // if we just removed today's one on one, let's mark today as incomplete to bring the 'new' button out again
                    if($filter('date')($scope.active.created, 'yyyy-MM-dd') === $scope.today) {
                        $scope.todayComplete = false;
                    } else {
                        $scope.todayComplete = true;
                    }

                    // deactivate this review so we aren't looking at it anymore by clearing it
                    $scope.active = { empty: true };

                    $scope.defineActiveLength();
                    
                    $scope.button.remove.state = $scope.button.remove.default;
                    // renables save/finish buttons
                    $scope.loading = false;

                }, 2000);
            });
        }

        // present this review
        $scope.present = function() {

            $scope.button.present.state = $scope.button.present.presenting;

            // if all answers have been completed, proceed with allowing them to finish this one on one
            if($scope.checkIfAnswersComplete()) {

                // save the one on one, then continue finishing it here
                $scope.saveProcess().then(function(id) {

                    $scope.synchronizeView(id, 'finishing').then(function() {

                        // create a payload to finish this one on one review
                        this.payload = {
                            route: 'reviews',
                            action: 'finish',
                            agent_username: $scope.selectedAgent,
                            id: $scope.active.id
                        };

                        // finish the one on one...
                        $http.post( $scope.apiUrl, this.payload ).then(function() {

                            $scope.button.present.state = $scope.button.present.presented;
                            $timeout(function() {
                                // disables save/finish buttons
                                $scope.button.present.state = $scope.button.present.default;
                            }, 2000);
                           
                        });
                    });
                });
            }
        }

        // saves a new or existing review which returns an id
        // this id is fed to saveAnswers() and synchronizeView()
        $scope.save = function() {

            return $q(function(resolve, reject) {

                $scope.loading = true;
                $scope.button.save.state = $scope.button.save.saving;

                $scope.saveProcess().then(function(id) {

                    $scope.synchronizeView(id).then(function() {

                        $scope.defineActiveLength();

                        $scope.button.save.state = $scope.button.save.saved;

                        $timeout(function() {

                            $scope.loading = false;
                            $scope.button.save.state = $scope.button.save.default;
                            resolve();

                        }, 2000);
                    });
                });
            });

            return promise;
        }

        // save one on one, then save answers, then resolve promise
        $scope.saveProcess = function() {

            return $q(function(resolve, reject) {

                this.payload = {
                    route: 'reviews',
                    action: 'save',
                    agent_username: $scope.selectedAgent,
                    id: $scope.active.id
                };

                // upsert the 1on1 itself (always returns an id of the updated or inserted row)
                $http.post( $scope.apiUrl, this.payload ).then(function(response) {

                    var id = response.data.one_on_one_id;

                    // build a payload for saving answers for this one on one
                    this.payload = {
                        route: 'answers',
                        action: 'save',
                        one_on_one_id: id,
                        agent_username: $scope.selectedAgent,
                        answers: $scope.active.answers
                    };

                    // save this 1on1s answers
                    // resolve the 1on1's id from the earlier $http.post() so we can use it in $scope.synchronizeView()
                    $http.post( $scope.apiUrl, this.payload ).then(function() {
                        resolve(id);
                    });
                });
            });

            return promise;
        }

        // synchronize the view after we save the review's answers
        $scope.synchronizeView = function(id, finishing) {

            return $q(function(resolve, reject) {

                $scope.regenerateDates();

                // New Review
                if(!$scope.active.id) {

                    // since this is a new review, the object does not exist in the view.
                    // we'll create it using what the user entered in the form ($scope.active)
                    this.payload = {
                        empty: false,
                        active: 1,
                        finished: 0,
                        id: id,
                        answers: $scope.active.answers,
                        agent_username: $scope.selectedAgent,
                        leadership_username: $rootScope.userinfo.username,
                        last_saved: $scope.today_ms,
                        created: $scope.today_ms,
                    };

                    if(finishing) {
                        this.payload.finished = $scope.today_ms;
                    }

                    // overwrite active with payload
                    $scope.active = this.payload;

                    // push the new review to this agent's scope.
                    $scope.agentOneOnOnes.push(this.payload);

                    // also push this to $rootScope in case they move back and forth between this user before this api call is auto-refreshed
                    if(!$rootScope.api.oneonones.details) { 
                        $rootScope.api.oneonones.details = [];
                    }
                    
                    $rootScope.api.oneonones.details.push(this.payload);

                    // if this is a new review, then we know today is now complete for this agent.
                    $scope.todayComplete = true;
                } 

                // Existing Review
                else {
                    
                    // store results for this particular agent's list of 1on1s
                    angular.forEach($scope.agentOneOnOnes, function(obj, key) {
                        if(obj.id === $scope.active.id) {

                            // mark the finished timestamp if they are finishing this 1on1
                            if(finishing) {
                                $scope.active.finished = $scope.today_ms;
                            }
                            // overwrite this agent's 1on1s list with the updated 1on1
                            $scope.agentOneOnOnes[key] = JSON.parse(JSON.stringify($scope.active));
                        }
                    });

                    // store results in master 1on1s list
                    angular.forEach($rootScope.api.oneonones.details, function(obj, key) {
                        if(obj.id === $scope.active.id) {
                            $rootScope.api.oneonones.details[key] = JSON.parse(JSON.stringify($scope.active));
                        }
                    });
                }

                resolve();
            });

            return promise;
        }

        $scope.updateActiveAnsweredCount = function() {

            $scope.checkIfAnswersComplete().then(function(response) {
                if(response) {
                    $scope.answersComplete = true;
                } else {
                    $scope.answersComplete = false;
                }
            });
        }

        // check if all questions have been answered for a particular review
        $scope.checkIfAnswersComplete = function() {

            //console.log('Checking if all answers are completed');

            return $q(function(resolve, reject) {

                // we assume the answer is finished unless we prove otherwise below. 
                // any empty answer will trigger an unfinished state
                var finished = true;

                angular.forEach($scope.active.answers, function(obj, key) {
                    if(obj.answer === undefined || obj.answer === '' || obj.answer === null) {
                        finished = false;
                    }
                });

                // it's important to pass true or false back with the promise because 
                // it let's us know if all answers are complete
                resolve(finished);
            });

            return promise;
        }

        // stores the active length in $scope for the active one on one.
        $scope.defineActiveLength = function() {

            $scope.activeLength = Object.keys($scope.active).length;
        }
    })

    .controller('questionsCtrl', function($http, $rootScope, $scope, $timeout) {
        
        $scope.showform = false;

        $scope.newForm = function() {
            $scope.question = {
                position: $scope.activeQuestionsCount + 1,
                question: ''
            };
        }

        $scope.newForm();

        // define different button states

        $scope.button = {

            question: {
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
                error: {
                    title: 'Error',
                    class: 'red',
                    icon: 'thumbs down',
                    disabled: true
                },
                dupe: {
                    title: 'Duplicate',
                    class: 'red',
                    icon: 'thumbs down',
                    disabled: true
                }
            }
        }

        $scope.button.question.state = $scope.button.question.default;

        // viewing active questions by default
        $scope.status = 'Active';

        // toggle new question form
        $scope.formToggle = function() {

            $scope.newForm();

            if(!$scope.form) {
                // new question form obj
                $scope.form = {};
                $scope.form.show = true;
            }

            if($scope.form.show) {
                $scope.form.show = false;
                $scope.form.title = 'New Question';
                $scope.form.icon = 'plus';
                $scope.form.color = 'blue';
            } else {
                $scope.form.show = true;
                $scope.form.title = 'Close Form';
                $scope.form.icon = 'minus';
                $scope.form.color = 'yellow';
            }
        }

        $scope.formToggle();

        // no errors by default until proven otherwise
        $scope.errors = false;

        // submit the question
        $scope.submit = function() {

            // check if this position is already active. if so, don't let them submit without changing the position
            angular.forEach($scope.questions, function(obj, key) {
                if(obj.active === 1) {
                    if($scope.question.position === obj.position) {
                        $scope.errors = true;
                    }
                }
            });

            // position already activated (dupe)
            if($scope.errors) {
                $scope.button.question.state = $scope.button.question.dupe;
                $timeout(function() {
                    $scope.errors = false;
                    $scope.button.question.state = $scope.button.question.default;
                }, 1500);
            }

            // no errors, no empty fields.
            else if(!$scope.errors && $scope.question.question != '' && $scope.question.position != '') {

                $scope.button.question.state = $scope.button.question.saving;

                // create a payload to save the one on one itself (not the answers yet)
                this.payload = {
                    route: 'questions',
                    action: 'save',
                    payload: $scope.question
                };

                $http.post($scope.apiUrl, this.payload).then(function(response) {

                    $scope.button.question.state = $scope.button.question.saved;

                    $timeout(function() {

                        var key = parseInt(response.data, 10);
                        var index = key-1;

                        // if the insert failed...
                        if(key === 0) {
                            $scope.button.question.state = $scope.button.question.error;
                            $timeout(function() {
                                $scope.button.question.state = $scope.button.question.default;
                            }, 1500);
                        } else {

                            // increase the active questions count
                            $scope.activeQuestionsCount++;
                            // enter our new question into the array of questions to update the list live
                            $scope.questions[index] = {
                                question_id: key,
                                question: $scope.question.question,
                                position: parseInt($scope.question.position, 10),
                                active: 1
                            };

                            $scope.button.question.state = $scope.button.question.default;
                            $scope.newForm();
                        }

                    }, 1500);
                });
            }
        }

        // deactivate a question based on the question_id
        // action == 'deactivate' or 'activate'
        $scope.toggleQuestion = function(action, question_id) {

            // iterate through questions and and remove the question from view
            angular.forEach($scope.questions, function(obj, key) {
                if(obj.question_id === question_id) {
                    if(action === 'deactivate') {
                        $scope.questions[key].active = 0;
                    } else {
                        $scope.questions[key].active = 1;
                    }
                }
            });

            // create a payload to save the one on one itself (not the answers yet)
            this.payload = {
                route: 'questions',
                action: action,
                question_id: question_id
            };

            $http.post($scope.apiUrl, this.payload);

            $scope.defineActiveQuestionsCount();
        }
    })
    
})();