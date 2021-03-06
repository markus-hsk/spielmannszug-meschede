/**
 * Created by mbuscher on 30.01.2017.
 */

angular.module('spzdb'	// So heißt die App
			   )


.controller('statsNowController', ['$scope', '$routeParams', 'memberService',
			function(me, _GET, memberService)
			{
				var getmode = _GET.mode;
				debugSpzDb('statsNowController Initialize', getmode);

				me.mode           = getmode;
				me.stateselector  = '';
				me.selector_group = '';

				me.filters                    = {};
				me.filters.search             = '';
				me.filters.state              = 'aktiv';
				me.filters.gender_w           = 1;
				me.filters.gender_m           = 1;
				me.filters.age_adult          = 1;
				me.filters.age_child          = 1;
				me.filters.instrument		  = 'all';

				me.load = function()
				{
					debugSpzDb('statsNowController->load() Call');

					$("#selectiontable").hide();
					$("#mainview").hide();
					$("#loader").show();

					me.chartoptions = {
						legend:              {display: false},
						tooltips:			 {intersect: false},
						showTooltips:        true,
						onAnimationComplete: function()
											 {
												 debugSpzDb('statsNowController->chart->onAnimationComplete() Call');
							
							 					 this.showTooltip(this.segments, true);
											 },
						onClick:			 function(points, evt)
											 {
												 debugSpzDb('statsNowController->chart->onClick() Call', evt);

												 // @see https://stackoverflow.com/a/11873839
												 me.$apply(function()
														 {
													 		me.showSelectionTable(evt[0]._model.label);
														 });
												 
												 return true;
											 },
						tooltipEvents:       [],
						tooltipCaretSize:    0
					};

					if(me.mode == 'gender')
					{
						me.labels        = ['weiblich', 'männlich'];
						me.colors        = ['#F781F3', '#5882FA'];
						me.data          = [0, 0];
						me.stateselector = '';
					}
					else if(me.mode == 'age')
					{
						me.labels        = ['Erwachsene', 'Kinder'];
						me.colors        = ['#0000ff', '#ff0000'];
						me.data          = [0, 0];
						me.stateselector = '';
					}
					else if(me.mode == 'state')
					{
						me.labels        = ['aktiv', 'passiv', 'Ausbildung'];
						me.colors        = ['#0000ff', '#FFA500', '#26802E'];
						me.data          = [0, 0, 0];
						me.stateselector = 'hidden';
					}
					else if(me.mode == 'duration')
					{
						me.labels        = ['0-10 Jahre', '10-20 Jahre', '20-30 Jahre', '>30 Jahre'];
						me.colors        = ['#0000ff', '#FFA500', '#26802E', '#FF0000'];
						me.data          = [0, 0, 0, 0];
						me.stateselector = 'hidden';
					}
					else if(me.mode == 'instrument')
					{
						me.labels        = ['Flöte', 'Trommel', 'Lyra', 'Pauke, Becken'];
						me.colors        = ['#0000ff', '#FFA500', '#26802E', '#FF0000'];
						me.data          = [0, 0, 0, 0];
						me.stateselector = '';
					}

					me.values = [];
					me.total  = 0;
					me.average = 0;
					me.selection_data = [];
					me.table_data = {
							'weiblich': [],
							'männlich': [],
							'Erwachsene': [],
							'Kinder': [],
							'aktiv': [],
							'passiv': [],
							'Ausbildung': [],
							'0-10 Jahre': [],
							'10-20 Jahre': [],
							'20-30 Jahre': [],
							'>30 Jahre': [],
							'Flöte': [],
							'Trommel': [],
							'Lyra': [],
							'Pauke, Becken': []
					};

					memberService.load(function()
									   {
										   $("#mainview").show();
										   $("#loader").hide();

										   var usefilters = jQuery.extend({}, me.getFilters());

										   if(me.mode == 'state')
										   		usefilters.state = 'alle';
										   else if(me.mode == 'duration')
										   		usefilters.state = 'aktiv';
										   var members = memberService.getList(usefilters, 'FIRSTNAME asc');

										   me.total = 0;
										   var total_age = 0;
										   var total_counted = 0;

										   for(var i = 0; i < members.length; i++)
										   {
											   var member = members[i];

											   if(me.mode == 'gender')
											   {
												   if(member.GENDER == 'w')
												   {
													   me.data[0]++;
													   me.table_data['weiblich'].push(member);
												   }
												   else if(member.GENDER == 'm')
												   {
													   me.data[1]++;
													   me.table_data['männlich'].push(member);
												   }
											   }
											   else if(me.mode == 'age')
											   {
												   if(member.AGE != null)
												   {
													   if(member.AGE >= 18)
													   {
														   me.data[0]++;
														   me.table_data['Erwachsene'].push(member);
													   }
													   else
													   {
														   me.data[1]++;
														   me.table_data['Kinder'].push(member);
													   }

													   total_age += member.AGE;
													   total_counted++;
												   }
											   }
											   else if(me.mode == 'state')
											   {
												   if(member.CURRENT_STATE == 'aktiv' || member.CURRENT_STATE == 'GF Vorstand' || member.CURRENT_STATE == 'Erw. Vorstand')
												   {
													   me.data[0]++;
													   me.table_data['aktiv'].push(member);
												   }
												   else if(member.CURRENT_STATE == 'passiv' || member.CURRENT_STATE == 'Ehrenmitglied')
												   {
													   me.data[1]++;
													   me.table_data['passiv'].push(member);
												   }
												   else if(member.CURRENT_STATE == 'Ausbildung')
												   {
													   me.data[2]++;
													   me.table_data['Ausbildung'].push(member);
												   }
												   else
												   {
													   continue;
												   }
											   }
											   else if(me.mode == 'duration')
											   {
												   if(member.AKTIV_JAHRE < 10)
												   {
													   me.data[0]++;
													   me.table_data['0-10 Jahre'].push(member);
												   }
												   else if(member.AKTIV_JAHRE < 20)
												   {
													   me.data[1]++;
													   me.table_data['10-20 Jahre'].push(member);
												   }
												   else if(member.AKTIV_JAHRE < 30)
												   {
													   me.data[2]++;
													   me.table_data['20-30 Jahre'].push(member);
												   }
												   else
												   {
													   me.data[3]++;
													   me.table_data['>30 Jahre'].push(member);
												   }
											   }
											   else if(me.mode == 'instrument')
											   {
												   if(member.INSTRUMENT.indexOf('Flöte') !== -1)
												   {
													   me.data[0]++;
													   me.table_data['Flöte'].push(member);
												   }
												   else if(member.INSTRUMENT.indexOf('Trommel') !== -1)
												   {
													   me.data[1]++;
													   me.table_data['Trommel'].push(member);
												   }
												   else if(member.INSTRUMENT.indexOf('Lyra') !== -1)
												   {
													   me.data[2]++;
													   me.table_data['Lyra'].push(member);
												   }
                                                   else if(member.INSTRUMENT.indexOf('Pauke') !== -1 || member.INSTRUMENT.indexOf('Becken') !== -1)
                                                   {
                                                	   me.data[3]++;
													   me.table_data['Pauke, Becken'].push(member);
                                                   }
                                                   else
                                                   {
                                                	   continue;
                                                   }
											   }

											   me.total++;
										   }

										   if(me.mode == 'age')
											   me.average = roundDecimal(total_age / total_counted, 1);

										   me.setLegend();
									   }
					);
				};

				me.setLegend = function()
				{
					debugSpzDb('statsNowController->setLegend() Call');
					
					for(var i = 0; i < me.data.length; i++)
					{
						me.values.push({
										   value:         	me.data[i],
										   color:         	me.colors[i],
										   label:         	me.labels[i],
										   percentage:    	Math.round(100 / me.total * me.data[i])
									   });
					}


				};

				me.getFilters = function()
				{
					debugSpzDb('statsNowController->getFilters() Call');
					
					return me.filters;
				};

				me.setMode = function(mode)
				{
					debugSpzDb('statsNowController->setMode() Call', mode);
					
					if(me.mode != mode)
					{
						me.mode = mode;
						$("#selectiontable").hide();
						me.load();
					}
				};
				
				me.showSelectionTable = function (group)
				{
					debugSpzDb('statsNowController->showSelectionTable() Call', group);
					
					me.selection_data = me.table_data[group];
					me.selector_group = group;
					
					$("#selectiontable").show();
				}

				me.load();
			}]);

