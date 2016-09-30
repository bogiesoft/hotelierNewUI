<?php

include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
//print_r($data);

$responce = "";
$resp = "false";
$respdata = array();

$action = $data["action"];
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

if ($action == "show_rooms_availability"){

		$propertyID = $data["propertyID"];
		$roomtypeID = "all"; //$data["roomtypeID"];
		$check_month = $data["month"];
		$check_year = $data["year"];

		$output = array();
	  $roomsIDents = array();

		// Create connection
	  $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	  // Check connection
	  if ($conn->connect_error) {
			 die("Connection failed: " . $conn->connect_error);
	  }

		if ($propertyID == "all") {

			$stmt = $conn->prepare("SELECT room_identify,propertyID,roomtypeID FROM rooms WHERE userID=?");
			$stmt->bind_param("i", $userID);

		} else {
			if ($roomtypeID == "all") {

				$stmt = $conn->prepare("SELECT room_identify,propertyID,roomtypeID FROM rooms WHERE propertyID=?");
			  $stmt->bind_param("i", $propertyID);

			} else {
				$stmt = $conn->prepare("SELECT room_identify,propertyID,roomtypeID FROM rooms WHERE roomtypeID=?");
				$stmt->bind_param("i", $roomtypeID);

			}
		}

	  if ($stmt->execute()) {

			 $stmt->bind_result($room_identify_result, $propertyID_result, $roomtypeID_result);

			 $i =0;
			 while ($stmt->fetch()) {
				 array_push($roomsIDents,$room_identify_result."$#$".$propertyID_result);
			 }

	  } else {
			 $responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
			 $resp = "false";
	  }

		$output = array();

		foreach ($roomsIDents as $roomIdentify) {
				$pieces = explode("$#$", $roomIdentify);
				$roomIdentify = $pieces[0];
				$propertyID = $pieces[1];
				$output[] =getRoomDetails ($roomIdentify, $propertyID, $check_month, $check_year);

				$responce = "Rooms Availability Data";
				$resp = "true";
			}

	  $respdata = $output;
	  $conn->close();


} else {

	$responce = "Invalid action.";
	$resp = "false";

}

//Return result
$result = array('responce' => $responce, 'resp' => $resp, 'RoomsData' => $respdata);
echo json_encode($result);


////////////////////////////////////////////////////////////////////////////
////////////////////      FUNCTIONS       //////////////////////////////////
////////////////////////////////////////////////////////////////////////////

