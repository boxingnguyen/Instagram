<html>
  <head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawVisualization);

      function drawVisualization() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable([
         ['Date', 'Comments'],
         
      		<?php if(isset($dataComments) && !empty($dataComments)) {?>
         	<?php foreach ($dataComments as $key => $val) { ?>
         			[<?php echo "'".$key. "'";?>,  <?php echo $val ; ?>],
         	<?php } } ?>
         
      ]);

    var options = {
      title : 'Monthly Instagram Comment',
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