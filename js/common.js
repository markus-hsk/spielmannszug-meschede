/**
 * Created by mbuscher on 14.12.2016.
 */


var current_page = '';
function loadPage()
{
	var url  = window.location.href;
	var page = url.indexOf("#") !== -1 ? url.substring(url.indexOf("#")+1) : 'mitglieder';

	if(current_page != '' && current_page == page)
		return true;

	switch(page)
	{
		default:
			console.error('Page ' + page + ' not available');

		case 'mitglieder':
			loadContent('content.php', {}, getMembers);
			break;
			
		case 'statsnow':
			loadContent('statsnow.php', {}, getCurrentStats);
			break;
	}

	current_page = page;
}

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


var members = null;
var members_sortby = 'LASTNAME ASC';
function getMembers()
{
	if(members === null)
	{
		getMembersFromServer(getMembers);
		return;
	}

	// Einträge sortieren
	sortMembers();

	// Filter ermitteln
	var search_string    = $('#search').val();
	var state_aktiv      = $('#state_aktiv').prop("checked");
	var state_passiv     = $('#state_passiv').prop("checked");
	var state_ehemalig   = $('#state_ehemalig').prop("checked");
	var state_vorstand   = $('#state_vorstand').prop("checked");
	var state_ausbildung = $('#state_ausbildung').prop("checked");
	var state_verstorben = $('#state_verstorben').prop("checked");
	var gender_male		 = $('#gender_m').prop("checked");
	var gender_female	 = $('#gender_w').prop("checked");
	var adult		 	 = $('#age_adult').prop("checked");
	var child	 		 = $('#age_child').prop("checked");

	var output = '';
	var shown_members = 0;
	for(i = 0; i < members.length; i++)
	{
		var member = members[i];

		if(search_string.length && (member.LASTNAME + ' ' + member.FIRSTNAME + ' ' + member.BIRTHNAME + ' ' + member.CITY + ' ' + member.STREET + ' ' + member.ZIP).indexOf(search_string) === -1)
			continue;

		if(member.CURRENT_STATE == 'aktiv'  	&& !state_aktiv)						continue;
		if(member.CURRENT_STATE == 'passiv' 	&& !state_passiv && !state_ehemalig)	continue;
		if(member.CURRENT_STATE == 'ehemalig'	&& !state_ehemalig)						continue;
		if(member.CURRENT_STATE == 'Vorstand'	&& !state_aktiv && !state_vorstand)		continue;
		if(member.CURRENT_STATE == 'Verstorben'	&& !state_verstorben)					continue;
		if(member.CURRENT_STATE == 'Ausbildung'	&& !state_ausbildung)					continue;

		if(member.GENDER == 'w' && !gender_female)										continue;
		if(member.GENDER == 'm' && !gender_male)										continue;

		var member_id = member.MEMBER_ID;
		var lastname = member.LASTNAME;
		var firstname = member.FIRSTNAME;
		var birthname = member.BIRTHNAME;
		var street = member.STREET;
		var zip = member.ZIP;
		var city = member.CITY;

		if(member.GENDER == 'w')
			var gendersign = '<i class="fa fa-venus female" aria-hidden="true" aria-label="weiblich"></i>';
		else
			var gendersign = '<i class="fa fa-mars male" aria-hidden="true" aria-label="männlich"></i>';

		var birthdate = (member.BIRTHDATE != null && member.BIRTHDATE.length && member.BIRTHDATE != '0000-00-00') ? new Date(member.BIRTHDATE) : null;
		var birthdate_output = birthdate != null ? birthdate.getDate() + '.' + (birthdate.getMonth()+1) + '.' + birthdate.getFullYear() : '<i>unbekannt</i>';

		if(member.DEATHDATE == null || !member.DEATHDATE.length || member.DEATHDATE == '0000-00-00')
		{
			var age = birthdate != null ? getAge(birthdate, null) : 999;
			var age_output = birthdate != null ? '(' + getAge(birthdate, null) + ')' : '';
			var deadsign = '';
			var state = member.CURRENT_STATE;
		}
		else
		{
			var deathdate = new Date(member.DEATHDATE);
			var deathdate_output = deathdate.getDate() + '.' + (deathdate.getMonth()+1) + '.' + deathdate.getFullYear();
			var deadsign = ' <b>&dagger;</b>';

			if(birthdate != null)
			{
				birthdate_output += ' -';
				var age = getAge(birthdate, deathdate);
				var age_output = deathdate_output + ' (' + getAge(birthdate, deathdate) + ')';
			}
			else
			{
				var age = 999;
				var age_output = '- ' + deathdate_output;
			}

			var state = 'verstorben';
		}

		if(age >= 18 && !adult)		continue;
		if(age < 18  && !child)		continue;

		var contact = '';

		output += '<tr class="simple">' +
						'<!-- <td>' + member_id + '</td> -->' +
						'<td data-label="Nachname" class="responsive_bolder">' + lastname + deadsign + ' <br><span class="birthname">' + birthname + '</span></td>' +
						'<td data-label="Vorname">' + firstname + '</td>' +
						'<td data-label="Geschlecht" class="hideResponsiveSimple">' + gendersign + '</td>' +
						'<td data-label="Adresse" class="hideResponsiveSimple">' +
							'<div>' + street + ' <br>' + zip + ' ' + city + '</div>' +
						'</td>' +
						'<td data-label="Geburtstag" class="hideResponsiveSimple">' + birthdate_output + ' <br>' + age + '</td>' +
						'<td data-label="Status" class="hideResponsiveSimple">' + state + '</td>'+
						'<td data-label="Kontaktdaten" class="hideResponsiveSimple">' +
							'<div>' + contact + '</div>' +
						'</td>' +
					'</tr>';

		shown_members++;
	}

	$('#memberlist').html(output);
	$('#footer_container').html('Anzahl Mitglieder: ' + shown_members + ' von ' + members.length);
}

