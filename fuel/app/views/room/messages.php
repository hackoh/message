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
					<div class="left"><a href="/" class="go-home"><i class="fa fa-comments"></i>&nbsp;talk</a></div>
					<div class="right"><a href="javascript:;" class="delete-button button button-fill"><i class="fa fa-trash"></i></a><a href="javascript:;" data-popup=".popup-setting" class="open-popup button"><i class="fa fa-wrench"></i></a></div>
				</div>
			</div>
			<!-- Pages container, because we use fixed-through navbar and toolbar, it has additional appropriate classes-->
			<div class="pages navbar-through toolbar-through">
				<!-- Page, "data-page" contains page name -->
				<div data-page="index" class="page">
					<!-- messagebar -->
					<div class="toolbar messagebar messagebar-init" data-max-height="200">
						<div class="toolbar-inner">
							<textarea placeholder="Message"></textarea><a href="javascript:;" data-sender="<?php echo $sender ?>" class="link send-button">Send</a>
						</div>
					</div>
					<!-- Scrollable page content -->
					<div class="page-content">
						<div class="messages">
<?php $time = 0; foreach ($room->messages as $message): ?>
<?php if (($message->created_at - $time) > 1800): ?>
							<div class="messages-date"><?php echo date('Y-m-d', $message->created_at) ?>&nbsp;<span><?php echo date('H:i', $message->created_at) ?></span></div>
<?php endif ?>
							<div class="message message-last message-with-tail message-<?php echo $message->sender == $sender ? 'sent': 'received' ?>">
								<div class="message-text"><?php echo nl2br(\Crypt::decode($message->text)) ?></div>
							</div>
<?php $time = $message->created_at; endforeach ?>	
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="popup popup-setting">
		<div class="navbar">
			<div class="navbar-inner">
				<div class="left"></div>
				<div class="center">Settings</div>
				<div class="right"><a href="javascript:;" class="close-popup button"><i class="fa fa-times"></i></a></div>
			</div>
		</div>
		
		<div class="content-block-title">Auto-sender settings</div>
		<div class="list-block">
			<ul>
				<li>
					<div class="item-content">
						<div class="item-inner">
							<div class="item-title label">E-mail</div>
							<div class="item-input">
								<input type="text" placeholder="E-mail" value="<?php echo $room->{'email_'.$sender} ?>" name="email">
							</div>
						</div>
					</div>
				</li>
			</ul>
		</div>
		<p class="content-block"><a href="javascript:;" class="submit-setting button button-fill color-pink">OK</a></p>
		
	</div>
<!-- Path to Framework7 Library JS-->
<script type="text/javascript" src="/assets/js/framework7.min.js"></script>
<script type="text/javascript" src="/assets/js/jquery.js"></script>
<script type="text/javascript" src="/assets/js/autogrow.min.js"></script>
<script>
	var myApp = new Framework7();
	var conn = new WebSocket('ws://m.impv.net:9000');
	conn.onopen = function(e) {
	    // console.log("Connection established!");
	    conn.send(JSON.stringify({
	    	number: '<?php echo $room->number ?>',
			sender: <?php echo $sender ?>,
			text: '',
			action: 'join'
		}));
	};
	conn.onclose = function(e) {
		myApp.alert('Connection closed. Auto reload activated.', 'Auto reload', function() {
			document.location.reload();
		});
	}
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
	function sendMessage(sender) {
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
		$('.submit-setting').on('click', function() {
			var email = $('[name=email]').val();
			if ((email != '') && ! email.match(/.+\@.+/)) {
				myApp.alert('Invalid E-mail', 'Error');
			} else {
				$.ajax({
					url: '<?php echo Uri::create('rooms/'.$room->number.'/'.$sender.'/email') ?>',
					type: 'post',
					dataType: 'json',
					data: {
						email: email
					},
					success: function(response) {
						myApp.closeModal('.popup-setting');
					},
					error: function() {
						myApp.alert('Internal server error', 'Error');
					}
				});
			}
		});

		$('.send-button').on('click', function() {
			var sender = $(this).attr('data-sender');
			sendMessage(sender);
		});

		$('.go-home').on('click', function() {
			document.location.href = '/';
		});

		$('.delete-button').on('click', function() {
			myApp.confirm('Do you wish to delete all logs?', 'Delete all', function() {
				$.ajax({
					url: '<?php echo Uri::create('rooms/'.$room->number.'/messages') ?>',
					type: 'DELETE',
					success: function() {
						$('.messages').empty();
					},
					error: function() {
						myApp.alert('Internal server error', 'Error');
					}
				});
			}, function() {
				// 
			});
		});
	});
</script>
</body>
</html>