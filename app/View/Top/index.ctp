<div class='col-xs-12'>
	<table class="table">
		<th class='center'>No.</th>
		<th class='center'>Username</th>
		<th class='center'>Followers</th>
		<th class='center'>Media</th>
		<th class='center'>Total likes</th>
		<th class='center'>Total comments</th>
		<?php
		$count = 1;
		foreach ($data as $value) : 
		?>
		<tr class='center'>
			<td><a class="badge inst_order" href="#"><?php echo $count; ?></a></td>
			<td><a class="badge inst_username" href="https://www.instagram.com/<?php echo $value['username']; ?>" target="_blank"><?php echo ($value['fullname'] != '') ? $value['fullname'] : $value['username']; ?></a></td>
			<td><a class="badge inst_follower" href="/follower?id=<?php echo $value['_id']; ?>"><?php echo number_format($value['followers']); ?></a></td>
			<td><a class="badge inst_media" href="/media?id=<?php echo $value['_id']; ?>"><?php echo number_format($value['media_count']); ?></a></td>
			<td><a class="badge inst_like" href="/like?id=<?php echo $value['_id']; ?>"><?php echo number_format($value['likes']); ?></a></td>
			<td><a class="badge inst_comment" href="/comment?id=<?php echo $value['_id']; ?>"><?php echo number_format($value['comments']); ?></a></td>
		</tr>
		<?php
		$count ++;
		endforeach;
		?>
	</table>
</div>