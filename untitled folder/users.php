<?php

include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

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

if ($action == "update_user") {

		$name = $data["name"];
		$surname = $data["surname"];
		$phone = $data["phone"];
		$mobile = $data["mobile"];
		$address = $data["address"];
		$town = $data["town"];
		$postcode = $data["postcode"];
		$country = $data["country"];
		$afm = $data["afm"];
		$doy = $data["doy"];
		$password = $data["password"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("UPDATE users SET name=?, surname=?, phone=?, mobile=?, address=?, town=?, postcode=?, country=?, afm=?, doy=? WHERE email=? AND userID=?");
		$stmt->bind_param("ssssssssssss",
		$name,
		$surname,
		$phone,
		$mobile,
		$address,
		$town,
		$postcode,
		$country,
		$afm,
		$doy,
		$email,
		$userID);

		if ($stmt->execute()) {
			$responce = "User updated sucessfully";
			$resp = "true";
		} else {
			$responce = "Update failed(" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


} elseif ($action == "show_user"){

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("SELECT name,surname,email,phone,mobile,address,town,postcode,country,afm,doy,registration_date,login_last_date FROM users WHERE userID=?");
		$stmt->bind_param("i", $userID);

		$responce = "Error";
		$resp = "false";

		if ($stmt->execute()) {

			$stmt->bind_result($name_result, $surname_result, $email_result,
			 $phone_result, $mobile_result, $address_result, $town_result,
			 $postcode_result, $country_result, $afm_result, $doy_result, $registration_date_result,$login_last_date_result);

			while ($stmt->fetch()) {
					$name = $name_result;
					$surname = $surname_result;
					$email = $email_result;
					$phone = $phone_result;
					$mobile = $mobile_result;
					$address = $address_result;
					$town = $town_result;
					$postcode = $postcode_result;
					$country = $country_result;
					$afm = $afm_result;
					$doy = $doy_result;
					$registration_date = $registration_date_result;
					$login_last_date = $login_last_date_result;
			}

			$responce = "User data";
			$resp = "true";
			$respdata = array('userID' => $userID
			, 'name' => $name
			, 'surname' => $surname
			, 'email' => $email
			, 'phone' => $phone
			, 'mobile' => $mobile
			, 'address' => $address
			, 'town' => $town
			, 'postcode' => $postcode
			, 'country' => $country
			, 'afm' => $afm
			, 'doy' => $doy
			, 'registration_date' => $registration_date
			, 'last_login_date' => $login_last_date);

		} else {
			$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


} elseif ($action == "delete_user"){

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("DELETE FROM users WHERE userID=?");
	$stmt->bind_param("i",$userID);

	if ($stmt->execute()) {
		$responce = "User delete.";
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

//Return rsult
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
