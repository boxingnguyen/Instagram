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
			<td>
				<?php 
					echo $this->Html->link(
							number_format($value['followers']),
							array('controller' => 'Chart', 'action' => 'follower','?' => array('id' => $value['_id'])),
							array('target' => '_blank', 'class' => 'badge inst_follower')
						)
				?>
			</td>
			<td>
				<?php 
					echo $this->Html->link(
							number_format($value['media_count']),
							array('controller' => '', 'action' => 'media','?' => array('id' => $value['_id'])),
							array('class' => 'badge inst_media')
						)
				?>
			</td>
			<td>
				<?php 
					echo $this->Html->link(
							number_format($value['likes']),
							array('controller' => 'Chart', 'action' => 'like','?' => array('id' => $value['_id'])),
							array('target' => '_blank','class' =>'badge inst_like')
						);
				?>
			</td>
			<td>
				<?php 
					echo $this->Html->link(
							number_format($value['comments']), 
							array('controller' => 'Chart', 'action' => 'comment','?' => array('id' => $value['_id'])), 
							array('target' => '_blank','class' =>'badge inst_comment')
						)
				?>
			</td>
		</tr>
		<?php
		$count ++;
		endforeach;
		?>
	</table>
</div>