<form method ='' action='' class="switchForm">
	<input type="checkbox" checked data-toggle="toggle" data-on="<?php echo strtolower($this->params['controller']) == 'top' ? 'HASHTAG' : 'TOP'?>" data-off="<?php echo strtolower($this->params['controller']) == 'top' ? 'TOP' : 'HASHTAG'?>" data-onstyle="warning" data-offstyle="info">
</form>