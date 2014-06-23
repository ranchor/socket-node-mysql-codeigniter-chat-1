
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="../../assets/ico/favicon.ico">

    <title>Welcome to ChatRoom</title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo base_url();?>asset/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap theme -->
    <link href="<?php echo base_url();?>asset/css/bootstrap-theme.min.css" rel="stylesheet">
	<!-- Font Awesome core CSS -->
    <link href="<?php echo base_url();?>asset/css/font-awesome/css/font-awesome.min.css" rel="stylesheet">
	<!-- Custom styles for this template -->
    <link href="<?php echo base_url();?>asset/css/theme.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body role="document">

    <!-- Fixed navbar -->
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">ChatRoom</a>
        </div>
		<div class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
		  <li class="active"><a href="#">Hi <?php echo $username?></a></li>
		  <li><a href="<?php echo base_url();?>index.php">Leave Room</a></li>
		  </ul>
		</div>  
      </div>
    </div>

    <div class="container theme-showcase" role="main">
		<div class="spinner-feeds" style="text-align: center; margin-top: 200px;"><i class="fa fa-spinner fa-spin fa-3x"></i></div>
		<div class="error-feeds alert alert-danger" style="display:none;margin: 0px;">Sorry seems like we are having some problems displaying the chat!!!</div>	
		<div class="panel panel-default" id="chat-window" style="display:none;">
		</div>


    </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="<?php echo base_url();?>asset/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>asset/js/moment.min.js"></script>
	<script src="http://localhost:3000/socket.io/socket.io.js"></script>
	<script>
	var user_id='<?php echo $user_id?>';
	var room_id=1;
	if(typeof io=="undefined")
	{
		$('.spinner-feeds').hide();
		$('#chat-window').hide();
		$('.error-feeds').show();
	}
	else
	{
		var socket = io.connect('http://localhost:3000');
		var chatpage=socket.of('/chatpage')
			.on('connect_failed', function (reason) {
			$('.spinner-feeds').hide();
			$('#chat-window').hide();
			$('.error-feeds').show();
			console.error('unable to connect chatpage to namespace', reason);
			})
			.on('error',function(reason){
			//alert("in error func");
			$('.spinner-feeds').hide();
			$('#chat-window').hide();
			$('.error-feeds').show();
			$('#chat-window').html('');
			console.error('unable to connect chatpage to namespace', reason);
			})
			.on('reconnect_failed',function(){
			alert("in reconnect fail func");
			$('.spinner-feeds').hide();
			$('#chat-window').hide();
			$('.error-feeds').show();
			$('#chat-window').html('');
			})
			.on('connect', function () {
			console.info('sucessfully established a connection of chatpage with the namespace');
			chatpage.emit('senddata',{user_id:user_id,room_id:room_id});
			});
		
		chatpage.on('chatdata',function(data){
			$('.error-feeds').hide();
			$('.spinner-feeds').hide();
			$('#chat-window').html('');
			$('#chat-window').show();
			var header='';
			var content='';
			var footer='';
			
			var cells='';
			if(data.memdata)
			{
				for(n in data.memdata)
				{
					cells+='<span class="label label-default"><input type="hidden" class="userId" value="'+data.memdata[n].user_id+'"/>'+data.memdata[n].username+'</span>&nbsp;';
				}
			}
			header='<div class="panel-heading"><h3 class="panel-title"><span class="label label-success">Online:</span> <span id="online-list"> '+cells+'</span></h3></div>';
			$('#chat-window').append(header);
			
			content='<div class="panel-body" style="min-height:410px;"><ul class="media-list" id="chat_block_list"></ul></div>';
			$('#chat-window').append(content);
			if(data.converdata)
			{
				for(n in data.converdata)
				{
					$('#chat_block_list').append('<li class="media"><div class="media-body"><h4 class="media-heading">'+data.converdata[n].username+'</h4><p>'+data.converdata[n].comment+'</p></div></li>');
				}
			}
			else
			{
				$('#chat_block_list').append('');
			}
			footer='<div class="panel-footer"><textarea class="form-control" rows="2" style="display:inline;width:95%" name="msg_box" id="msg_box"></textarea><button type="button" class="btn btn-primary btn-sm pull-right" id="msg_send">Send</button></div>';
			$('#chat-window').append(footer);	
		});	
		chatpage.on('showcomment',function(data){
			$('#chat_block_list').append('<li class="media"><div class="media-body"><h4 class="media-heading">'+data.room_comment[0].username+'</h4><p>'+data.room_comment[0].comment+'</p></div></li>');
			$('#msg_box').val('');
		});
		chatpage.on('newuser',function(data){
			$('#online-list').append('<span class="label label-default"><input type="hidden" class="userId" value="'+data.userdata[0].user_id+'"/>'+data.userdata[0].username+'</span>&nbsp;') 
		});
		chatpage.on('removeuser',function(data){
			$('#online-list span').each(function(index){
				var user_id=$(this).find('.userId').val();
				if(user_id==data.user_id)
				{
					$(this).remove();
				}
			});	
		});
		$('body').on("keypress",'#msg_box', function(e) {	
		if (e.which == 13) {
			$(this).blur();
			var message = $(this).val();
			if(message)
			{
				sendChat(message);
			}
			return false; // prevent the button click from happening
		}
		});	
		$('body').on("click",'#msg_send', function(e) {	
			var message = $('#msg_box').val();
			if(message)
			{
				sendChat(message);
			}
		});
		function sendChat(message)	
		{
			var curr_date= moment().format("YYYY-MM-DDTHH:mm:ss.SSSZZ");
			chatpage.emit('sendcomment', {msg:message,user_id:user_id,room_id:room_id,datetime:curr_date});
		}
	}
	function confirmExit()
	{
	 alert("exiting");
	 window.location.href='<? echo base_url();?>index.php';
	 return true;
	}
	window.onbeforeunload = confirmExit;
	</script>
  </body>
</html>
