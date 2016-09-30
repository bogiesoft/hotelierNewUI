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

if ($action == "add_service"){

		$service_name = $data["service_name"];
		$service_descr = $data["service_descr"];
		$price = $data["price"];
		$currency = htmlentities($data["currency"], ENT_QUOTES);
		$type = $data["type"];
		$propertyID= $data["propertyID"];
		$roomtypeID = $data["roomtypeID"];



		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		// prepare and bind
		$stmt = $conn->prepare("INSERT INTO services (service_name, service_descr, price, currency, type, propertyID, roomtypeID)
		VALUES (?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("sssssii",
		$service_name,
		$service_descr,
		$price,
		$currency,
		$type,
		$propertyID,
		$roomtypeID);

		if ($stmt->execute()) {

			//Return status
			$responce = "Service added sucessfully.";
			$resp = "true";

		} else {
			$responce = "Service add failed: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


} elseif ($action == "update_service") {

		$serviceID = $data["serviceID"];

		$service_name = $data["service_name"];
		$service_descr = $data["service_descr"];
		$price = $data["price"];
		$currency = htmlentities($service["currency"], ENT_QUOTES);
		$type = $data["type"];
		$propertyID= $data["propertyID"];
		$roomtypeID = $data["roomtypeID"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("UPDATE services SET service_name=?, service_descr=?, price=?, currency=?, type=?, propertyID=?, roomtypeID=? WHERE serviceID=?");

		$stmt->bind_param("sssssiii",
		$service_name,
		$service_descr,
		$price,
		$currency,
		$type,
		$propertyID,
		$roomtypeID,
		$serviceID);

		if ($stmt->execute()) {
			$responce = "Service updated sucessfully";
			$resp = "true";
		} else {
			$responce = "Update failed(" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


} elseif ($action == "show_service"){

		$serviceID = $data["serviceID"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("SELECT service_name,service_descr, price, currency, type,
		propertyID, roomtypeID FROM services WHERE serviceID=?");
		$stmt->bind_param("i", $serviceID);

		$responce = "Error";
		$resp = "false";

		if ($stmt->execute()) {

			$stmt->bind_result($service_name_result,$service_descr_result,
			$price_result,$currency_result,$type_result,
			$propertyID_result,$roomtypeID_result);

			while ($stmt->fetch()) {
				$service_name = $service_name_result;
				$service_descr = $service_descr_result;
				$price = $price_result;
				$currency = $currency_result;
				$type = $type_result;
				$propertyID = $propertyID_result;
				$roomtypeID = $roomtypeID_result;
			}

			$responce = "Service data.";
			$resp = "true";
			$respdata = array('serviceID' => $serviceID
			, 'service_name' => $service_name
			, 'service_descr' => $service_descr
			, 'price' => $price
			, 'currency' => $currency
			, 'type' => $type
			, 'propertyID' => $propertyID
			, 'roomtypeID' => $roomtypeID);

		} else {
			$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


} elseif ($action == "show_services_roomtype"){

		$roomtypeID = $data["roomtypeID"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("SELECT service_name,service_descr, price, currency, type,
		propertyID, roomtypeID FROM services WHERE roomtypeID=?");
		$stmt->bind_param("i", $roomtypeID);

		$responce = "Error";
		$resp = "false";

		if ($stmt->execute()) {

			$stmt->bind_result($service_name_result,$service_descr_result,
			$price_result,$currency_result,$type_result,
			$propertyID_result,$roomtypeID_result);

			while ($stmt->fetch()) {

				$output[]=array("service_name" => $service_name_result,
												"service_descr" => $service_descr_result,
												"price" => $price_result,
												"currency" => $currency_result,
												"type" => $type_result,
												"propertyID" => $propertyID_result,
												"roomtypeID" => $roomtypeID_result);
			}

			$responce = "Services data.";
			$resp = "true";
			$respdata = $output;

		} else {
			$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();

	} elseif ($action == "show_services_property"){

			$propertyID= $data["propertyID"];

			// Create connection
			$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

			// Check connection
			if ($conn->connect_error) {
				die("Connection failed: " . $conn->connect_error);
			}

			$stmt = $conn->prepare("SELECT service_name,service_descr, price, currency, type,
			propertyID, roomtypeID FROM services WHERE propertyID=?");
			$stmt->bind_param("i", $propertyID);

			$responce = "Error";
			$resp = "false";

			if ($stmt->execute()) {

				$stmt->bind_result($service_name_result,$service_descr_result,
				$price_result,$currency_result,$type_result,
				$propertyID_result,$roomtypeID_result);

				while ($stmt->fetch()) {

					$output[]=array("service_name" => $service_name_result,
													"service_descr" => $service_descr_result,
													"price" => $price_result,
													"currency" => $currency_result,
													"type" => $type_result,
													"propertyID" => $propertyID_result,
													"roomtypeID" => $roomtypeID_result);				}

				$responce = "Services data.";
				$resp = "true";
				$respdata = $output;

			} else {
				$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
				$resp = "false";
			}

			$conn->close();

} elseif ($action == "delete_service"){

	$serviceID = $data["serviceID"];

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("DELETE FROM services WHERE serviceID=?");
	$stmt->bind_param("i",$serviceID);

	if ($stmt->execute()) {
		$responce = "Service deleted.";
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
