<?php

include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
//print_r($data);

$result;
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

if ($action == "show_checkout"){

		$propertyID = $data["propertyID"];
		$check_date = $data["check_date"];


		$checkin = array();
		$checkout = array();

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}


// SELECT bookings_rooms.roomIdentify, bookings.bookingID, bookings.checkin, bookings.checkout FROM bookings_rooms INNER JOIN bookings ON bookings_rooms.bookingID=bookings.bookingID WHERE propertyID=? AND checkin=? OR checkout=?

		//$stmt = $conn->prepare("SELECT bookingID,checkin,checkout	FROM bookings WHERE propertyID=? AND checkin=? OR checkout=?");
		$stmt = $conn->prepare("SELECT bookings_rooms.roomIdentify, bookings.bookingID, bookings.checkin, bookings.checkout FROM bookings_rooms INNER JOIN bookings ON bookings_rooms.bookingID=bookings.bookingID WHERE propertyID=? AND checkin=? OR checkout=?");
		$stmt->bind_param("iss", $propertyID, $check_date, $check_date);

		$responce = "Error";
		$resp = "false";

		if ($stmt->execute()) {

			$stmt->bind_result($roomIdentify_result,
			$bookingID_result,
			$checkin_result,
			$checkout_result);

			while ($stmt->fetch()) {

					if ($checkin_result == $check_date) {
						$checkin[]=array("bookingID" => $bookingID_result,
															"roomIdentify" => $roomIdentify_result);
					} else {
						$checkout[]=array("bookingID" => $bookingID_result,
															"roomIdentify" => $roomIdentify_result);
					}

			}

			//$rooms = getBookingRooms($bookingID);

			$responce = "Check-in-out data.";
			$resp = "true";
			$respdata = array('checkout' => $checkout);

		} else {
			$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

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


function getBookingRooms($bookingID) {

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

			$output[]=array("book_room_id" => $book_room_id_result,
											"bookingID" => $bookingID,
											"roomtypeID" => $roomtypeID_result,
											"roomIdentify" => $roomIdentify_result,
											"adults" => $adults_result,
											"children" => $childrens_result,
											"infants" => $infants_result,
											"price" => $price_result,
											"currency" => $currency_result);
		}

	} else {
		$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	$conn->close();
	return $output;

}

?>
