<div class="col-xs-12">
	<div class='col-xs-1'></div>
	<div class='col-xs-10' >
		<div id='notification'></div>
		<table class="table responstable hashtag-detail">
			<tr>
				<th class='center'>No.</th>
				<th class='center'>Media URL</th>
				<th class='center'>Caption</th>
				<th class='center'><a class="hashtag_href" href='?hashtag=<?php echo $this->request->query['hashtag'] . '&sort=like'?>'>Like</a></th>
				<th class='center'><a class="hashtag_href" href='?hashtag=<?php echo $this->request->query['hashtag'] . '&sort=comment'?>'>Comment</a></th>
			</tr>
		</table>
	</div>
	<div class='col-xs-1' ></div>
</div>
<div style="text-align: center;" >
	<a href="javascript:void(0)" class="loadMore-hashtag">Load more</a>
</div>
<a href="#" class="back-to-top">Back to Top</a>
