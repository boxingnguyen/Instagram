$().ready(function(){
	var page = 0;
	var limit = 20;
	$('.loadMore').click(function(){
		page++;
		$('.loadMore').html('Loading...');
		$.ajax({
			method: "POST",
			url: '/media/more',
			dataType: 'json',
			data: {page:page},
			success: function(data){
				console.log(data);
				if(data.length>0){
					for(var i=0;i<data.length;i++){
						
					    var myDate = new Date(1000*data[i].created_time);
//					    console.log(myDate.toLocaleString());
					    
						var textmess = '';
						if(typeof(data[i].caption) != "undefined" && data[i].caption !== null){
						 textmess = data[i].caption.text;
						}
						
						var typeOfPost = '<div class="cd-timeline-img cd-picture"><img src="/img/cd-icon-picture.svg" alt="Picture"></div>';
						var post = '<a href="'+data[i].link+'" target="_blank"><img class="pre-media" src="'+ data[i].images.low_resolution.url +'" ></a>';
						if(data[i].type == 'video'){
							typeOfPost = '<div class="cd-timeline-img cd-movie"><img src="/img/cd-icon-movie.svg" alt="Movie"></div>';
							post = '<video class="pre-media" controls width="320" height="320"><source src="'+data[i].alt_media_url+'" type="video/mp4"></video>';
						}
						
						var location = '';
						if(data[i].location !== null){
							typeOfPost = '<div class="cd-timeline-img cd-location"><img src="/img/cd-icon-location.svg" alt="Location"></div>';
							var location = data[i].location.name;
						}
						
						var html = '<div class="cd-timeline-block">'+
										typeOfPost+ 
										'<div class="cd-timeline-content">'+
											'<a href="'+ data[i].link +'" target="_blank"><h2>'+ myDate.toUTCString().substr(5, 11)+'</h2></a>'+
											post+
											'<p>'+textmess+'</p>'+
											'<div class="div-like-insta">'+
												'<img  class="icon-insta" src="/img/like_insta.png" alt="Picture">'+
												'<span class="number-insta">'+ data[i].likes.count+'</span>'+
											'</div>'+
										'<div>'+
											'<img class="icon-insta comment" src="/img/cmt_insta.png" alt="Picture" data-id="'+ data[i].id +'">'+
											'<span class="number-insta">' +data[i].comments.count+ '</span>'+
										'</div>'+
										'<span class="cd-date">'+location+'</span>'+
										'<div class="listComment">'+
											'<ul class="'+data[i].id+'">'+
											'</ul>'+
										'</div>'+
										'<div id="'+ data[i].id +'" class="addComment">'+
											'<input placeholder="Add Comment" class="form-control" data-id="'+ data[i].id +'">'+
										'</div>'+		
									'</div>'+
									'</div>';
						$('#cd-timeline').append(html);
						$('.loadMore').html('Load More');
					}
					if(data.length < limit){
						$('.loadMore').remove();
					}
				}else {
					$('#notification').append('<p class= "danger">Not found media</p>');
					$('.loadMore').remove();
					$('#cd-timeline').remove();
				}

				//show form comment
				$('.addComment').hide();
				$('.comment').click(function(){
					var id = $(this).attr('data-id');
					$('#'+id).show();
				});

				// ajax send comment
				$('.form-control').keyup(function(e){
					if(e.keyCode ==13 && $(this).val() != ""){
						var id = $(this).attr('data-id');
						var text = $(this).val().trim();
					$.ajax({
							url: '/media/postComment',
							type: 'post',
							data: {id:id,text:text},
							success: function (data) {
								if(data == 400){
								 alert('error');
								 e.preventDefault();
								}
							 	else{
							 		$('.form-control').val('');
							 		var html = '<li>'+
													'<a href="' +'https://www.instagram.com/'+data+'/' +'">'+ data + '</a>'+
													'<label>'+ text + '</label>'+
												'</li>';
									$('.'+id).append(html);
							 	}
			
							}
						});

					}
					else e.preventDefault();
				});


				$.ajax({
					method: "POST",
					url: '/media/total',
					dataType: 'json',
					success: function(data){
						console.log(data);
						if(data%limit == 0){
							if(page == data/limit){
								$('.loadMore').remove();
							}
						}
					},
					error: function(){
						alert('Sorry, Ajax has some problem!');
						$('.loadMore').remove();
					}
				});
			},
			error: function(){
				alert('Sorry, Ajax has some problem!');
				$('.loadMore').remove();
			}
		})
		
		
	});
	$('.loadMore').click();
	
	var amountScrolled = 300;

	$(window).scroll(function() {
		if ( $(window).scrollTop() > amountScrolled ) {
			$('a.back-to-top').fadeIn('slow');
		} else {
			$('a.back-to-top').fadeOut('slow');
		}
	});
	$('a.back-to-top').click(function() {
		$('html, body').animate({
			scrollTop: 0
		}, 700);
		return false;
	});

	
});