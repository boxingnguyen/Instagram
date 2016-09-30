$().ready(function(){
	$('button.modalReg').click(function(){
		$.ajax({
			method: "POST",
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