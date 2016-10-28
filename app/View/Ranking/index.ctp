<script type="text/javascript">
	
	$(function () {
	var page = -1;
	var limit = 20;
	$('#loadMore').click(function(){
		page++;
		$('#loadMore').html('Loading...');
		$.ajax({
			method: "POST",
			url: '/Ranking/index',
			dataType: 'json',
			data:{
				page: page
			},
			success: function(data){
			                console.log(data);
			                	}
				
				
		
		});
		
	});
});
	$('#loadMore').click();
</script>
<div class='col-xs-3' ></div>
<div class='col-xs-6' >
	<table class="table responstable">
	
		<tr>
			<th class='center'>No.</th>
			<th class='center'>Fullname</th>
			<th class='center'>Username</th>
			<th class='center'>Followers</th>
		</tr>
	
	<?php $i = 0; ?>
	<?php foreach($data as $val) {  ?>
		<tr class='center'>
			<td><?php echo $i ?></td>
			<td><?php echo $val['full_name']?></td>
			<td><?php echo $val['username']?></td>
			<td><?php echo $val['totalFollow']?></td>
		</tr>
	<?php $i++; } ?>

	</table>
</div>
<div class='col-xs-12' >
	<button id="loadMore">LOAD MORE</button>
</div>