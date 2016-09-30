<?php

include_once  'config.php';
include_once  '../../common.php';

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Roomier Booking :: <?php echo $pref['PROPERTY_NAME']; ?></title>

<link href="../../hotelier/css/bootstrap.min.css" rel="stylesheet">
<link href="../../hotelier/css/datepicker3.css" rel="stylesheet">
<link href="../../hotelier/css/styles.css" rel="stylesheet">
<link href="../../hotelier/css/progress-wizard.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/jquery.e-calendar.css"/>
<link id="mainStyle" href="css/custom.booking.css" rel="stylesheet">
<style>
<?php
$background_image = "../../uploads/files/backgrounds/background_" . $pref['PROPERTYID'] . ".png";
if (file_exists($background_image)) {
	echo "body {";
	echo "	background-image: url(" . $background_image . ");";
	echo "}";
}
?>
		ul {
			 margin: 0;
			 padding: 0.4em;
			 list-style-type: square;
		}
		li {
			 padding-left: 0.5em;
			 line-height: 2.4em;
		}
		.progress-indicator.custom-complex {
        background-color: #f1f1f1;
        padding: 10px 5px;
        border: 1px solid #ddd;
        border-radius: 10px;
				text-transform: none;
    }
</style>

<!--[if lt IE 9]>
<script src="js/html5shiv.js"></script>
<script src="js/respond.min.js"></script>
<![endif]-->
<!--Icons-->
<script src="/hotelier/js/lumino.glyphs.js"></script>
</head>

