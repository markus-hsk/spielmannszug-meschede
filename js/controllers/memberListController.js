/**
 * Created by mbuscher on 31.01.2017.
 */

angular.module('spzdb')

.controller('memberListController', ['$scope', '$location', '$routeParams', 'memberService',
		   function(me, $location, _GET, memberService)
		   {
			   debugSpzDb('memberListController Initialize');

			   me.current_sort_field = 'LASTNAME';
			   me.current_sort_dir   = 'asc';

			   me.filter_open                = false;
			   me.filters                    = {};
			   me.filters.search             = '';
			   me.filters.state              = _GET.state;
			   me.filters.gender_w           = 1;
			   me.filters.gender_m           = 1;
			   me.filters.age_adult          = 1;
			   me.filters.age_child          = 1;
			   me.filters.instrument_floete  = 1;
			   me.filters.instrument_trommel = 1;

			   me.rows = [];

			   me.load = function()
			   {
				   debugSpzDb('memberListController->load() Call');

				   $("#mainview").hide();
				   $("#loader").show();

				   memberService.load(function()
									  {
										  $("#mainview").show();
										  $("#loader").hide();

										  me.rows = memberService.getList(me.getFilters(), me.getSortBy());

										  $('#footer_container')
											  .html('Anzahl Mitglieder: ' + me.rows.length + ' von ' + memberService.getTotalUnfiltered());
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

			   me.clearSearch = function()
			   {
				   me.filters.search = '';
				   me.filterList();
			   };

			   me.load();
		   }]);