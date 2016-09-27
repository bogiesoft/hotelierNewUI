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
		$roomtypeID = null;//$data["roomtypeID"];
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
			if (isset($roomtypeID)) {

			  $stmt = $conn->prepare("SELECT room_identify,roomtypeID FROM rooms WHERE roomtypeID=?");
			  $stmt->bind_param("i", $roomtypeID);

			} else {

				$stmt = $conn->prepare("SELECT room_identify,roomtypeID FROM rooms WHERE propertyID=?");
			  $stmt->bind_param("i", $propertyID);

			}

		} else {
			$stmt = $conn->prepare("SELECT room_identify,roomtypeID FROM rooms WHERE userID=?");
			$stmt->bind_param("i", $userID);
		}


	  if ($stmt->execute()) {

			 $stmt->bind_result($room_identify_result, $roomtypeID_result);

			 $i =0;
			 while ($stmt->fetch()) {
				 array_push($roomsIDents,$room_identify_result);
			 }

	  } else {
			 $responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
			 $resp = "false";
	  }

		$output = array();

		//for ($i=0; $i < 10; $i++) {




			foreach ($roomsIDents as $roomIdentify) {
					$output[] =getRoomDetails ($roomIdentify, $check_month, $check_year);

					$responce = "Rooms Availability Data";
					$resp = "true";
				}

		//}


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

function getRoomDetails($roomIdentify, $ch_month, $ch_year) {

	$days_ofMonth = cal_days_in_month (CAL_GREGORIAN, $ch_month, $ch_year);

	$datedata = array();

	for ($i=1; $i <= $days_ofMonth; $i++) {
		$datedata[] = getDayDetails($roomIdentify,$i, $ch_month, $ch_year);
	}

	$respdata = array('roomIdentify' => $roomIdentify
	, 'daydata' => $datedata);



return $respdata;

}

function getDayDetails($roomIdentify, $ch_day, $ch_month, $ch_year) {

	$dayPrice = getPrice($roomIdentify, $ch_day, $ch_month, $ch_year);
	$daycurrency = getCurrency($roomIdentify, $ch_day, $ch_month, $ch_year);
	$dayStatus = getStatus($roomIdentify, $ch_day, $ch_month, $ch_year);

	if ($dayStatus == 2) {
		$bookingID = "";//getBookingID();
	} else {
		$bookingID = "";
	}


	$datedata = array('date' => $ch_year . "-" . $ch_month . "-" . $ch_day
	, 'price' => $dayPrice
	, 'currency' => $daycurrency
	, 'status' => $dayStatus
	, 'bookingID' => $bookingID);

	return $datedata;
}

function getPrice($roomIdentify, $ch_day, $ch_month, $ch_year) {

	return 0;
}


function getStatus($roomIdentify, $ch_day, $ch_month, $ch_year) {

	return rand(0,3);
}


function getCurrency($roomIdentify, $ch_day, $ch_month, $ch_year) {

	return 0;
}


function getBookingID($roomIdentify, $ch_day, $ch_month, $ch_year) {

	return 0;
}

?>
