/**
 * Created by mbuscher on 19.03.2019.
 */

angular.module('spzdb')

.controller('mailAddressesController', ['$scope', '$location', '$routeParams', 'memberService',
		   function(me, $location, _GET, memberService)
		   {
			   debugSpzDb('mailAddressesController Initialize');

			   me.filter_open                = false;
			   me.filters                    = {};
			   me.filters.search             = '';
			   me.filters.status_aktiv       = 1;
			   me.filters.status_passiv      = 0;
			   me.filters.gender_w           = 1;
			   me.filters.gender_m           = 1;
			   me.filters.age_adult          = 1;
			   me.filters.age_child          = 1;
               me.filters.instrument		 = 'all';
               
			   me.rows = [];

			   me.load = function()
			   {
				   debugSpzDb('mailAddressesController->load() Call');

				   $("#mainview").hide();
				   $("#loader").show();

				   memberService.load(function()
									  {
										  $("#mainview").show();
										  $("#loader").hide();

										  me.rows = [];
										  var all = memberService.getList(me.getFilters(), 'MEMBER_ID asc');
										  
										  for(var i = 0; i < all.length; i++)
										  {
											  console.log(all[i]);
											  if(all[i].CONTACT.email && all[i].CONTACT.email.length > 0)
											  {
												  me.rows.push(all[i]);
											  }
									      }
									  }
				   );
			   };

			   me.filterList = function()
			   {
				   me.load();
			   };

			   me.getFilters = function()
			   {
				   if(me.filters.status_aktiv == 1 && me.filters.status_passiv == 1)
				   {
					   me.filters.state = 'aktivpassiv';
				   }
				   else if(me.filters.status_passiv == 0)
				   {
					   me.filters.status_aktiv = 1; // reset to aktiv
					   me.filters.state = 'aktiv';
				   }
				   else
				   {
					   me.filters.state = 'passiv';
				   }
				   
				   return me.filters;
			   };

			   me.toggleFilter = function()
			   {
				   me.filter_open = !me.filter_open;
			   };

			   me.clearSearch = function()
			   {
				   me.filters.search = '';
				   me.filterList();
			   };
			   
			   me.copyToClipboard = function()
			   {
				   debugSpzDb('mailAddressesController->copyToClipboard() Call');
				   
				   listelement = document.getElementById('addresslist');
				   
				   var range = document.createRange();
				   range.selectNode(listelement);
				   window.getSelection().removeAllRanges();
				   window.getSelection().addRange(range);

				   document.execCommand('copy');
				   window.getSelection().removeAllRanges();
			   }

			   me.load();
		   }]);