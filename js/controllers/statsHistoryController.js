/**
 * Created by mbuscher on 01.02.2017.
 */


angular.module('spzdb'	// So heißt die App
			   )


.controller('statsHistoryController', ['$scope', '$routeParams', 'memberService',
			function(me, _GET, memberService)
			{
				debugSpzDb('statsHistoryController Initialize');

				me.filters                    = {};
				me.filters.search             = '';
				me.filters.state              = 'alle';
				me.filters.gender_w           = 1;
				me.filters.gender_m           = 1;
				me.filters.age_adult          = 1;
				me.filters.age_child          = 1;
				me.filters.instrument_floete  = 1;
				me.filters.instrument_trommel = 1;

				me.current_year = new Date().getFullYear();

				me.year_from  = me.current_year - 10;
				me.year_until = me.current_year;

				me.years   = null;
				me.series  = [];
				me.labels  = [];
				me.data    = [];
				me.colors  = ['#0000ff'];
				me.options = {
					scales: {
						yAxes: [
							{
								id:       'y-axis-1',
								type:     'linear',
								display:  true,
								position: 'left'
							}
						]
					}
				};
				me.table_data = [];
				me.onClick = function(points, evt)
				{
					console.log(points, evt);
				};

				me.load = function()
				{
					debugSpzDb('statsHistoryController->load() Call');

					if(me.years === null)
					{
						$("#mainview").hide();
						$("#loader").show();

						me.years = [];

						memberService.load(function()
										   {
											   $("#mainview").show();
											   $("#loader").hide();

											   var members = memberService.getList(me.filters, '');

											   for(var i = 0; i < members.length; i++)
											   {
												   var member = members[i];

												   for(var state_index = 0; state_index < member.STATES.length; state_index++)
												   {
													   var state = member.STATES[state_index];

													   if(state.STATE == 'aktiv')
													   {
														   var start_year = parseInt(state.START_DATE.substr(0, 4), 10);

														   if(state.END_DATE !== null)
														   {
															   var end_year = parseInt(state.END_DATE.substr(0, 4), 10);
														   }
														   else
														   {
															   var end_year = me.current_year;
														   }

														   for(var y = start_year; y <= end_year; y++)
														   {
															   if(y < me.year_from || y > me.year_until)
															   {
																   continue;
															   }

															   if(y in me.years)
															   {
																   me.years[String(y)]++;
															   }
															   else
															   {
																   me.years[String(y)] = 1;
															   }
														   }
													   }
												   }
											   }

											   me.writeYearsToChart();
										   }
						);
					}
					else
					{
						me.writeYearsToChart();
					}
				};

				me.writeYearsToChart = function()
				{
					me.series  = ['Mitglieder'];
					me.labels  = [];
					me.data    = [[]];

					var change     = null;
					var difference = '';
					var lastyear   = 0;

					for(var key in me.years)
					{
						me.labels.push(key);
						me.data[0].push(me.years[key]);

						if(change === null)
						{
							change     = '';
							difference = '';
						}
						else if(me.years[key] >= lastyear)
						{
							change     = 'changeplus';
							difference = '+ ' + (me.years[key] - lastyear);
						}
						else
						{
							change     = 'changeminus';
							difference = '- ' + ((me.years[key] - lastyear)*-1);
						}

						me.table_data.push({
											   year:       key,
											   members:    me.years[key],
											   change:     change,
											   difference: difference
										   });

						lastyear = me.years[key];
					}
				};

				me.load();

			}]);