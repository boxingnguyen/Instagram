$().ready(function(){
	$('button.modalReg').click(function(){
	var username = $('#inputUserName').val();
		$.ajax({
			method: "POST",
			data: {username:username},
			url: '/check',
			dataType: 'json',
			success: function(data){
				console.log(data);
			},
			error: function(){
				alert('Sorry, Ajax has some problem!');
			}
		});
	});
});