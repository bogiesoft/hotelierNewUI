<?php

include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
//print_r($data);

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
		$roomsCurrency = $data["roomsCurrency"];

		$payment_method = $data["payment-method"];
		$payment_data = "";//$data["payment-data"]; //TO BE UPDATED
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
			$responce = "Booking failed: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$bookingID = 0;

		$bookingID = $conn->insert_id;

		if ($bookingID == 0) {
			$responce = "Booking failed(" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
			$respdata = "";
			$result = array('responce' => $responce, 'resp' => $resp, 'data' => $respdata);
			$conn->close();
			echo json_encode($result);
		}

		$conn->close();

		for ($i=0; $i < count($rooms); $i++) {

			$currentRoomIdentity = getRoomIdentity($rooms[$i]["roomID"],$checkin,$checkout);

			writeBookingRooms($bookingID,$rooms[$i]["roomID"],$currentRoomIdentity, $guests[$i]["adults"],$guests[$i]["children"],$guests[$i]["infants"],$rooms[$i]["totalPrice"],$rooms[$i]["currency"]);

			$lastNight = date('Y-m-d', strtotime($checkout. ' - 1 days'));

			/*
			BOOKING VALUES
			0 : Room free
			1 : Booking exist, but status pending
			2 : Booking Complete
			*/
			$booking_value = 2;

			createNewBooking($propertyID,$checkin,$lastNight,$currentRoomIdentity,$booking_value);


		}

		//Store hotel services
		for ($k=0; $k < count($hotelServices); $k++) {

				$serviceID = $hotelServices[$k]["serviceID"];
				$roomtypeID = $hotelServices[$k]["roomtypeID"];
				$service_name = $hotelServices[$k]["service-name"];
				$service_cost = $hotelServices[$k]["service-cost"];
				$service_currency = $hotelServices[$k]["service-currency"];

				writeBookingServices($bookingID, $serviceID, $roomtypeID, $service_name, $service_cost, $service_currency);
		}

		//Store room services
		for ($l=0; $l < count($roomServices); $l++) {

				$serviceID = $roomServices[$l]["serviceID"];
				$roomtypeID = $roomServices[$l]["roomtypeID"];
				$service_name = $roomServices[$l]["service-name"];
				$service_cost = $roomServices[$l]["service-cost"];
				$service_currency = $roomServices[$l]["service-currency"];

				writeBookingServices($bookingID, $serviceID, $roomtypeID, $service_name, $service_cost, $service_currency);
		}


		//SEND email to customer
		$emailSubject='Reception message by Roomier Booking system';
		$emailTo=$email;

		$do="<div>";
		$dc="</div>";
		$br = "<br>";

		$message  = '<div style="font-size: 11px; padding: 20px; display: table; color:#DDDDDD; background: #333333;">This message was sent from Roomier Booking system</div>';
		//$message .= $n.$n;
		$message .= '<div style="padding: 20px; background: #EEEEEE; color: #222222;">';
		$message .= $do."Επώνυμο:".$lastname.$dc.$br;
		$message .= $do."Ονομα:".$dc.$br;
		$message .= $do."Email:".$dc.$br;
		$message .= $do."Δωμάτιο:".$dc.$br;
		$message .= $do."Μύνημα:".$dc.$br;
		$message .= '</div>';

		//$headers = 'From: '."\r\n".'Reply-To: '.$emailFrom."\r\n" .'X-Mailer: PHP/' . phpversion();
		//$headers = "From: " . strip_tags($_POST['req-email']) . "\r\n";
		//$headers .= "Reply-To: ". $email . "\r\n";
		//$headers .= "CC: susan@example.com\r\n";
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		sleep(1);
		mail( $emailTo, $emailSubject, $message, $headers) or die("Message send failed!");


		//SEND email to hotelier




		$resp = "true";
		$respdata = array('pin' => $pin, 'reservationCode' => $reservationCode);


} elseif ($action == "update_booking_room") {

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
			} else {
				$responce = "Update failed(" . $stmt->errno . ") " . $stmt->error;
				$resp = "false";
			}

			$conn->close();


} elseif ($action == "show_booking"){

		$bookingID = $data["bookingID"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("SELECT userID,propertyID,roomtypeID,room_identify,
			checkin,checkout,orderDate,adults,childs,status,
			customerID,shipping_name,shipping_surname,
			shipping_company,shipping_address,shipping_city,
			shipping_postcode,shipping_country,shipping_state,
			payment,transactionID
			FROM bookings WHERE bookingID=?");
		$stmt->bind_param("i", $bookingID);

		$responce = "Error";
		$resp = "false";

		if ($stmt->execute()) {

			$stmt->bind_result(
			$userID_result,
			$propertyID_result,
			$roomtypeID_result,
			$room_identify_result,
			$checkin_result,
			$checkout_result,
			$orderDate_result,
			$adults_result,
			$childs_result,
			$status_result,
			$customerID_result,
			$shipping_name_result,
			$shipping_surname_result,
			$shipping_company_result,
			$shipping_address_result,
			$shipping_city_result,
			$shipping_postcode_result,
			$shipping_country_result,
			$shipping_state_result,
			$payment_result,
			$transactionID_result);

			while ($stmt->fetch()) {

				$userID = $userID_result;
				$propertyID = $propertyID_result;
				$roomtypeID = $roomtypeID_result;
				$room_identify = $room_identify_result;
				$checkin = $checkin_result;
				$checkout = $checkout_result;
				$orderDate = $orderDate_result;
				$adults = $adults_result;
				$childs = $childs_result;
				$status = $status_result;
				$customerID = $customerID_result;
				$shipping_name = $shipping_name_result;
				$shipping_surname = $shipping_surname_result;
				$shipping_company = $shipping_company_result;
				$shipping_address = $shipping_address_result;
				$shipping_city = $shipping_city_result;
				$shipping_postcode = $shipping_postcode_result;
				$shipping_country = $shipping_country_result;
				$shipping_state = $shipping_state_result;
				$payment = $payment_result;
				$transactionID = $transactionID_result;

			}

			$responce = "Room data.";
			$resp = "true";
			$respdata = array('userID' => $userID_result
			, 'propertyID' => $propertyID
			, 'roomtypeID' => $roomtypeID
			, 'room_identify' => $room_identify
			, 'checkin' => $checkin
			, 'checkout' => $checkout
			, 'orderDate' => $orderDate
			, 'adults' => $adults
			, 'childs' => $childs
			, 'status' => $status
			, 'customerID' => $customerID
			, 'shipping_name' => $shipping_name
			, 'shipping_surname' => $shipping_surname
			, 'shipping_company' => $shipping_company
			, 'shipping_address' => $shipping_address
			, 'shipping_city' => $shipping_city
			, 'shipping_postcode' => $shipping_postcode
			, 'shipping_country' => $shipping_country
			, 'shipping_state' => $shipping_state
			, 'payment' => $payment
			, 'transactionID' => $transactionID);

		} else {
			$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


} elseif ($action == "show_property_bookings"){

		$propertyID = $data["propertyID"];
		$status = $data["status"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		if (isset($status)) {
					$stmt = $conn->prepare("SELECT bookingID,pin,reservationCode,booking_origin,checkin,checkout,orderDate,bookingStatus,bookingTotalPrice,paymentMethod,name,lastname FROM bookings WHERE propertyID=? AND bookingStatus=?");
					$stmt->bind_param("is", $propertyID,$status);

		} else {
					$stmt = $conn->prepare("SELECT bookingID,pin,reservationCode,booking_origin,checkin,checkout,orderDate,bookingStatus,bookingTotalPrice,paymentMethod,name,lastname FROM bookings WHERE propertyID=?");
					$stmt->bind_param("i", $propertyID);
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

		$bookingID = $data["bookingID"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
		    die("Connection failed: " . $conn->connect_error);
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


		//TO BE ADDED CODE TO FREE ROOMs included in Booking



		$conn->close();

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


function getRoomIdentity($roomtypeID,$checkin,$checkout) {
	return "single_1";
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
		$responce = "Booking failed: (" . $stmt->errno . ") " . $stmt->error;
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


function createNewBooking($propertyID,$firstNight,$lastNight,$roomID,$booking_value) {

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
		if ($i < $diff) {
			//echo "I::$i  DIFF::$diff\n";																				//for usage in update
			$myUPDATEdaycolumns .= ","; 																	//for usage in update
		}																																//for usage in update


    //Dates more than months days (e.g. 28/12-2/1)
    if ($firstD_day+$i == cal_days_in_month (CAL_GREGORIAN, $firstD_month, $firstD_year)) {
        $new_firstD_day =1;
        if ($firstD_month == 12) {
          $new_firstD_month = 1;
          $new_firstD_year = $firstD_year + 1;
        } else {
          $new_firstD_month = $firstD_month + 1;
          $new_firstD_year = $firstD_year;
        }
        //Continue booking on next month
        break;
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
	$stmt->bind_param("iiss", $propertyID,$roomID,$firstD_year,$firstD_month);

	if ($stmt->execute()) {

		$stmt->bind_result($avail_id_result);

		while ($stmt->fetch()) {
			$avail_id = $avail_id_result;
		}
	}

	if ($avail_id == 0) {
		$sql = "INSERT INTO availability (propertyID, roomID, year, month" . $mydaycolumns . ")";
		$sql .= "VALUES ('" . $propertyID . "','" . $roomID . "', '" . $firstD_year . "', '" . $firstD_month . "'" .$mydayvalues . ")";
		//echo "INSERT1::$sql\n";
	} else {
		$sql = "UPDATE availability SET " . $myUPDATEdaycolumns . " WHERE propertyID=" . $propertyID . " AND roomID='" . $roomID . "' AND year=" . $firstD_year . " AND month=" . $firstD_month . " ";
		//echo "UPDATE1::$sql\n";

	}

  if ($conn->query($sql) === TRUE) {
      //echo "Booking Added!!!";
  } else {
      //echo "Error: " . $sql . "<br>" . $conn->error;
  }

  if ($new_firstD_day == "1") {

    $mydaycolumns = "";
    $mydayvalues = "";

		$myUPDATEdaycolumns = "";																					//for usage in update

    $j=0;
    while($j<$k -1) {

      $ch_day = "d" . ($new_firstD_day + $j);
      $mydaycolumns .= ", " . $ch_day;
      $mydayvalues .= ", '" . $booking_value . "'";

			$myUPDATEdaycolumns .= $ch_day . "='" . $booking_value . "'"; 	//for usage in update
			if ($j < $k) { 																									//for usage in update
				$myUPDATEdaycolumns .= ","; 																	//for usage in update
			}

      $j++;

    }

		$avail_id2 = 0;
		$stmt = $conn->prepare("SELECT avail_id FROM availability WHERE propertyID=? AND roomID=? AND year=? AND month=?");
		$stmt->bind_param("iiss", $propertyID,$roomID,$new_firstD_year,$new_firstD_month);

		if ($stmt->execute()) {

			$stmt->bind_result($avail_id_result);

			while ($stmt->fetch()) {
				$avail_id2 = $avail_id_result;
			}
		}

		if ($avail_id2 == 0) {
			$sql2 = "INSERT INTO availability (propertyID, roomID, year, month" . $mydaycolumns . ")";
			$sql2 .= "VALUES ('" . $propertyID . "','" . $roomID . "', '" . $new_firstD_year . "', '" . $new_firstD_month . "'" .$mydayvalues . ")";
			//echo "INSERT2::$sql2\n";
		} else {
			$sql2 = "UPDATE availability SET " . $myUPDATEdaycolumns . " WHERE propertyID=" . $propertyID . " AND roomID='" . $roomID . "' AND year=" . $new_firstD_year . " AND month=" . $new_firstD_month . " ";
			//echo "UPDATE2::$sql2\n";
		}

    if ($conn->query($sql2) === TRUE) {
        //echo "Booking Added!!!";
    } else {
        //echo "Error: " . $sql2 . "<br>" . $conn->error;
    }

  }

}


function updateBooking($firstNight,$lastNight,$b_room,$conn,$prefix) {

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

  $i=0;
  $k=$diff+1;
  while($i<$diff+1) {

    $ch_day = "d" . ($firstD_day + $i);
    $mydaycolumns .= $ch_day . "='0'";

    if ($i < $diff) {
      $mydaycolumns .= ",";
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
        //Continue booking on next month
        break;
    }
    $i++;
    $k--;

  }

  $sql = "UPDATE " . $prefix . "apb_availability SET " . $mydaycolumns . " WHERE roomID =" . $b_room . " AND year=" . $firstD_year . " AND month=" . $firstD_month . " ";

  if ($conn->query($sql) === TRUE) {
      echo "Record updated successfully";
  } else {
      echo "Error updating record: " . $conn->error;
  }

}


?>
