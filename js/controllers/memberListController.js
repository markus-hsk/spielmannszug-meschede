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
			   
			   me.show_address = false;
			   me.show_age = true;
			   me.show_instrument = true;
			   me.show_contact = false;

			   me.filter_open                = false;
			   me.filters                    = {};
			   me.filters.search             = '';
			   me.filters.state              = _GET.state;
			   me.filters.gender_w           = 1;
			   me.filters.gender_m           = 1;
			   me.filters.age_adult          = 1;
			   me.filters.age_child          = 1;
               me.filters.instrument		 = 'all';

               me.page = 1;
               me.max_rows = 30;
               me.max_pages = 1;
               me.all = [];
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

										  me.all = memberService.getList(me.getFilters(), me.getSortBy());
										  
										  if(me.all.length > me.max_rows)
										  {
											  me.max_pages = Math.ceil(me.all.length / me.max_rows);
											  page = Math.min(me.page, me.max_pages);
											  
											  me.showPage(page);
										  }
										  else
										  {
											  me.max_pages = 1;
											  me.showPage(1);
										  }
									  }
				   );
			   };
			   
			   me.showPage = function(page)
			   {
				   debugSpzDb('memberListController->showPage() Call', page);

				   me.page = page;
				   me.rows = [];
				   
				   offset = me.max_rows * (me.page - 1);
				   for(i = 0; i < me.max_rows; i++)
				   {
					   if(me.all.length <= offset + i)
					   {
						   break;
					   }
					   
					   record = me.all[offset + i];
					   record.index = offset + i + 1;
					   me.rows.push(record);
				   }
			   };
			   
			   me.firstPage = function()
			   {
				   debugSpzDb('memberListController->firstPage() Call');

				   if(me.page > 1)
				   {
					   me.showPage(1);
				   }
			   };
			   
			   me.lastPage = function()
			   {
				   debugSpzDb('memberListController->lastPage() Call');

				   if(me.page < me.max_pages)
				   {
					   me.showPage(me.max_pages);
				   }
			   };
			   
			   me.previousPage = function()
			   {
				   debugSpzDb('memberListController->previousPage() Call');

				   if(me.page > 1)
				   {
					   me.showPage(me.page - 1);
				   }
			   };
			   
			   me.nextPage = function()
			   {
				   debugSpzDb('memberListController->nextPage() Call');

				   if(me.page < me.max_pages)
				   {
					   me.showPage(me.page + 1);
				   }
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

				   me.load();
			   };

			   me.filterList = function()
			   {
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
			   
			   me.csvExport = function()
			   {
				   debugSpzDb('memberListController->csvExport() Call');
				   
				   var csv_string = '';
				   
				   csv_string = csv_string + '"#",';
				   csv_string = csv_string + '"LfdNr",';
				   csv_string = csv_string + '"Name",';
				   csv_string = csv_string + '"Vorname",';
				   csv_string = csv_string + '"Geburtsname",';
				   csv_string = csv_string + '"Geschlecht",';
				   csv_string = csv_string + '"Straße",';
				   csv_string = csv_string + '"PLZ",';
				   csv_string = csv_string + '"Ort",';
				   csv_string = csv_string + '"Status",';
				   csv_string = csv_string + '"Instrument",';
				   csv_string = csv_string + '"Geburtstag"' + "\n";

				   for(var i = 0; i < me.all.length; i++)
				   {
					   var record = me.all[i];
					   
					   csv_string = csv_string + (i + 1) + ",";						// No
					   csv_string = csv_string + record.MEMBER_ID + ",";
					   csv_string = csv_string + '"' + record.LASTNAME + '",';
					   csv_string = csv_string + '"' + record.FIRSTNAME + '",';
					   csv_string = csv_string + '"' + record.BIRTHNAME + '",';
					   csv_string = csv_string + '"' + record.GENDER + '",';
					   csv_string = csv_string + '"' + record.STREET + '",';
					   csv_string = csv_string + '"' + record.ZIP + '",';
					   csv_string = csv_string + '"' + record.CITY + '",';
					   csv_string = csv_string + '"' + record.CURRENT_STATE + '",';
					   csv_string = csv_string + '"' + record.INSTRUMENT + '",';
					   csv_string = csv_string + '"' + record.BIRTHDATE + '",';
					   
					   // remove last comma
					   csv_string = csv_string.substring(0, csv_string.length - 1);
					   csv_string = csv_string + "\n";
				   }
				   
				   // remove last next line
				   csv_string = csv_string.substring(0, csv_string.length - 1);
				   
				   debugSpzDb('memberListController->csvExport() csv_string', csv_string);
				   
				   var a = $('<a/>',
						   {
					   			style: 'display:none',
					   			href: 'data:application/octet-stream;base64,' + btoa(csv_string),
					   			download: 'mitgliederliste.csv',
					   			target: 'blank'
						   }).appendTo('body');
				   a[0].click();
				   a.remove();
			   };

			   me.load();
		   }]);