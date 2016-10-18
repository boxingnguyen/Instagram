$().ready(function(){
	$('.buttonLogout').click(function(){
		$('body').append('<div style="display:none"><iframe src="https://instagram.com/accounts/logout/" width="0" height="0"></iframe></div>');
		sleep(10000);
		$.ajax({
			url: '/register/logout/',
			dataType: 'json',
			success: function(data){
				console.log(data);
			},
			error: function(){
				alert('Sorry, Ajax has some problem!');
			}
		})
	});
	function sleep(delay) {
        var start = new Date().getTime();
        while (new Date().getTime() < start + delay);
      }
});