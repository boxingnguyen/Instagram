<div style = "float:right">
	<button type="button" class="buttonReg" data-toggle="modal" data-target="#myModal">Register</button>
	
	<!-- Modal -->
	<div class="modal fade " id="myModal"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-body">
	       <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <div class="form">
			    <form class="register-form">
			      <input type="text"  type="text" class="form-control" placeholder="name"/>
			      <p class="message"><i>Example: https://www.instagram.com/instagram/</i></p>
			      <button type="button" class="modalReg"><b>REGIST</b></button>
			    </form>
			  </div>
	      </div>
	    </div>
	  </div>
	</div>
</div>
<div class='col-xs-12'>
	<table class="table responstable">
		<tr>
			<th class='center'>No.</th>
			<th class='center'>Username</th>
			<th class='center'>Followers</th>
			<th class='center'>Total media</th>
			<th class='center'>Media get</th>
			<th class='center'>Status</th>
			<th class='center'>Total likes</th>
			<th class='center'>Total comments</th>
		</tr>
		<?php
		$count = 1;
		foreach ($data as $value) : 
		?>
		<tr class='center'>
			<td><a class="badge inst_order" href="javascript:void(0)"><?php echo $count; ?></a></td>
			<td><a href="https://www.instagram.com/<?php echo $value['username']; ?>" target="_blank"><?php echo ($value['fullname'] != '') ? $value['fullname'] : $value['username']; ?></a></td>
			<td>
				<?php 
					echo $this->Html->link(
							number_format($value['followers']),
							array('controller' => 'Chart', 'action' => 'follower','?' => array('id' => $value['_id'])),
							array('target' => '_blank')
						)
				?>
			</td>
			<td>
				<?php 
					echo $this->Html->link(
							number_format($value['media_count']),
							array('controller' => '', 'action' => 'media','?' => array('id' => $value['_id'])),
							array('target' => '_blank')
						)
				?>
			</td>
			<td><?php echo number_format($value['media_get']) . " (" . round($percentage = ($value['media_get'] / $value['media_count'] * 100), 2) . "%)"?></td>
			<td>
				<?php 
				if ($percentage == 100) {
					$image = 'right.png';				
				} else if ($percentage >= 90) {
					$image = 'warning.png';
				} else {
					$image = 'wrong.png';
				}
				echo $this->Html->image($image, array('height' => 15, 'width' => 15));
				?>
			</td>
			<td>
				<?php 
					echo $this->Html->link(
							number_format($value['likes']),
							array('controller' => 'Chart', 'action' => 'like','?' => array('id' => $value['_id'])),
							array('target' => '_blank')
						);
				?>
			</td>
			<td>
				<?php 
					echo $this->Html->link(
							number_format($value['comments']), 
							array('controller' => 'Chart', 'action' => 'comment','?' => array('id' => $value['_id'])), 
							array('target' => '_blank')
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