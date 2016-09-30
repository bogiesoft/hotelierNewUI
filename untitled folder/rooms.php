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

if ($action == "add_room"){

		$room_identify = $data["room_identify"];
		$propertyID = $data["propertyID"];
		$roomtypeID = $data["roomtypeID"];

		$result = add_room($room_identify,$userID,$propertyID,$roomtypeID);

} elseif ($action == "update_room") {

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


} elseif ($action == "update_room_availability") {

		$roomID = $data["roomID"];

		$onDuty = $data["onDuty"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("UPDATE rooms SET onDuty=? WHERE roomID=?");
		$stmt->bind_param("si",
		$onDuty,
		$roomID);

		if ($stmt->execute()) {
			$responce = "Room updated sucessfully " . $stmt->affected_rows;
			$resp = "true";
		} else {
			$responce = "Update failed(" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


} elseif ($action == "update_room_availability_startDate") {

		$roomID = $data["roomID"];

		$startDate = $data["startDate"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("UPDATE rooms SET startDate=? WHERE roomID=?");
		$stmt->bind_param("si",
		$startDate,
		$roomID);

		if ($stmt->execute()) {
			$responce = "Room updated sucessfully " . $stmt->affected_rows;
			$resp = "true";
		} else {
			$responce = "Update failed(" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


} elseif ($action == "update_room_availability_endDate") {

		$roomID = $data["roomID"];

		$endDate = $data["endDate"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("UPDATE rooms SET endDate=? WHERE roomID=?");
		$stmt->bind_param("si",
		$endDate,
		$roomID);

		if ($stmt->execute()) {
			$responce = "Room updated sucessfully ";// . $stmt->affected_rows;
			$resp = "true";
		} else {
			$responce = "Update failed(" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


} elseif ($action == "show_room"){

		$roomID = $data["roomID"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("SELECT roomID,room_identify,room_name,available FROM rooms WHERE roomID=?");
		$stmt->bind_param("i", $roomID);

		$responce = "Error";
		$resp = "false";

		if ($stmt->execute()) {

			$stmt->bind_result($roomID_result,$room_identify_result,$room_name_result,$available_result);

			while ($stmt->fetch()) {
				$roomID = $roomID_result;
				$room_identify = $room_identify_result;
				$room_name = $room_name_result;
				$available = $available_result;
			}

			$responce = "Room data.";
			$resp = "true";
			$respdata = array('roomID' => $roomID
			, 'room_identify' => $room_identify
			, 'room_name' => $room_name
			, 'available' => $available);

		} else {
			$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


} elseif ($action == "show_property_rooms"){

		$propertyID = $data["propertyID"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("SELECT roomID,room_identify,userID,propertyID,roomtypeID,room_name,available FROM rooms WHERE propertyID=?");
		$stmt->bind_param("i", $propertyID);

		$responce = "Error";
		$resp = "false";

		if ($stmt->execute()) {

			$stmt->bind_result($roomID_result,
												 	$room_identify_result,
													$userID_result,
													$propertyID_result,
													$roomtypeID_result,
													$room_name_result,
													$available_result);

			while ($stmt->fetch()) {

				$output[]=array("roomID" => $roomID_result,
												"room_identify" => $room_identify_result,
												"userID" => $userID_result,
												"propertyID" => $propertyID_result,
												"roomtypeID" => $roomtypeID_result,
												"room_name" => $room_name_result,
												"_room_name_class" => "_room_name_class_editable",
												"_room_name_id" => "_room_name_id" . $propertyID_result . "_editable",
												"available" => $available_result,
												"_available_class" => "_available_class_editable",
												"_available_id" => "_available_id" . $propertyID_result . "_editable");
			}

			$responce = "Rooms data.";
			$resp = "true";
			//$respdata = array('userID' => $userID, 'properties' => $properties);
			$respdata = $output;

		} else {
			$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();

} elseif ($action == "show_roomtype_rooms"){

		$roomtypeID = $data["roomtypeID"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("SELECT roomID,room_identify,userID,propertyID,roomtypeID,room_name,onDuty,startDate,endDate FROM rooms WHERE roomtypeID=?");
		$stmt->bind_param("i", $roomtypeID);

		$responce = "Error";
		$resp = "false";

		if ($stmt->execute()) {

			$stmt->bind_result($roomID_result,
												 	$room_identify_result,
													$userID_result,
													$propertyID_result,
													$roomtypeID_result,
													$room_name_result,
													$onDuty_result,
													$startDate_result,
													$endDate_result);

			while ($stmt->fetch()) {

				$output[]=array("roomID" => $roomID_result,
												"room_identify" => $room_identify_result,
												"_room_identify_class" => "_room_identify_class_editable",
												"userID" => $userID_result,
												"propertyID" => $propertyID_result,
												"roomtypeID" => $roomtypeID_result,
												"room_name" => $room_name_result,
												"_room_name_class" => "_room_name_class_editable",
												"_room_name_id" => "_room_name_id" . $roomID_result . "_editable",
												"onDuty" => $onDuty_result,
												"_onDuty_class" => "_onDuty_class_editable",
												"_onDuty_id" => "_onDuty_id" . $roomID_result . "_editable",
												"startDate" => $startDate_result,
												"_startDate_class" => "_startDate_class_editable",
												"_startDate_id" => "_startDate_id" . $roomID_result . "_editable",
												"endDate" => $endDate_result,
												"_endDate_class" => "_endDate_class_editable",
												"_endDate_id" => "_endDate_id" . $roomID_result . "_editable");
			}

			$responce = "Rooms data.";
			$resp = "true";
			//$respdata = array('userID' => $userID, 'properties' => $properties);
			$respdata = $output;

		} else {
			$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


} elseif ($action == "delete_room"){

	$roomID = $data["roomID"];

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("DELETE FROM rooms WHERE roomID=?");
	$stmt->bind_param("i",$roomID);

	if ($stmt->execute()) {
		$responce = "Room deleted.";
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

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	// prepare and bind
	$stmt = $conn->prepare("INSERT INTO rooms (room_identify, userID, propertyID, roomtypeID) VALUES (?, ?, ?, ?)");
	$stmt->bind_param("siii",
	$room_identify,
	$userID,
	$propertyID,
	$roomtypeID);

	if ($stmt->execute()) {

		//Return status
		$responce = "Room added sucessfully.";
		$resp = "true";

	} else {
		$responce = "Room add failed: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	$conn->close();

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
