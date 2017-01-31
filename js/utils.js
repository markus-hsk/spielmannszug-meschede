/**
 * Created by mbuscher on 27.01.2017.
 */

var debugSpzDbEnabled = true;
function debugSpzDb()
{
	if(debugSpzDbEnabled)
	{
		console.log.apply(console, arguments);
	}
}

function roundDecimal(value, decimals)
{
	return +((value).toFixed(decimals));
}


function formatTelephoneNumber(telephone_number)
{
	var str = telephone_number.split('/');

	str[0] = str[0].trim();
	str[1] = str[1].trim();

	return str[0] + ' / ' + str[1];
}