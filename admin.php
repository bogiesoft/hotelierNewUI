<?php

include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

$responce = "";
$resp = "false";
$respdata = array();

$action = $data["action"];
$token = $data["token"];
$email = $data["email"];

$adminID = checkToken($token,$email);

if ($adminID == "error") {
	$responce = "Authentication failed";
	$resp = "false";
	$result = array('responce' => $responce, 'resp' => $resp, 'data' => $respdata);
	echo json_encode($result);
	exit;
}

if ($action == "activate_user") {

		$userID = $data["userID"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("UPDATE users SET status='active' WHERE userID=?");
		$stmt->bind_param("i",
		$userID);

		if ($stmt->execute()) {
			$responce = "User activated sucessfully";
			$resp = "true";
		} else {
			$responce = "Activation failed(" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


} elseif ($action == "update_user_data") {		

		$id = $data["id"];
		$tbl = $data["tbl"];
		$fld = $data["fld"];
		$val = $data["val"];
		$fldid = $data["fldid"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("UPDATE users SET status=? WHERE userID=?");
		$stmt->bind_param("si", $val, $id);

		if ($stmt->execute()) {
			$responce = "User deactivated sucessfully_".$val."-".$id;
			$resp = "true";
		} else {
			$responce = "Deactivation failed(" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


} elseif ($action == "deactivate_user") {

		$userID = $data["userID"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("UPDATE users SET status='non_active' WHERE userID=?");
		$stmt->bind_param("i",
		$userID);

		if ($stmt->execute()) {
			$responce = "User deactivated sucessfully";
			$resp = "true";
		} else {
			$responce = "Deactivation failed(" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


} elseif ($action == "show_user"){

		$userID = $data["userID"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("SELECT name,surname,email,phone,mobile,address,town,postcode,country,afm,registration_date FROM users WHERE userID=?");
		$stmt->bind_param("i", $userID);

		$responce = "Error";
		$resp = "false";

		if ($stmt->execute()) {

			$stmt->bind_result($name_result, $surname_result, $email_result,
			 $phone_result, $mobile_result, $address_result, $town_result,
			 $postcode_result, $country_result, $afm_result, $registration_date_result);

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
					$registration_date = $registration_date_result;
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
			, 'registration_date' => $registration_date
			, 'last_login_date' => $login_last_date);

		} else {
			$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


} elseif ($action == "show_users") {

		$type = $data["type"];

		$userID = "";
		$name = "";
		$surname = "";

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("SELECT userID, name, surname,email,phone,mobile,address, town, postcode, country, afm, doy, type, registration_date, status FROM users WHERE type=?");
		$stmt->bind_param("s", $type);

		$responce = "Error";
		$resp = "false";

		if ($stmt->execute()) {

			$stmt->bind_result($userID_result, $name_result, $surname_result,$email_result, $phone_result, $mobile_result, 
				$address_result, $town_result, $postcode_result, $country_result, $afm_result, $doy_result, $type_result, $registration_date_result, $status_result);

			$users = array();
			while ($stmt->fetch()) {
				$output[]=array("id" => $userID_result,
											"_id_class" => "_id_class_editable", 
											"_id_id" => "_id_id" . $userID_result . "_editable", 
											"name" => $name_result, 
											"_name_class" => "_name_class_editable", 
											"_name_id" => "_name_id" . $userID_result . "_editable", 
											"surname" => $surname_result,
											"_surname_class" => "_surname_class_editable", 
											"_surname_id" => "_surname_id" . $userID_result . "_editable", 
											"email" => $email_result, 
											"phone" => $phone_result,
											"_phone_class" => "_phone_class_editable", 
											"_phone_id" => "_phone_id" . $userID_result . "_editable", 
											"mobile" => $mobile_result, 
											"_mobile_class" => "_mobile_class_editable", 
											"_mobile_id" => "_mobile_id" . $userID_result . "_editable", 
											"address" => $address_result, 
											"_address_class" => "_address_class_editable", 
											"_address_id" => "_address_id" . $userID_result . "_editable", 
											"town" => $town_result, 
											"_town_class" => "_town_class_editable", 
											"_town_id" => "_town_id" . $userID_result . "_editable", 
											"postcode" => $postcode_result, 
											"_postcode_class" => "_postcode_class_editable", 
											"_postcode_id" => "_postcode_id" . $userID_result . "_editable", 
											"country" => $country_result, 
											"_country_class" => "_country_class_editable", 
											"_country_id" => "_country_id" . $userID_result . "_editable", 
											"afm" => $afm_result, 
											"_afm_class" => "_afm_class_editable", 
											"_afm_id" => "_afm_id" . $userID_result . "_editable", 
											"doy" => $doy_result, 
											"_doy_class" => "_doy_class_editable", 
											"_doy_id" => "_doy_id" . $userID_result . "_editable", 
											"type" => $type_result,
											"status" => $status_result,
											"_status_class" => "_status_class_editable", 
											"_status_id" => "_status_id" . $userID_result . "_editable", 
											"registration_date" => $registration_date_result
										);
			}



			$responce = "Users data.";
			$resp = "true";
			$respdata = $output;

		} else {
			$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


} elseif ($action == "delete_user"){

	$userID = $data["userID"];

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

} elseif ($action == "add_admin"){

	$name = $data["name"];
	$surname = $data["surname"];
	$phone = $data["phone"];
	$mobile = $data["mobile"];
	$newadminemail = $data["newadminemail"];
	$password = $data["password"];

	//Generate salt & hash_pass values
	$salt = createSalt();
	$hash_pass = hash('sha256', $password.$salt);

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$type = "admin";

	// prepare and bind
	$stmt = $conn->prepare("INSERT INTO users (name, surname, email, phone, mobile, type, salt, hash_pass)
	VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
	$stmt->bind_param("ssssssss",
	$name,
	$surname,
	$newadminemail,
	$phone,
	$mobile,
	$type,
	$salt,
	$hash_pass);

	if ($stmt->execute()) {

		//Return status
		$responce = $data["name"] . " " . $data["surname"] . " registered sucessfully.";
		$resp = "true";

	} else {
		$responce = "Registation failed: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

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

function createSalt(){
    $text = md5(uniqid(rand(), true));
    return substr($text, 0, 3);
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

		$stmt = $conn->prepare("SELECT userID FROM users WHERE email=? AND token = ? AND type='admin'");
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
