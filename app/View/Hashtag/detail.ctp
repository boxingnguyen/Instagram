<div class="col-xs-12">
	<div class='col-xs-1'></div>
	<div class='col-xs-10' >
		<div id='notification'></div>
		<table class="table responstable hashtag-detail">
			<tr>
				<th class='center'>No.</th>
				<th class='center'>Media</th>
				<th class='center'>Caption</th>
				<th class='center hashtag-sort'><a class="hashtag_href" href='?hashtag=<?php echo $this->request->query['hashtag'] . '&sort=like'?>'>Likes <span class="caret"></a></th>
				<th class='center hashtag-sort'><a class="hashtag_href" href='?hashtag=<?php echo $this->request->query['hashtag'] . '&sort=comment'?>'>Comments <span class="caret"></a></th>
			</tr>

            <?php
            $count = 1;
            if(!isset($topPost)) echo "the deo nao";
                foreach ($topPost as $value) :
            ?>
            <tr>
                <td><?php echo $count; ?></td>
                <td><img src="<?php echo $value->display_src; ?>" alt="" style="width: 30%; margin: 0 auto; display: block;"></td>
                <td><?php echo $value->caption; ?></td>
                <td><?php echo $value->likes->count; ?></td>
                <td><?php echo $value->comments->count; ?></td>
            </tr>
            <?php
                $count ++;
                endforeach;
            ?>
		</table>
	</div>
	<div class='col-xs-1' ></div>
</div>
<div style="text-align: center;" >
<!--	<a href="javascript:void(0)" class="loadMore-hashtag">Load more</a>-->
</div>
<a href="#" class="scroll-top">Back to Top</a>
