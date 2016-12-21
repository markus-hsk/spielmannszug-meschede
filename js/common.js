/**
 * Created by mbuscher on 14.12.2016.
 */


function loadContent(url, params, callback)
{
	$("#mainview").hide();
	$("#loader").show();

	$.ajax({
			   url:  url,
			   data: params
		   }).done(function(data)
				   {
					   $('#mainview').html(data);
					   $("#loader").fadeOut(400, function()
					   {
						   $("#mainview").fadeIn(400);
					   });

					   if(typeof callback == 'function')
					   {
						   callback();
					   }
				   });
}


function searchMembers()
{
	var params = {
		filter: $('#search').val()
	};

	loadContent('content.php', params);
}


var members = null;
function getMembers()
{
	if(members === null)
	{
		getMembersFromServer();
		return;
	}

	var output = '';
	for(i = 0; i < members.length; i++)
	{
		var member = members[i];

		output += '<tr class="simple">' +
						'<!-- <td>' + member.MEMBER_ID + '</td> -->' +
						'<td data-label="Nachname" class="responsive_bolder">' + member.LASTNAME + member.DEAD + ' <br><span class="birthname">' + member.BIRTHNAME + '</span></td>' +
						'<td data-label="Vorname">' + member.FIRSTNAME + '</td>' +
						'<td data-label="Geschlecht" class="hideResponsiveSimple">' + member.GENDER + '</td>' +
						'<td data-label="Adresse" class="hideResponsiveSimple">' +
							'<div>' + member.STREET + ' <br>' + member.ZIP + ' ' + member.CITY + '</div>' +
						'</td>' +
						'<td data-label="Geburtstag" class="hideResponsiveSimple">' + member.BIRTHDATE + ' <br>' + member.AGE + '</td>' +
						'<td data-label="Status" class="hideResponsiveSimple">' + member.STATE + '</td>'+
						'<td data-label="Kontaktdaten" class="hideResponsiveSimple">' +
							'<div>' + member.CONTACT + '</div>' +
						'</td>' +
					'</tr>';
	}

	$('#memberlist').html(output);
}

function getMembersFromServer()
{
	$.ajax({
			   url:  'memberlist.php',
			   data: {},
		   }).done(function(data)
							  {
								  members = data;

								  getMembers();
							  });
}