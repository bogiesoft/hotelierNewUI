<?php

include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
//print_r($data);

$responce = "";
$resp = "false";
$respdata = array();
$output = array();
$action = $data["action"];
$token = $data["token"];
$email = $data["email"];

$result = array('status' => "error");

$userID = checkToken($token,$email);

if ($userID == "error") {
	$result = array('status' => "authentication_failed");
	echo json_encode($result);
	exit;
}

if ($action == "add_roomtype"){

		$propertyID = $data["propertyID"];
		$roomtype_name = $data["roomtype_name"];
		$roomtype_descr = $data["roomtype_descr"];
		$quantity = $data["quantity"];
		$price = $data["price"];
		$currency = htmlentities($data["currency"], ENT_QUOTES);
		$capacity_min = $data["capacity_min"];
		$capacity_max = $data["capacity_max"];
		$child_min = $data["child_min"];
		$child_max = $data["child_max"];
		$minimum_stay = $data["minimum_stay"];
		$services = $data["services"];
		$specialprices = $data["specialprices"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		// prepare and bind
		$stmt = $conn->prepare("INSERT INTO room_types (userID, propertyID, roomtype_name, roomtype_descr, quantity, price, currency, capacity_min, capacity_max, child_min, child_max, minimum_stay)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("iississiiiii",
		$userID,
		$propertyID,
		$roomtype_name,
		$roomtype_descr,
		$quantity,
		$price,
		$currency,
		$capacity_min,
		$capacity_max,
		$child_min,
		$child_max,
		$minimum_stay);

		if ($stmt->execute()) {

			$last_id = mysqli_insert_id($conn);

			//Return status
			$responce = "Room type added sucessfully.";
			$resp = "true";

			deleterooms($roomtypeID);
			for ($x = 1; $x <= $quantity; $x++) {
				add_room(str_replace(" ", "_", $roomtype_name) . "_" . $x,$userID,$propertyID,$last_id);
			}

			delete_services($last_id);
			foreach ($services as $service) {
				add_service($service["service_name"],$service["service_descr"],$service["price"],htmlentities($service["currency"], ENT_QUOTES),$service["type"],$service["propertyID"],$last_id,$service["daily"]);
			}

			delete_specialprices($last_id);
			foreach ($specialprices as $specialprice) {
				add_specialprice($last_id,$specialprice["price"],$specialprice["startDate"],$specialprice["endDate"]);
			}

		} else {
			$responce = "Room type add failed: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}




		$conn->close();


} elseif ($action == "update_roomtype") {

		$roomtypeID = $data["roomtypeID"];
		$propertyID = $data["propertyID"];
		$roomtype_name = $data["roomtype_name"];
		$roomtype_descr = $data["roomtype_descr"];
		$quantity = $data["quantity"];
		$price = $data["price"];
		$currency = htmlentities($data["currency"], ENT_QUOTES);
		$capacity_min = $data["capacity_min"];
		$capacity_max = $data["capacity_max"];
		$child_min = $data["child_min"];
		$child_max = $data["child_max"];
		$minimum_stay = $data["minimum_stay"];
		$services = $data["services"];
		$specialprices = $data["specialprices"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("UPDATE room_types SET roomtype_name=?, roomtype_descr=?, quantity=?, price=?, currency=?, capacity_min=?, capacity_max=?, child_min=?, child_max=?, minimum_stay=? WHERE roomtypeID=? AND userID=?");
		$stmt->bind_param("ssissiiiiiii",
		$roomtype_name,
		$roomtype_descr,
		$quantity,
		$price,
		$currency,
		$capacity_min,
		$capacity_max,
		$child_min,
		$child_max,
		$minimum_stay,
		$roomtypeID,
		$userID);

		if ($stmt->execute()) {

			deleterooms($roomtypeID);
			for ($x = 1; $x <= $quantity; $x++) {
				//echo str_replace(" ", "_", $roomtype_name) . "_" . $x,$userID,$propertyID,$roomtypeID;
				add_room(str_replace(" ", "_", $roomtype_name) . "_" . $x,$userID,$propertyID,$roomtypeID);
			}

			delete_services($roomtypeID);
			foreach ($services as $service) {
				add_service($service["service_name"],$service["service_descr"],$service["price"],htmlentities($service["currency"], ENT_QUOTES),$service["type"],$service["propertyID"],$roomtypeID,$service["daily"]);
			}

			delete_specialprices($roomtypeID);
			foreach ($specialprices as $specialprice) {
				add_specialprice($roomtypeID,$specialprice["price"],$specialprice["startDate"],$specialprice["endDate"]);
			}

			$responce = "Room type updated sucessfully";
			$resp = "true";
		} else {
			$responce = "Update failed(" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


} elseif ($action == "show_roomtype"){

		$roomtypeID = $data["roomtypeID"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("SELECT roomtypeID,
		userID,
		propertyID,
		roomtype_name,
		roomtype_descr,
		quantity,
		price,
		currency,
		capacity_min,
		capacity_max,
		child_min,
		child_max,
		minimum_stay FROM room_types WHERE roomtypeID=?");
		$stmt->bind_param("i", $roomtypeID);

		$responce = "Error";
		$resp = "false";

		if ($stmt->execute()) {

			$stmt->bind_result(
			$roomtypeID_result,
			$userID_result,
			$propertyID_result,
			$roomtype_name_result,
			$roomtype_descr_result,
			$quantity_result,
			$price_result,
			$currency_result,
			$capacity_min_result,
			$capacity_max_result,
			$child_min_result,
			$child_max_result,
			$minimum_stay_result);

			$specialprices = show_specialprices($roomtypeID);
			$services = show_services($roomtypeID);

			while ($stmt->fetch()) {
				$roomtypeID = $roomtypeID_result;
				$userID = $userID_result;
				$propertyID = $propertyID_result;
				$roomtype_name = $roomtype_name_result;
				$roomtype_descr = $roomtype_descr_result;
				$quantity = $quantity_result;
				$price = $price_result;
				$currency = $currency_result;
				$capacity_min = $capacity_min_result;
				$capacity_max = $capacity_max_result;
				$child_min = $child_min_result;
				$child_max = $child_max_result;
				$minimum_stay = $minimum_stay_result;

			}

			$responce = "Room type data.";
			$resp = "true";
			$respdata = array('roomtypeID' => $roomtypeID
			, 'userID' => $userID
			, 'propertyID' => $propertyID
			, 'roomtype_name' => $roomtype_name
			, 'roomtype_descr' => $roomtype_descr
			, 'quantity' => $quantity
			, 'price' => $price
			, 'currency' => $currency
			, 'capacity_min' => $capacity_min
			, 'capacity_max' => $capacity_max
			, 'child_min' => $child_min
			, 'child_max' => $child_max
			, 'minimum_stay' => $minimum_stay
			, 'services' => $services
			, 'specialprices' => $specialprices);

		} else {
			$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


} elseif ($action == "show_roomtypes"){

		$propertyID = $data["propertyID"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("SELECT roomtypeID, roomtype_name, roomtype_descr FROM room_types WHERE userID=? AND propertyID=?");
		$stmt->bind_param("ii", $userID, $propertyID);

		$responce = "Error";
		$resp = "false";

		if ($stmt->execute()) {

			$stmt->bind_result($roomtypeID_result,$roomtype_name_result,$roomtype_descr_result);

			while ($stmt->fetch()) {

				$output[]=array("roomtypeID" => $roomtypeID_result,
												"roomtype_name" => $roomtype_name_result,
												"roomtype_descr" => $roomtype_descr_result);

			}

			$responce = "Room types data.";
			$resp = "true";
			$respdata = $output;

		} else {
			$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


		} elseif ($action == "show_roomtypes_all"){

				// Create connection
				$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

				// Check connection
				if ($conn->connect_error) {
					die("Connection failed: " . $conn->connect_error);
				}

				$stmt = $conn->prepare("SELECT roomtypeID, properties.property_name as property_name,roomtype_name, roomtype_descr FROM room_types INNER JOIN (properties) ON (room_types.propertyID = properties.propertyID) WHERE room_types.userID=?");
				$stmt->bind_param("i", $userID);

				$responce = "Error";
				$resp = "false";

				if ($stmt->execute()) {

					$stmt->bind_result($roomtypeID_result,$property_name_result,$roomtype_name_result,$roomtype_descr_result);

					while ($stmt->fetch()) {

						$output[]=array("roomtypeID" => $roomtypeID_result,
														"property_name" => $property_name_result,
														"roomtype_name" => $roomtype_name_result,
														"roomtype_descr" => $roomtype_descr_result);

					}

					$responce = "Room types data.";
					$resp = "true";
					$respdata = $output;

			} else {
				$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
				$resp = "false";
			}

			$conn->close();


} elseif ($action == "delete_roomtype"){

	$roomtypeID = $data["roomtypeID"];

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("DELETE FROM room_types WHERE roomtypeID=?");
	$stmt->bind_param("i",$roomtypeID);

	if ($stmt->execute()) {

		deleterooms($roomtypeID);
		delete_services($roomtypeID);
		delete_specialprices($last_id);

		$responce = "Room type deleted.";
		$resp = "true";
	} else {
		$responce = "Delete failed: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	$conn->close();

} else {

	$result = array('status' => "rejected", 'reason' => "Invalid action");

}

//Return result
$result = array('responce' => $responce, 'resp' => $resp, 'data' => $respdata);
echo json_encode($result);


////////////////////////////////////////////////////////////////////////////
////////////////////      FUNCTIONS       //////////////////////////////////
////////////////////////////////////////////////////////////////////////////

function show_services($roomtypeID) {
	$output = array();
	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT service_name, service_descr, price, currency, type, daily, propertyID, roomtypeID FROM services WHERE roomtypeID=?");
	$stmt->bind_param("i", $roomtypeID);

	$responce = "Error";
	$resp = "false";
	if ($stmt->execute()) {

		$stmt->bind_result($service_name_result,$service_descr_result,
		$price_result,$currency_result,$type_result, $daily_result,
		$propertyID_result,$roomtypeID_result);

		while ($stmt->fetch()) {
			$output[]=array("service_name" => $service_name_result,
											"service_descr" => $service_descr_result,
											"price" => $price_result,
											"currency" => $currency_result,
											"type" => $type_result,
											"daily" => $daily_result,
											"propertyID" => $propertyID_result,
											"roomtypeID" => $roomtypeID_result);
		}

	} else {
		$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	$conn->close();
	return $output;

}

function show_specialprices($roomtypeID) {
	$output = array();
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
	 $output;

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

	} else {
		$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

		$conn->close();

		return $output;

}

function add_specialprice($roomtypeID,$price,$startDate,$endDate){

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

}

function delete_specialprices($roomtypeID){

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("DELETE FROM special_prices WHERE roomtypeID=?");
	$stmt->bind_param("i",$roomtypeID);

	if ($stmt->execute()) {
		$responce = "Special price deleted.";
		$resp = "true";
	} else {
		$responce = "Delete failed: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	$conn->close();

}

function add_service($service_name,$service_descr,$price,$currency,$type,$propertyID,$roomtypeID,$daily){

			// Create connection
			$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

			// Check connection
			if ($conn->connect_error) {
				die("Connection failed: " . $conn->connect_error);
			}

			// prepare and bind
			$stmt = $conn->prepare("INSERT INTO services (service_name, service_descr, price, currency, type, daily, propertyID, roomtypeID)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
			$stmt->bind_param("sssssiii",
			$service_name,
			$service_descr,
			$price,
			$currency,
			$type,
			$daily,
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

}

function delete_services($roomtypeID){

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("DELETE FROM services WHERE roomtypeID=?");
	$stmt->bind_param("i",$roomtypeID);

	if ($stmt->execute()) {
		$responce = "Service deleted.";
		$resp = "true";
	} else {
		$responce = "Delete failed: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	//$conn->close();

}

function add_room($room_identify,$userID,$propertyID,$roomtypeID) {
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
}

function deleterooms($roomtypeID){

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("DELETE FROM rooms WHERE roomtypeID=?");
	$stmt->bind_param("i",$roomtypeID);

	if ($stmt->execute()) {
		$responce = "Rooms deleted.";
		$resp = "true";
	} else {
		$responce = "Delete failed: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	$conn->close();


}

function checkToken($token, $email) {
    $userID= "error";
		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$sql = "SELECT userID FROM users WHERE email='" . $email . "' AND token='" . $token . "'";
		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
		    // output data of each row
		    while($row = $result->fetch_assoc()) {
		        $userID = $row["userID"];
		    }
		} else {
		    $userID= "error";
		}

		$conn->close();

    return $userID;
}

?>
