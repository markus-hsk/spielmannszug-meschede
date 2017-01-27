/**
 * Created by mbuscher on 27.01.2017.
 */


/**
 * Created by mbuscher on 26.01.2017.
 */


angular.module('spzdb',	// So heißt die App
			   ['ngRoute', 'chart.js'])	// Plugins

.controller('memberListController', ['$scope', '$location', 'memberService',
		   function(me, $location, memberService)
		   {
			   debugSpzDb('memberListController Initialize');

			   $("#mainview").hide();
			   $("#loader").show();

			   me.current_sort_field = 'LASTNAME';
			   me.current_sort_dir = 'asc';

			   me.filter_open = false;
			   me.filters = {};
			   me.filters.search    = '';
			   me.filters.state     = 'aktiv';
			   me.filters.gender_w  = 1;
			   me.filters.gender_m  = 1;
			   me.filters.age_adult = 1;
			   me.filters.age_child = 1;

			   me.rows = [];

			   me.load = function()
			   {
				   debugSpzDb('memberListController->load() Call');
				   memberService.load(function()
									{
										$("#mainview").show();
										$("#loader").hide();

										me.rows = memberService.getList(me.getFilters(), me.getSortBy());

										$('#footer_container').html('Anzahl Mitglieder: ' + me.rows.length + ' von ' + memberService.getTotalUnfiltered());
									}
				   );
			   };

			   me.sortBy = function(field)
			   {
				   debugSpzDb('memberListController->sortBy() Call', field);

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

				   //me.rows = memberService.getList(me.getFilters(), me.getSortBy());
				   me.load();
			   };

			   me.filterList = function()
			   {
				   //me.rows = memberService.getList(me.getFilters(), me.getSortBy());
				   me.load();
			   };

			   me.getFilters = function()
			   {
				   return me.filters;
			   };

			   me.getSortBy = function()
			   {
				   return me.current_sort_field + ' ' + me.current_sort_dir;
			   };

			   me.toggleFilter = function()
			   {
				   me.filter_open = !me.filter_open;
			   };

			   me.getTotalUnfiltered = function()
			   {
				   return memberService.getTotalUnfiltered();
			   };

			   me.load();
		   }])


