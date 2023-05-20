(function() {

	angular

	.module('ReadableTime', [])

 	// translate an amount of seconds into human-readable format (X years, X days, X hrs, X minutes, X seconds, each where applicable)
	.filter('secondsReadable', function() {

		// seconds is the total amount of seconds for converting. form affects the output (short, medium or long)
		return function(seconds, form, spacing) {

			seconds = parseInt(seconds);

			if(seconds > 0) {

				calcs = {};
				calcs.hours = {};
				calcs.minutes = {};
				calcs.seconds = {};

				format = {};
				format.hours = {};
				format.minutes = {};
				format.seconds = {};

				// using 7512 seconds (or 2 hours, 5 minutes, 12 seconds) as an example
				calcs.hours.raw = seconds / 60 / 60;
					// 2.08666666666667
				calcs.hours.round = Math.floor(calcs.hours.raw);
					// 2
				calcs.minutes.raw = ( calcs.hours.raw - calcs.hours.round ) * 60;
					// 5.2000000000002
				calcs.minutes.round = Math.floor(calcs.minutes.raw);
					// 5
				calcs.seconds.raw = ( calcs.minutes.raw - calcs.minutes.round ) * 60;
					// 12.000000000012
				calcs.seconds.round = Math.round(calcs.seconds.raw); 
					// 12

				// define formats
				format.hours.short = ['h', 'h'];
				format.minutes.short = ['m', 'm'];
				format.seconds.short = ['s', 's'];
				format.hours.medium = ['hrs', 'hrs'];
				format.minutes.medium = ['min', 'mins'];
				format.seconds.medium = ['sec', 'secs'];
				format.hours.long = ['hours', 'hours'];
				format.minutes.long = ['minute', 'minutes'];
				format.seconds.long = ['second', 'seconds'];

				// build the output for display with...
				// the specified time unit (years, days, hours, minutes, seconds)
				// the desired form (short, medium, long)
				// the calculations already performed
				buildOutput = function(unit, form, calcs, spacing) {

					var output;

					// are there any units remaining?
					if(calcs[unit].raw != 0) {

						// append actual units into unit output with a space at the end
						output = calcs[unit].round;

						// only build output if the number isn't 0.
						if(output !== 0) {

							// add a space between the number and the label if requested
							if(spacing) {
								output += ' ';
							}

							// if there is more than 1 unit remaining, append the plural form right after the units remaining
							if(calcs[unit].raw > 1) {
								output += format[unit][form][1];
							} else {
								output += format[unit][form][0];
							}
						} else {
							output = '';
						}
					} else {
						output = '';
					}

					// return the built output for this particular unit
					return output;
				}

				// concat the outputs together
				var completeOutput = 
				buildOutput('hours', form, calcs) + ' ' + 
				buildOutput('minutes', form, calcs) + ' ' + 
				buildOutput('seconds', form, calcs);
				
				return completeOutput;
			} else {
				return 'n/a';
			}
	  	};
	})
})()