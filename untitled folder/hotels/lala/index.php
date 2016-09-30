<?php

include_once  'config.php';
include_once  '../../common.php';

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Roomier Booking :: <?php echo $pref['PAGE_TITLE']; ?></title>

<link href="../../hotelier/css/bootstrap.min.css" rel="stylesheet">
<link href="../../hotelier/css/datepicker3.css" rel="stylesheet">
<link href="../../hotelier/css/styles.css" rel="stylesheet">
<link href="css/custom.booking.css" rel="stylesheet">

<?php
$background_image = "../../uploads/files/backgrounds/background_" . $pref['PROPERTYID'] . ".png";
if (file_exists($background_image)) {
	echo "<style>";
	echo "body {";
	echo "	background-image: url(" . $background_image . ");";
	echo "}";
	echo "</style>";
}
?>


<!--[if lt IE 9]>
<script src="js/html5shiv.js"></script>
<script src="js/respond.min.js"></script>
<![endif]-->

</head>

<body>

	<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<div class="container-fluid">
			<div class="navbar-header">
				<a class="navbar-brand" href="#"><img src="../../hotelier/img/Roomier_logo_final_greyscale.png" alt="Roomier"/></a>
			</div>
		</div><!-- /.container-fluid -->

			<?php
			$langs_result = getLangs($pref['PROPERTYID']);
			$langs_array = explode(",", $langs_result);

			foreach($langs_array as $lang_val) {
			?>
				<a href="?lang=<?= $lang_val ?>"><?= $lang_val ?></a> |
			<?php
			}
			?>
	</nav>






	<div class="row">
		<div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-4 col-md-offset-4">
			<div class="booking-panel panel panel-default">
				<div id="bookingPanel">
					<div class="panel-heading"><?php echo $lang['CHECK_AVAILABILITY_TITLE']; ?></div>
					<div class="panel-body">
						<fieldset>
							<div class="form-group">
								<label><?php echo $lang['ARRIVAL_DATE']; ?></label>
								<input class="form-control" name="booking-form-from" id="booking-form-from" type="text">
							</div>
							<div class="form-group">
								<label><?php echo $lang['DEPARTURE_DATE']; ?></label>
								<input class="form-control" name="booking-form-to" id="booking-form-to" type="text">
							</div>
							<div class="form-group">
								<label><?php echo $lang['NUMBER_OF_ROOMS']; ?></label>
								<select class="form-control" id="booking-form-rooms-num">
									<option value="1">1</option>
									<option value="2">2</option>
									<option value="3">3</option>
									<option value="4">4</option>
									<option value="5">5</option>
									<option value="6">6</option>
									<option value="7">7</option>
									<option value="8">8</option>
									<option value="9">9</option>
									<option value="10">10</option>
									<option value="11">11</option>
									<option value="12">12</option>
									<option value="13">13</option>
									<option value="14">14</option>
									<option value="15">15</option>
									<option value="16">16</option>
									<option value="17">17</option>
									<option value="18">18</option>
									<option value="19">19</option>
									<option value="20">20</option>
									<option value="21">21</option>
									<option value="22">22</option>
									<option value="23">23</option>
									<option value="24">24</option>
									<option value="25">25</option>
									<option value="26">26</option>
									<option value="27">27</option>
									<option value="28">28</option>
									<option value="29">29</option>
									<option value="30">30</option>
								</select>
							</div>
							<div class="form-group">
									<label><?php echo $lang['ADULTS_PER_ROOM']; ?></label>
									<select class="form-control" id="booking-form-adults">
										<option value="1">1</option>
										<option value="2">2</option>
										<option value="3">3</option>
										<option value="4">4</option>
										<option value="5">5</option>
										<option value="6">6</option>
									</select>
							</div>
							<div class="form-group">
									<label><?php echo $lang['CHILDREN_PER_ROOM']; ?></label>
									<select class="form-control"  id="booking-form-children">
										<option value="0">0</option>
										<option value="1">1</option>
										<option value="2">2</option>
										<option value="3">3</option>
										<option value="4">4</option>
									</select>
								</div>
							<button class="btn btn-primary"  id="booking-form-check" name="booking-form-check"><?php echo $lang['CHECK_AVAILABILITY']; ?></button>
						</fieldset>
					</div>
				</div>
			</div>
		</div><!-- /.col-->
	</div><!-- /.row -->



	<!-- <script src="js/jquery-1.11.1.min.js"></script> -->
	<script src="/hotelier/js/jquery-1.12.3.min.js"></script>
	<script src="/hotelier/js/bootstrap.min.js"></script>
	<script src="/hotelier/js/bootstrap-datepicker.js"></script>
	<script type="text/javascript" src="/hotelier/js/roomier.config.js"></script>
	<script src="js/roomier.booking.js"></script>

	<script>

		$(window).on('resize', function () {
		  if ($(window).width() > 768) $('#sidebar-collapse').collapse('show')
		})
		$(window).on('resize', function () {
		  if ($(window).width() <= 767) $('#sidebar-collapse').collapse('hide')
		})
	</script>
</body>

</html>