<body>

	<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<div class="container-fluid">
			<div class="navbar-header">
				<a class="navbar-brand" href="#">
					<?php
						$logo_image = "../../uploads/files/logos/logo_" . $pref['PROPERTYID'] . ".png";
						if (!file_exists($logo_image)) {
							$logo_image = "../../hotelier/img/Roomier_logo_final_greyscale.png";
						}
					?>
					<img src="<?php echo $logo_image ?>"/></a>
			</div>
		</div><!-- /.container-fluid -->
		<div id="langs">
			<?php
			$langs_result = getLangs($pref['PROPERTYID']);
			$langs_array = explode(",", $langs_result);

			foreach($langs_array as $lang_val) {
			?>
				<a href="?lang=<?= $lang_val ?>"><?= $lang_val ?></a> |
			<?php
			}
			?>
		</div>
	</nav>

	<div id="bookAlert" class="row">
		<div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-4 col-md-offset-4">
			<div class="alert bg-success" role="alert">
				<svg class="glyph stroked empty-message"><use xlink:href="#stroked-empty-message"></use></svg> <span id="successTxt"></span><a href="#" class="pull-right"><span class="glyphicon glyphicon-remove"></span></a>
			</div>
			<div class="alert bg-warning" role="alert">
				<svg class="glyph stroked flag"><use xlink:href="#stroked-flag"></use></svg><span id="warningTxt"></span><a href="#" class="pull-right"><span class="glyphicon glyphicon-remove"></span></a>
			</div>
			<div class="alert bg-danger" role="alert">
				<svg class="glyph stroked cancel"><use xlink:href="#stroked-cancel"></use></svg> <span id="dangerTxt"></span> <a href="#" class="pull-right"><span class="glyphicon glyphicon-remove"></span></a>
			</div>
		</div>
	</div><!--/.row-->

	<div class="row">
		<!--div class=""-->
		<!--div class="col-lg-10 col-xs-offset-1 col-sm-10 col-xs-offset-1 col-md-6 col-xs-offset-4"-->
		<div class="col-xs-10">
			<div class="panel panel-default">
				<div id="progressBar">
					<!--div class="panel-heading">
						Progress
					</div-->
					<div class="panel-body">
						<ul class="progress-indicator custom-complex">
					      <li id="step-0" class="completed">
   		              <span class="bubble"></span>
			              <i class="fa fa-check-circle"></i>
			              <?php echo $lang['PLAN_YOUR_STAY']; ?>
					      </li>
					      <li id="step-1">
			              <span class="bubble"></span>
			              <i class="fa fa-check-circle"></i>
			              <?php echo $lang['CHOOSE_ROOMS_AND_RATES']; ?>
					      </li>
					      <li id="step-2">
					          <span class="bubble"></span>
					          <i class="fa fa-check-circle"></i>
					          <?php echo $lang['ADD_ROOM_SERVICES']; ?>
					      </li>
					      <li id="step-3">
					          <span class="bubble"></span>
					          <i class="fa fa-check-circle"></i>
					          <?php echo $lang['CONFIRM_BOOKING']; ?>
					      </li>
					  </ul>
					</div>
				</div>
			</div>
		</div><!-- /.col-->
	</div><!-- /.row -->


	<div id="shCheckAvailForm" class="row">
		<div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-4 col-md-offset-4">
			<div class="booking-panel panel panel-default">
				<div id="bookingPanel">
					<div class="panel-heading"><?php echo $lang['CHECK_AVAILABILITY_TITLE']; ?></div>
					<div class="panel-body">
						<fieldset>
							<div class="row">
								<div class="col-md-12">
									<label><span id="required" >*</span><?php echo $lang['REQUIRED_FIELDS']; ?></label>
								</div>
							</div>
							<div class="row">
								<!--div class="col-md-1"></div-->
								<div class="col-md-3">
									<div class="form-group">
										<label><?php echo $lang['ARRIVAL_DATE']; ?><span id="required" >*</span></label>
										<input class="form-control availability_form" name="booking-form-from" id="booking-form-from" type="text" required>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label><?php echo $lang['DEPARTURE_DATE']; ?><span id="required" >*</span></label>
										<input class="form-control availability_form" name="booking-form-to" id="booking-form-to" type="text" required>
									</div>
								</div>
							</div>
							<div class="row guests">
								<!--div class="col-md-1"></div-->
								<div class="col-md-3">
									<div class="form-group">
											<label><?php echo $lang['ADULTS_PER_ROOM']; ?></label>
											<select class="form-control availability_form" id="booking-form-adults" name="adults">
												<option value="1">1</option>
												<option value="2">2</option>
												<option value="3">3</option>
												<option value="4">4</option>
												<option value="5">5</option>
												<option value="6">6</option>
											</select>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
											<label><?php echo $lang['CHILDREN_PER_ROOM']; ?></label>
											<select class="form-control availability_form"  id="booking-form-children" name="children">
												<option value="0">0</option>
												<option value="1">1</option>
												<option value="2">2</option>
												<option value="3">3</option>
												<option value="4">4</option>
											</select>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
											<label><?php echo $lang['INFANTS_PER_ROOM']; ?></label>
											<select class="form-control availability_form"  id="booking-form-infants" name="infants">
												<option value="0">0</option>
												<option value="1">1</option>
												<option value="2">2</option>
												<option value="3">3</option>
												<option value="4">4</option>
											</select>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group rm">
											<button class="btn btn-primary rmGuests" type="button" name="rmGuests" title="remove Guests">
												<i class="glyphicon glyphicon glyphicon-minus icon-minus"></i>&nbsp;<?php echo $lang['REMOVE']; ?>
											</button>
									</div>
								</div>
							</div>
							<div class="guests_div">

							</div>
							<div class="row">
								<div class="col-md-2"></div>
								<div class="col-md-3">
									<button id="addGuests" class="btn btn-primary" type="button" name="addGuests" title="add Guests">
										<i class="glyphicon glyphicon glyphicon-plus icon-plus"></i>&nbsp;<?php echo $lang['ADD_ROOM']; ?>
									</button>
									<br/><br/>
								</div>
								<div class="col-md-3">
									<button class="btn btn-primary"  id="booking-form-check" name="booking-form-check">
										<?php echo $lang['CHOOSE_ROOMS_AND_RATES']; ?>
									</button>
								</div>
								<div class="col-md-3"></div>
							</div>
						</fieldset>
					</div>
				</div>
			</div>
		</div><!-- /.col-->
	</div><!-- /.row -->

	<div id="shAvailRooms" class="row">
		<!--div class=""-->
		<div class="col-md-6 col-xs-offset-2">
			<div class="panel panel-default">
				<div id="roomPanel">
					<div class="panel-heading">
						<?php echo $lang['CHECK_AVAILABILITY_TITLE']; ?>
						<div class="bookCart">
							<!--<svg class="glyph stroked bag"><use xlink:href="#stroked-bag"></use></svg><div class="bookCartItems"></div>-->
							<ul class="book-cart-menu">
								<li class="dropdown pull-right">
									<!--a href="#" class="dropdown-toggle" data-toggle="dropdown"><svg class="glyph stroked basket "><use xlink:href="#stroked-basket"/></svg>My BookCart <span class="caret"></span></a>
									<ul class="dropdown-menu" role="menu">
										<li id="emptyRow">BookCart is Empty</li>
										<li id="book-total-cart">Total: <span id="total-cart">0EUR</span></li>
									</ul-->

									<button id="backCheckAvail" class='btn btn-primary'><?php echo $lang['BACK_CHECK_AVAIL']; ?></button>
								</li>
							</ul>
						</div>
					</div>
					<div class="panel-body">
						<div id="availRooms"></div>
						<!--button id="backCheckAvail" class='btn btn-primary'><?php echo $lang['BACK_CHECK_AVAIL']; ?></button-->
					</div>
				</div>
			</div>
		</div><!-- /.col-->
		<div class="col-md-2">
			<div class="panel panel-default">
				<div class="panel-heading"><svg class="glyph stroked clipboard-with-paper"><use xlink:href="#stroked-clipboard-with-paper"></use></svg><?php echo $lang['BOOKINGS']; ?></div>
				<div class="panel-body booking-list-body">
					<ul class="booking-list">
						<li id="emptyRow">No bookings...</li>
						<li id="book-total-cart">Total: <span id="total-cart">0 EUR</span></li>
					</ul>
				</div>
				<div class="panel-footer booking-list-footer">
					<button id="checkout" class='btn btn-primary'><?php echo $lang['BOOK_NOW']; ?></button>
				</div>
			</div>
		</div><!--/.col-->
	</div><!-- /.row -->

	<div id="shCheckout" class="row">
		<div class="col-md-5 col-xs-offset-2">
			<div class="panel panel-default">
				<div id="checkoutRooms">
					<div class="panel-heading">
						<?php echo $lang['PROPERTY_SERVICES']; ?>
					</div>
					<div class="panel-body">
						<div id="propertyServices"></div>
					</div>
				</div>
				<div id="checkoutRooms">
					<div class="panel-heading">
						<?php echo $lang['ADD_ROOM_SERVICES']; ?>
					</div>
					<div class="panel-body">
						<div id="roomServices"></div>
					</div>
				</div>
				<div id="checkoutRooms">
					<div class="panel-heading">
						<?php echo $lang['PAYMENT']; ?>
					</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label><?php echo $lang['PAYMENT_METHOD']; ?><span id="required" >*</span></label>
									<select class="form-control complete-booking" name="booking-form-payment-method" required>
										<option value="cash"><?php echo $lang['PAYMENT_CASH']; ?></option>
										<option value="credit_card"><?php echo $lang['PAYMENT_CREDIT']; ?></option>
										<option value="bank_tranfer"><?php echo $lang['PAYMENT_BANK_TRANSFER']; ?></option>
										<option value="paypal"><?php echo $lang['PAYMENT_PAYPAL']; ?></option>
									</select>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label><?php echo $lang['PAYMENT_RECEIPT']; ?><span id="required" >*</span></label>
									<select class="form-control complete-booking invoice-type" name="booking-form-payment-receipt" required>
										<option value="receipt"><?php echo $lang['RECEIPT']; ?></option>
										<option value="company_invoice"><?php echo $lang['COMPANY_INVOICE']; ?></option>
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div id="checkoutForm">
					<div class="panel-heading">
						<?php echo $lang['CHECKOUT_FORM']; ?>
					</div>
					<div class="panel-body">
						<div id="checkout_form">
							<div class="row">
								<div class="col-md-12">
									<label><span id="required" >*</span><?php echo $lang['REQUIRED_FIELDS']; ?></label>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label><?php echo $lang['FIRST_NAME']; ?><span id="required" >*</span></label>
										<input class="form-control complete-booking" name="booking-form-firstname" type="text" required>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label><?php echo $lang['LAST_NAME']; ?><span id="required" >*</span></label>
										<input class="form-control complete-booking" name="booking-form-lastname" type="text" required>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<label><?php echo $lang['EMAIL_ADDRESS']; ?><span id="required" >*</span></label>
										<input class="form-control complete-booking" name="booking-form-email" type="text" required>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label><?php echo $lang['TELEPHONE']; ?><span id="required" >*</span></label>
										<input class="form-control complete-booking" name="booking-form-telephone" type="text" required>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label><?php echo $lang['COUNTRY']; ?><span id="required" >*</span></label>
										<select id="CustomerCountry" class="form-control complete-booking" name="booking-form-country" required>
										    <option value="">Select your country</option>
												<option value="Afghanistan">Afghanistan</option>
												<option value="Åland Islands">Åland Islands</option>
												<option value="Albania">Albania</option>
												<option value="Algeria">Algeria</option>
												<option value="American Samoa">American Samoa</option>
												<option value="Andorra">Andorra</option>
												<option value="Angola">Angola</option>
												<option value="Anguilla">Anguilla</option>
												<option value="Antarctica">Antarctica</option>
												<option value="Antigua and Barbuda">Antigua and Barbuda</option>
												<option value="Argentina">Argentina</option>
												<option value="Armenia">Armenia</option>
												<option value="Aruba">Aruba</option>
												<option value="Australia">Australia</option>
												<option value="Austria">Austria</option>
												<option value="Azerbaijan">Azerbaijan</option>
												<option value="Bahamas">Bahamas</option>
												<option value="Bahrain">Bahrain</option>
												<option value="Bangladesh">Bangladesh</option>
												<option value="Barbados">Barbados</option>
												<option value="Belarus">Belarus</option>
												<option value="Belgium">Belgium</option>
												<option value="Belize">Belize</option>
												<option value="Benin">Benin</option>
												<option value="Bermuda">Bermuda</option>
												<option value="Bhutan">Bhutan</option>
												<option value="Bolivia">Bolivia</option>
												<option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
												<option value="Botswana">Botswana</option>
												<option value="Bouvet Island">Bouvet Island</option>
												<option value="Brazil">Brazil</option>
												<option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
												<option value="Brunei Darussalam">Brunei Darussalam</option>
												<option value="Bulgaria">Bulgaria</option>
												<option value="Burkina Faso">Burkina Faso</option>
												<option value="Burundi">Burundi</option>
												<option value="Cambodia">Cambodia</option>
												<option value="Cameroon">Cameroon</option>
												<option value="Canada">Canada</option>
												<option value="Cape Verde">Cape Verde</option>
												<option value="Cayman Islands">Cayman Islands</option>
												<option value="Central African Republic">Central African Republic</option>
												<option value="Chad">Chad</option>
												<option value="Chile">Chile</option>
												<option value="China">China</option>
												<option value="Christmas Island">Christmas Island</option>
												<option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
												<option value="Colombia">Colombia</option>
												<option value="Comoros">Comoros</option>
												<option value="Congo">Congo</option>
												<option value="Congo, The Democratic Republic of The">Congo, The Democratic Republic of The</option>
												<option value="Cook Islands">Cook Islands</option>
												<option value="Costa Rica">Costa Rica</option>
												<option value="Cote D'ivoire">Cote D'ivoire</option>
												<option value="Croatia">Croatia</option>
												<option value="Cuba">Cuba</option>
												<option value="Cyprus">Cyprus</option>
												<option value="Czech Republic">Czech Republic</option>
												<option value="Denmark">Denmark</option>
												<option value="Djibouti">Djibouti</option>
												<option value="Dominica">Dominica</option>
												<option value="Dominican Republic">Dominican Republic</option>
												<option value="Ecuador">Ecuador</option>
												<option value="Egypt">Egypt</option>
												<option value="El Salvador">El Salvador</option>
												<option value="Equatorial Guinea">Equatorial Guinea</option>
												<option value="Eritrea">Eritrea</option>
												<option value="Estonia">Estonia</option>
												<option value="Ethiopia">Ethiopia</option>
												<option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
												<option value="Faroe Islands">Faroe Islands</option>
												<option value="Fiji">Fiji</option>
												<option value="Finland">Finland</option>
												<option value="France">France</option>
												<option value="French Guiana">French Guiana</option>
												<option value="French Polynesia">French Polynesia</option>
												<option value="French Southern Territories">French Southern Territories</option>
												<option value="Gabon">Gabon</option>
												<option value="Gambia">Gambia</option>
												<option value="Georgia">Georgia</option>
												<option value="Germany">Germany</option>
												<option value="Ghana">Ghana</option>
												<option value="Gibraltar">Gibraltar</option>
												<option value="Greece">Greece</option>
												<option value="Greenland">Greenland</option>
												<option value="Grenada">Grenada</option>
												<option value="Guadeloupe">Guadeloupe</option>
												<option value="Guam">Guam</option>
												<option value="Guatemala">Guatemala</option>
												<option value="Guernsey">Guernsey</option>
												<option value="Guinea">Guinea</option>
												<option value="Guinea-bissau">Guinea-bissau</option>
												<option value="Guyana">Guyana</option>
												<option value="Haiti">Haiti</option>
												<option value="Heard Island and Mcdonald Islands">Heard Island and Mcdonald Islands</option>
												<option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option>
												<option value="Honduras">Honduras</option>
												<option value="Hong Kong">Hong Kong</option>
												<option value="Hungary">Hungary</option>
												<option value="Iceland">Iceland</option>
												<option value="India">India</option>
												<option value="Indonesia">Indonesia</option>
												<option value="Iran, Islamic Republic of">Iran, Islamic Republic of</option>
												<option value="Iraq">Iraq</option>
												<option value="Ireland">Ireland</option>
												<option value="Isle of Man">Isle of Man</option>
												<option value="Israel">Israel</option>
												<option value="Italy">Italy</option>
												<option value="Jamaica">Jamaica</option>
												<option value="Japan">Japan</option>
												<option value="Jersey">Jersey</option>
												<option value="Jordan">Jordan</option>
												<option value="Kazakhstan">Kazakhstan</option>
												<option value="Kenya">Kenya</option>
												<option value="Kiribati">Kiribati</option>
												<option value="Korea, Democratic People's Republic of">Korea, Democratic People's Republic of</option>
												<option value="Korea, Republic of">Korea, Republic of</option>
												<option value="Kuwait">Kuwait</option>
												<option value="Kyrgyzstan">Kyrgyzstan</option>
												<option value="Lao People's Democratic Republic">Lao People's Democratic Republic</option>
												<option value="Latvia">Latvia</option>
												<option value="Lebanon">Lebanon</option>
												<option value="Lesotho">Lesotho</option>
												<option value="Liberia">Liberia</option>
												<option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
												<option value="Liechtenstein">Liechtenstein</option>
												<option value="Lithuania">Lithuania</option>
												<option value="Luxembourg">Luxembourg</option>
												<option value="Macao">Macao</option>
												<option value="FYROM">FYROM</option>
												<option value="Madagascar">Madagascar</option>
												<option value="Malawi">Malawi</option>
												<option value="Malaysia">Malaysia</option>
												<option value="Maldives">Maldives</option>
												<option value="Mali">Mali</option>
												<option value="Malta">Malta</option>
												<option value="Marshall Islands">Marshall Islands</option>
												<option value="Martinique">Martinique</option>
												<option value="Mauritania">Mauritania</option>
												<option value="Mauritius">Mauritius</option>
												<option value="Mayotte">Mayotte</option>
												<option value="Mexico">Mexico</option>
												<option value="Micronesia, Federated States of">Micronesia, Federated States of</option>
												<option value="Moldova, Republic of">Moldova, Republic of</option>
												<option value="Monaco">Monaco</option>
												<option value="Mongolia">Mongolia</option>
												<option value="Montenegro">Montenegro</option>
												<option value="Montserrat">Montserrat</option>
												<option value="Morocco">Morocco</option>
												<option value="Mozambique">Mozambique</option>
												<option value="Myanmar">Myanmar</option>
												<option value="Namibia">Namibia</option>
												<option value="Nauru">Nauru</option>
												<option value="Nepal">Nepal</option>
												<option value="Netherlands">Netherlands</option>
												<option value="Netherlands Antilles">Netherlands Antilles</option>
												<option value="New Caledonia">New Caledonia</option>
												<option value="New Zealand">New Zealand</option>
												<option value="Nicaragua">Nicaragua</option>
												<option value="Niger">Niger</option>
												<option value="Nigeria">Nigeria</option>
												<option value="Niue">Niue</option>
												<option value="Norfolk Island">Norfolk Island</option>
												<option value="Northern Mariana Islands">Northern Mariana Islands</option>
												<option value="Norway">Norway</option>
												<option value="Oman">Oman</option>
												<option value="Pakistan">Pakistan</option>
												<option value="Palau">Palau</option>
												<option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option>
												<option value="Panama">Panama</option>
												<option value="Papua New Guinea">Papua New Guinea</option>
												<option value="Paraguay">Paraguay</option>
												<option value="Peru">Peru</option>
												<option value="Philippines">Philippines</option>
												<option value="Pitcairn">Pitcairn</option>
												<option value="Poland">Poland</option>
												<option value="Portugal">Portugal</option>
												<option value="Puerto Rico">Puerto Rico</option>
												<option value="Qatar">Qatar</option>
												<option value="Reunion">Reunion</option>
												<option value="Romania">Romania</option>
												<option value="Russian Federation">Russian Federation</option>
												<option value="Rwanda">Rwanda</option>
												<option value="Saint Helena">Saint Helena</option>
												<option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
												<option value="Saint Lucia">Saint Lucia</option>
												<option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
												<option value="Saint Vincent and The Grenadines">Saint Vincent and The Grenadines</option>
												<option value="Samoa">Samoa</option>
												<option value="San Marino">San Marino</option>
												<option value="Sao Tome and Principe">Sao Tome and Principe</option>
												<option value="Saudi Arabia">Saudi Arabia</option>
												<option value="Senegal">Senegal</option>
												<option value="Serbia">Serbia</option>
												<option value="Seychelles">Seychelles</option>
												<option value="Sierra Leone">Sierra Leone</option>
												<option value="Singapore">Singapore</option>
												<option value="Slovakia">Slovakia</option>
												<option value="Slovenia">Slovenia</option>
												<option value="Solomon Islands">Solomon Islands</option>
												<option value="Somalia">Somalia</option>
												<option value="South Africa">South Africa</option>
												<option value="South Georgia and The South Sandwich Islands">South Georgia and The South Sandwich Islands</option>
												<option value="Spain">Spain</option>
												<option value="Sri Lanka">Sri Lanka</option>
												<option value="Sudan">Sudan</option>
												<option value="Suriname">Suriname</option>
												<option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
												<option value="Swaziland">Swaziland</option>
												<option value="Sweden">Sweden</option>
												<option value="Switzerland">Switzerland</option>
												<option value="Syrian Arab Republic">Syrian Arab Republic</option>
												<option value="Taiwan, Province of China">Taiwan, Province of China</option>
												<option value="Tajikistan">Tajikistan</option>
												<option value="Tanzania, United Republic of">Tanzania, United Republic of</option>
												<option value="Thailand">Thailand</option>
												<option value="Timor-leste">Timor-leste</option>
												<option value="Togo">Togo</option>
												<option value="Tokelau">Tokelau</option>
												<option value="Tonga">Tonga</option>
												<option value="Trinidad and Tobago">Trinidad and Tobago</option>
												<option value="Tunisia">Tunisia</option>
												<option value="Turkey">Turkey</option>
												<option value="Turkmenistan">Turkmenistan</option>
												<option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
												<option value="Tuvalu">Tuvalu</option>
												<option value="Uganda">Uganda</option>
												<option value="Ukraine">Ukraine</option>
												<option value="United Arab Emirates">United Arab Emirates</option>
												<option value="United Kingdom">United Kingdom</option>
												<option value="United States">United States</option>
												<option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
												<option value="Uruguay">Uruguay</option>
												<option value="Uzbekistan">Uzbekistan</option>
												<option value="Vanuatu">Vanuatu</option>
												<option value="Venezuela">Venezuela</option>
												<option value="Viet Nam">Viet Nam</option>
												<option value="Virgin Islands, British">Virgin Islands, British</option>
												<option value="Virgin Islands, U.S.">Virgin Islands, U.S.</option>
												<option value="Wallis and Futuna">Wallis and Futuna</option>
												<option value="Western Sahara">Western Sahara</option>
												<option value="Yemen">Yemen</option>
												<option value="Zambia">Zambia</option>
												<option value="Zimbabwe">Zimbabwe</option>
										</select>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<label><?php echo $lang['ADDRESS']; ?></label>
										<input class="form-control complete-booking" name="booking-form-address" data-name="" type="text">
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label><?php echo $lang['CITY']; ?></label>
										<input class="form-control complete-booking" name="booking-form-city" type="text">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label><?php echo $lang['ZIP_CODE']; ?></label>
										<input class="form-control complete-booking" name="booking-form-zip" type="text">
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<label><?php echo $lang['NOTES']; ?></label>
										<textarea class="form-control complete-booking" name="booking-form-notes" rows="3"></textarea>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div id="checkoutRooms-invoice">
					<div class="panel-heading">
						<?php echo $lang['INVOICE_INFORMATION']; ?>
					</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label><?php echo $lang['COMPANY_NAME']; ?></label>
									<input class="form-control complete-booking" name="booking-form-company-name" type="text">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label><?php echo $lang['INDUSTRY']; ?></label>
									<input class="form-control complete-booking" name="booking-form-industry" type="text">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label><?php echo $lang['COMPANY_VAT_NUM']; ?></label>
									<input class="form-control complete-booking" name="booking-form-company-vat" type="text">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label><?php echo $lang['COMPANY_ADDRESS']; ?></label>
									<input class="form-control complete-booking" name="booking-form-company-address" type="text">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label><?php echo $lang['COMPANY_ZIP']; ?></label>
									<input class="form-control complete-booking" name="booking-form-company-zip" type="text">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label><?php echo $lang['COMPANY_PHONE']; ?></label>
									<input class="form-control complete-booking" name="booking-form-company-phone" type="text">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label><?php echo $lang['NOTES']; ?></label>
									<textarea class="form-control complete-booking" name="booking-form-company-notes" rows="3"></textarea>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div id="checkoutRooms">
					<div class="panel-body">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<button id="complete_book" class='btn btn-primary'><?php echo $lang['BOOK_NOW']; ?></button>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<button id="backCheckAvail-2" class='btn btn-primary'><?php echo $lang['CANCEL']; ?></button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div><!-- /.col-->
		<div class="col-md-3">
			<div class="panel panel-default">
				<div class="panel-heading"><svg class="glyph stroked clipboard-with-paper"><use xlink:href="#stroked-clipboard-with-paper"></use></svg><?php echo $lang['BOOKINGS']; ?></div>
				<div class="panel-body booking-list-checkout-body">
					<div class="check_in_out">
						<label><?php echo $lang['CHECK_IN']; ?>:&nbsp;</label><span id="check_in"></span><br/>
						<label><?php echo $lang['CHECK_OUT']; ?>:&nbsp;</label><span id="check_out"></span><br/>
						<label><?php echo $lang['NIGHTS']; ?>:&nbsp;</label>(<span id="numNights"></span>)<br/>
					</div>
					<!--ul class="booking-list-checkout">
						<li id="emptyRow">No bookings...</li>
					</ul-->
					<div class="booking-list-checkout">
						<p id="emptyRow">No bookings...</p>
					</div>
					<label class="hotelSrv">Hotel Services</label>
					<ul class="hotel-list-services">
						<li id="emptyRow">None service is selected...</li>
					</ul>
					<label class="roomsSrv">Room Services</label>
					<ul class="rooms-list-services">
						<li id="emptyRow">None service is selected...</li>
					</ul>
				</div>
				<div class="panel-footer booking-list-checkout-footer">
					<label><?php echo $lang['GRAND_TOTAL']; ?>:&nbsp;</label><span class="checkoutTotal"></span>
				</div>
			</div>
		</div><!--/.col-->
	</div><!-- /.row -->


	<div id="shBookingConfirmation" class="row">

		<div class="col-xs-10">
			<div id="printarea" class="panel panel-default">
					<div class="panel-heading">
						<?php echo $lang['NEW_REQUEST']; ?>
					</div>
					<div id="confirmation-reservation" class="panel-body">
						<div class="row">
							<div class="col-md-3">
								<div class="form-group">
									<label><?php echo $lang['RESERVATION_CODE']; ?></label>
									<span class="confirmation-reservation-code"><span></br>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label><?php echo $lang['PIN_CODE']; ?></label></br>
									<span class="confirmation-reservation-pin"><span>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label><?php echo $lang['CHECK_IN']; ?></label></br>
									<span class="confirmation-reservation-checkin"><span>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label><?php echo $lang['CHECK_OUT']; ?></label></br>
									<span class="confirmation-reservation-checkout"><span>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-3">
								<div class="form-group">
									<label><?php echo $lang['FULL_NAME']; ?></label></br>
									<span class="confirmation-reservation-fullname"><span>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label><?php echo $lang['EMAIL_ADDRESS']; ?></label></br>
									<span class="confirmation-reservation-email"><span>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label><?php echo $lang['TELEPHONE']; ?></label></br>
									<span class="confirmation-reservation-telephone"><span>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">

								</div>
							</div>
						</div>
						<div class="row room-row room-row-data">
							<div class="row">
								<div class="col-md-3">
									<div class="form-group">
										<label class="room-row-num"></label></br>
										<span class="confirmation-reservation-roomDesc"><span>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label></label></br>
										<span class="confirmation-reservation-quests"><span>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label></label></br>
										<span class="confirmation-reservation-standard-rate"><?php echo $lang['STANDARD_RATE']; ?><span>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label></label></br>
										<span class="confirmation-reservation-cost"><span>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="roomsSrv">Room Services</label>
										<ul class="confirmation-reservation-rooms-services">
											<li id="emptyRow">None service is selected...</li>
										</ul>
									</div>
								</div>
							</div>
						</div>
						<div class="row room-row-total">
							<div class="col-md-3">
								<div class="form-group">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label><?php echo $lang['GRAND_TOTAL']; ?>:</label>
									<span class="confirmation-reservation-grand-total"><span>
								</div>
							</div>
						</div>
					</div>
			</div>
		</div><!-- /.col-->
	</div><!-- /.row -->



	<div class="modal"><div class='imgContainer'><img src='/hotelier/images/ajax-loader.gif' /> Please wait...</div><!-- Place at bottom of page --></div>
	<!-- <script src="js/jquery-1.11.1.min.js"></script> -->
	<script src="/hotelier/js/jquery-1.12.3.min.js"></script>
	<script src="/hotelier/js/bootstrap.min.js"></script>
	<script src="/hotelier/js/bootstrap-datepicker.js"></script>
	<script type="text/javascript" src="js/jquery.e-calendar.js"></script>
	<script src="js/jquery.cookie.js"></script>
	<script type="text/javascript" src="/hotelier/js/roomier.config.js"></script>
	<script>
		var propertyID = <?php echo $pref['PROPERTYID']; ?>;
	</script>
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
