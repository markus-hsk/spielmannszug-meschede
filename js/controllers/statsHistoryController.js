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
                me.filters.instrument		  = 'all';

				me.current_year = new Date().getFullYear();

				me.year_from  = me.current_year - 10;
				me.year_until = me.current_year;

				me.years   = null;
				me.series  = [];
				me.labels  = [];
				me.data    = [];
				me.colors  = ['#0000ff', '#00ff00', '#ff0000'];
				me.options = {
					scales: {
						yAxes: [
							{
								id:       'y-axis-1',
								type:     'linear',
								display:  true,
			                    position: 'left',
			                    ticks: {
			                        suggestedMin: 0//,     // minimum will be 0, unless there is a lower value.
			                        //beginAtZero:  true   // minimum value will be 0.
			                    }
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
						me.adds = [];
						me.leaves = [];

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
														   
														   if(start_year in me.adds)
														   {
															   me.adds[String(start_year)]++;
														   }
														   else
														   {
															   me.adds[String(start_year)] = 1;
														   }

														   if(state.END_DATE !== null)
														   {
															   var end_year = parseInt(state.END_DATE.substr(0, 4), 10) - 1; // -1 weil das jahr des Austritts nicht mehr gewertet werden darf
															   
															   if(end_year+1 in me.leaves)
															   {
																   me.leaves[String(end_year+1)]++;
															   }
															   else
															   {
																   me.leaves[String(end_year+1)] = 1;
															   }
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
					me.series  = ['Mitglieder', 'Aufnahmen', 'Austritte'];
					me.labels  = [];
					me.data    = [[],[],[]];

					var change     = null;
					var difference = '';
					var lastyear   = 0;

					for(var key in me.years)
					{
						me.labels.push(key);
						me.data[0].push(me.years[key]);
						
						if(key in me.adds)
						{
							add = me.adds[key];
						}
						else
						{
							add = 0;
						}
						me.data[1].push(add);
						
						if(key in me.leaves)
						{
							leave = me.leaves[key];
						}
						else
						{
							leave = 0;
						}
						me.data[2].push(leave);

						if(add == leave)
						{
							change     = '';
							difference = '0';
						}
						else if(add > leave)
						{
							change     = 'changeplus';
							difference = '+ ' + (add - leave);
						}
						else
						{
							change     = 'changeminus';
							difference = '- ' + (leave - add);
						}
						
						

						me.table_data.push({
											   year:       key,
											   members:    me.years[key],
											   change:     change,
											   add:		   add,
											   leave:	   leave,
											   difference: difference
										   });

						lastyear = me.years[key];
					}
				};

				me.load();

			}]);