/**
 * Created by mbuscher on 26.04.2017
 */


angular.module('spzdb'	// So heißt die App
			   )


.controller('anwesenheitsController', ['$scope', '$routeParams', 'memberService', 'eventService', '$location',
			function(me, _GET, memberService, eventService, $location)
			{
				// load events
				me.event_id = _GET.event_id;
				
				if(typeof me.event_id == 'undefined')
				{
					me.event_id = 0;
				}
				
				me.event_name = '';
				
				debugSpzDb('anwesenheitsController Initialize', me.event_id);
				
				$("#mainview").hide();
				$("#loader").show();				   
				
				me.filter_open                = false;
			    me.filters                    = {};
				me.filters.search             = '';
				me.filters.state              = 'Auftritt';
				me.filters.gender_w           = 1;
				me.filters.gender_m           = 1;
				me.filters.age_adult          = 1;
				me.filters.age_child          = 1;
                me.filters.instrument		  = 'all';
				
                me.events = [];
				me.table_data = [];
				
				me.load = function()
				{
					debugSpzDb('anwesenheitsController->load() Call');

					me.events = [];
					me.table_data = [];
					
					eventService.load(function ()
									  {
										  var events = eventService.getList([], 'INSERT_TS desc');
										  
										  for(var i = 0; i < events.length; i++)
										  {
											   var event = events[i];
											   
											   if(me.event_id == 0)
											   {
												   $location.path('/anwesenheit/' + event.EVENT_ID);
												   return;
											   }

											   me.events.push({	
												   				NAME: event.NAME,
												   				EVENT_ID: event.EVENT_ID,
												   				INSERT_TS: event.INSERT_TS
											   });
											   
											   if(event.EVENT_ID == me.event_id)
											   {
												   me.event_name = event.NAME;
											   }
										  }
										  
										  memberService.load(function()
												   {
													   var members = memberService.getList(me.filters, 'LASTNAME asc');

													   for(var i = 0; i < members.length; i++)
													   {
														   var member = members[i];

														   me.table_data.push({
																				  NAME:         member.LASTNAME + ', ' + member.FIRSTNAME,
																				  MEMBER_ID:	member.MEMBER_ID,
																				  ANWESENHEIT:  'X'
																			  });
													   }
													   
													   $("#mainview").show();
													   $("#loader").hide();
												   }
										  );
									  }
					);
				};
				
				me.setAnwesenheit = function(member_id, value)
				{
					debugSpzDb('anwesenheitsController->setAnwesenheit() Call', arguments);
					
					$('#x' + member_id).removeClass('btn-success');
					$('#e' + member_id).removeClass('btn-warning');
					$('#o' + member_id).removeClass('btn-danger');
					
					switch(value)
					{
						case 'x':
							console.log('add btn_success');
							$('#x' + member_id).addClass('btn-success');
							break;
							
						case 'e':
							console.log('add btn_warn');
							$('#e' + member_id).addClass('btn-warning');
							break;
							
						case 'o':
							console.log('add btn_danger');
							$('#o' + member_id).addClass('btn-danger');
							break;
					}
				};
				
				me.addEvent = function()
				{
					debugSpzDb('anwesenheitsController->addEvent() Call');
					
					result = window.prompt("Name des Auftritts?", "");
					debugSpzDb('anwesenheitsController->addEvent() result ', result);
					
					if (result == null || result == "")
					{
						return;
					}
					else
					{
						// @todo implement
					}
				}

				me.load();
			}]);