.controller('statsNowController', ['$scope', '$location', 'memberService',
			function(me, $location, memberService)
			{
				debugSpzDb('statsNowController Initialize');

				$("#mainview").hide();
				$("#loader").show();

				me.mode          = 'gender';
				me.stateselector = '';

				me.filter_open       = false;
				me.filters           = {};
				me.filters.search    = '';
				me.filters.state     = 'aktiv';
				me.filters.gender_w  = 1;
				me.filters.gender_m  = 1;
				me.filters.age_adult = 1;
				me.filters.age_child = 1;

				me.load = function()
				{
					debugSpzDb('memberListController->load() Call');

					me.chartoptions = {
						legend:              {display: false},
						tooltips:			 {intersect: false},
						showTooltips:        true,
						onAnimationComplete: function()
											 {
												 this.showTooltip(this.segments, true);
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
						me.labels        = ['Flöte', 'Trommel'];
						me.colors        = ['#0000ff', '#ff0000'];
						me.data          = [0, 0];
						me.stateselector = '';
					}

					me.values = [];
					me.total  = 0;
					me.average = 0;

					memberService.load(function()
									   {
										   $("#mainview").show();
										   $("#loader").hide();

										   var usefilters = jQuery.extend({}, me.getFilters());

										   if(me.mode == 'state')
										   		usefilters.state = 'alle';
										   else if(me.mode == 'duration')
										   		usefilters.state = 'aktiv';
										   var members = memberService.getList(usefilters, '');

										   me.total = 0;
										   var total_age = 0;

										   for(var i = 0; i < members.length; i++)
										   {
											   var member = members[i];

											   if(me.mode == 'gender')
											   {
												   if(member.GENDER == 'w')
													   me.data[0]++;
												   else
													   me.data[1]++;
											   }
											   else if(me.mode == 'age')
											   {
												   if(member.AGE >= 18)
													   me.data[0]++;
												   else
													   me.data[1]++;

												   total_age += member.AGE;
											   }
											   else if(me.mode == 'state')
											   {
												   if(member.CURRENT_STATE == 'aktiv' || member.CURRENT_STATE == 'Vorstand')
													   me.data[0]++;
												   else if(member.CURRENT_STATE == 'passiv')
													   me.data[1]++;
												   else if(member.CURRENT_STATE == 'Ausbildung')
													   me.data[2]++;
												   else
													   continue;
											   }
											   else if(me.mode == 'duration')
											   {
												   if(member.AKTIV_JAHRE < 10)
												   		me.data[0]++;
												   else if(member.AKTIV_JAHRE < 20)
												   		me.data[1]++;
												   else if(member.AKTIV_JAHRE < 30)
												   		me.data[2]++;
												   else
												   		me.data[3]++;
											   }
											   else if(me.mode == 'instrument')
											   {
												   if(member.INSTRUMENT == 'Flöte')
													   me.data[0]++;
												   else
													   me.data[1]++;

												   total_age += member.AGE;
											   }

											   me.total++;
										   }

										   if(me.mode == 'age')
											   me.average = roundDecimal(total_age / me.total, 1);

										   me.setLegend();
									   }
					);
				};

				me.setLegend = function()
				{
					for(var i = 0; i < me.data.length; i++)
					{
						me.values.push({
										   value:         me.data[i],
										   color:         me.colors[i],
										   label:         me.labels[i],
										   percentage:    Math.round(100 / me.total * me.data[i])
									   });
					}


				};

				me.getFilters = function()
				{
					return me.filters;
				};

				me.setMode = function(mode)
				{
					if(me.mode != mode)
					{
						me.mode = mode;
						me.load();
					}
				};

				me.load();
			}])


.directive('genderSign',
		  function()
		  {
			  return {
				  template: '<i class="fa {{row.GENDER == \'w\' ? \'fa-venus female\' : \'fa-mars male\'}}" aria-hidden="true" aria-label="{{row.GENDER == \'w\' ? \'weiblich\' : \'männlich\'}}"></i>'
			  };
		  })

.config(function($routeProvider, ChartJsProvider)
	   {
		   $routeProvider

			   .when('/mitglieder', {
				   controller:  'memberListController',
				   templateUrl: 'templates/memberlist.html'
			   })
			   .when('/statsnow', {
				   controller:  'statsNowController',
				   templateUrl: 'templates/statsnow.html'
			   })
			   .otherwise({
							  redirectTo: '/mitglieder'
						  });
	   })

.service('memberService',
		function($http)
		{
			var memberlist = [];
			var total_unfiltered = 0;

			this.load = function(callback)
			{
				debugSpzDb('memberService->load() Call');

				if(memberlist.length)
				{
					callback();
					return;
				}

				$http({
						  method: 'GET',
						  url:    './memberlist.php'
					  }).then(function successCallback(response)
							  {
								  memberlist = response.data;
								  debugSpzDb('Mitgliederliste geladen', memberlist);

								  callback();
							  },
							  function errorCallback(response)
							  {
								  console.error('Mitgliederliste konnte nicht geladen werden', response);
							  });

			};

			this.getTotalUnfiltered = function()
			{
				return total_unfiltered;
			};

			this.getList = function(filters, sortby)
			{
				debugSpzDb('memberService->getList() Call', filters, sortby);

				var list = [];
				total_unfiltered = 0;

				// Alle Mitglieder durchlaufenund filter anwenden
				// @todo Filter implementieren
				for(i = 0; i < memberlist.length; i++)
				{
					var member = memberlist[i];

					if(filters.state == 'aktiv'  		&& (member.CURRENT_STATE != 'aktiv' && member.CURRENT_STATE != 'Vorstand'))		continue;
					if(filters.state == 'passiv' 		&& (member.CURRENT_STATE != 'passiv'))											continue;
					if(filters.state == 'Ehemalig'		&& (member.CURRENT_STATE != 'Ehemalig'))										continue;
					if(filters.state == 'Vorstand'		&& (member.CURRENT_STATE != 'Vorstand'))										continue;
					if(filters.state == 'verstorbene'	&& (member.CURRENT_STATE != 'Verstorben'))										continue;
					if(filters.state == 'Ausbildung'	&& (member.CURRENT_STATE != 'Ausbildung'))										continue;

					total_unfiltered++;

					if(filters.search.length && (member.LASTNAME + ' ' + member.FIRSTNAME + ' ' + member.BIRTHNAME + ' ' + member.CITY + ' ' + member.STREET + ' ' + member.ZIP).indexOf(filters.search) === -1)
						continue;

					if(filters.gender_w == 0 && member.GENDER == 'w')																	continue;
					if(filters.gender_m == 0 && member.GENDER == 'm')																	continue;

					if(filters.age_adult == 0 && member.AGE >= 18)																		continue;
					if(filters.age_child == 0 && member.AGE < 18)																		continue;

					list.push(member);
				}

				// Liste sortieren
				if(sortby != '')
				{
					list.sort(function(a, b)
							  {
								  var sortby_splitted = sortby.split(' ');
								  var result = 0;

								  switch(sortby_splitted[0])
								  {
									  default:
									  case 'LASTNAME':
										  if(a.LASTNAME != b.LASTNAME)
											  result = a.LASTNAME < b.LASTNAME ? -1 : 1;
										  else
											  result = a.FIRSTNAME < b.FIRSTNAME ? -1 : 1;
										  break;

									  case 'FIRSTNAME':
										  if(a.FIRSTNAME != b.FIRSTNAME)
											  result = a.FIRSTNAME < b.FIRSTNAME ? -1 : 1;
										  else
											  result = a.LASTNAME < b.LASTNAME ? -1 : 1;
										  break;

									  case 'GENDER':
										  if(a.GENDER != b.GENDER)
											  result = a.GENDER < b.GENDER ? -1 : 1;
										  else
											  result = 0;
										  break;

									  case 'BIRTHDATE':
										  if(a.BIRTHDATE != b.BIRTHDATE)
											  result = a.BIRTHDATE < b.BIRTHDATE ? -1 : 1;
										  else
											  result = 0;
										  break;

									  case 'STATE':
										  if(a.CURRENT_STATE != b.CURRENT_STATE)
											  result = a.CURRENT_STATE < b.CURRENT_STATE ? -1 : 1;
										  else
											  result = 0;
										  break;
								  }

								  if(sortby_splitted[1] == 'desc')
									  result = result * -1;

								  return result;
							  });
				}

				debugSpzDb('memberService->getList() Return', list);

				return list;
			};
		})

;