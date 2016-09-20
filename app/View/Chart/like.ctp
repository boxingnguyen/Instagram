<html>
  <head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawVisualization);

      function drawVisualization() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable([
         ['Date', 'Likes'],
         
        <?php foreach ($dataLikes as $key => $val) { ?>
        		[<?php echo "'".$key."'";?> ,  <?php  echo $val; ?>],
               
        <?php } ?>
         
         
//          ['2005/06',  135,      1120,        599],
//          ['2006/07',  157,      1167,        587],
//          ['2007/08',  139,      1110,        615],
//          ['2008/09',  136,      691,         629]
         
      ]);

    var options = {
      title : 'Monthly Instagram Likes',
      vAxis: {title: 'Total'},
      hAxis: {title: 'Day'},
      seriesType: 'bars',
      series: {5: {type: 'line'}}
    };

    var chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
    chart.draw(data, options);
  }
    </script>
  </head>
  <body>
    <div id="chart_div" style="width: 900px; height: 500px;"></div>
  </body>
</html>