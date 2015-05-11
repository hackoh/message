<!DOCTYPE html>
<html>
<head>
	<!-- Required meta tags-->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<!-- Your app title -->
	<title><?php echo $room->number ?> | talk</title>
	<!-- Path to Framework7 Library CSS-->
	<link rel="stylesheet" href="/assets/css/framework7.min.css">
	<!-- Path to your custom app styles-->
	<link rel="stylesheet" href="/assets/css/font-awesome.min.css">
</head>
<body class="theme-pink">
	<!-- Status bar overlay for full screen mode (PhoneGap) -->
	<div class="statusbar-overlay"></div>
	<!-- Panels overlay-->
	<div class="panel-overlay"></div>
	<!-- Views -->
	<div class="views">
		<!-- Your main view, should have "view-main" class -->
		<div class="view view-main color-red">
			<!-- Top Navbar-->
			<div class="navbar">
				<div class="navbar-inner">
					<div class="left"><a href="/"><i class="fa fa-comments"></i>&nbsp;talk</a></div>
				</div>
			</div>
			<!-- Pages container, because we use fixed-through navbar and toolbar, it has additional appropriate classes-->
			<div class="pages navbar-through toolbar-through">
				<!-- Page, "data-page" contains page name -->
				<div data-page="index" class="page">
					<!-- messagebar -->
					<div class="toolbar messagebar messagebar-init" data-max-height="200">
						<div class="toolbar-inner">
							<textarea placeholder="Message"></textarea><a href="javascript: send(<?php echo $sender ?>)" class="link">Send</a>
						</div>
					</div>
					<!-- Scrollable page content -->
					<div class="page-content">
						<div class="messages">
<?php foreach ($room->messages as $message): ?>
							<div class="message message-last message-with-tail message-<?php echo $message->sender == $sender ? 'sent': 'received' ?>">
								<div class="message-text"><?php echo nl2br(\Crypt::decode($message->text)) ?></div>
							</div>
<?php endforeach ?>	
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<!-- Path to Framework7 Library JS-->
<script type="text/javascript" src="/assets/js/framework7.min.js"></script>
<script type="text/javascript" src="/assets/js/jquery.js"></script>
<script type="text/javascript" src="/assets/js/autogrow.min.js"></script>
<script>
	var conn = new WebSocket('ws://m.impv.net:9000');
	conn.onopen = function(e) {
	    // console.log("Connection established!");
	};
	conn.onmessage = function(e) {
	    // console.log(e.data);
	    var data = $.parseJSON(e.data);
	    if (data.action == 'send') {
	    	var $message = $('<div class="message message-last message-with-tail"><div class="message-text"></div></div>');
	    	if (data.sender == '<?php echo $sender ?>') {
	    		$message.addClass('message-sent');
	    	} else {
	    		$message.addClass('message-received');
	    	}
	    	$message.find('.message-text').html(data.text);
	    	$('.messages').append($message);
	    	$('.messages .message-received.message-input').remove();
	    	$('.page-content').animate({
	    		scrollTop: $('.messages').height()
	    	});
	    } else if (data.action == 'input' && data.sender != '<?php echo $sender ?>') {
	    	if ($('.messages .message-received.message-input').length == 0) {
	    		var $message = $('<div class="message-last message-with-tail message message-received message-input"><div class="message-text">Entering text...</div></div>');
	    		$('.messages').append($message);
	    	}
	    	$('.page-content').animate({
	    		scrollTop: $('.messages').height()
	    	});
	    } else if (data.action == 'stop' && data.sender != '<?php echo $sender ?>') {
	    	$('.messages .message-received.message-input').remove();
	    }

	};
	function send(sender) {
		var msg = $('textarea').val();
		$('textarea').val('');
		$('textarea').css({
			height: 26
		});
		$('.messagebar').height($('textarea').height() + 24);

		conn.send(JSON.stringify({
			sender: sender,
			text: msg,
			action: 'send',
			number: '<?php echo $room->number ?>'
		}));
	}
	$(function() {
		$('.page-content').animate({
			scrollTop: $('.messages').height()
		});
		$('textarea').on('keyup', function() {
			if ($('textarea').val() != '') {
				conn.send(JSON.stringify({
					sender: '<?php echo $sender ?>',
					text: '',
					action: 'input',
					number: '<?php echo $room->number ?>'
				}));
			} else {
				conn.send(JSON.stringify({
					sender: '<?php echo $sender ?>',
					text: '',
					action: 'stop',
					number: '<?php echo $room->number ?>'
				}));
			}
			setTimeout(function() {
				$('html,body').animate({
					scrollTop: $('html,body').height()
				}, 1);
				$('.messagebar').height($('textarea').height() + 24);
			}, 10);
		});
		$('textarea').autogrow({
			speed: 0
		});
	});
</script>
</body>
</html>