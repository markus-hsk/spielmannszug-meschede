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