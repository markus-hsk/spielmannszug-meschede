/**
 * Created by mbuscher on 31.01.2017.
 */


angular.module('spzdb')

.service('memberService',
		function($http)
		{
			var memberlist = [];
			var total_unfiltered = 0;
			var do_reload = false;

			this.load = function(callback)
			{
				debugSpzDb('memberService->load() Call');

				if(!do_reload && memberlist.length)
				{
					debugSpzDb('memberService->load() No reload necessary');
					callback();
					return;
				}

				$http({
						  method: 'GET',
						  url:    './memberlist.php'
					  }).then(function successCallback(response)
							  {
								  memberlist = response.data;
								  do_reload = false;
								  debugSpzDb('memberService->load() Mitgliederliste geladen', memberlist);

								  callback();
							  },
							  function errorCallback(response)
							  {
								  console.error('memberService->load() Mitgliederliste konnte nicht geladen werden', response);
								  
								  if(response.status == 401)
								  {
									  window.location.href = '401.html';
								  }
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

					if(filters.state == 'Mitglied'  	&& (member.CURRENT_STATE == 'Ehemalig' || member.CURRENT_STATE == 'verstorben'))										continue;
					if(filters.state == 'aktiv'  		&& (member.CURRENT_STATE != 'aktiv' && member.CURRENT_STATE.indexOf('Vorstand') === -1))								continue;
					if(filters.state == 'passiv' 		&& (member.CURRENT_STATE != 'passiv' && member.CURRENT_STATE != 'Ehrenmitglied'))										continue;
					if(filters.state == 'Ehemalig'		&& (member.CURRENT_STATE != 'Ehemalig'))																				continue;
					if(filters.state == 'Vorstand'		&& (member.CURRENT_STATE.indexOf('Vorstand') === -1))																	continue;
					if(filters.state == 'verstorbene'	&& (member.CURRENT_STATE != 'verstorben'))																				continue;
					if(filters.state == 'Ausbildung'	&& (member.CURRENT_STATE != 'Ausbildung'))																				continue;
					if(filters.state == 'Auftritt'		&& (member.CURRENT_STATE != 'aktiv' && member.CURRENT_STATE != 'Ausbildung'))											continue;
					if(filters.state == 'aktivpassiv'	&& (member.CURRENT_STATE != 'aktiv' && member.CURRENT_STATE != 'passiv' && member.CURRENT_STATE != 'Ehrenmitglied'))	continue;

					total_unfiltered++;

					if(filters.search.length && (member.LASTNAME + ' ' + member.FIRSTNAME + ' ' + member.BIRTHNAME + ' ' + member.CITY + ' ' + member.STREET + ' ' + member.ZIP).indexOf(filters.search) === -1)
						continue;

					if(filters.gender_w == 0 && member.GENDER == 'w')		continue;
					if(filters.gender_m == 0 && member.GENDER == 'm')		continue;

					if(filters.age_adult == 0 && member.AGE >= 18)			continue;
					if(filters.age_child == 0 && member.AGE < 18)			continue;

					if(filters.instrument != 'all')
					{
                        if(member.INSTRUMENT.indexOf(filters.instrument) === -1)
							continue;
					}

					// Der Eintrag darf angezeigt werden
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
										  if(a.LASTNAME.toLowerCase() != b.LASTNAME.toLowerCase())
											  result = a.LASTNAME.toLowerCase() < b.LASTNAME.toLowerCase() ? -1 : 1;
										  else
											  result = a.FIRSTNAME.toLowerCase() < b.FIRSTNAME.toLowerCase() ? -1 : 1;
										  break;

									  case 'FIRSTNAME':
										  if(a.FIRSTNAME.toLowerCase() != b.FIRSTNAME.toLowerCase())
											  result = a.FIRSTNAME.toLowerCase() < b.FIRSTNAME.toLowerCase() ? -1 : 1;
										  else
											  result = a.LASTNAME.toLowerCase() < b.LASTNAME.toLowerCase() ? -1 : 1;
										  break;

									  case 'GENDER':
										  if(a.GENDER.toLowerCase() != b.GENDER.toLowerCase())
											  result = a.GENDER.toLowerCase() < b.GENDER.toLowerCase() ? -1 : 1;
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
										  if(a.CURRENT_STATE.toLowerCase() != b.CURRENT_STATE.toLowerCase())
											  result = a.CURRENT_STATE.toLowerCase() < b.CURRENT_STATE.toLowerCase() ? -1 : 1;
										  else
											  result = 0;
										  break;

									  case 'INSTRUMENT':
										  if(a.INSTRUMENT.toLowerCase() != b.INSTRUMENT.toLowerCase())
											  result = a.INSTRUMENT.toLowerCase() < b.INSTRUMENT.toLowerCase() ? -1 : 1;
										  else
											  result = 0;
										  break;

									  case 'AKTIV_JAHRE':
										  if(a.AKTIV_JAHRE != b.AKTIV_JAHRE)
											  result = a.AKTIV_JAHRE < b.AKTIV_JAHRE ? -1 : 1;
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

			this.getMemberById = function(member_id)
			{
				for(var i = 0; i < memberlist.length; i++)
				{
					if(memberlist[i].MEMBER_ID == member_id)
						return memberlist[i];
				}
			};

			this.save = function (member_id, memberdata, callback)
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
								  
								  if(response.status == 401)
								  {
									  window.location.href = '401.html';
								  }
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
								  
								  if(response.status == 401)
								  {
									  window.location.href = '401.html';
								  }
							  });
			};
		});