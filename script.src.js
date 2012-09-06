(function($){
    $.fn.equalHeights = function() {
        var currentTallest = 0;
        $(this).each(function(){
            if ($(this).height() > currentTallest) {
                currentTallest = $(this).height();
            }
        });
        $(this).height(currentTallest);
        return this;
    };
    $(".half").equalHeights();

        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'booster-chart',
                zoomType: 'xy'
            },
            title: {
                text: 'WP Booster Statistical Information'
            },
            xAxis: [{
                categories: categories
            }],
            yAxis: [{ // Primary yAxis
                labels: {
                    formatter: function() {
                        return this.value +' MB';
                    },
                    style: {
                        color: '#89A54E'
                    }
                },
                title: {
                    text: 'Transfer',
                    style: {
                        color: '#89A54E'
                    }
                },
                opposite: true
            }, { // Secondary yAxis
                gridLineWidth: 0,
                title: {
                    text: 'Requests',
                    style: {
                        color: '#4572A7'
                    }
                },
                labels: {
                    formatter: function() {
                        return this.value +' req';
                    },
                    style: {
                        color: '#4572A7'
                    }
                }
            }],
            tooltip: {
                formatter: function() {
                    var unit = {
                        'Requests': 'req',
                        'Transfer': 'MB'
                    }[this.series.name];
                    return ''+
                        this.x +': '+ this.y +' '+ unit;
                }
            },
            legend: {
                layout: 'vertical',
                align: 'left',
                x: 120,
                verticalAlign: 'top',
                y: 80,
                floating: true,
                backgroundColor: '#FFFFFF'
            },
            series: [{
                name: 'Requests',
                color: '#4572A7',
                type: 'spline',
                yAxis: 1,
                data: requests
            }, {
                name: 'Transfer',
                color: '#89A54E',
                type: 'spline',
                data: transfers
            }]
        });
})(jQuery);

