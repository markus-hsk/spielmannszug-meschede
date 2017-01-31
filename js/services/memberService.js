/**
 * Created by mbuscher on 31.01.2017.
 */


angular.module('spzdb')

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

					if(filters.state == 'aktiv'  		&& (member.CURRENT_STATE != 'aktiv' && member.CURRENT_STATE != 'Vorstand'))			continue;
					if(filters.state == 'passiv' 		&& (member.CURRENT_STATE != 'passiv' && member.CURRENT_STATE != 'Ehrenmitglied'))	continue;
					if(filters.state == 'Ehemalig'		&& (member.CURRENT_STATE != 'Ehemalig'))											continue;
					if(filters.state == 'Vorstand'		&& (member.CURRENT_STATE != 'Vorstand'))											continue;
					if(filters.state == 'verstorbene'	&& (member.CURRENT_STATE != 'verstorben'))											continue;
					if(filters.state == 'Ausbildung'	&& (member.CURRENT_STATE != 'Ausbildung'))											continue;

					total_unfiltered++;

					if(filters.search.length && (member.LASTNAME + ' ' + member.FIRSTNAME + ' ' + member.BIRTHNAME + ' ' + member.CITY + ' ' + member.STREET + ' ' + member.ZIP).indexOf(filters.search) === -1)
						continue;

					if(filters.gender_w == 0 && member.GENDER == 'w')																	continue;
					if(filters.gender_m == 0 && member.GENDER == 'm')																	continue;

					if(filters.age_adult == 0 && member.AGE >= 18)																		continue;
					if(filters.age_child == 0 && member.AGE < 18)																		continue;

					if(filters.instrument_floete == 0 && member.INSTRUMENT == 'Flöte')													continue;
					if(filters.instrument_trommel == 0 && member.INSTRUMENT == 'Trommel')												continue;

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

									  case 'INSTRUMENT':
										  if(a.INSTRUMENT != b.INSTRUMENT)
											  result = a.INSTRUMENT < b.INSTRUMENT ? -1 : 1;
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
		});