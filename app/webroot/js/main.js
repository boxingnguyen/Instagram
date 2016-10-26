$(document).ready(function() {
	$('.toggle-group .btn').click(function() {
		var controller = window.location.pathname.split("/")[1];
		if (controller == 'top') {
			window.location.replace('/hashtag');
		} else if (controller == 'hashtag') {
			window.location.replace('/top');
		}
	});
	
	// sort ranking hashtag by like, comment
	$('.rank-by-like').click(function() {
		window.location.replace('./hashtag?sort=like');
	});
	$('.rank-by-comment').click(function() {
		window.location.replace('./hashtag?sort=comment');
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