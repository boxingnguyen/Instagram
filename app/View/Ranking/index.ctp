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