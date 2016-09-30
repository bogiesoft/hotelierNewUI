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

if ($action == "add_specialprice"){

		$roomtypeID = $data["roomtypeID"];
		$price = $data["price"];
		$startDate = $data["startDate"];
		$endDate = $data["endDate"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		// prepare and bind
		$stmt = $conn->prepare("INSERT INTO special_prices (roomtypeID, price, startDate, endDate) VALUES (?, ?, ?, ?)");
		$stmt->bind_param("isss",
		$roomtypeID,
		$price,
		$startDate,
		$endDate);

		if ($stmt->execute()) {

			//Return status
			$responce = "Price added sucessfully.";
			$resp = "true";

		} else {
			$responce = "Price add failed: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();

} elseif ($action == "show_specialprices"){

		$roomtypeID = $data["roomtypeID"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("SELECT spriceID,roomtypeID,price,startDate,endDate FROM special_prices WHERE roomtypeID=?");
		$stmt->bind_param("i", $roomtypeID);

		$responce = "Error";
		$resp = "false";

		if ($stmt->execute()) {

			$stmt->bind_result($spriceID_result,
												 	$roomtypeID_result,
													$price_result,
													$startDate_result,
													$endDate_result);

			while ($stmt->fetch()) {

				$output[]=array("spriceID" => $spriceID_result,
												"roomtypeID" => $roomtypeID_result,
												"price" => $price_result,
												"startDate" => $startDate_result,
												"endDate" => $endDate_result);
			}

			$responce = "Special prices data.";
			$resp = "true";
			$respdata = $output;

		} else {
			$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();

} elseif ($action == "delete_specialprice"){

	$spriceID = $data["spriceID"];

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("DELETE FROM special_prices WHERE spriceID=?");
	$stmt->bind_param("i",$spriceID);

	if ($stmt->execute()) {
		$responce = "Special price deleted.";
		$resp = "true";
	} else {
		$responce = "Delete failed: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}


	///DELETE ALL RELATED DATA TO OTHER TABLES>>>> TO BE ADDED!!!!!


	$conn->close();

} elseif ($action == "delete_property_rooms"){

	$propertyID = $data["propertyID"];

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("DELETE FROM rooms WHERE propertyID=?");
	$stmt->bind_param("i",$propertyID);

	if ($stmt->execute()) {
		$responce = "Rooms deleted.";
		$resp = "true";
	} else {
		$responce = "Delete failed: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}


	///DELETE ALL RELATED DATA TO OTHER TABLES>>>> TO BE ADDED!!!!!


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

function add_room($room_identify,$userID,$propertyID,$roomtypeID)
{
	$responce = "";
	$resp = "false";
	$respdata = array();


	 $result = array('responce' => $responce, 'resp' => $resp, 'data' => $respdata);
	 return $result;
}

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

?>
