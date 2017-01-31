/**
 * Created by mbuscher on 31.01.2017.
 */


angular.module('spzdb')

.directive('genderSign',
		  function()
		  {
			  return {
				  template: '<i class="fa {{row.GENDER == \'w\' ? \'fa-venus female\' : \'fa-mars male\'}}" aria-hidden="true" aria-label="{{row.GENDER == \'w\' ? \'weiblich\' : \'männlich\'}}" title="{{row.GENDER == \'w\' ? \'weiblich\' : \'männlich\'}}"></i>'
			  };
		  });