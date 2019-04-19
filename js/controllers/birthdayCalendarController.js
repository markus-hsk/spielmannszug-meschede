/**
 * Created by mbuscher on 19.04.2019
 */


angular.module('spzdb'	// So heißt die App
)


    .controller('birthdayCalendarController', ['$scope', '$routeParams', 'memberService',

        function (me, _GET, memberService) {
            debugSpzDb('birthdayCalendarController Initialize');

            me.filter_open = false;
            me.filters = {};
            me.filters.search = '';
            me.filters.state = 'Geburtstagsliste';
            me.filters.gender_w = 1;
            me.filters.gender_m = 1;
            me.filters.age_adult = 1;
            me.filters.age_child = 1;
            me.filters.instrument = 'all';

            me.currentYear = new Date().getFullYear();

            me.load = function () {
                debugSpzDb('birthdayCalendarController->load() Call');

                $("#mainview").hide();
                $("#loader").show();

                me.table_data = [];

                memberService.load(function () {
                        $("#mainview").show();
                        $("#loader").hide();

                        var members = memberService.getList(me.filters, 'LASTNAME ASC');

                        var data_source = [];
                        for (var i = 0; i < members.length; i++) {
                            var member = members[i];
                            var birthdate = new Date(member.BIRTHDATE);
                            var birthday = new Date(me.currentYear, birthdate.getMonth(), birthdate.getDate());
                            var age = birthday.getFullYear() - birthdate.getFullYear();

                            data_source.push({
                                id: i,
                                name: member.FIRSTNAME + ' ' + member.LASTNAME + ' (' + age + ')',
                                startDate: birthday,
                                endDate: birthday
                            });
                        }

                        me.putDates(data_source);
                    }
                );
            };

            me.putDates = function (data_source)
            {
                debugSpzDb('birthdayCalendarController->putDates() Call', data_source);

                var today = new Date();

                $('#calendar').calendar({
                    style:'custom',
                    displayWeekNumber: false,
                    enableRangeSelection: false,
                    language: 'de',
                    minDate: new Date(me.currentYear, 0, 1),
                    maxDate: new Date(me.currentYear, 11, 31),
                    startYear: me.currentYear,
                    dataSource: data_source,
                    customDataSourceRenderer: function(element, date, events) {
                        $(element).css('border', '1px solid red');
                    },
                    customDayRenderer: function(element, date) {
                        if(date.toDateString() == today.toDateString()) {
                            $(element).css('background-color', '#fbeead');
                            $(element).css('font-weight', 'bold');
                            //$(element).css('border-radius', '15px');
                        }},
                    mouseOnDay: function(e) {
                        if(e.events.length > 0) {
                            var content = '';

                            for(var i in e.events) {
                                content += '<div class="event-tooltip-content">'
                                    + '<div class="event-name">' + e.events[i].name + '</div>'
                                    + '</div>';
                            }

                            $(e.element).popover({
                                trigger: 'manual',
                                container: 'body',
                                html:true,
                                content: content
                            });

                            $(e.element).popover('show');
                        }
                    },
                    mouseOutDay: function(e) {
                        if(e.events.length > 0) {
                            $(e.element).popover('hide');
                        }
                    },
                    dayContextMenu: function(e) {
                        $(e.element).popover('hide');
                    }
                });

                $('#calendar .calendar-header').remove();
            };

            me.load();
        }]);
