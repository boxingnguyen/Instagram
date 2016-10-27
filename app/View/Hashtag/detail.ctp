<?php 
	if (isset($data[0]['tag_name'])):
	$hashtag = $data[0]['tag_name'];
	$i = 0;
?>
	<div class="col-xs-12">
		<div class="col-xs-1"></div>
		<div class="col-xs-4"><h3>Top 100 media of #<?php echo $data[0]['tag_name'];?></h3></div>
		<div class="col-xs-7"></div>
	</div>
	<div class="col-xs-12">
		<div class='col-xs-1'></div>
		<div class='col-xs-10' >
			<table class="table responstable">
				<tr>
					<th class='center'>No.</th>
					<th class='center'>Media URL</th>
					<th class='center'>Caption</th>
					<th class='center'><a class="hashtag_href" href='/<?php echo $this->params['controller'] . '/' . $this->params['action'] . '?hashtag=' . $hashtag . '&sort=like'?>'>Like</a></th>
					<th class='center'><a class="hashtag_href" href='/<?php echo $this->params['controller'] . '/' . $this->params['action'] . '?hashtag=' . $hashtag . '&sort=comment'?>'>Comment</a></th>
				</tr>
				<?php foreach ($data as $media) :
					$i ++;
				?>
				<tr class='center'>
					<td><?php echo $i?></td>
					<td><a target = "_blank" href="https://instagram.com/p/<?php echo $media['code']?>"><?php echo "https://instagram.com/p/" . $media['code']?></td>
					<td><?php echo $media['caption']?></td>
					<td><?php echo $media['likes']['count']?></td>
					<td><?php echo $media['comments']['count']?></td>
				</tr>
				<?php endforeach; ?>
			</table>
		</div>
		<div class='col-xs-1' ></div>
	</div>
<?php endif;?>