function checkToken($token, $email) {
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

function getRoomDetails($roomIdentify, $propertyID, $ch_month, $ch_year) {

	$days_ofMonth = cal_days_in_month (CAL_GREGORIAN, $ch_month, $ch_year);

	$datedata = array();

	$monthStatus = getStatus($roomIdentify, $propertyID, $ch_month, $ch_year);
	$monthStatus = updateMonthStatus($monthStatus,$propertyID,$roomIdentify, $ch_month, $ch_year);
	$dayStatus = explode("#", $monthStatus);

	$monthPrice = getPrice($roomIdentify, $propertyID, $ch_month, $ch_year);
	$monthPrice = updateMonthPrices($monthPrice,$propertyID,$roomIdentify, $ch_month, $ch_year);
	$dayPrice = explode("#", $monthPrice);

	$daycurrency = $dayPrice[31];

	for ($i=1; $i <= $days_ofMonth; $i++) {
		$datedata[] = getDayDetails($roomIdentify,$dayStatus[$i-1],$dayPrice[$i-1], $daycurrency,$i, $ch_month, $ch_year);
	}

	$respdata = array('roomIdentify' => $roomIdentify
	, 'daydata' => $datedata);


return $respdata;

}

function getDayDetails($roomIdentify, $dayStatus, $dayPrice, $daycurrency,  $ch_day, $ch_month, $ch_year) {

	if ($dayStatus == 1 || $dayStatus == 2) {
		$bookingID = getBookingID($roomIdentify, $ch_day, $ch_month, $ch_year);
	} else {
		$bookingID = "";
	}

	if ($dayStatus == 3) {
		$dayPrice = "";
		$daycurrency = "";
	}


	$datedata = array('date' => $ch_year . "-" . $ch_month . "-" . $ch_day
	, 'price' => $dayPrice
	, 'currency' => $daycurrency
	, 'status' => $dayStatus
	, 'bookingID' => $bookingID);

	return $datedata;
}

function getPrice($roomIdentify, $propertyID, $ch_month, $ch_year) {

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	// SELECT room_types.price, room_types.currency FROM room_types
	// INNER JOIN rooms
	// ON room_types.roomtypeID=rooms.roomtypeID WHERE rooms.room_identify=? AND rooms.propertyID=?;
	$stmt = $conn->prepare("SELECT room_types.price, room_types.currency FROM room_types
	INNER JOIN rooms ON room_types.roomtypeID=rooms.roomtypeID WHERE rooms.room_identify=? AND rooms.propertyID=?");
	$stmt->bind_param("si",$roomIdentify,$propertyID);

	if ($stmt->execute()) {

		$stmt->bind_result($price_r,$currency_r);
		while ($stmt->fetch()) {
			$price=$price_r;
			$currency=$currency_r;
		}
	}

	$result = "";
	for ($i=0; $i < 31; $i++) {
		$result .= $price . "#";
	}

$result .= $currency;
return $result;

}


function getStatus($roomIdentify, $propertyID, $ch_month, $ch_year) {

	if ($ch_month < 10) {
		$ch_month = "0" . $ch_month;
	}

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT d1,d2,d3,d4,d5,d6,d7,d8,d9,d10,d11,d12,d13,d14,d15,d16,d17,d18,d19,d20,d21,d22,d23,d24,d25,d26,d27,d28,d29,d30,d31
		FROM availability WHERE propertyID=? AND roomID=? AND year=? AND month=?");
	$stmt->bind_param("isss", $propertyID,$roomIdentify,$ch_year,$ch_month);

	if ($stmt->execute()) {

		$stmt->bind_result($d1_r,$d2_r,$d3_r,$d4_r,$d5_r,$d6_r,$d7_r,$d8_r,$d9_r,$d10_r,
											 $d11_r,$d12_r,$d13_r,$d14_r,$d15_r,$d16_r,$d17_r,$d18_r,$d19_r,$d20_r,
											 $d21_r,$d22_r,$d23_r,$d24_r,$d25_r,$d26_r,$d27_r,$d28_r,$d29_r,$d30_r,$d31_r);
		$i=0;

		while ($stmt->fetch()) {
			$i++;

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

	if ($i == 0) {
		$result = "0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0#0";
	} else {
		$result = $d1 . "#" . $d2 . "#" . $d3 . "#" . $d4 . "#" . $d5 . "#" . $d6 . "#" . $d7 . "#" . $d8 . "#" . $d9
		. "#" . $d10 . "#" . $d11 . "#" . $d12 . "#" . $d13 . "#" . $d14 . "#" . $d15 . "#" . $d16 . "#" . $d17 . "#" . $d18 . "#" . $d19
		. "#" . $d20 . "#" . $d21 . "#" . $d22 . "#" . $d23 . "#" . $d24 . "#" . $d25 . "#" . $d26 . "#" . $d27 . "#" . $d28 . "#" . $d29
		. "#" . $d30 . "#" . $d31;
	}

	return $result;

}


function getBookingID($roomIdentify, $ch_day, $ch_month, $ch_year) {
	if ($ch_month < 10) {
		$ch_month = "0" . $ch_month;
	}

	$check_date = $ch_year . "-" . $ch_month . "-" . $ch_day;

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT bookings.bookingID FROM bookings_rooms INNER JOIN bookings ON bookings_rooms.bookingID = bookings.bookingID WHERE bookings_rooms.roomIdentify=? AND bookings.checkin<=? AND bookings.checkout>=? ");
	$stmt->bind_param("sss",$roomIdentify,$check_date,$check_date);

	if ($stmt->execute()) {

		$stmt->bind_result($bookingID_r);

		while ($stmt->fetch()) {
			$bookingID = $bookingID_r;
		}
	}

	return $bookingID;

}

function updateMonthStatus($monthStatus,$propertyID,$roomIdentify, $ch_month, $ch_year) {

	$days_ofMonth = cal_days_in_month (CAL_GREGORIAN, $ch_month, $ch_year);

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT onDuty, startDate, endDate FROM rooms WHERE propertyID=? AND room_identify=?");
	$stmt->bind_param("is", $propertyID,$roomIdentify);


	if ($stmt->execute()) {

		$stmt->bind_result($onDuty_result,$startDate_result, $endDate_result);

		while ($stmt->fetch()) {

				if ($onDuty_result == "Off") {

				 	$dayStatus = explode("#", $monthStatus);
					$newStatus = "";

					for ($i=1; $i < 32; $i++) {

						$ch_date = $ch_year . "-" . $ch_month . "-" . $i;

						$checkDate=date('Y-m-d', strtotime($ch_date));;
				    $DateBegin = date('Y-m-d', strtotime($startDate_result));
				    $DateEnd = date('Y-m-d', strtotime($endDate_result));

				    if (($checkDate >= $DateBegin) && ($checkDate <= $DateEnd)) {
							$newStatus = $newStatus . "3";
				    } else {
				      $newStatus .= $dayStatus[$i-1];
				    }

						if ($i < 31) {
							$newStatus .= "#";
						}

					}

					return $newStatus;

				} else {
					return $monthStatus;
				}

		}

	} else {
		$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	$conn->close();

}


function updateMonthPrices($monthPrices,$propertyID,$roomIdentify, $ch_month, $ch_year) {

	return $monthPrices;
}


?>
