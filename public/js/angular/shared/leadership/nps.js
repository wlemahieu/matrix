(function() {

    angular

    .module('NPS', [])

    .service('NPS', function($q, $rootScope) {

        this.calculate = function(surveys, field) {

            return $q(function(resolve, reject) {

                nps = {
                    detractors: 0,
                    detractorsPerc: 0,
                    passives: 0,
                    promoters: 0,
                    promotersPerc: 0,
                    total: 0
                };

                angular.forEach(surveys, function(object, key) {

                    if(object[field] > 0 && object[field] <= 6) {
                        object.nps_type = 'Detractor';
                        nps.detractors++;
                    }
                    else if(object[field] > 6 && object[field] < 9) {
                        object.nps_type = 'Passive';
                        nps.passives++;
                    }
                    else if(object[field] >= 9 && object[field] <= 10) {
                        object.nps_type = 'Promoter';
                        nps.promoters++;
                    }
                    nps.total++;
                });

                nps.detractorsPerc = nps.detractors / nps.total * 100;
                nps.promotersPerc = nps.promoters / nps.total * 100;
                nps.score = nps.promotersPerc - nps.detractorsPerc;
                
                resolve(nps);
            });

        }
    })

})()