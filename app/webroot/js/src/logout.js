$().ready(function(){
	$('.buttonLogout').click(function(){
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
});