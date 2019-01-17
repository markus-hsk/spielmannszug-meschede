/**
 * Created by mbuscher on 02.11.2018.
 */


angular.module('spzdb')

.service('eventService',
		function($http)
		{
			var eventlist = [];
			var total_unfiltered = 0;
			var do_reload = false;

			this.load = function(callback)
			{
				debugSpzDb('eventService->load() Call');

				if(!do_reload && eventlist.length)
				{
					callback();
					return;
				}

				$http({
						  method: 'GET',
						  url:    './events.php'
					  }).then(function successCallback(response)
							  {
								  eventlist = response.data;
								  do_reload = false;
								  debugSpzDb('Eventliste geladen', eventlist);

								  callback();
							  },
							  function errorCallback(response)
							  {
								  console.error('Eventliste konnte nicht geladen werden', response);
							  });

			};

			this.getTotalUnfiltered = function()
			{
				return total_unfiltered;
			};

			this.getList = function(filters, sortby)
			{
				debugSpzDb('eventService->getList() Call', filters, sortby);

				var list = [];
				total_unfiltered = 0;

				// Alle Events durchlaufen und filter anwenden
				// @todo Filter implementieren
				for(i = 0; i < eventlist.length; i++)
				{
					var event = eventlist[i];

					total_unfiltered++;

					if(filters && filters.search && filters.search.length && event.NAME.indexOf(filters.search) === -1)
						continue;

					// Der Eintrag darf angezeigt werden
					list.push(event);
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
									  case 'NAME':
										  if(a.NAME != b.NAME)
											  result = a.NAME < b.NAME ? -1 : 1;
										  else
											  result = 0;
										  break;
										  
									  case 'INSERT_TS':
										  if(a.INSERT_TS != b.INSERT_TS)
											  result = a.INSERT_TS < b.INSERT_TS ? -1 : 1;
										  else
											  result = 0;
										  break;
								  }

								  if(sortby_splitted[1] == 'desc')
									  result = result * -1;

								  return result;
							  });
				}

				debugSpzDb('eventService->getList() Return', list);

				return list;
			};

			this.getEventById = function(event_id)
			{
				for(var i = 0; i < eventlist.length; i++)
				{
					if(eventlist[i].EVENT_ID == event_id)
						return eventlist[i];
				}
			};

			this.save = function (event_id, eventdata, callback)
			{
				$http({
						  method: 'POST',
						  url:    './save_member.php?member_id=' + member_id,
						  data:   memberdata
					  }).then(function successCallback(response)
							  {
								  debugSpzDb('save success', response);

								  // Mitgliedsdaten ändern in geladenen Daten
                                  for(var i = 0; i < memberlist.length; i++)
                                  {
                                      if(memberlist[i].MEMBER_ID == member_id)
                                          memberlist[i] = memberdata;
                                  }

								  do_reload = true;

								  callback();
							  },
							  function errorCallback(response)
							  {
								  console.error('save error', response);
							  });
			};

			this.delete = function(member_id, callback)
			{
				$http({
						  method: 'DELETE',
						  url:    './delete_member.php?member_id=' + member_id
					  }).then(function successCallback(response)
							  {
								  debugSpzDb('delete success', response);

                                  // Mitgliedsdaten ändern in geladenen Daten
                                  for(var i = 0; i < memberlist.length; i++)
                                  {
                                      if(memberlist[i].MEMBER_ID == member_id)
                                          memberlist.splice(i, 1);
                                  }

								  do_reload = true;

								  callback();
							  },
							  function errorCallback(response)
							  {
								  console.error('delete error', response);
							  });
			};
		});