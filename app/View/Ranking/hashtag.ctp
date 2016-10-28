<style type="text/css">
	#frame_content_error{
		border: 1px solid; 
		background: #2d5986;
		border-radius: 5px;
	}
	

	#frame_error_404{
		border:1px solid; 
		border-color:#2d5986;  
		background:white; 
		margin-top:50px; 
		margin-bottom:50px; 
		border-radius:20px;
	}
	#error_404{
		border-right:1px solid silver;
		text-align: center;

	}
	
	@media (max-width: 1920px){
		h1 {
			font-size: 1500%;
		}
	}
	@media (max-width: 1500px){
		h1 {
			font-size: 1000%;
		}
		#authen h3{
          padding-top:40px !important;
	}
	}
	@media (max-width: 1200px){
		h1 {
			font-size: 800%;
		}
		#error_404 {
			border-right: none;
			border-bottom: 1px solid silver;
		}
		#authen h3{
          padding-top:5px !important;
	}
	}
	
	#error_404 h1{
		color:#2d5986; 
		font-weight:bold
	}
	
	
	#authen h3{
          padding-top:100px;
	}
</style>
<div class="container">
	<div class="col-md-8 col-md-offset-2" id="frame_content_error">
	    <div class="col-md-8 col-md-offset-2" id="frame_error_404">
			<div class="col-md-6" id="error_404">
				<h1 >404</h1>
			</div>	
			<div class="col-md-6" id="authen">
				<h3>This account has logged into this system. Please invite them to sign in !!!</h3>
			</div>
		</div>
	</div>
</div>