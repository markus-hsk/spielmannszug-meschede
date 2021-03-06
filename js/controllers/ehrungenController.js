/**
 * Created by mbuscher on 01.02.2017.
 */


angular.module('spzdb'	// So heißt die App
			   )


.controller('ehrungenController', ['$scope', '$routeParams', 'memberService',
			function(me, _GET, memberService)
			{
				debugSpzDb('ehrungenController Initialize');

				me.current_sort_field = 'NEXT_JUB';
				me.current_sort_dir   = 'asc';
				   
				me.filter_open                = false;
			    me.filters                    = {};
				me.filters.search             = '';
				me.filters.state              = 'alle';
				me.filters.gender_w           = 1;
				me.filters.gender_m           = 1;
				me.filters.age_adult          = 1;
				me.filters.age_child          = 1;
				me.filters.instrument		  = 'all';
                me.filters.nadel		      = 'all';
				me.filters.nextjub 			  = null;

				me.current_year = new Date().getFullYear();

				me.table_data = [];

				me.load = function()
				{
					debugSpzDb('ehrungenController->load() Call');

					$("#mainview").hide();
					$("#loader").show();

					me.table_data = [];

					memberService.load(function()
									   {
										   $("#mainview").show();
										   $("#loader").hide();

										   var members = memberService.getList(me.getFilters(), 'LASTNAME asc');

										   for(var i = 0; i < members.length; i++)
										   {
											   var member = members[i];

											   if(member.CURRENT_STATE != 'aktiv' && member.CURRENT_STATE != 'Ehrenmitglied' && member.CURRENT_STATE != 'GF Vorstand' && member.CURRENT_STATE != 'Erw. Vorstand')
											   {
												   continue;
											   }

											   var m_start_year = '';
											   var active_years = 0;

											   var jub10   = '';
											   var jub20   = '';
											   var jub25   = '';
											   var nextjub = '';

											   for(var state_index = 0; state_index < member.STATES.length; state_index++)
											   {
												   var state = member.STATES[state_index];

												   if(state.STATE == 'aktiv' || state.STATE == 'Ehrenmitglied' || state.STATE == 'GF Vorstand' || state.STATE == 'Erw. Vorstand')
												   {
													   var start_year = parseInt(state.START_DATE.substr(0, 4), 10);
													   if(m_start_year == '')
													   {
														   m_start_year = start_year;
													   }

													   if(state.END_DATE !== null)
													   {
														   var end_year = parseInt(state.END_DATE.substr(0, 4), 10) - 1; // -1 weil das jahr des Austritts nicht mehr gewertet werden darf
													   }
													   else
													   {
														   var end_year = me.current_year;
													   }

													   for(var y = start_year; y <= end_year; y++)
													   {
														   if(active_years == 10)
														   {
															   jub10 = y;
														   }
														   else if(active_years == 20)
														   {
															   jub20 = y;
														   }
														   else if(active_years == 25)
														   {
															   jub25 = y;
														   }

														   active_years++;
													   }
												   }
											   }

											   // Das nächste Jubiläum ermitteln
											   var year = me.current_year;
											   var nextjub_year = '';
											   for(y = active_years; true; y++)
											   {
												   year++;
												   if(y == 10)
												   {
													   nextjub_year = year;
													   nextjub = year + ' (10 Jahre)';
													   break;
												   }
												   else if(y == 20)
												   {
													   nextjub_year = year;
													   nextjub = year + ' (20 Jahre)';
													   break;
												   }
												   else if(y == 25)
												   {
													   nextjub_year = year;
													   nextjub = year + ' (25 Jahre)';
													   break;
												   }
												   else if(y > 25 && y % 5 == 0)
												   {
													   nextjub_year = year;
													   nextjub = year + ' (' + y + ' Jahre)';
													   break;
												   }
											   }

											   if(me.filters.nextjub != null && me.filters.nextjub != nextjub_year)
											   {
												   continue;
											   }
											   
											   if(me.filters.nadel != 'all')
											   {
												   if ((me.filters.nadel == 'bronze' && active_years < 10) ||
													   (me.filters.nadel == 'silber' && active_years < 20) ||
													   (me.filters.nadel == 'gold' && active_years < 25))
													   continue;
											   }

											   me.table_data.push({
																	  NAME:         member.LASTNAME + ', ' + member.FIRSTNAME,
																	  AKTIVE_JAHRE: active_years-1,
																	  START_YEAR:   m_start_year,
																	  JUB10:        jub10,
																	  JUB20:        jub20,
																	  JUB25:        jub25,
																	  NEXT_JUB:     nextjub
																  });
											   
											   me.sortBy('');
										   }
									   }
					);
				};

				me.filterList = function()
				{
					me.load();
				};

				me.getFilters = function()
				{
					return me.filters;
				};

			    me.sortBy = function(field)
			    {
				   debugSpzDb('ehrungenController->sortBy() Call', field);
				   
				   if(field != '')
				   {
					   if(me.current_sort_field == field)
					   {
						   if(me.current_sort_dir == 'asc')
							   me.current_sort_dir = 'desc';
						   else
							   me.current_sort_dir = 'asc';
					   }
					   else
					   {
						   me.current_sort_field = field;
						   me.current_sort_dir   = 'asc';
					   }
				   }
				   
				   me.table_data.sort(function(a, b)
							  {
					   			  switch(me.current_sort_field)
								  {
									  default:
									  case 'LASTNAME':
										  if(a.NAME != b.NAME)
											  result = a.NAME < b.NAME ? -1 : 1;
										  else
											  result = 0;
										  break;

									  case 'START_YEAR':
										  if(a.START_YEAR != b.START_YEAR)
											  result = a.START_YEAR < b.START_YEAR ? -1 : 1;
										  else
											  result = 0;
										  break;

									  case 'NEXT_JUB':
										  if(a.NEXT_JUB != b.NEXT_JUB)
											  result = a.NEXT_JUB < b.NEXT_JUB ? -1 : 1;
										  else
											  result = 0;
										  break;
								  }

								  if(me.current_sort_dir == 'desc')
									  result = result * -1;
								  
								  return result;
							  });
			    };

				me.toggleFilter = function()
				{
					me.filter_open = !me.filter_open;
				};

				me.load();

			}]);