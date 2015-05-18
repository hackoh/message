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
					<div class="right">
						<a href="javascript:;" class="delete-button button button-fill"><i class="fa fa-trash"></i></a>
						<a href="javascript:;" data-popup=".popup-album" class="open-popup button"><i class="fa fa-th-large"></i></a>
						<a href="javascript:;" data-popup=".popup-setting" class="open-popup button"><i class="fa fa-wrench"></i></a>
					</div>
				</div>
			</div>
			<!-- Pages container, because we use fixed-through navbar and toolbar, it has additional appropriate classes-->
			<div class="pages navbar-through toolbar-through">
				<!-- Page, "data-page" contains page name -->
				<div data-page="index" class="page">
					<!-- messagebar -->
					<div class="toolbar messagebar messagebar-init" data-max-height="200">
						<div class="toolbar-inner">
							<a href="javascript:;" data-sender="<?php echo $sender ?>" class="image-button link"><i class="fa fa-photo fa-lg"></i></a>&nbsp;
							<textarea placeholder="Message"></textarea>
							<a href="javascript:;" data-sender="<?php echo $sender ?>" class="link send-button">Send</a>
						</div>
					</div>
					<!-- Scrollable page content -->
					<div class="page-content">
						<div class="messages">
<?php $time = 0; foreach ($room->messages as $message): ?>
<?php if (($message->created_at - $time) > 1800): ?>
							<div class="messages-date"><?php echo date('Y-m-d', $message->created_at) ?>&nbsp;<span><?php echo date('H:i', $message->created_at) ?></span></div>
<?php endif ?>
<?php if ($message->action == 'image') : ?>
							<div class="message message-last message-with-tail message-<?php echo $message->sender == $sender ? 'sent': 'received' ?> message-pic">
								<div class="message-text"><img data-src="/files/images/<?php echo nl2br(\Crypt::decode($message->text)) ?>" src="/files/thumbs/<?php echo nl2br(\Crypt::decode($message->text)) ?>" style="height: 160px" class="message-image"></div>
							</div>
<?php else: ?>
							<div class="message message-last message-with-tail message-<?php echo $message->sender == $sender ? 'sent': 'received' ?>">
								<div class="message-text"><?php echo nl2br(\Crypt::decode($message->text)) ?></div>
							</div>
<?php endif ?>
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
	<div class="popup popup-album">
		<div class="navbar">
			<div class="navbar-inner">
				<div class="left"></div>
				<div class="center">Album</div>
				<div class="right"><a href="javascript:;" class="close-popup button"><i class="fa fa-times"></i></a></div>
			</div>
		</div>
		<div class="page-content">
			<div class="content-block row">
<?php $i = 0; foreach ($room->images as $image): ?>
				<div class="col-33" style="overflow: hidden">
					<a href="javascript:;" class="album-image" data-index="<?php echo $i ?>"><img src="<?php echo Uri::create('/files/thumbs/'.$image->saved_as) ?>" style="height: 100%"></a>
				</div>
<?php $i++; endforeach ?>
			</div>
		</div>
	</div>
	<form id="form" enctype="multipart/form-data" method="post"></form>
	<!--audio id="remote"></audio-->
