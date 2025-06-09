(function ($)
{
    "use strict";

    $(document).ready(function()
    {
        let chart_reports_by_the_number_of_appointments;
        let chart_reports_by_appointment_earnings;
        let chart_most_earning_locations;
        let chart_most_earning_staffs;

        let colors = [
            '#FFD17A',
            '#DBC9FF',
            '#85CEF9',
            '#9FFFB2',
            'rgb(242, 210, 73)',
            'rgb(147, 185, 198)',
            'rgb(204, 197, 168)',
            'rgb(83, 186, 205)',
            'rgb(156, 196, 45)',
            'rgb(152, 170, 252)',
            'rgb(97, 166, 86)',
            'rgb(225, 124, 36)',
            'rgb(210, 32, 48)'
        ];

        function formatDateTime(value, type) {
            const locale = ($('#reports-locale').val() || 'en-US').replace('_', '-');

            try {
                let year, month, day;

                if (type === 'daily') {
                    [year, month, day] = value.split('-').map(n => parseInt(n, 10));
                    month = month - 1;
                } else if (type === 'monthly') {
                    const months = {
                        'January': 0, 'February': 1, 'March': 2, 'April': 3,
                        'May': 4, 'June': 5, 'July': 6, 'August': 7,
                        'September': 8, 'October': 9, 'November': 10, 'December': 11
                    };

                    const [monthStr, yearStr] = value.split(',').map(s => s.trim());
                    year = parseInt(yearStr, 10);
                    month = months[monthStr];
                    day = 1;
                } else if (type === 'annually') {
                    year = parseInt(value, 10);
                    month = 0;
                    day = 1;
                }

                if (isNaN(year) || isNaN(month) || (day && isNaN(day))) {
                    new Error('Invalid date components');
                }

                // This avoids Safari's date parsing quirks
                const utcDate = new Date(0);
                utcDate.setUTCFullYear(year);
                utcDate.setUTCMonth(month);
                utcDate.setUTCDate(day || 1);
                utcDate.setUTCHours(0, 0, 0, 0);

                const options = {
                    timeZone: 'UTC', // Force UTC to prevent any timezone shifts
                    ...type === 'daily' ? {
                        month: 'short',
                        day: '2-digit',
                        year: '2-digit'
                    } : type === 'monthly' ? {
                        month: 'short',
                        year: 'numeric'
                    } : {
                        year: 'numeric'
                    }
                };

                return new Intl.DateTimeFormat(locale, options).format(utcDate);

            } catch (error) {
                console.error('Date formatting failed:', {
                    error,
                    value,
                    type,
                    locale
                });
                return 'Invalid Date';
            }
        }

        $(document).on('click', '[data-appointment-report-via-count-type]', function()
        {
            const type = $(this).attr('data-appointment-report-via-count-type');

            $(this).parent().prev().children('span').text( $(this).text() );

            let filters = {};
            $(this).closest('.fs_portlet').find('select[data-filter]').each(function()
            {
                let filter_name = $(this).data('filter'),
                    filter_val  = $(this).val();

                filters[ filter_name ] = filter_val;
            });

            $(this).parent().children('.selected-item').removeClass('selected-item');
            $(this).addClass('selected-item');

            booknetic.ajax('Reports.get_appointment_report_via_count', {type, filters}, function(response)
            {
                if ( chart_reports_by_the_number_of_appointments )
                {
                    chart_reports_by_the_number_of_appointments.destroy();
                }

                let bgColors = [];

                for ( let i = 0; i <= response.response.values.length; i++ )
                {
                    bgColors.push( colors[ i % colors.length ] );
                }

                var ctx = document.getElementById('appointment-count').getContext('2d');
                chart_reports_by_the_number_of_appointments = new Chart( ctx, {
                    type: 'bar',
                    data: {
                        labels: response.response.labels.map(label => formatDateTime(label, type)),
                        datasets: [ {
                            data: response.response.values,
                            backgroundColor: bgColors,
                            borderColor: bgColors,
                            borderWidth: 1
                        } ]
                    },
                    options: {
                        legend: {
                            display: false
                        },
                        scales: {
                            yAxes: [ {
                                ticks: {
                                    beginAtZero: true
                                }
                            } ]
                        },
                        gridLines: {
                            color: '#E3EAF3'
                        }
                    }
                } );

            });

        }).on('click', '[data-appointment-report-via-price-type]', function()
        {
            const type = $(this).attr('data-appointment-report-via-price-type');

            $(this).parent().prev().children('span').text( $(this).text() );

            let filters = {};
            $(this).closest('.fs_portlet').find('select[data-filter]').each(function()
            {
                let filter_name = $(this).data('filter'),
                    filter_val  = $(this).val();

                filters[ filter_name ] = filter_val;
            });

            $(this).parent().children('.selected-item').removeClass('selected-item');
            $(this).addClass('selected-item');

            booknetic.ajax('Reports.get_appointment_report_via_price', {type, filters}, function(response)
            {
                if ( chart_reports_by_appointment_earnings )
                {
                    chart_reports_by_appointment_earnings.destroy();
                }

                let ctx = document.getElementById( 'appointment-price' ).getContext( '2d' );
                chart_reports_by_appointment_earnings = new Chart( ctx, {
                    type: 'line',
                    data: {
                        labels: response.response.labels.map(label => formatDateTime(label, type)),
                        datasets: [ {
                            data: response.response.values,
                            fill: true,
                            borderColor: "#5DD775",
                            borderWidth: 2,
                            backgroundColor: "#eefbf1",
                            pointBackgroundColor: '#5DD775',
                            pointRadius: 4,
                            pointHoverBackgroundColor: "#eefbf1",
                            pointHoverRadius: 4,
                            pointHoverBorderWidth: 2,
                            pointHoverBorderColor: '#5DD775'
                        } ]
                    },
                    options: {
                        legend: {
                            display: false
                        },
                        scales: {
                            yAxes: [ {
                                ticks: {
                                    beginAtZero: true
                                },
                                gridLines: {
                                    color: '#E3EAF3'
                                }
                            } ]
                        }
                    }
                } );

            });
        }).on('click', '[data-report-by-location-type]', function()
        {
            const type = $(this).attr('data-report-by-location-type');

            $(this).parent().prev().children('span').text( $(this).text() );

            booknetic.ajax('Reports.get_location_report', {'type': type}, function(response)
            {
                if ( chart_most_earning_locations )
                {
                    chart_most_earning_locations.destroy();
                }

                let bgColors = [];

                for ( let i = 0; i <= response.response.values.length; i++ )
                {
                    bgColors.push( colors[ i % colors.length ] );
                }

                let ctx = document.getElementById( 'location-report' ).getContext( '2d' );

                chart_most_earning_locations = new Chart( ctx, {
                    type: 'doughnut',
                    data: {
                        datasets: [ {
                            backgroundColor: bgColors,
                            data: response.response.values
                        } ],

                        labels: response.response.labels
                    },
                    options: {
                        legend: {
                            position: 'right',

                            labels: {
                                usePointStyle: true,
                                generateLabels: function (chart)
                                {
                                    var data = chart.data;
                                    if ( data.labels.length && data.datasets.length )
                                    {
                                        return data.labels.map( function (label, i) {
                                            var meta = chart.getDatasetMeta( 0 );
                                            var ds = data.datasets[ 0 ];
                                            var arc = meta.data[ i ];
                                            var custom = arc && arc.custom || {};
                                            var getValueAtIndexOrDefault = Chart.helpers.getValueAtIndexOrDefault;
                                            var arcOpts = chart.options.elements.arc;
                                            var fill = custom.backgroundColor ? custom.backgroundColor : getValueAtIndexOrDefault( ds.backgroundColor, i, arcOpts.backgroundColor );
                                            var stroke = custom.borderColor ? custom.borderColor : getValueAtIndexOrDefault( ds.borderColor, i, arcOpts.borderColor );
                                            var bw = custom.borderWidth ? custom.borderWidth : getValueAtIndexOrDefault( ds.borderWidth, i, arcOpts.borderWidth );

                                            var value = chart.config.data.datasets[ arc._datasetIndex ].data[ arc._index ];

                                            return {
                                                // Instead of `text: label,`
                                                // We add the value to the string
                                                text: label + " : " + value,
                                                fillStyle: fill,
                                                strokeStyle: stroke,
                                                lineWidth: bw,
                                                hidden: isNaN( ds.data[ i ] ) || meta.data[ i ].hidden,
                                                index: i
                                            };
                                        } );
                                    }
                                    else
                                    {
                                        return [];
                                    }
                                }
                            }


                        },
                        responsive: true
                    }
                } );

            });
        }).on('click', '[data-report-by-staff-type]', function()
        {
            const type = $(this).attr('data-report-by-staff-type');

            $(this).parent().prev().children('span').text( $(this).text() );

            booknetic.ajax('Reports.get_staff_report', {'type': type}, function(response)
            {
                if ( chart_most_earning_staffs )
                {
                    chart_most_earning_staffs.destroy();
                }

                let bgColors = [];

                for ( let i = 0; i <= response.response.values.length; i++ )
                {
                    bgColors.push( colors[ i % colors.length ] );
                }

                let ctx = document.getElementById( 'staff-report' ).getContext( '2d' );

                chart_most_earning_staffs = new Chart( ctx, {
                    type: 'doughnut',
                    data: {
                        datasets: [ {
                            backgroundColor: bgColors,
                            data: response.response.values
                        } ],

                        labels: response.response.labels
                    },
                    options: {
                        legend: {
                            position: 'right',

                            labels: {
                                usePointStyle: true,
                                generateLabels: function (chart)
                                {
                                    var data = chart.data;
                                    if ( data.labels.length && data.datasets.length )
                                    {
                                        return data.labels.map( function (label, i) {
                                            var meta = chart.getDatasetMeta( 0 );
                                            var ds = data.datasets[ 0 ];
                                            var arc = meta.data[ i ];
                                            var custom = arc && arc.custom || {};
                                            var getValueAtIndexOrDefault = Chart.helpers.getValueAtIndexOrDefault;
                                            var arcOpts = chart.options.elements.arc;
                                            var fill = custom.backgroundColor ? custom.backgroundColor : getValueAtIndexOrDefault( ds.backgroundColor, i, arcOpts.backgroundColor );
                                            var stroke = custom.borderColor ? custom.borderColor : getValueAtIndexOrDefault( ds.borderColor, i, arcOpts.borderColor );
                                            var bw = custom.borderWidth ? custom.borderWidth : getValueAtIndexOrDefault( ds.borderWidth, i, arcOpts.borderWidth );

                                            var value = chart.config.data.datasets[ arc._datasetIndex ].data[ arc._index ];

                                            return {
                                                // Instead of `text: label,`
                                                // We add the value to the string
                                                text: label + " : " + value,
                                                fillStyle: fill,
                                                strokeStyle: stroke,
                                                lineWidth: bw,
                                                hidden: isNaN( ds.data[ i ] ) || meta.data[ i ].hidden,
                                                index: i
                                            };
                                        } );
                                    }
                                    else
                                    {
                                        return [];
                                    }
                                }
                            }


                        },
                        responsive: true
                    }
                } );
            });
        });

        $(document).find('[data-appointment-report-via-count-type]:eq(0)').click();
        $(document).find('[data-appointment-report-via-price-type]:eq(0)').click();
        $(document).find('[data-report-by-location-type]:eq(0)').click();
        $(document).find('[data-report-by-staff-type]:eq(0)').click();

        $('.fs_portlet_content select[data-filter]').select2({
            theme: 'bootstrap',
            allowClear: true
        }).on('change', function ()
        {
            $(this).closest('.fs_portlet').find('.dropdown-item.selected-item').click();
        });

    });

})(jQuery);
