<?php echo $this->element('switch_top_hashtag'); ?>
<div style = "float:right;display: inline-flex;">
	<button type="button" class="buttonHead buttonReg" data-toggle="modal" data-target="#myModal">Register</button>
	
	<!-- Modal -->
	<div class="modal fade " id="myModal"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-body">
	       <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <div class="form">
			      <input type="text"  type="text" id="inputHashtag" class="form-control" placeholder="#instagram"/>
			      <p class="message">Example: #instagram</p>
			      <button class="modalRegTag" data-toggle="modal" data-target="#regisForm"><b>REGIST</b></button>
			  </div>
	      </div>
	    </div>
	  </div>
	  
	</div>
	<div class="modal fade " id="regisForm"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-body">
	       		 <p class="messRegis">This is mess</p>
	       		 <div style="text-align: center;margin-top: 12%;">
				      <button type="button" class="btn btn-primary cancel" >Ok</button>
			      </div>
	      </div>
	    </div>
	  </div>
	  <div class="loader"></div>
	</div>
	<a class="buttonLogout buttonHead" href="javascript:void(0)">Logout</a>
</div>
<div class='col-xs-12'>
	<div class='col-xs-3' ></div>	
	<div class='col-xs-6'>
		<table class="table responstable">
			<tr>
				<th class='center'>No.</th>
				<th class='center'>Hashtag</th>
				<th class='center'>Media count</th>
			</tr>
			<tr class='center'>
				<td>1</td>
				<td><a href="/hashtag/ranking" target="_blank">#cat</a></td>
				<td>12,000</td>
			</tr>
		</table>
	</div>
	<div class='col-xs-3' ></div>
</div>