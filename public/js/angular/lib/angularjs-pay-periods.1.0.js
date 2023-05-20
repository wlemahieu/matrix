(function() {

	angular

	// module name for injecting in the primary module
	.module('Time', [])

    .service('PayPeriods', function($rootScope) {

    	// initializes the entire pay periods module
        this.initialize = function(year, monthIndex, day, interval, totalPeriods) {

        	// i.e. Mon Jan 04 2016 11:02:31 GMT-0800 (PST)
			var today = new Date();

			// by default we'll assume that we are not currently within our definer pay period (the pay period passed in as a starting-point reference)
			var inPeriod = false;

			// create the genesis pay period based on their defining parameters passed through to this.initialize()
			// genesis pay period is crucial so we can start generating pay periods backwards from the current pay period we're in.
			obj = this.generateGenesisPeriod(year, monthIndex, day, interval);

			// keep re-creating the "current pay period" until we're actually in it.
			while(!inPeriod) {

				// next pay period
				obj = this.findNextPeriod(obj, interval);
				// re-check if this period is our current period
				inPeriod = this.checkIfCurrentPeriod(today, obj, interval);
			}

			// return all of the generate periods
			return this.generatePeriods(obj, interval, totalPeriods);
        }

		// check if we are currently within the given period (boolean)
        this.checkIfCurrentPeriod = function(today, period, interval) {

        	var answer;

			if(today > period.finish) {
				answer = false;
			} else {
				answer = true;
			}

			return answer;
        }

    	// generate genesis pay period which is our starting point reference and defines what a "period" is
    	this.generateGenesisPeriod = function(year, monthIndex, day, interval) {

    		obj = {};

			// create the first period's start date (genesis)
			obj.start = new Date(year, monthIndex, day);

			// create the first period's end (finish)
			obj.finish = new Date(obj.start);
			obj.finish = new Date(obj.finish.setDate(obj.finish.getDate()+interval-1));

			return obj;
    	}

    	this.findNextPeriod = function (lastPeriod, interval) {

			obj = {};

			// set the first period's start date
			obj.start = new Date(lastPeriod.finish);
			obj.start = new Date(obj.start.setDate(obj.start.getDate()+1));

			// set the first period's finish date
			obj.finish = new Date(obj.start);
			obj.finish = new Date(obj.finish.setDate(obj.finish.getDate()+interval-1));

			return obj;
		}

    	// iterate through pay periods, formatting and storing in a master array
		this.generatePeriods = function(obj, interval, periods) {

			// create the master periods array
			payPeriods = [];

			// clone genesis and it's finish into start / end for iteration / modification
			start = new Date(obj.start),
			end = new Date(obj.finish);

			// push the current pay period into the array first
			period = {};
			period.start = this.formatDate(new Date(start));
			period.end = this.formatDate(new Date(end));
			payPeriods.push(period);

			// iterate through each period, storing it in the master array
			for(var i = 0; i < periods-1; i++) {

				// our empty period object
				period = {};
				
				start = new Date(start.setDate(start.getDate()-interval));
				period.start = this.formatDate(start);

				// based on the genesis / modified start date object, find the previous pay period's end 
				end = new Date(end.setDate(end.getDate()-interval));
				period.end = this.formatDate(end);

				// store this period object into the periods array
				payPeriods.push(period);
			}

			return payPeriods;
		}

    	// takes a JS date object and formats it as MM/DD/YYYY
    	this.formatDate = function(obj) {

    		// fetch date components
    		var 
			day = obj.getDate(),
			month = obj.getMonth()+1,
			year = obj.getFullYear();

			// zerofill the months
			if(month < 10) {
				month = '0' + month;
			}

			// zerofill the days
			if(day < 10) {
				day = '0' + day;
			}

			// format as MM/DD/YYYY
			formattedDate = month + '/' + day + '/' + year;

			return formattedDate;
    	}
    })

})()