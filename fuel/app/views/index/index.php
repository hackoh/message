<!DOCTYPE html>
<html>
<head>
	<!-- Required meta tags-->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<!-- Your app title -->
	<title>talk</title>
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
					<!-- Scrollable page content -->
					<div class="page-content">
						<div class="content-block-title">Enter number &amp; passcode</div>
						<div class="list-block">
							<ul>
								<!-- Text inputs -->
								<li>
									<div class="item-content">
										<div class="item-inner">
											<div class="item-title label">Number</div>
											<div class="item-input">
												<input type="number" name="number">
											</div>
										</div>
									</div>
								</li>
								<li>
									<div class="item-content">
										<div class="item-inner">
											<div class="item-title label">Passcode</div>
											<div class="item-input">
												<input type="number" name="passcode">
											</div>
										</div>
									</div>
								</li>
							</ul>
						</div>
						<div class="content-block">
							<div class="row">
							  <div class="col-50">
							    <a href="javascript: loginRoom(1);" class="button button-big color-pink button-fill">As A</a>
							  </div>
							  <div class="col-50">
							    <a href="javascript: loginRoom(2);" class="button button-big color-blue button-fill">As B</a>
							  </div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Path to Framework7 Library JS-->
	<script type="text/javascript" src="/assets/js/framework7.min.js"></script>
	<script type="text/javascript" src="/assets/js/jquery.js"></script>
	<script type="text/javascript">
	var loginRoom = function(sender) {
		$.ajax({
			url: '/login',
			dataType: 'json',
			type: 'post',
			data: {
				number: $('[name=number]').val(),
				passcode: $('[name=passcode]').val()
			},
			success: function(response) {
				if (response.result) {
					document.location.href = '/rooms/'+$('[name=number]').val()+'/'+sender;
				} else {
					alert('Login Incorrect');
				}
			},
			error: function() {
				alert('Login Incorrect');
			}
		});
	}
	</script>
</body>
</html>