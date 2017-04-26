/**
 * Created by mbuscher on 26.04.2017
 */


angular.module('spzdb'	// So heißt die App
			   )


.controller('anwesenheitsController', ['$scope', '$routeParams', 'memberService',
			function(me, _GET, memberService)
			{
				debugSpzDb('anwesenheitsController Initialize');

				me.filter_open                = false;
			    me.filters                    = {};
				me.filters.search             = '';
				me.filters.state              = 'alle';
				me.filters.gender_w           = 1;
				me.filters.gender_m           = 1;
				me.filters.age_adult          = 1;
				me.filters.age_child          = 1;
                me.filters.instrument		  = 'all';
				
				me.table_data = [];
				
				me.load = function()
				{
					debugSpzDb('ehrungenController->load() Call');

					$("#mainview").hide();
					$("#loader").show();

					me.table_data = [];

					memberService.load(function()
									   {
										   $("#mainview").show();
										   $("#loader").hide();

										   var members = memberService.getList(me.filters, 'LASTNAME ASC');

										   for(var i = 0; i < members.length; i++)
										   {
											   var member = members[i];

											   me.table_data.push({
																	  NAME:         member.LASTNAME + ', ' + member.FIRSTNAME,
																	  MEMBER_ID:	member.MEMBER_ID,
																	  ANWESENHEIT:  'X'
																  });
										   }
									   }
					);
				};
				
				me.setAnwesenheit = function(member_id, value)
				{
					debugSpzDb('ehrungenController->load() Call', arguments);
					
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

				me.load();
			}]);