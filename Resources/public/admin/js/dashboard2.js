$(document).ready(function () {

    //
    // FlotChart Line Chart
    //
    var visits = [
            [0, 5691],
            [1, 5403],
            [2, 15574],
            [3, 16211],
            [4, 16427],
            [5, 16486],
            [6, 14737],
            [7, 5838],
            [8, 5542],
            [9, 15560],
            [10, 18940],
            [11, 16970],
            [12, 17580],
            [13, 17511],
            [14, 6601],
            [15, 6158],
            [16, 17353]
            ];


    var visitors = [
            [0, 4346],
            [1, 4116],
            [2, 11356],
            [3, 11875],
            [4, 11966],
            [5, 12086],
            [6, 10916],
            [7, 4507],
            [8, 4202],
            [9, 11523],
            [10, 14431],
            [11, 12599],
            [12, 13094],
            [13, 13234],
            [14, 5213],
            [15, 4806],
            [16, 12639]
            ];


    var plotdata = [{
        data: visits,
        color: '#1F9FD4'
            }, {
        data: visitors,
        color: '#28d8b3'
            }];

    $.plot($('#line-chart'), plotdata, {
        series: {
            points: {
                show: true,
                radius: 3
            },
            lines: {
                show: true,
                lineWidth: 1,
            },
            shadowSize: 0
        },
        grid: {
            color: '#c2c2c2',
            borderColor: '#f0f0f0',
            borderWidth: 0,
            hoverable: true
        },
        yaxis: {
            autoscaleMargin: 0,
            labelWidth: 1
        }
    });

    // Tooltip

    function showTooltip(x, y, contents) {
        $('<div id="tooltip">' + contents + '</div>').css({
            top: y - 10,
            left: x + 20
        }).appendTo('body').fadeIn(200);
    }

    var previousPoint = null;

    $('#line-chart').bind('plothover', function (event, pos, item) {
        if (item) {
            if (previousPoint !== item.dataIndex) {
                previousPoint = item.dataIndex;
                $('#tooltip').remove();
                var x = item.datapoint[0],
                    y = item.datapoint[1];
                showTooltip(item.pageX, item.pageY, y + ' at ' + x);
            }
        } else {
            $('#tooltip').remove();
            previousPoint = null;
        }
    });

});
