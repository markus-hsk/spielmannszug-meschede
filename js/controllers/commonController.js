/**
 * Created by mbuscher on 27.01.2017.
 */


/**
 * Created by mbuscher on 26.01.2017.
 */


angular.module('spzdb',	// So heißt die App
			   ['ngRoute', 'chart.js', '720kb.datepicker'])	// Plugins

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
			   .when('/statstime', {
				   controller:  'statsHistoryController',
				   templateUrl: 'templates/statshistory.html'
			   })
			   .when('/ehrungen', {
				   controller:  'ehrungenController',
				   templateUrl: 'templates/ehrungen.html'
			   })
			   .when('/mitglied/:member_id', {
				   controller:  'memberDetailController',
				   templateUrl: 'templates/memberdetail.html'
			   })
			   .when('/anwesenheit/:event_id', {
				   controller:  'anwesenheitsController',
				   templateUrl: 'templates/anwesenheit.html'
			   })
			   .when('/anwesenheit', {
				   controller:  'anwesenheitsController',
				   templateUrl: 'templates/anwesenheit.html'
			   })
			   .when('/mailadressen', {
				   controller:  'mailAddressesController',
				   templateUrl: 'templates/mailaddresses.html'
			   })
			   .when('/birthdays', {
				   controller:  'birthdayCalendarController',
				   templateUrl: 'templates/birthdaycalendar.html'
			   })
			   .otherwise({
							  redirectTo: '/mitglieder'
						  });
	   })

.controller('commonController', ['$scope', '$location', '$routeParams', 'memberService',
		   function(me, $location, _GET, memberService)
		   {
			   me.title = 'Verwaltung Spz-Meschede'; // @todo should be configurable
			   me.version = '190419' // @todo should be determined automatically
		   }])


.controller('datePickerController',
			['$scope', '$interval',
			function ($scope, $interval)
			{
				$scope.visibility = true;

				$interval(function setInterval()
						  {
							  //toggling manually everytime
							  $scope.visibility = !$scope.visibility;
						  }, 3500);
			}])

;
