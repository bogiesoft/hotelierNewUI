<?php

include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
//print_r($data);
$output = array();
$result;
$responce = "";
$resp = "false";
$respdata = array();

$action = $data["action"];

if ($action == "add_booking"){

		$propertyID = $data["propertyID"];
		$checkin = $data["check-in"];
		$checkout = $data["check-out"];
		$orderDate = date("Y-m-d");
		$bookingStatus = 1; //TO BE UPDATED!!!!!

		$guests = $data["guests"]; //Array
		$rooms = $data["rooms"];	 //Array

		$roomsTotal = $data["roomsTotal"];
		$roomsCurrency = htmlentities($data["roomsCurrency"], ENT_QUOTES);

		$payment_method = $data["payment-method"];
		$payment_data = ""; //$data["payment-data"]; //TO BE UPDATED
		$payment_receipt = $data["payment-receipt"];

		$firstname = $data["firstname"];
		$lastname = $data["lastname"];
		$email = $data["email"];
		$telephone = $data["telephone"];
		$country = $data["country"];
		$address = $data["address"];
		$city = $data["city"];
		$zip = $data["zip"];
		$notes = $data["notes"];

		$company_name = $data["company-name"];
		$industry = $data["industry"];
		$company_vat = $data["company-vat"];
		$company_address = $data["company-address"];
		$company_zip = $data["company-zip"];
		$company_phone = $data["company-phone"];
		$company_notes = $data["company-notes"];

		$hotelServices = $data["hotelServices"];
		$roomServices = $data["roomServices"];

		$pin = rand(10000, 32767);
		$reservationCode = generateRandomString(8);
		$booking_origin = "local";

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		// prepare and bind
		$stmt = $conn->prepare("INSERT INTO bookings (pin,reservationCode,booking_origin,propertyID,checkin,checkout,orderDate,bookingStatus,
			roomsTotalPrice,bookingTotalPrice,bookingCurrency,paymentMethod,paymentData,paymentReceipt,name,lastname,
			email,phone,country,address,city,zip,notes,companyName,companyVat,companyIndustry,companyAddress,companyZip,companyPhone,companyNotes)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("sssissssssssssssssssssssssssss",
		$pin,
		$reservationCode,
		$booking_origin,
		$propertyID,
		$checkin,
		$checkout,
		$orderDate,
		$bookingStatus,
		$roomsTotal,
	  $roomsTotal, //TO BE UPDATED
		$roomsCurrency,
		$payment_method,
		$payment_data,
		$payment_receipt,
		$firstname,
		$lastname,
		$email,
		$telephone,
		$country,
		$address,
		$city,
		$zip,
		$notes,
		$company_name,
		$industry,
		$company_vat,
		$company_address,
		$company_zip,
		$company_phone,
		$company_notes);

		if ($stmt->execute()) {

			//Return status
			$responce = "Booking completed sucessfully.";
			$resp = "true";

		} else {
			$responce = "Booking failed due to insert failure: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$bookingID = 0;

		$bookingID = $conn->insert_id;

		if ($bookingID == 0) {
			$responce = "Booking failed due to zero bookingID(" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
			$respdata = "";
			$result = array('responce' => $responce, 'resp' => $resp, 'data' => $respdata);
			$conn->close();
			echo json_encode($result);
		}

		$conn->close();

		for ($i=0; $i < count($rooms); $i++) {

			$currentRoomIdentity = getRoomIdentity($rooms[$i]["roomID"],$propertyID,$checkin,$checkout);

			if ($currentRoomIdentity == "error") {

					$responce = "Booking failed on roomIdentify(" . $stmt->errno . ") " . $stmt->error;
					$resp = "false";
					$respdata = "";
					$result = array('responce' => $responce, 'resp' => $resp, 'data' => $respdata);
					echo json_encode($result);

			} else {

					writeBookingRooms($bookingID,$rooms[$i]["roomID"],$currentRoomIdentity, $guests[$i]["adults"],$guests[$i]["children"],$guests[$i]["infants"],$rooms[$i]["totalPrice"],htmlentities($rooms[$i]["currency"], ENT_QUOTES));

					$lastNight = date('Y-m-d', strtotime($checkout. ' - 1 days'));

					/*
					BOOKING VALUES
					0 : Room free
					1 : Booking exist, but status pending
					2 : Booking Complete
					*/
					$booking_value = 1;

					changeRoomsBookingStatus($propertyID,$checkin,$lastNight,$currentRoomIdentity,$booking_value);
			}

		}

		//Store hotel services
		for ($k=0; $k < count($hotelServices); $k++) {

				$serviceID = $hotelServices[$k]["serviceID"];
				$roomtypeID = $hotelServices[$k]["roomtypeID"];
				$service_name = $hotelServices[$k]["service-name"];
				$service_cost = $hotelServices[$k]["service-cost"];
				$service_currency = $hotelServices[$k]["service-currency"];

				writeBookingServices($bookingID, $serviceID, $roomtypeID, $service_name, $service_cost,htmlentities($service_currency, ENT_QUOTES));
		}

		//Store room services
		for ($l=0; $l < count($roomServices); $l++) {

				$serviceID = $roomServices[$l]["serviceID"];
				$roomtypeID = $roomServices[$l]["roomtypeID"];
				$service_name = $roomServices[$l]["service-name"];
				$service_cost = $roomServices[$l]["service-cost"];
				$service_currency = $roomServices[$l]["service-currency"];

				writeBookingServices($bookingID, $serviceID, $roomtypeID, $service_name, $service_cost, htmlentities($service_currency, ENT_QUOTES));
		}


		////Get property data////

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("SELECT property_name,email,website,address,town,
		postcode,country FROM properties WHERE propertyID=?");
		$stmt->bind_param("i", $propertyID);

		if ($stmt->execute()) {

			$stmt->bind_result($property_name_result,$email_result,$website_result,$address_result,$town_result,
			$postcode_result,$country_result);

			while ($stmt->fetch()) {
				$property_name = $property_name_result;
				$hotelier_email = $email_result;
				$website = $website_result;
				$address = $address_result;
				$town = $town_result;
				$postcode = $postcode_result;
				$country = $country_result;
			}
		}

		$conn->close();

		//SEND email to customer
		$emailSubject='Reservation receipt';
		$emailTo=$email;

		 $message  = '<div width="100%" style="padding: 25px; background: #ededed">';
		 $message .= '<img src="http://roomier.eu/clients/images/roomier-logo.png">';
//		 $message .= '<img src="http://booking.roomier.gr/uploads/files/logos/logo_' . $propertyID . '.png" >';
		 $message .= '</div>';
		 //main content
		 $message .= '<div width="100%" style="padding: 20px; background: #f8f8f8">';
		//  $message .= '<p><b>Room: </b>' . $_POST['inputRoom'];
		//  $message .= '<p><b>Customer name: </b>' . $_POST['inputFirstName'];
		//  $message .= ' ' . $_POST['inputLastName'] . '</p>';
		//  $message .= '<p><b>Customer Email: </b>' . $_POST['inputEMail'] . '</p>';
		//  $message .= '<p><b>Customer Comments and Questions: </b></p>';
		//  $message .= '<p>' . $_POST['inputComments'] . '</p>';
		 $message .= '<table>';
	 		$message .= '<tr height=60px; style="font-size: 20px; padding: 20px; color:red;"><td width=300px>Reservation Code: <b>' . $reservationCode . '</b></td><td width=300px>PIN: <b>' . $pin . '</b></td></tr>';
	 		$message .= '<tr height=30px;><td width=300px>Name: ' . $firstname . '</td><td width=300px>Surname: ' . $lastname . '</td></tr>';
	 		$message .= '<tr height=30px;><td width=300px>Email: ' . $email . '</td><td width=300px>Phone: ' . $telephone . '</td></tr>';
	 		$message .= '<tr height=30px;><td width=300px>Check-in: ' . $checkin . '</td><td width=300px>Check-out: ' . $checkout . '</td></tr>';

	 		$message .= '<tr height=50px;><td width=300px><b>Rooms</b></td></tr>';
	 		for ($i=0; $i < count($rooms); $i++) {
	 			$message .= '<tr height=30px;><td width=300px>Room type: ' . $rooms[$i]["roomType"] . '</td><td width=300px>Room type total cost: ' . $rooms[$i]["totalPrice"] . $rooms[$i]["currency"] . '</td></tr>';
	 		}


	 		$message .= '<tr height=50px;><td width=300px><b>Room Services</b></td></tr>';
	 		for ($l=0; $l < count($roomServices); $l++) {
	 			$message .= '<tr height=30px;><td width=300px>Room Services: ' . $roomServices[$l]["service-name"] . '</td><td width=300px>Room service cost: ' . $roomServices[$l]["service-cost"] . $roomServices[$l]["service-currency"] . '</td></tr>';
	 		}


	 		$message .= '<tr height=50px;><td width=300px><b>Hotel Services</b></td></tr>';
	 		for ($k=0; $k < count($hotelServices); $k++) {
	 			$message .= '<tr height=30px;><td width=300px>Hotel Service: ' . $hotelServices[$k]["service-name"] . '</td><td width=300px>Room service cost: ' . $hotelServices[$k]["service-cost"] . $hotelServices[$k]["service-currency"] . '</td></tr>';
	 		}

	 		$message .= '<tr height=50px;><td width=300px><b>' . $property_name . ' Address</b></td></tr>';
	 		$message .= '<tr height=30px;><td width=200px>Address: ' . $address . '</td><td width=200px>Town: ' . $town . '</td><td width=200px>Postcode: ' . $postcode . '</td></tr>';
	 		$message .= '<tr height=30px;><td width=200px>WebSite: ' . $website . '</td></tr>';


	 		$message .= '</table>';
		 $message .= '</div>';
		 //message footer
		 $message .= '<div align="center" width="100%" style="padding: 25px; background: #ededed">';
		 $message .= '<img style="padding: 0px 5px;" src="https://ci4.googleusercontent.com/proxy/UqiHU_kJJMA11QYI05_0GgX1z7kwVKX4RzmVEhndfka-VnyJn_dFcA6WaE1n6kGICnLIHySR8tp3p4gr8cm1nGPrsFQKmUHP3p2hPy1Brg=s0-d-e1-ft#http://www.electroholic.gr/newsletter/images/facebook.png">';
		 $message .= '<img style="padding: 0px 5px;" src="https://ci3.googleusercontent.com/proxy/kEH2WgCla9YHd2i_3ODYkG_tms6yclPGY-3Zb5tsoFNjPAJ0wai1RDtP51shaD5utisT6Fw2SnePHGZKNVD4w117fmuKunmIhiKQhBbW=s0-d-e1-ft#http://www.electroholic.gr/newsletter/images/twitter.png">';
		 $message .= '<img style="padding: 0px 5px;" src="https://ci6.googleusercontent.com/proxy/NZvpbsJPr9OC29ffRg19CH4eku01J4f72AsADNlv664lXGCGafcIZ96fyR_oMIwGiXAb1AV4xR-9RnR1jzL7YqJVypEfFS3xrbs0VRhI=s0-d-e1-ft#http://www.electroholic.gr/newsletter/images/youtube.png">';
		 $message .= '<img style="padding: 0px 5px;" src="https://ci3.googleusercontent.com/proxy/wjqT1Rv039Ovl4GTOHShWl6VWVtNXvGf9sChq0UUMUx2boAPUyafF_BlGCCsaJ5B1EEtY2_gXM4v2f-xPMJ9t3SsxH2cWBDPHbZaEGvGLJPo3A=s0-d-e1-ft#http://www.electroholic.gr/newsletter/images/google-plus.png">';
		 $message .= '<img style="padding: 0px 5px;" src="https://ci5.googleusercontent.com/proxy/85--knV_LYznbAvK7fiVxkzJpwxmsmiJJyS3N5Ar8vfxFMmkVmqLdxBYVk9NhIfHsJjsdDtblIMiDmUNPi-9ljvecu_s29gV9NrilYLPx8E=s0-d-e1-ft#http://www.electroholic.gr/newsletter/images/instagram.png">';
		 $message .= '<p><a href="http://roomier.gr/" style="color: #4b4a4a; text-decoration: none;">visit our website | </a>';
		 $message .= '<a href="http://booking.roomier.gr/hotelier/login.html" style="color: #4b4a4a; text-decoration: none;">log in to your account | </a>';
		 $message .= '<a href="http://roomier.eu/clients/clientarea.php" style="color: #4b4a4a; text-decoration: none;">get support</a>';
		 $message .= '</div>';

		// $message  = '<img src="http://booking.roomier.gr/uploads/files/logos/logo_' . $propertyID . '.png" >';
		// $message .= '<div style="font-size: 20px; padding: 20px; display: table; color:#DDDDDD; background: #333333;">' . $property_name . ' booking</div>';
		// $message .= '<div style="padding: 20px; background: #EEEEEE; color: #222222;">';
		// $message .= '<table>';
		// $message .= '<tr height=60px; style="font-size: 20px; padding: 20px; color:red;"><td width=300px>Reservation Code: <b>' . $reservationCode . '</b></td><td width=300px>PIN: <b>' . $pin . '</b></td></tr>';
		// $message .= '<tr height=30px;><td width=300px>Name: ' . $firstname . '</td><td width=300px>Surname: ' . $lastname . '</td></tr>';
		// $message .= '<tr height=30px;><td width=300px>Email: ' . $email . '</td><td width=300px>Phone: ' . $telephone . '</td></tr>';
		// $message .= '<tr height=30px;><td width=300px>Check-in: ' . $checkin . '</td><td width=300px>Check-out: ' . $checkout . '</td></tr>';
		//
		// $message .= '<tr height=50px;><td width=300px><b>Rooms</b></td></tr>';
		// for ($i=0; $i < count($rooms); $i++) {
		// 	$message .= '<tr height=30px;><td width=300px>Room type: ' . $rooms[$i]["roomType"] . '</td><td width=300px>Room type total cost: ' . $rooms[$i]["totalPrice"] . $rooms[$i]["currency"] . '</td></tr>';
		// }
		//
		//
		// $message .= '<tr height=50px;><td width=300px><b>Room Services</b></td></tr>';
		// for ($l=0; $l < count($roomServices); $l++) {
		// 	$message .= '<tr height=30px;><td width=300px>Room Services: ' . $roomServices[$l]["service-name"] . '</td><td width=300px>Room service cost: ' . $roomServices[$l]["service-cost"] . $roomServices[$l]["service-currency"] . '</td></tr>';
		// }
		//
		//
		// $message .= '<tr height=50px;><td width=300px><b>Hotel Services</b></td></tr>';
		// for ($k=0; $k < count($hotelServices); $k++) {
		// 	$message .= '<tr height=30px;><td width=300px>Hotel Service: ' . $hotelServices[$k]["service-name"] . '</td><td width=300px>Room service cost: ' . $hotelServices[$k]["service-cost"] . $hotelServices[$k]["service-currency"] . '</td></tr>';
		// }
		//
		// $message .= '<tr height=50px;><td width=300px><b>' . $property_name . ' Address</b></td></tr>';
		// $message .= '<tr height=30px;><td width=200px>Address: ' . $address . '</td><td width=200px>Town: ' . $town . '</td><td width=200px>Postcode: ' . $postcode . '</td></tr>';
		// $message .= '<tr height=30px;><td width=200px>WebSite: ' . $website . '</td></tr>';
		//
		//
		// $message .= '</table>';
		// $message .= '</div>';
		// $message .= '<div style="font-size: 16px; padding: 20px; display: table; color:#DDDDDD; background: #333333;">This reservation was powered by Roomier Booking system</div>';

		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		//sleep(1);

		mail( $emailTo, $emailSubject, $message, $headers); //or die("Message send failed!");


		//SEND email to hotelier

		$emailTo = $hotelier_email;

		if ($emailTo != "") {

			$emailSubject='New booking';

			sleep(1);
			mail( $emailTo, $emailSubject, $message, $headers); //or die("Message send failed!");


		}

		$resp = "true";
		$respdata = array('pin' => $pin, 'reservationCode' => $reservationCode);


} elseif ($action == "update_booking_room") {
		$token = $data["token"];
		$email = $data["email"];
		$userID = checkToken($token,$email);

		if ($userID == "error") {
			$responce = "Authentication failed";
			$resp = "false";
			$result = array('responce' => $responce, 'resp' => $resp, 'data' => $respdata);
			echo json_encode($result);
			exit;
		}

		$roomID = $data["roomID"];

		$room_name = $data["room_name"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("UPDATE rooms SET room_name=? WHERE roomID=?");
		$stmt->bind_param("si",
		$room_name,
		$roomID);

		if ($stmt->execute()) {
			$responce = "Room updated sucessfully";
			$resp = "true";
		} else {
			$responce = "Update failed(" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();

	} elseif ($action == "update_booking_status") {
			$token = $data["token"];
			$email = $data["email"];
			$checkin = "";
			$checkout = "";
			// $checkin = $data["checkin"];
			// $checkout = $data["checkout"];
			$userID = checkToken($token,$email);

			if ($userID == "error") {
				$responce = "Authentication failed";
				$resp = "false";
				$result = array('responce' => $responce, 'resp' => $resp, 'data' => $respdata);
				echo json_encode($result);
				exit;
			}

			$bookingID = $data["bookingID"];
			$status = $data["status"];

			// Create connection
			$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

			// Check connection
			if ($conn->connect_error) {
				die("Connection failed: " . $conn->connect_error);
			}

			$stmt = $conn->prepare("UPDATE bookings SET bookingStatus=? WHERE bookingID=?");
			$stmt->bind_param("si",
			$status,
			$bookingID);

			if ($stmt->execute()) {
				$responce = "Booking updated sucessfully";
				$resp = "true";

				//Read rooms included in booking
				$rooms = getBookingRooms($bookingID,"999",$checkin,$checkout);

				//FREE ALL ROOMs included in Booking
				for ($i=0; $i < count($rooms); $i++) {

					/*
					BOOKING VALUES
					0 : Room free
					1 : Booking exist, but status pending
					2 : Booking Complete
					*/
					$booking_value = 2;
					$currentRoomIdentity = $rooms[$i]["roomIdentify"];
					$lastNight = date('Y-m-d', strtotime($checkout. ' - 1 days'));

					changeRoomsBookingStatus($propertyID,$checkin,$lastNight,$currentRoomIdentity,$booking_value);

				}



			} else {
				$responce = "Update failed(" . $stmt->errno . ") " . $stmt->error;
				$resp = "false";
			}

			$conn->close();


} elseif ($action == "show_booking"){
		$token = $data["token"];
		$email = $data["email"];
		$userID = checkToken($token,$email);

		if ($userID == "error") {
			$responce = "Authentication failed";
			$resp = "false";
			$result = array('responce' => $responce, 'resp' => $resp, 'data' => $respdata);
			echo json_encode($result);
			exit;
		}

		$bookingID = $data["bookingID"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("SELECT pin,reservationCode,booking_origin,propertyID,checkin,checkout,orderDate,bookingStatus,
			roomsTotalPrice,bookingTotalPrice,bookingCurrency,paymentMethod,paymentData,paymentReceipt,name,lastname,
			email,phone,country,address,city,zip,notes,companyName,companyVat,companyIndustry,companyAddress,companyZip,companyPhone,companyNotes
			FROM bookings WHERE bookingID=?");
		$stmt->bind_param("i", $bookingID);

		$responce = "Error";
		$resp = "false";

		if ($stmt->execute()) {

			$stmt->bind_result(
			$pin_result,
			$reservationCode_result,
			$booking_origin_result,
			$propertyID_result,
			$checkin_result,
			$checkout_result,
			$orderDate_result,
			$bookingStatus_result,
			$roomsTotalPrice_result,
			$bookingTotalPrice_result,
			$bookingCurrency_result,
			$paymentMethod_result,
			$paymentData_result,
			$paymentReceipt_result,
			$name_result,
			$lastname_result,
			$email_result,
			$phone_result,
			$country_result,
			$address_result,
			$city_result,
			$zip_result,
			$notes_result,
			$companyName_result,
			$companyVat_result,
			$companyIndustry_result,
			$companyAddress_result,
			$companyZip_result,
			$companyPhone_result,
			$companyNotes_result);

			while ($stmt->fetch()) {

				$pin = $pin_result;
				$reservationCode = $reservationCode_result;
				$booking_origin = $booking_origin_result;
				$propertyID = $propertyID_result;
			  $checkin	= $checkin_result;
				$checkout = $checkout_result;
				$orderDate = $orderDate_result;
				$bookingStatus = $bookingStatus_result;
				$roomsTotalPrice = $roomsTotalPrice_result;
				$bookingTotalPrice = $bookingTotalPrice_result;
				$bookingCurrency = $bookingCurrency_result;
				$paymentMethod = $paymentMethod_result;
				$paymentData = $paymentData_result;
				$paymentReceipt = $paymentReceipt_result;
				$name = $name_result;
				$lastname = $lastname_result;
				$email = $email_result;
				$phone = $phone_result;
				$country = $country_result;
				$address = $address_result;
				$city = $city_result;
				$zip = $zip_result;
				$notes = $notes_result;
				$companyName = $companyName_result;
				$companyVat = $companyVat_result;
				$companyIndustry = $companyIndustry_result;
				$companyAddress = $companyAddress_result;
				$companyZip = $companyZip_result;
				$companyPhone = $companyPhone_result;
				$companyNotes = $companyNotes_result;

			}

			$rooms = getBookingRooms($bookingID,$propertyID,$checkin,$checkout);
			$hotelServices = getBookingHotelServices($bookingID);
			$roomServices = getBookingRoomServices($bookingID);


			$responce = "Room data.";
			$resp = "true";
			$respdata = array('pin' => $pin
			, 'reservationCode' => $reservationCode
			, 'booking_origin' => $booking_origin
			, 'propertyID' => $propertyID
			, 'checkin' => $checkin
			, 'checkout' => $checkout
			, 'orderDate' => $orderDate
			, 'bookingStatus' => $bookingStatus
			, 'roomsTotalPrice' => $roomsTotalPrice
			, 'bookingTotalPrice' => $bookingTotalPrice
			, 'bookingCurrency' => $bookingCurrency
			, 'paymentMethod' => $paymentMethod
			, 'paymentData' => $paymentData
			, 'paymentReceipt' => $paymentReceipt
			, 'name' => $name
			, 'lastname' => $lastname
			, 'email' => $email
			, 'phone' => $phone
			, 'country' => $country
			, 'address' => $address
			, 'city' => $city
			, 'zip' => $zip
			, 'notes' => $notes
			, 'companyName' => $companyName
			, 'companyVat' => $companyVat
			, 'companyIndustry' => $companyIndustry
			, 'companyAddress' => $companyAddress
			, 'companyZip' => $companyZip
			, 'companyPhone' => $companyPhone
			, 'companyNotes' => $companyNotes
			, 'rooms' => $rooms
			, 'hotelServices' => $hotelServices
			, 'roomServices' => $roomServices);

		} else {
			$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


} elseif ($action == "show_property_bookings"){
		$token = $data["token"];
		$email = $data["email"];
		$userID = checkToken($token,$email);

		if ($userID == "error") {
			$responce = "Authentication failed";
			$resp = "false";
			$result = array('responce' => $responce, 'resp' => $resp, 'data' => $respdata);
			echo json_encode($result);
			exit;
		}


		$propertyID = $data["propertyID"];
		$status = "all"; //$data["status"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		if ($status == "all") {
			$stmt = $conn->prepare("SELECT bookingID,pin,reservationCode,booking_origin,checkin,checkout,orderDate,bookingStatus,bookingTotalPrice,paymentMethod,name,lastname FROM bookings WHERE propertyID=?");
			$stmt->bind_param("i", $propertyID);
		} else {
			$stmt = $conn->prepare("SELECT bookingID,pin,reservationCode,booking_origin,checkin,checkout,orderDate,bookingStatus,bookingTotalPrice,paymentMethod,name,lastname FROM bookings WHERE propertyID=? AND bookingStatus=?");
			$stmt->bind_param("is", $propertyID,$status);
		}

		$responce = "Error";
		$resp = "false";

		if ($stmt->execute()) {

			$stmt->bind_result($bookingID_result,
												 $pin_result,
												 $reservationCode_result,
												 $booking_origin_result,
												 $checkin_result,
												 $checkout_result,
												 $orderDate_result,
												 $bookingStatus_result,
												 $bookingTotalPrice_result,
												 $paymentMethod_result,
												 $name_result,
												 $lastname_result);

			while ($stmt->fetch()) {

				$output[]=array("bookingID" => $bookingID_result,
												"pin" => $pin_result,
												"reservationCode" => $reservationCode_result,
												"booking_origin" => $booking_origin_result,
												"checkin" => $checkin_result,
												"checkout" => $checkout_result,
												"orderDate" => $orderDate_result,
												"status" => $bookingStatus_result,
												"_status_class" => "_status_class_editable",
												"_status_id" => "_status_id" . $bookingID_result . "_editable",
												"bookingTotalPrice" => $bookingTotalPrice_result,
												"paymentMethod" => $paymentMethod_result,
												"name" => $name_result,
												"lastname" => $lastname_result);
			}

			$responce = "Bookings data.";
			$resp = "true";
			$respdata = $output;

		} else {
			$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();

} elseif ($action == "delete_booking"){
		$token = $data["token"];
		$email = $data["email"];
		$userID = checkToken($token,$email);

		if ($userID == "error") {
			$responce = "Authentication failed";
			$resp = "false";
			$result = array('responce' => $responce, 'resp' => $resp, 'data' => $respdata);
			echo json_encode($result);
			exit;
		}

		$bookingID = $data["bookingID"];

		$checkin = "";
		$checkout = "";

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
		    die("Connection failed: " . $conn->connect_error);
		}

		//Read booking checkin/checkout
		$stmt = $conn->prepare("SELECT propertyID,checkin,checkout FROM bookings WHERE bookingID=?");
		$stmt->bind_param("i", $bookingID);

		if ($stmt->execute()) {

			$stmt->bind_result($propertyID_result,$checkin_result,$checkout_result);

			while ($stmt->fetch()) {
				$propertyID = $propertyID_result;
				$checkin = $checkin_result;
				$checkout = $checkout_result;
			}
		}


		$stmt = $conn->prepare("DELETE FROM bookings WHERE bookingID=?");
		$stmt->bind_param("i",$bookingID);

		if ($stmt->execute()) {
			$responce = "Booking deleted.";
			$resp = "true";
		} else {
			$responce = "Delete failed: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		//Read rooms included in booking
		$rooms = getBookingRooms($bookingID,"","","");



		$stmt = $conn->prepare("DELETE FROM bookings_rooms WHERE bookingID=?");
		$stmt->bind_param("i",$bookingID);

		if ($stmt->execute()) {
			$responce = "Booking deleted.";
			$resp = "true";
		} else {
			$responce = "Delete failed: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$stmt = $conn->prepare("DELETE FROM bookings_services WHERE bookingID=?");
		$stmt->bind_param("i",$bookingID);

		if ($stmt->execute()) {
			$responce = "Booking deleted.";
			$resp = "true";
		} else {
			$responce = "Delete failed: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}


		$conn->close();

		//FREE ALL ROOMs included in Booking
		for ($i=0; $i < count($rooms); $i++) {

			/*
			BOOKING VALUES
			0 : Room free
			1 : Booking exist, but status pending
			2 : Booking Complete
			*/
			$booking_value = 0;
			$currentRoomIdentity = $rooms[$i]["roomIdentify"];
			$lastNight = date('Y-m-d', strtotime($checkout. ' - 1 days'));
			deleteRoomsBookingStatus($propertyID,$checkin,$lastNight,$currentRoomIdentity,$booking_value);


		}


} else {

	$responce = "Invalid action.";
	$resp = "false";

}

//Return result
$result = array('responce' => $responce, 'resp' => $resp, 'data' => $respdata);
echo json_encode($result);


////////////////////////////////////////////////////////////////////////////
////////////////////      FUNCTIONS       //////////////////////////////////
////////////////////////////////////////////////////////////////////////////

function checkToken($token, $email)
{
    $userID= "error";
		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$userID= "error";

		$stmt = $conn->prepare("SELECT userID FROM users WHERE email=? AND token = ?");
		$stmt->bind_param("ss", $email, $token);

		if ($stmt->execute()) {

			$stmt->bind_result($userID_result);

			while ($stmt->fetch()) {
					$userID = $userID_result;
			}
		}

		$conn->close();

    return $userID;
}


function getRoomIdentity($roomtypeID,$propertyID,$checkin,$checkout) {

	$roomsIdentify_onDuty = array();
	$roomsIdentify_onDuty = get_roomsID_onDuty($roomtypeID,$checkin,$checkout);

	if (count($roomsIdentify_onDuty) == 0) {
		return "error";
	}

	//Check if selected room types are available from the booking option
	//Cleanup previous lists from rooms that are allready booked
	$roomIdentify_free = get_roomsID_free($roomsIdentify_onDuty,$propertyID, $checkin,$checkout)[0];

	return $roomIdentify_free;

}


function get_roomsID_onDuty($roomtype_suitable,$arrival_date,$departure_date) {

	$output = array();
	$rooms_onDuty = array();


	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT roomID, room_identify, available, onDuty, startDate, endDate FROM rooms WHERE roomtypeID=?");
	$stmt->bind_param("i", $roomtype_suitable);


	if ($stmt->execute()) {

		$stmt->bind_result($roomID_result,$room_identify_result,
		$available_result,$onDuty_result,$startDate_result, $endDate_result);

		while ($stmt->fetch()) {
				//if ($available_result == 1) {
				if ($onDuty_result == "off") {
					array_push($rooms_onDuty,$room_identify_result);
				}	else {
					if($arrival_date >= $startDate_result && $arrival_date <= $endDate_result) {
									continue;
					} else {
									if($departure_date >= $startDate_result && $departure_date <= $endDate_result) {
													continue;
									} else {
										array_push($rooms_onDuty,$room_identify_result);
									}
					}
				}
		}

	} else {
		$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	//print_r($rooms_onDuty);
	$conn->close();
	return $rooms_onDuty;
}


function get_roomsID_free($roomsIdentify_onDuty,$propertyID, $arrival_date,$departure_date) {
	$rooms_in_booking = array();
	$free_rooms = array();
	$rooms_to_check = array();

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$sql = "SELECT * FROM availability WHERE propertyID='" . $propertyID . "'"; //to add property id
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				if (!in_array($row["roomID"], $rooms_in_booking)) {
					$rooms_in_booking[] = $row["roomID"];
				}
				$calendar[] = $row;
			}
	}

	if (count($roomsIdentify_onDuty) == 1) {
		if (!in_array($roomsIdentify_onDuty, $rooms_in_booking)) {
			$free_rooms[] = $roomsIdentify_onDuty;
		} else {
			$rooms_to_check[] = $roomsIdentify_onDuty;
		}
	} else {
		foreach ($roomsIdentify_onDuty as $current_room) {
			if (!in_array($current_room, $rooms_in_booking)) {
				$free_rooms[] = $current_room;
			} else {
				$rooms_to_check[] = $current_room;
			}
		}

	}

	$firstD = explode("-", $arrival_date);
	$firstD_year = $firstD[0];
	$firstD_month = $firstD[1];
	$firstD_day = $firstD[2];

	$lastD = explode("-", $departure_date);
	$lastD_year = $lastD[0];
	$lastD_month = $lastD[1];
	$lastD_day = $lastD[2];

	$date1 = new DateTime($arrival_date);
	$date2 = new DateTime($departure_date);

	$diff = $date2->diff($date1)->format("%a");
	//Loop for the rooms that should be checked

	 foreach ($rooms_to_check as $room_ch) {

		$new_firstD_day = "";
		$new_firstD_month = "";
		$new_firstD_year = "";

		$j=0;
		$room_status = "free";

		//Loop for all records in calendar
		for ($j = 0; $j < count($calendar); $j++) {

			//Same room under check with the room_id in calendar
			if($calendar[$j]["roomID"] == $room_ch && $calendar[$j]["year"] == $firstD_year && $calendar[$j]["month"] == $firstD_month){

					//Check if selected dates exist in bookings table
					$i=0;
					while($i<$diff) {

						$ch_day = "d" . ($firstD_day + $i);
						if ($calendar[$j][$ch_day] != "0") {
							$room_status = "booked";
						}

						//Dates excide months days (e.g. 28/12-2/1)
						if ($firstD_day+$i == cal_days_in_month (CAL_GREGORIAN, $firstD_month, $firstD_year)) {
								$new_firstD_day =1;
								if ($firstD_month == 12) {
									$new_firstD_month = 1;
									$new_firstD_year = $firstD_year + 1;
								} else {
									$new_firstD_month = $firstD_month + 1;
									$new_firstD_year = $firstD_year;

								}
								//Continue checks on next month
								break;
						}

						$i++;

					}
			}
	 	}

		//Continue checking on next month
		if ($new_firstD_day == "1" && $room_status == "free") {
			$new_arrival_date = $new_firstD_year . "-" . $new_firstD_month  .  "-01";
			$new_departure_date = $departure_date;
			$free_rooms[] = get_roomsID_free($room_ch,$propertyID, $new_arrival_date,$new_departure_date)[0];
		} else {
			if ($room_status == "free") {
				$free_rooms[] = $room_ch;
			}
		}
	}

	return $free_rooms;

}



function writeBookingRooms($bookingID,$roomtypeID,$currentRoomIdentity, $adults,$children,$infants,$totalPrice,$currency) {
	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	// prepare and bind
	$stmt = $conn->prepare("INSERT INTO bookings_rooms (bookingID,roomtypeID,roomIdentify,adults,childrens,infants,price,currency)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
	$stmt->bind_param("iisiiiss",
	$bookingID,
	$roomtypeID,
	$currentRoomIdentity,
	$adults,
	$children,
	$infants,
	$totalPrice,
	$currency);

	if ($stmt->execute()) {

		//Return status
		$responce = "Booking completed sucessfully.";
		$resp = "true";

	} else {
		$responce = "Booking failed on room availability setup: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	$conn->close();


}


function writeBookingServices($bookingID, $serviceID, $roomtypeID, $service_name, $service_cost, $service_currency) {

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	// prepare and bind
	$stmt = $conn->prepare("INSERT INTO bookings_services (bookingID, serviceID, roomtypeID, service_name, service_cost, service_currency)
		VALUES (?, ?, ?, ?, ?, ?)");
	$stmt->bind_param("iiisss",
	$bookingID,
	$serviceID,
	$roomtypeID,
	$service_name,
	$service_cost,
	$service_currency);

	if ($stmt->execute()) {

		//Return status
		$responce = "Services added sucessfully.";
		$resp = "true";

	} else {
		$responce = "Services storage failed: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	$conn->close();
}


function generateRandomString($length = 10) {

		//$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function getBookingRooms($bookingID,$propertyID,$checkin,$checkout) {

	$output = array();

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT book_room_id,bookingID,roomtypeID,roomIdentify,adults,childrens,infants,price,currency FROM bookings_rooms WHERE bookingID=? ");
	$stmt->bind_param("i", $bookingID);


	$responce = "Error";
	$resp = "false";

	if ($stmt->execute()) {
		$stmt->bind_result($book_room_id_result,
											 $bookingID,
											 $roomtypeID_result,
											 $roomIdentify_result,
											 $adults_result,
											 $childrens_result,
											 $infants_result,
											 $price_result,
											 $currency_result);


		while ($stmt->fetch()) {

			$free_rooms = getSameFreeRooms($roomtypeID_result,$propertyID,$checkin,$checkout);

			$output[]=array("book_room_id" => $book_room_id_result,
											"bookingID" => $bookingID,
											"roomtypeID" => $roomtypeID_result,
											"roomIdentify" => $roomIdentify_result,
											"adults" => $adults_result,
											"children" => $childrens_result,
											"infants" => $infants_result,
											"price" => $price_result,
											"currency" => $currency_result,
											"freeRooms" => $free_rooms);
		}

	} else {
		$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	$conn->close();
	return $output;

}

function getSameFreeRooms($roomtypeID,$propertyID,$checkin,$checkout) {

	$roomsIdentify_onDuty = array();
	$roomsIdentify_onDuty = get_roomsID_onDuty($roomtypeID,$checkin,$checkout);

	if (count($roomsIdentify_onDuty) == 0) {
		return "error";
	}

	//Check if selected room types are available from the booking option
	//Cleanup previous lists from rooms that are allready booked
	$roomIdentify_free = get_roomsID_free($roomsIdentify_onDuty,$propertyID, $checkin,$checkout);

	return $roomIdentify_free;

}


function getBookingHotelServices($bookingID) {

	$output = array();

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT book_serv_id,bookingID,serviceID,roomtypeID,service_name,service_cost,service_currency FROM bookings_services WHERE bookingID=? AND roomtypeID='0'");
	$stmt->bind_param("i", $bookingID);


	$responce = "Error";
	$resp = "false";

	if ($stmt->execute()) {
		$stmt->bind_result($book_serv_id_result,
											 $bookingID,
											 $serviceID_result,
											 $roomtypeID_result,
											 $service_name_result,
											 $service_cost_result,
											 $service_currency_result);


		while ($stmt->fetch()) {

			$output[]=array("book_serv_id" => $book_serv_id_result,
											"bookingID" => $bookingID,
											"serviceID" => $serviceID_result,
											"roomtypeID" => $roomtypeID_result,
											"service_name" => $service_name_result,
											"service_cost" => $service_cost_result,
											"service_currency" => $service_currency_result);
		}

	} else {
		$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	$conn->close();
	return $output;

}


function getBookingRoomServices($bookingID) {

	$output = array();

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT book_serv_id,bookingID,serviceID,roomtypeID,service_name,service_cost,service_currency FROM bookings_services WHERE bookingID=? AND roomtypeID !='0'");
	$stmt->bind_param("i", $bookingID);


	$responce = "Error";
	$resp = "false";

	if ($stmt->execute()) {
		$stmt->bind_result($book_serv_id_result,
											 $bookingID,
											 $serviceID_result,
											 $roomtypeID_result,
											 $service_name_result,
											 $service_cost_result,
											 $service_currency_result);


		while ($stmt->fetch()) {

			$output[]=array("book_serv_id" => $book_serv_id_result,
											"bookingID" => $bookingID,
											"serviceID" => $serviceID_result,
											"roomtypeID" => $roomtypeID_result,
											"service_name" => $service_name_result,
											"service_cost" => $service_cost_result,
											"service_currency" => $service_currency_result);
		}

	} else {
		$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	$conn->close();
	return $output;

}


function changeRoomsBookingStatus($propertyID,$firstNight,$lastNight,$roomID,$booking_value) {

  $firstD = explode("-", $firstNight);
	$firstD_year = $firstD[0];
	$firstD_month = $firstD[1];
	$firstD_day = $firstD[2];

	$lastD = explode("-", $lastNight);
	$lastD_year = $lastD[0];
	$lastD_month = $lastD[1];
	$lastD_day = $lastD[2];

	$date1 = new DateTime($firstNight);
	$date2 = new DateTime($lastNight);

	$diff = $date2->diff($date1)->format("%a");

  $new_firstD_day = "";
  $new_firstD_month = "";
  $new_firstD_year = "";

  $mydaycolumns = "";
  $mydayvalues = "";

	$myUPDATEdaycolumns = ""; //for usage in update


  $i=0;
  $k=$diff+1;
  while($i<$diff+1) {

    $ch_day = "d" . ($firstD_day + $i);
    $mydaycolumns .= ", " . $ch_day;
    $mydayvalues .= ", '" . $booking_value . "'";

		$myUPDATEdaycolumns .= $ch_day . "='" . $booking_value . "'"; 	//for usage in update

    //Dates more than months days (e.g. 28/12-2/1)
    if ($firstD_day+$i == cal_days_in_month (CAL_GREGORIAN, $firstD_month, $firstD_year)) {
        $new_firstD_day =1;
        if ($firstD_month == 12) {
          $new_firstD_month = "01";
          $new_firstD_year = $firstD_year + 1;
        } else {
          $new_firstD_month = $firstD_month + 1;
					if ($new_firstD_month < 10 ) {
						$new_firstD_month = "0" . $new_firstD_month;
					}
          $new_firstD_year = $firstD_year;
        }
        //Continue booking on next month
        break;
    } else {
			if ($i < $diff) {
				$myUPDATEdaycolumns .= ","; 																				//for usage in update
			}
		}

    $i++;
    $k--;

  }

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$avail_id = 0;

	$stmt = $conn->prepare("SELECT avail_id FROM availability WHERE propertyID=? AND roomID=? AND year=? AND month=?");
	$stmt->bind_param("isss", $propertyID,$roomID,$firstD_year,$firstD_month);

	if ($stmt->execute()) {

		$stmt->bind_result($avail_id_result);

		while ($stmt->fetch()) {
			$avail_id = $avail_id_result;
		}
	}

	if ($avail_id == 0) {
		$sql = "INSERT INTO availability (propertyID, roomID, year, month" . $mydaycolumns . ")";
		$sql .= "VALUES ('" . $propertyID . "','" . $roomID . "', '" . $firstD_year . "', '" . $firstD_month . "'" .$mydayvalues . ")";
	} else {
		$sql = "UPDATE availability SET " . $myUPDATEdaycolumns . " WHERE propertyID=" . $propertyID . " AND roomID='" . $roomID . "' AND year=" . $firstD_year . " AND month=" . $firstD_month . " ";
	}

  if ($conn->query($sql) === TRUE) {
      //echo "Booking Added!!!";
  } else {
      //echo "Error: " . $sql . "<br>" . $conn->error;
  }


  if ($new_firstD_day == "1") {

		$new_firstNight = $new_firstD_year . "-" . $new_firstD_month . "-" . $new_firstD_day;
		changeRoomsBookingStatus($propertyID,$new_firstNight,$lastNight,$roomID,$booking_value);

  }

}


function deleteRoomsBookingStatus($propertyID,$firstNight,$lastNight,$roomID,$booking_value) {

  $firstD = explode("-", $firstNight);
	$firstD_year = $firstD[0];
	$firstD_month = $firstD[1];
	$firstD_day = $firstD[2];

	$lastD = explode("-", $lastNight);
	$lastD_year = $lastD[0];
	$lastD_month = $lastD[1];
	$lastD_day = $lastD[2];

	$date1 = new DateTime($firstNight);
	$date2 = new DateTime($lastNight);

	$diff = $date2->diff($date1)->format("%a");

  $new_firstD_day = "";
  $new_firstD_month = "";
  $new_firstD_year = "";

  $mydaycolumns = "";
  $mydayvalues = "";

	$myUPDATEdaycolumns = ""; //for usage in update


  $i=0;
  $k=$diff+1;
  while($i<$diff+1) {

    $ch_day = "d" . ($firstD_day + $i);
    $mydaycolumns .= ", " . $ch_day;
    $mydayvalues .= ", '" . $booking_value . "'";

		$myUPDATEdaycolumns .= $ch_day . "='" . $booking_value . "'"; 	//for usage in update

    //Dates more than months days (e.g. 28/12-2/1)
    if ($firstD_day+$i == cal_days_in_month (CAL_GREGORIAN, $firstD_month, $firstD_year)) {
        $new_firstD_day =1;
        if ($firstD_month == 12) {
          $new_firstD_month = "01";
          $new_firstD_year = $firstD_year + 1;
        } else {
          $new_firstD_month = $firstD_month + 1;
					if ($new_firstD_month < 10 ) {
						$new_firstD_month = "0" . $new_firstD_month;
					}
          $new_firstD_year = $firstD_year;
        }
        //Continue booking on next month
        break;
    } else {
			if ($i < $diff) {
				$myUPDATEdaycolumns .= ","; 																				//for usage in update
			}
		}

    $i++;
    $k--;

  }

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$avail_id = 0;
	$book_status = "free";

	$sql = "UPDATE availability SET " . $myUPDATEdaycolumns . " WHERE propertyID=" . $propertyID . " AND roomID='" . $roomID . "' AND year=" . $firstD_year . " AND month=" . $firstD_month . " ";

  if ($conn->query($sql) === TRUE) {

		$stmt = $conn->prepare("SELECT avail_id,d1,d2,d3,d4,d5,d6,d7,d8,d9,d10,d11,d12,d13,d14,d15,d16,d17,d18,d19,d20,d21,d22,d23,d24,d25,d26,d27,d28,d29,d30,d31 FROM availability WHERE propertyID=? AND roomID=? AND year=? AND month=?");
		$stmt->bind_param("iiss", $propertyID,$roomID,$firstD_year,$firstD_month);

		if ($stmt->execute()) {

			$stmt->bind_result($avail_id_result,
													$d1_r,$d2_r,$d3_r,$d4_r,$d5_r,$d6_r,$d7_r,$d8_r,$d9_r,$d10_r,
													$d11_r,$d12_r,$d13_r,$d14_r,$d15_r,$d16_r,$d17_r,$d18_r,$d19_r,$d20_r,
													$d21_r,$d22_r,$d23_r,$d24_r,$d25_r,$d26_r,$d27_r,$d28_r,$d29_r,$d30_r,$d31_r);

			while ($stmt->fetch()) {
				$avail_id = $avail_id_result;
				$d1 = $d1_r;
				$d2 = $d2_r;
				$d3 = $d3_r;
				$d4 = $d4_r;
				$d5 = $d5_r;
				$d6 = $d6_r;
				$d7 = $d7_r;
				$d8 = $d8_r;
				$d9 = $d9_r;
				$d10 = $d10_r;
				$d11 = $d11_r;
				$d12 = $d12_r;
				$d13 = $d13_r;
				$d14 = $d14_r;
				$d15 = $d15_r;
				$d16 = $d16_r;
				$d17 = $d17_r;
				$d18 = $d18_r;
				$d19 = $d19_r;
				$d20 = $d20_r;
				$d21 = $d21_r;
				$d22 = $d22_r;
				$d23 = $d23_r;
				$d24 = $d24_r;
				$d25 = $d25_r;
				$d26 = $d26_r;
				$d27 = $d27_r;
				$d28 = $d28_r;
				$d29 = $d29_r;
				$d30 = $d30_r;
				$d31 = $d31_r;

			}
		}

		for ($i=1; $i < 32; $i++) {
			$ch_day = "d" . $i . "_r";
			if (${$ch_day} != 0) {
				$book_status = "booked";
			}
		}

		if ($book_status == "free") {
			$stmt = $conn->prepare("DELETE FROM availability WHERE avail_id=?");
			$stmt->bind_param("i",$avail_id);

			if ($stmt->execute()) {
				$responce = "Booking deleted.";
				$resp = "true";
			}
		}


  }


  if ($new_firstD_day == "1") {

		$new_firstNight = $new_firstD_year . "-" . $new_firstD_month . "-" . $new_firstD_day;
		deleteRoomsBookingStatus($propertyID,$new_firstNight,$lastNight,$roomID,$booking_value);

  }

}

?>
