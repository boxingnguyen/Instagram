$().ready(function(){
	$('.loader').hide();
	$('.buttonReg').click(function(){
		console.log(123);
		$('#myModal').show();
	})
	$('input[type=text]').click(function(){
		$(this).val('');
		$('p.message').text('Example: https://www.instagram.com/instagram/').css("color",'lightblue');
		$('button.modalReg').prop('disabled', false);
	});
	$('input[type=text]').change(function(){
		var input = $(this).val();
		//remove spaces in string input
		input = input.replace(/\s/g, '');
		var username = '';
		if (input.match(/^http([s]?):\/\/.*/)) {
			if (input.match(/^http([s]?):\/\/www\.instagram\.com\/.*/)) {
				username = input.substr(26);	
			}else{
				console.log('try agian');
				$('p.message').text('Sorry! This link has the wrong format. Please re-type!').css("color",'red');
			}
		}else{
			username = input;
		}
		if(username.slice(-1) == '/'){
			username = username.slice(0, -1);
		}
		console.log(username);
	});
	$('button.modalReg').click(function(){
		if($('input[type=text]').val() == ''){
			$('p.message').text('Please input the link of instagram!').css("color",'red');
			$('button.modalReg').prop('disabled', true);
		}else{
			$('#myModal').modal('hide');
			$('.loader').show();
			$('.modal-body').css('opacity','0.4');
			$.ajax({
				method: "POST",
				url: '/check',
				dataType: 'json',
				success: function(data){
					if(data == 1){
						$('p.messRegis').text('You have successfully registered');
					}else{
						if($.type(data) === "string"){
							$('p.messRegis').text(data);
						}
					}
					$('.loader').hide();
					$('.modal-body').css('opacity','1');
				},
				error: function(){
					$('.loader').hide();
					$('.modal-body').css('opacity','1');
					$('p.messRegis').text('Sorry, Ajax has some problem!');
				}
			});
		}
		
	});
	$('button.cancel').click(function(){
		$('#regisForm').modal('hide');
		$('.modal-backdrop, .modal-backdrop.fade.in').css('opacity',"0");
	})
});