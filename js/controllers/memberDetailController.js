/**
 * Created by mbuscher on 01.02.2017.
 */

angular.module('spzdb')

.controller('memberDetailController', ['$scope', '$location', '$routeParams', 'memberService',
		   function(me, $location, _GET, memberService)
		   {
			   me.member_id = _GET.member_id;
			   debugSpzDb('memberDetailController Initialize', me.member_id);
			   
			   if(_GET.state)
				   me.return_state = _GET.state;
			   else
				   me.return_state = 'aktiv';

			   me.member     = {};
			   me.title      = '';
			   me.deleteable = false;

			   me.test  = '10.10.1960';
			   me.dateOptions = {
				   'year-format': "'yy'",
				   'show-weeks':  false
			   };

			   me.load = function()
			   {
				   debugSpzDb('memberDetailController->load() Call');

				   $("#mainview").hide();
				   $("#loader").show();

				   memberService.load(function()
									  {
										  $("#mainview").show();
										  $("#loader").hide();

										  me.member = memberService.getMemberById(me.member_id);

			   							  me.deleteable = true;

										  debugSpzDb('memberDetailController->load() callback', me.member);

										  if(me.member == null)
										  {
											  $location.url('/mitglieder/' + me.return_state);
										  }
										  else
										  {

										  }
									  }
				   );
			   };

			   me.save = function()
			   {
				   var result = confirm('Sollen die Daten gespeichert werden?');

				   debugSpzDb('memberDetailController->save()', me.member, result);

				   if(result)
				   {
					   var callback = function()
					   {
						   memberService.do_reload = true;
						   $location.url('/mitglieder/' + me.return_state);
					   };
					   memberService.save(me.member_id, me.member, callback);
				   }
			   };

			   me.cancel = function()
			   {
				   var result = confirm('Eingabe wirklich abbrechen?');

				   debugSpzDb('memberDetailController->cancel()', me.member, result);

				   if(result)
				   {
					   $location.url('/mitglieder/' + me.return_state);
				   }
			   };

			   me.delete = function()
			   {
				   var result = confirm('Dieses Mitglied wirklich löschen?');

				   debugSpzDb('memberDetailController->delete()', me.member, result);

				   if(result)
				   {
					   var callback = function()
					   {
						   $location.url('/mitglieder/' + me.return_state);
					   };

					   memberService.delete(me.member_id, callback);
				   }
			   };

			   me.addState = function()
			   {
				   var start_date = date_to_string("Y-m-d");
				   var membership_id = 'NEW_' + date_to_string('His');

				   if(!me.member.STATES)
				   		me.member.STATES = [];

				   me.member.STATES.push({	STATE: '',
										 	START_DATE: start_date,
					   						END_DATE: null,
					   						MEMBERSHIP_ID: membership_id
										 });

				   debugSpzDb('memberDetailController->addState()', me.member.STATES);
			   };

			   me.deleteState = function(membership_id)
			   {
				    var result = confirm('Diesen Status wirklich löschen?');

				   debugSpzDb('memberDetailController->deleteState()', membership_id, result);

				   if(result)
				   {
					   for(var i = 0; i < me.member.STATES.length; i++)
					   {
						   if(me.member.STATES[i].MEMBERSHIP_ID == membership_id)
						   {
							   me.member.STATES.splice(i, 1);
							   break;
						   }
					   }
				   }
			   };

			   if(me.member_id == 'new')
			   {
				   me.title = 'Mitglied anlegen';

				   $("#mainview").show();
				   $("#loader").hide();
			   }
			   else
			   {
				   me.title = 'Mitgliedsdaten bearbeiten';
				   me.load();
			   }
		   }]);