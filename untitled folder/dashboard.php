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

if ($action == "show_dashboard"){

		//$propertyID = $data["propertyID"];
		$check_date = $data["check_date"];

		$D = explode("-", $check_date);

		if ($D[1] < 10) {
			$D[1] = "0" . $D[1];
		}
		$check_date = $D[0] . "-" . $D[1] . "-" . $D[2];

		
		$total_bookings = 0;
		$pending_bookings = 0;
		$checkin = 0;
		$checkout = 0;

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$firstD = explode("-", $check_date);
		$checkD = $firstD[0] . "-" . $firstD[1];


		$stmt = $conn->prepare("SELECT bookings.bookingID, bookings.checkin, bookings.checkout, bookings.bookingStatus FROM bookings INNER JOIN properties ON bookings.propertyID=properties.propertyID WHERE properties.userID=?
");
		$stmt->bind_param("i", $userID);

		$responce = "Error";
		$resp = "false";

		if ($stmt->execute()) {

				$stmt->bind_result($bookingID_result,
				$checkin_result,
				$checkout_result,
				$bookingStatus);

				while ($stmt->fetch()) {

					$firstD = explode("-", $checkin_result);
					$checkinD = $firstD[0] . "-" . $firstD[1];

					$firstD = explode("-", $checkout_result);
					$checkoutD = $firstD[0] . "-" . $firstD[1];

						if ($checkD == $checkinD || $checkD == $checkoutD) {
							$total_bookings++;
							if ($bookingStatus == 1) {
								$pending_bookings++;
							}

						}

				}

			}

			//$stmt = $conn->prepare("SELECT bookingID,checkin,checkout	FROM bookings WHERE propertyID=? AND checkin=? OR checkout=?");
			$stmt = $conn->prepare("SELECT bookings_rooms.roomIdentify, bookings.bookingID, bookings.checkin, bookings.checkout FROM bookings_rooms Left JOIN bookings ON bookings_rooms.bookingID=bookings.bookingID RIGHT JOIN properties ON properties.propertyID = bookings.propertyID WHERE properties.userID=? AND bookings.checkin=? OR bookings.checkout=?");
			$stmt->bind_param("iss", $userID, $check_date, $check_date);

			$responce = "Error";
			$resp = "false";

			if ($stmt->execute()) {

				$stmt->bind_result($roomIdentify_result,
				$bookingID_result,
				$checkin_result,
				$checkout_result);

				while ($stmt->fetch()) {

						if ($checkin_result == $check_date) {
							$checkin++;
						} elseif ($checkout_result == $check_date) {
							$checkout++;
						}

				}

			//$rooms = getBookingRooms($bookingID);

			$responce = "Dashboard data.";
			$resp = "true";
			$respdata = array('total_bookings' => $total_bookings
				, 'pending_bookings' => $pending_bookings
				, 'checkin' => $checkin
				, 'checkout' => $checkout);

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
