/**
 * Created by mbuscher on 27.01.2017.
 */


/**
 * Created by mbuscher on 26.01.2017.
 */


angular.module('spzdb',	// So heißt die App
			   ['ngRoute', 'chart.js'])	// Plugins

.config(function($routeProvider, ChartJsProvider)
	   {
		   $routeProvider

			   .when('/mitglieder/:state', {
				   controller:  'memberListController',
				   templateUrl: 'templates/memberlist.html'
			   })
			   .when('/mitglieder', {
				   redirectTo: '/mitglieder/aktiv'
			   })
			   .when('/statsnow/:mode', {
				   controller:  'statsNowController',
				   templateUrl: 'templates/statsnow.html'
			   })
			   .when('/statsnow', {
				   redirectTo: '/statsnow/gender'
			   })
			   .otherwise({
							  redirectTo: '/mitglieder'
						  });
	   })

;