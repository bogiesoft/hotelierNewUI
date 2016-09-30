<?php

include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
//print_r($data);

$responce = "";
$resp = "false";
$respdata = array();

$action = $data["action"];

if ($action == "show_service"){

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

		$stmt = $conn->prepare("SELECT serviceID, service_name,service_descr, price, currency, type,
		propertyID, roomtypeID FROM services WHERE roomtypeID=?");
		$stmt->bind_param("i", $roomtypeID);

		$responce = "Error";
		$resp = "false";

		if ($stmt->execute()) {

			$stmt->bind_result($serviceID_result,$service_name_result,$service_descr_result,
			$price_result,$currency_result,$type_result,
			$propertyID_result,$roomtypeID_result);

			while ($stmt->fetch()) {

				$output[]=array("serviceID" => $serviceID_result,
												"service_name" => $service_name_result,
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

		$stmt = $conn->prepare("SELECT  serviceID,service_name,service_descr, price, currency, type,
		propertyID, roomtypeID FROM services WHERE propertyID=?");
		$stmt->bind_param("i", $propertyID);

		$responce = "Error";
		$resp = "false";

		if ($stmt->execute()) {

			$stmt->bind_result($serviceID_result,$service_name_result,$service_descr_result,
			$price_result,$currency_result,$type_result,
			$propertyID_result,$roomtypeID_result);

			$roomsServices = showRoomTypesServices($propertyID);

			while ($stmt->fetch()) {

				$output[]=array("serviceID" => $serviceID_result,
                        "service_name" => $service_name_result,
												"service_descr" => $service_descr_result,
												"price" => $price_result,
												"currency" => $currency_result,
												"type" => $type_result,
												"propertyID" => $propertyID_result,
												"roomtypeID" => $roomtypeID_result);				}

			$responce = "Services data.";
			$resp = "true";
			$respdata = array('propertyServices' => $output, 'roomsServices' => $roomsServices);

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

function showRoomTypesServices($propertyID) {
	$output = array();

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT room_types.roomtypeID,services.serviceID,services.service_name,services.service_descr,services.price,services.currency,services.`type`,services.daily FROM room_types
													INNER JOIN services ON ( room_types.roomtypeID = services.roomtypeID) WHERE room_types.propertyID=?");
	$stmt->bind_param("i", $propertyID);

	$responce = "Error";
	$resp = "false";

	if ($stmt->execute()) {

		$stmt->bind_result($roomtypeID_result,$serviceID_result,$service_name_result,
		$service_descr_result,$price_result,$currency_result,
		$type_result,$daily_result);

		while ($stmt->fetch()) {

			$output[]=array("serviceID" => $serviceID_result,
                      "service_name" => $service_name_result,
											"service_descr" => $service_descr_result,
											"price" => $price_result,
											"currency" => $currency_result,
											"type" => $type_result,
											"roomtypeID" => $roomtypeID_result,
										  "daily" => $daily_result);				}

		$responce = "Services data.";
		$resp = "true";
		$respdata = $output;

	} else {
		$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	$conn->close();
	return $output;
}

?>