function getMembersFromServer(callback)
{
	$.ajax({
			   url:  'memberlist.php',
			   data: {}
		   }).done(function(data)
							  {
								  members = data;

								  callback();
							  });
}

function sortMembers()
{
	members.sort(function(a,b)
				 {
					 var sortby = members_sortby.split(' ');
					 var result = 0;

					 switch(sortby[0])
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
					 }

					 if(sortby[1] == 'DESC')
					 	result = result * -1;

					 // Sortierlink anpassen
					 $('.sorter').removeClass('active_sorter');
					 $('.sorter i.sortindicator').addClass('fa-sort');
					 $('.sorter i.sortindicator').removeClass('fa-sort-asc');
					 $('.sorter i.sortindicator').removeClass('fa-sort-desc');

					 $('#SORTER_' + sortby[0]).addClass('active_sorter');

					 if(sortby[1] == 'ASC')
						 $('#SORTER_' + sortby[0] + ' i.sortindicator').addClass('fa-sort-asc');
					 else
						 $('#SORTER_' + sortby[0] + ' i.sortindicator').addClass('fa-sort-desc');

					 return result;
				 });
}

function membersSortBy(sortby)
{
	var old_sortby = members_sortby.split(' ');

	if(old_sortby[0] == sortby && old_sortby[1] == 'ASC')
		members_sortby = sortby + ' DESC';
	else
		members_sortby = sortby + ' ASC';

	getMembers();
}

function getAge(birthdate, until)
{
	if(until == null)
		until = new Date();

	var ageDifMs = until.getTime() - birthdate.getTime();
    var ageDate = new Date(ageDifMs); // miliseconds from epoch
    return Math.abs(ageDate.getUTCFullYear() - 1970);
}



function getCurrentStats()
{
	if(members === null)
	{
		getMembersFromServer(getCurrentStats);
		return;
	}

	
	var state_aktiv      = $('#state_aktiv').prop("checked");
	var state_passiv     = $('#state_passiv').prop("checked");
	var state_ehemalig   = $('#state_ehemalig').prop("checked");
	var state_vorstand   = $('#state_vorstand').prop("checked");
	var state_ausbildung = $('#state_ausbildung').prop("checked");
	var state_verstorben = $('#state_verstorben').prop("checked");
	
	if($('#mode_gender').prop("checked"))
		var mode = 'gender'
	else
		var mode = 'age';
	
	var total = 0;
	var female = 0;
	var male = 0;
	var adult = 0;
	var child = 0;
	var total_age = 0;
	
	for(i = 0; i < members.length; i++)
	{
		var member = members[i];

		if(member.CURRENT_STATE == 'aktiv'  	&& !state_aktiv)						continue;
		if(member.CURRENT_STATE == 'passiv' 	&& !state_passiv && !state_ehemalig)	continue;
		if(member.CURRENT_STATE == 'ehemalig'	&& !state_ehemalig)						continue;
		if(member.CURRENT_STATE == 'Vorstand'	&& !state_aktiv && !state_vorstand)		continue;
		if(member.CURRENT_STATE == 'Verstorben'	&& !state_verstorben)					continue;
		if(member.CURRENT_STATE == 'Ausbildung'	&& !state_ausbildung)					continue;

		total++;
		
		if(member.GENDER == 'w')
			female++;
		else
			male++;
		
		if(member.AGE > 18)
			adult++;
		else
			child++;
		total_age = total_age + member.AGE; 
	}
	
	var avg_age = Math.round(total_age / total, 1);
	
	switch(mode)
	{
		case 'gender':
			var title = 'Geschlechterverteilung';
			var subtitle = '';
			var data = [{ name: 'Frauen', y: female, color: '#0000FF' },{ name: 'Männer', y: male, color: '#FF0000'}];
			break;
			
		case 'age':
			var title = 'Altersverteilung';
			var subtitle = 'Durchschnitt: ' + avg_age + ' Jahre';
			var data = [{ name: 'Erwachsen', y: adult, color: '#0000FF' },{ name: 'Kind', y: child, color: '#FF0000'}];
			break;
	}
	
	
	Highcharts.chart('chartcontainer', {
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie',
            height: 300
        },
        title: {
            text: title
        },
        subtitle: {
            text: subtitle
        },
        tooltip: {
            pointFormat: '{point.y:.0f} von {point.total:.0f} = {point.percentage:.1f}%'
        },
        plotOptions: {
            pie: {
                allowPointSelect: false,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '{point.name}<br>{point.y:.0f}/{point.total:.0f}<br>{point.percentage:.1f}%',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    }
                }
            }
        },
        series: [{
            name: 'Verteilung',
            colorByPoint: true,
            data: data
        }]
    });
}