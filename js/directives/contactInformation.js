/**
 * Created by mbuscher on 31.01.2017.
 */


angular.module('spzdb')

.directive('contactInformation',
		  function($compile)
		  {
			  // siehe: http://stackoverflow.com/questions/20810862/passing-value-of-a-variable-to-angularjs-directive-template-function
			  var getTemplate = function(type, value)
			  {
				  if(value == '')
				  	return '';

				  switch(type)
				  {
					  case 'email':
						  return '<i class="fa fa-fw fa-envelope" aria-hidden="true" title="E-Mail"></i> <a href="mailto:' + value + '">' + value + '</a>';

					  case 'phone':
					  case 'mobile':
						  return '<i class="fa fa-fw fa-phone" aria-hidden="true"></i> <span>' + formatTelephoneNumber(value) + '</span>';

					  default:
						  return type + ': ' + value;

				  }
			  };

			  return {
				  scope:    {
					  type: "=",
					  value: "="
				  },
				  link:     function(scope, element, attrs)
							{
								var el = $compile(getTemplate(scope.type, scope.value))(scope);
								element.replaceWith(el);
							}
			  };
		  });