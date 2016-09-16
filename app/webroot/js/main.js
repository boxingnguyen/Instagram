var instagram_data;

$(document).ready(function() {
	$.ajax({
		url: '/chart/getReaction',
		dataType: 'json',
		type: "POST",
		success: function(data) {
			for (var i = 0; i < data.length; i++) {
				for (var j = i; j < data.length; j++) {
					if (data[i].reaction < data[j].reaction) {
						tmp_obj = data[i];
						data[i] = data[j];
						data[j] = tmp_obj;
					}
				}
			}
			tmp_data = [];
			var header = ['Username', 'Total Reaction', 'Likes', 'Comments'];
			tmp_data.push(header);
			$.each (data, function(key, value) {
				var row = [value.username, value.reaction, value.total_likes, value.total_comments]
				tmp_data.push(row);
			});
			instagram_data = tmp_data;
			google.charts.load('current', {'packages':['bar']});
			google.charts.setOnLoadCallback(drawChart);
		},
		error: function() {
			alert('Ajax error!');
		}
	});
});

function drawChart() {
    var data = google.visualization.arrayToDataTable(instagram_data);
    var options = {
      chart: {
        title: 'Number of Likes/Comments'
      },
      bars: 'vertical',
      vAxis: {format: 'decimal'},
      height: 500,
      colors: ['#12922e', '#d02020', '#1500ff']
    };

    var chart = new google.charts.Bar(document.getElementById('chart_div'));

    chart.draw(data, google.charts.Bar.convertOptions(options));

  }