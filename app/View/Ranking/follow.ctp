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
<div class='col-xs-3' ></div>
<footer class="footer col-xs-12">

	<div style="text-align: center;" >
		<a href="javascript:void(0)" class="cd-read-more loadMore ">Load more</a>
	</div>
	<a href="#" class="back-to-top" style = "display: inline;"> Back to Top</a>
	<style> 
		a.back-to-top { 
 			display: none; 
			width: 60px; 
			height: 60px; 
			text-indent: -9999px; 
			position: fixed; 
			z-index: 999; 
			right: 20px; 
			bottom: 20px; 
			background: #303E49 url("/img/arrow_up.png") no-repeat center; 
			background-size: 90%; 
			-webkit-border-radius: 30px; 
			-moz-border-radius: 30px; 
		border-radius: 30px; 
	} 
	</style>
 </footer>
	