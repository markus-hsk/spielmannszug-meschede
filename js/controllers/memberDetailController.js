/**
 * Created by mbuscher on 01.02.2017.
 */

angular.module('spzdb')

.controller('memberDetailController', ['$scope', '$location', '$routeParams', 'memberService',
		   function(me, $location, _GET, memberService)
		   {
			   me.member_id = _GET.member_id;
			   debugSpzDb('memberDetailController Initialize', me.member_id);

			   me.member = {};
			   me.title = '';

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

										  debugSpzDb('memberDetailController->load() callback', me.member);

										  if(me.member == null)
										  {
											  $location.url('/mitglieder/aktiv');
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
						   $location.url('/mitglieder/aktiv');
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
					   $location.url('/mitglieder/aktiv');
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