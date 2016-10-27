$(document).ready(function() {
		var page = -1;
		var pageCurrent = 5; //total record /page
		var baseUrl = (window.location).href; // You can also use document.URL
		var koopId = baseUrl.substring(baseUrl.lastIndexOf('=') + 1);
		$('#loadMore').click(function(){
			page++;
			var start = page*pageCurrent + 1;
			$('#loadMore').html('Loading ...');
			$.ajax({
				method: "POST",
				url: '/PHPInstagram/Ranking/ajax',
				dataType: 'json',
				data: {page:page,id:koopId,currentPage:pageCurrent},
				success: function (result) {
					console.log((result));
					var html = '';var i = start;
					result.forEach(function(item) {
						html += "<tr class='center'>";
						html += "	<td>"+i+"</td>";
						html += "	<td>"+item.full_name+"</td>";
						html += "	<td>"+item.username+"</td>";
						html += "	<td>"+item.totalFollow+"</td>";
						html += "</tr>";
						i++;
					});
					$('#appendFollow').append(html);
					if((result.length) < pageCurrent) {
						$('#loadMore').fadeOut();
					}
				},
				error: function(a,b,c) {
					console.log(a); console.log(b); console.log(c);
				},
				complete: function() {
					$('#loadMore').html('Load more');
					
				}			
			});
			
			$('html,body').animate({
	            scrollTop: $(this).offset().top
	        }, 1500);
			
		});
		
		$('#loadMore').click();

});

