<!DOCTYPE html>
<html>
<head>
	<title>Crawls Sitemap</title>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<style type="text/css">
		body{
			font-size: 16px;
		    font-family: Helvetica,Arial, sans-serif;
		}
		.form-crawls{
			text-align: center;
			padding: 20px;
		}
		.input-link{
			width: 320px;
			height: 32px;
			border: 1px solid #ccc;
			padding: 0 10px;
		}
		.button-crawls{
			height: 32px;
			border: 1px solid #ccc;
			padding: 0 10px;
			cursor: pointer;
			background-color: #cccccc;
		}
		table{
		    border-collapse: collapse;
		    border-spacing: 0;
		    width: 100%;
		}
		td,th {
		    border: 1px solid #ddd;
		    text-align: left;
		    padding: 8px;
		}
		th {
		    padding-top: 11px;
		    padding-bottom: 11px;
		    background-color: #04AA6D;
		    color: white;
		}
		tr:nth-child(2n) {
		    background-color: #f2f2f2;
		}
		.loading{
			display: none;
			text-align: center;
		}
		.load-more{
			cursor: pointer;
			text-align: center;
			margin: auto;
			padding: 20px;
			border: 1px solid #ddd;
			background-color: #cccccc;
			margin-top: 20px;
		}
		.load-more:hover,.button-crawls:hover{
			background-color: #04AA6D;
		}
		.show_incoming_links{
			cursor: pointer;
		}
		.wrap_link,.number_loading{
			display: none;
			
		}
		.too-many-headlines{
			color: red;
		}
		.number_loading{
			text-align: center;
		}
		/*table
		{
		    counter-reset: rowNumber;
		}

		table tr > td:first-child
		{
		    counter-increment: rowNumber;
		}

		table tr td:first-child::before
		{
		    content: counter(rowNumber);
		    min-width: 1em;
		    margin-right: 0.5em;
		}*/
	</style>
</head>
<body>
	<div class="wrapper">
		<div class="wrapper_inner">
			<div class="form-crawls">
				<input type="input" class="input-link" name="input-link">
				<input type="button" class="button-crawls" name="button-crawls" value="Crawls">
			</div>
			<div class="number_loading">Load <span class="num_load"></span> of <span class="total_result"></span></div>
			<div id="result-crawls"><table><tr><th>HEADLINE</th><th>LINKS</th><th>URL</th></tr></table></div>
			<div class="loading"><img src="loading.gif"></div>
			<div class="liveprogress"></div>
		</div>
	</div>
	<script type="text/javascript" language="javascript">
	$(document).ready(function() {
		var array_urls = [];
        $('.button-crawls').click(function(e) { //alert('dddd');
        	//e.preventDefault();
        	//$('#result-crawls table tbody tr').remove();
        	$('.loading').show();
        	var getlink = $('.input-link').val();
        	if(getlink == ''){
        		alert('Please enter a link');
        	}else{
        		$.ajax({
	                url: 'ajax.php',
	                type: 'POST',
	                dataType: 'json',//script, html, json
	                data: { action: 'getxml',url: getlink},
	            }).done(function(data) {
	            	//alert('ww');
	            	//var getData = $.parseJSON(data);
	            	
	            	/*$.each(data, function(index, element) {
	            		alert(element);
	            	});*/
	            	
	                //$('#result-crawls').html(data);
	                var numno = 0;
	                var total_cout = data.length
	            	$.each(data, function(index, element) {
	            		//alert(index);
	            		//numno = index + 1;
	            		$('.number_loading .total_result').html(total_cout);
	            		if(index < 1000){
	            			$.ajax({
				                url: 'ajax.php',
				                type: 'POST',
				                dataType: 'html',//script, html, json
				                data: { action: 'getresult',url: element,dataarray:data},
				            }).done(function(ketqua) {
				            	numno +=1;
				            	$('.loading').hide();
				            	$('#result-crawls table').append(ketqua);
				            	$('.number_loading').show();
				            	$('.number_loading .num_load').html(numno);

				            	//array_urls = ketqua;
				            	//$('.loading').hide();
				                //$('#result-crawls').html(ketqua);
				            });
	            		}
	            		
	            	});
	            	/*array_urls = ketqua;*/
	            	
	            });
        	}
        });
        $( document ).on( "click", ".show_incoming_links", function(e) {
        	$(this).siblings('.wrap_link').toggle();
        });
        $( document ).on( "click", ".load-more", function(e) {
        	//e.preventDefault();
        	//$('#result-crawls').html('');
        	$('.loading').show();
        	var current_p = parseInt($(this).attr('data-page-current'));
        	var nextpage = parseInt(current_p) + 1;
        	var total_pages = parseFloat($(this).attr('data-pages'));
        	var getlink = $('.input-link').val();
        	$(this).attr('data-page-current',nextpage);
        	if(nextpage > total_pages){
        		$('.load-more').hide();
        	}
        	if(total_pages >= current_p){
        		$.ajax({
	                url: 'ajax.php',
	                type: 'POST',
	                dataType: 'html',//script, html, json
	                data: { action: 'loadmore',p: current_p}
	            }).done(function(ketqua) {
	            	$('.loading').hide();
	                $('#result-crawls table').append(ketqua);
	            });
        	}
        });
    });
	</script>
</body>
</html>