<!-- Path to Framework7 Library JS-->
<script type="text/javascript" src="/assets/js/framework7.min.js"></script>
<script type="text/javascript" src="/assets/js/jquery.js"></script>
<script type="text/javascript" src="/assets/js/autogrow.min.js"></script>
<script type="text/javascript" src="/assets/js/desktop-notify.js"></script>
<script src="https://skyway.io/dist/0.3/peer.js"></script>
<script>

	var getUserMedia = ( navigator.getUserMedia ||
					   navigator.webkitGetUserMedia ||
					   navigator.mozGetUserMedia ||
					   navigator.msGetUserMedia);

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
		var data = $.parseJSON(e.data);
		if (data.action == 'send') {
			var $message = $('<div class="message message-last message-with-tail"><div class="message-text"></div></div>');
			if (data.sender == '<?php echo $sender ?>') {
				$message.addClass('message-sent');
			} else {
				$message.addClass('message-received');
				notify.createNotification("New message", { body: data.text});
			}
			$message.find('.message-text').html(data.text);
			$('.messages').append($message);
			$('.messages .message-received.message-input').remove();
			$('.page-content').animate({
				scrollTop: $('.messages').height()
			});
		} else if (data.action == 'input' && data.sender != '<?php echo $sender ?>') {
			if ($('.messages .message-received.message-input').length == 0) {
				var $message = $('<div class="message-last message-with-tail message message-received message-input"><div class="message-text"  style="padding-bottom: 0px"><img src="<?php echo Uri::create('assets/img/entering.gif') ?>" width="45" /></div></div>');
				$('.messages').append($message);
			}
			$('.page-content').animate({
				scrollTop: $('.messages').height()
			});
		} else if (data.action == 'stop' && data.sender != '<?php echo $sender ?>') {
			$('.messages .message-received.message-input').remove();
		} else if (data.action == 'image') {

			var $img = $('<img>');
			$img.attr('src', '/files/thumbs/'+data.text);
			$img.attr('data-src', '/files/images/'+data.text);
			$img.css('height', '160px');
			$img.addClass('message-image');

			var $message = $('<div class="message message-last message-with-tail message-pic"><div class="message-text"></div></div>');
						
			if (data.sender == '<?php echo $sender ?>') {
				$message.addClass('message-sent');
			} else {
				$message.addClass('message-received');
			}
			$message.find('.message-text').append($img);
			$('.messages').append($message);
			$('.messages .message-received.message-input').remove();
			$('.page-content').animate({
				scrollTop: $('.messages').height()
			});
		} else if (data.action == 'join' && data.sender != '<?php echo $sender ?>') {
			notify.config({autoClose: 5000});
			notify.createNotification("User joined", { body: "Opponent was entering"});
		}

		// else if (data.action == 'call' && data.sender != '<?php echo $sender ?>') {
		// 	var $message = $('<div class="message-last message-with-tail message message-received message-call"><div class="message-text"><span>Call from '+(data.sender == 1 ? 'A' : 'B')+'</span><a href="javascript:;" class="button button-pink button-fill">Accept</a></div></div>');
		// 		$('.messages').append($message);
		// 	var $accept = $message.find('a');
		// 	var remoteId = data.text;
		// 	$accept.on('click', function() {
		// 		$('.messages .message-received.message-call').remove();
		// 		getUserMedia({"video":false, "audio":true}, function(stream){
		// 			peerCall_ = peer.call(remoteId, stream);
		// 			peerCall_.on('stream', function(remoteStream){
		// 				var $remoteAudio_ = $('#remote');
		// 				$remoteAudio_.attr('src', URL.createObjectURL(remoteStream));
		// 				$remoteAudio_[0].play();
		// 			})
		// 			peerCall_.on('close',function(){
		// 				var $remoteAudio_ = $('#remote');
		// 				$remoteAudio_[0].pause();
		// 			})
		// 		}, function() {
		// 			myApp.alert('Failed to get user microphone.', 'Error');
		// 		})
		// 	})
		// } else if (data.action == 'call' && data.sender == '<?php echo $sender ?>') {

		// } else if (data.action == 'call_cancel' && data.sender != '<?php echo $sender ?>') {
		// 	$('.messages .message-received.message-call').remove();
		// } else if (data.action == 'accept' && data.sender != '<?php echo $sender ?>') {
		// 	var $message = $('<div class="message-last message-with-tail message message-received message-call"><div class="message-text"><span>Call to '+(data.sender == 1 ? 'B' : 'A')+'</span><a href="javascript:;" class="button">Cancel</a></div></div>');
		// 		$('.messages').append($message);
		// 	var $cancel = $message.find('a');
		// 	$cancel.on('click', function() {
		// 		conn.send(JSON.stringify({
		// 			sender: '<?php echo $sender ?>',
		// 			text: '',
		// 			action: 'call_cancel',
		// 			number: '<?php echo $room->number ?>'
		// 		}));
		// 	})
		// }

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

		notify.requestPermission();

		$(document).on('click', '.message-image', function() {
			var Photo = myApp.photoBrowser({
				photos: [$(this).attr('data-src')],
				type: 'standalone'
			});
			Photo.open();
		});

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

		$('.image-button').on('touchend', function() {
			$('#form').empty();
			var $input = $('<input type="file" accept="image/jpeg, image/gif, image/png" name="file" style="display: none">');

			$('#form').append($input);
			
			$input.on('change', function() {
				if (this.files[0]) {
					fr = new FileReader();

					fr.onload = function(e) {
						// var $img = $('<img>');
						// $img.attr('src', e.target.result);
						// $img.css('height', '160px');
						

						// var $message = $('<div class="message message-last message-with-tail message-pic"><div class="message-text"></div></div>');
						// $message.addClass('message-sent');

						// $message.find('.message-text').append($img);
						// $('.messages').append($message);
						// $('.page-content').animate({
						// 	scrollTop: $('.messages').height()
						// });

						var fd = new FormData(document.getElementById('form'));

						$.ajax({
	                        url: '<?php echo Uri::create('rooms/'.$room->number.'/images') ?>',
	                        type: 'POST',
	                        data: fd,
	                        dataType: 'json',
	                        contentType: false,
	                        processData: false,
	                        success: function(data) {
	                        	// $img.attr('src', data.thumb);

	                        	conn.send(JSON.stringify({
									sender: '<?php echo $sender ?>',
									text: data.saved_as,
									action: 'image',
									number: '<?php echo $room->number ?>'
								}));
	                        }
	                    });

					};
					fr.readAsDataURL(this.files[0]);

					
				}
			});
			$input.trigger('click');
		});
	});

<?php if ($room->number == 1) : ?>
	
	// var peer = new Peer({key: 'fe062558-02b1-4ec8-8b37-59da55f67cd6'});
	// var peerId = null;
	// peer.on('open', function(id) {
	// 	peerId = id;
	// 	alert(peerId);
	// });
	// peer.on('call', function(call) {
	// 	console.log(call);
	// 	getUserMedia({"video":false,"audio":true}, function(stream){
	// 		call.answer(stream);
	// 	}, function(){
	//         myApp.alert('Failed to get user microphone.', 'Error');
	//     })
	// 	peerCall_ = call;
 //        call.on('stream', function(stream){
 //        	 var $remoteAudio_ = $('#remote');
 //            $remoteAudio_.attr('src',URL.createObjectURL(stream));
 //            $remoteAudio_[0].play();
 //        })
 //        peerid_ = call.peer;
	// 	// myApp.confirm('Touch OK to start voicechat!', 'Accepted', function(call) {
			
	// 	// });
	// })

	// var startVoice = function() {
	// 	conn.send(JSON.stringify({
	// 		sender: '<?php echo $sender ?>',
	// 		text: peerId,
	// 		action: 'call',
	// 		number: '<?php echo $room->number ?>'
	// 	}));
	// }

<?php endif; ?>
	

var Album = myApp.photoBrowser({
	photos : [
<?php foreach ($room->images as $image) : ?>
		'<?php echo Uri::create('/files/images/'.$image->saved_as) ?>',
<?php endforeach; ?>
	],
	type: 'standalone'
});

	$('.album-image').on('click', function() {
		Album.open($(this).attr('data-index'));
	});


</script>
</body>
</html>