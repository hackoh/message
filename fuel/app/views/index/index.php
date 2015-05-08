<!DOCTYPE html>
<html>
<head>
	<!-- Required meta tags-->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<!-- Your app title -->
	<title>m</title>
	<!-- Path to Framework7 Library CSS-->
	<link rel="stylesheet" href="/assets/css/framework7.min.css">
	<!-- Path to your custom app styles-->
	<link rel="stylesheet" href="/assets/css/my-app.css">
</head>
<body>
	<!-- Status bar overlay for full screen mode (PhoneGap) -->
	<div class="statusbar-overlay"></div>
	<!-- Panels overlay-->
	<div class="panel-overlay"></div>
	<!-- Views -->
	<div class="views">
		<!-- Your main view, should have "view-main" class -->
		<div class="view view-main">
			<!-- Top Navbar-->
			<div class="navbar">
				<div class="navbar-inner">
					<!-- We need cool sliding animation on title element, so we have additional "sliding" class -->
					<div class="center sliding">m</div>
				</div>
			</div>
			<!-- Pages container, because we use fixed-through navbar and toolbar, it has additional appropriate classes-->
			<div class="pages navbar-through toolbar-through">
				<!-- Page, "data-page" contains page name -->
				<div data-page="index" class="page">
					<!-- Scrollable page content -->
					<div class="page-content">
						<div class="content-block-title">Enter room number &amp; pass code.</div>
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
											<div class="item-title label">Pass code</div>
											<div class="item-input">
												<input type="number" name="passcode">
											</div>
										</div>
									</div>
								</li>
							</ul>
						</div>
						<div class="content-block">
							<p class="buttons-row">
							  <a href="javascript: loginRoom(1);" class="button">as A</a>
							  <a href="javascript: loginRoom(2);" class="button">as B</a>
							</p>
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