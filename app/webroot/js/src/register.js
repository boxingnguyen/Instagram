$().ready(function(){
	$('.loader').hide();
	$('button.modalReg').prop('disabled', true);
	$('button.modalRegTag').prop('disabled', true);
	
	$('.buttonReg').click(function(){
		$('#myModal').show();
		initModal();
	})
	$('input[type=text]').click(function(){
		initModal();
	});
	$('#inputUserName').change(function(){
		var input = $(this).val();
		//remove spaces in string input
		input = input.replace(/\s/g, '');
		//check input
		if (input.match(/^http([s]?):\/\/.*/)) {
			if (!input.match(/^http([s]?):\/\/www\.instagram\.com\/.*/)) {
				$('p.message').text('Sorry! This link has the wrong format. Please re-type!').css("color",'red');
				$('button.modalReg').prop('disabled', true);
			}
		}
	});
	$('#inputHashtag').change(function(){
		var input = $(this).val();
		//remove spaces in string input
		input = input.replace(/\s/g, '');
		//check input
		if (!input.match(/^#.*/)) {
			$('p.message').text('Sorry! This hashtag has wrong format. Please re-type!').css("color",'red');
			$('button.modalRegTag').prop('disabled', true);
		}
	});
	
	$('button.modalReg').click(function(){
		var input = $('input[type=text]').val();
		
		if(input == ''){
			$('p.message').text('Please input the link of instagram!').css("color",'red');
			$('button.modalReg').prop('disabled', true);
		}else{
			//remove spaces in string input
			input = input.replace(/\s/g, '');
			
			var username = '';
			if (input.match(/^http([s]?):\/\/.*/)) {
				username = getQueryVariable(input);
			}else{
				username = input;
			}
			if(username.slice(-1) == '/'){
				username = username.slice(0, -1);
			}
			
			$('#myModal').modal('hide');
			$('.loader').show();
			$('.modal-body').css('opacity','0.4');
			$('button.cancel').prop('disabled', true);
			
			$.ajax({
				method: "POST",
				url: '/register/register',
				data: {username:username},
				dataType: 'json',
				success: function(data){
					console.log(username);
					console.log(data);
					if(data == 1){
						$('p.messRegis').text('You have successfully registered');
					}else{
						if($.type(data) === "string"){
							$('p.messRegis').text(data);
						}
					}
					$('.loader').hide();
					$('.modal-body').css('opacity','1');
					$('button.cancel').prop('disabled', false);
				},
				error: function(){
					$('.loader').hide();
					$('.modal-body').css('opacity','1');
					$('button.cancel').prop('disabled', false);
					$('p.messRegis').text('Sorry, Ajax has some problem!');
				}
			});
		}
	});
	
	$('button.modalRegTag').click(function(){
		var input = $('input[type=text]').val();
		
		if (input == '') {
			$('p.message').text('Please input hastag!').css("color",'red');
			$('button.modalRegTag').prop('disabled', true);
		} else {
			//remove spaces in string input
			hashtag = input.replace(/\s/g, '');
			console.log(hashtag);
			
			$('#myModal').modal('hide');
			$('.loader').show();
			$('.modal-body').css('opacity','0.4');
			$('button.cancel').prop('disabled', true);
			
			var controller = window.location.pathname.split("/")[1];
			
			$.ajax({
				method: "POST",
				url: controller + '/register',
				data: {hashtag:hashtag},
				dataType: 'json',
				success: function(data) {
					if (data == 1) {
						$('p.messRegis').text('You have successfully registered');
					} else {
						if ($.type(data) === "string") {
							$('p.messRegis').text(data);
						}
					}
					$('.loader').hide();
					$('.modal-body').css('opacity','1');
					$('button.cancel').prop('disabled', false);
				},
				error: function(){
					$('.loader').hide();
					$('.modal-body').css('opacity','1');
					$('button.cancel').prop('disabled', false);
					$('p.messRegis').text('Sorry, Ajax has some problem!');
				}
			});
		}
	});
//	$('input[type=text]').keypress(function (e) {
//		  if (e.which == 13) {
//			  var input = $(this).val();
//				//remove spaces in string input
//				input = input.replace(/\s/g, '');
//				//check input
//				if (input.match(/^http([s]?):\/\/.*/)) {
//					if (!input.match(/^http([s]?):\/\/www\.instagram\.com\/.*/)) {
//						$('p.message').text('Sorry! This link has the wrong format. Please re-type!').css("color",'red');
//						$('button.modalReg').prop('disabled', true);
//					}else{
//						$('button.modalReg').click();
//					  }
//				}else{
//					$('button.modalReg').click();
//				}
//		  }
//		});
	
	$('button.cancel').click(function(){
		$('#regisForm').modal('hide');
		$('.modal-backdrop, .modal-backdrop.fade.in').css('opacity',"0");
		$('p.messRegis').text("This is mess");
	});
	
	function initModal(){
		$('#inputUserName').val('');
		$('#inputHashtag').val('');
		$('p.message').text(' ').css("color",'lightblue');
		//$('p.message').text('Example: https://www.instagram.com/instagram/').css("color",'lightblue');
		$('button.modalReg').prop('disabled', false);
		$('button.modalRegTag').prop('disabled', false);
	}
	function getQueryVariable(query){
	       var vars = query.split("/");
	       return vars[3];
	}
});