
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawVisualization);

      function drawVisualization() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable([
         ['Date', 'Comment_Diff'],
         
        <?php foreach ($data as $value) { ?>
            [<?php echo "'".$value['date']."'";?> ,  <?php  echo $value['total_comments']; ?>],      
        <?php } ?> 
         
      ]);

    var options = {
      title : 'Daily amount of comment rising',
      vAxis: {title: 'Total'},
      hAxis: {title: 'Day'},
      seriesType: 'bars',
      series: {5: {type: 'line'}}
    };

    var chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
    chart.draw(data, options);
  }
    </script>
    <div id="chart_div"></div>
