var instagram_data;

$(document).ready(function() {
	$.ajax({
		url: '/chart/getHashtags',
		dataType: 'json',
		type: "POST",
		success: function(data) {
			instagram_data = data;
			google.charts.load('current', {packages: ['corechart', 'bar']});
			google.charts.setOnLoadCallback(drawBasic);
		},
		error: function() {
			alert('Ajax error!');
		}
	});
});

function drawBasic() {
    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Hashtags name');
    data.addColumn('number', 'Hashtags count');
    var chart_data = [];
    var tmp_data = {};
    $.each(instagram_data, function(key, value) {
    	hashtag = value.tags[0]
    	if (typeof tmp_data[hashtag] === 'undefined') {
    		tmp_data[hashtag] = 1;
    	} else {
    		tmp_data[hashtag] += 1;
    	}
    });
    
    for (var key in tmp_data) {
    	if (tmp_data.hasOwnProperty(key)) {
    		chart_data.push([('#' + key), tmp_data[key]]);
    	}
    }

    data.addRows(chart_data);

    var options = {
      title: 'Most popular hashtags',
      vAxis: {
        title: 'Number of hashtags'
      }
    };

    var chart = new google.visualization.ColumnChart(
      document.getElementById('chart_div'));

    chart.draw(data, options);
}