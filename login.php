<?php

include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

$responce = "";
$resp = "false";
$respdata = array();

$action = $data["action"];

if ($action == "signup") {

	$name = $data["name"];
	$surname = $data["surname"];
	$phone = $data["phone"];
	$mobile = $data["mobile"];
	$address = $data["address"];
	$town = $data["town"];
	$postcode = $data["postcode"];
	$country = $data["country"];
	$afm = $data["afm"];
	$email = $data["email"];
	$password = $data["password"];

	//Generate salt & hash_pass values
	$salt = createSalt();
	$hash_pass = hash('sha256', $password.$salt);

	//Store data to the database

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	// prepare and bind
	$stmt = $conn->prepare("INSERT INTO users (name, surname, email, phone, mobile, address, town, postcode, country, afm, salt, hash_pass)
	VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
	$stmt->bind_param("ssssssssssss",
	$name,
	$surname,
	$email,
	$phone,
	$mobile,
	$address,
	$town,
	$postcode,
	$country,
	$afm,
	$salt,
	$hash_pass);

	if ($stmt->execute()) {

		//Return status
		$responce = $data["name"] . " " . $data["surname"] . " registered sucessfully.";
		$resp = "true";

	} else {
		$responce = "Registration failed: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	$conn->close();

} elseif ($action == "change_password"){

	$email = $data["email"];
	$old_password = $data["old_password"];
	$new_password = $data["new_password"];

	//Read hash_pass, salt and userID by email

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT userID, salt, hash_pass FROM users WHERE email=?");
	$stmt->bind_param("s", $email);

	if ($stmt->execute()) {

		$stmt->bind_result($userID_result, $salt_result, $hash_pass_result);

		while ($stmt->fetch()) {
				$userID = $userID_result;
				$salt = $salt_result;
				$hash_pass_stored = $hash_pass_result;

		}
	} else {
		$responce = "Password change failed: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	//Generate hash_pass values
	$hash_pass = hash('sha256', $old_password.$salt);

	//Compare hash_pass values
	if ($hash_pass == $hash_pass_stored) {

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		//Generate salt & hash_pass values
		$new_salt = createSalt();
		$new_hash_pass = hash('sha256', $new_password.$new_salt);

		$stmt = $conn->prepare("UPDATE users SET salt=?, hash_pass=? WHERE email=?");
		$stmt->bind_param("sss",
		$new_salt,
		$new_hash_pass,
		$email);

		if ($stmt->execute()) {
			$responce = "Password changed sucessfully.";
			$resp = "true";
		} else {
			$responce = "Password change failed: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

	} else {
		$responce = "Password change failed. Wrong password.";
		$resp = "false";
	}

	$conn->close();

} elseif ($action == "reset_password"){

	$email = $data["email"];

	$new_password = random_password(8);

	echo $new_password;
	//exit;


	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	//Generate salt & hash_pass values
	$new_salt = createSalt();
	$new_hash_pass = hash('sha256', $new_password.$new_salt);

	$stmt = $conn->prepare("UPDATE users SET salt=?, hash_pass=? WHERE email=?");
	$stmt->bind_param("sss",
	$new_salt,
	$new_hash_pass,
	$email);

	if ($stmt->execute()) {
		$responce = "Password changed sucessfully.";
		$resp = "true";
	} else {
		$responce = "Password change failed: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	$conn->close();

	//Email to the user the new password.
	$subject = "Roomier Password reset for user: " . $email;

	$message = "
	<html>
	<head>
	<title>Password reset</title>
	</head>
	<body>
	<p>You received this email because you asked for password reset!</p>
	<table>
	<tr>
	<td>New password: </td><td> " . $new_password . "</td>
	</tr>
	<tr>
	</tr>
	<tr>
	<th>Please change your password as soon as possible!</th>
	</tr>
	</table>
	</body>
	</html>
	";

	email_send($email,$subject,$message);


} elseif ($action == "login"){

	$email = $data["email"];
	$password = $data["password"];
	$userID = "";
	$name = "";
	$surname = "";
	$type = "";
	$salt = "";
	$hash_pass_stored = "";

	//Read hash_pass, salt and userID by email

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT userID, name, surname, type, salt, hash_pass FROM users WHERE email=? AND status = 'active'");
	$stmt->bind_param("s", $email);

	if ($stmt->execute()) {

		$stmt->bind_result($userID_result, $name_result, $surname_result, $type_result, $salt_result, $hash_pass_result);

		while ($stmt->fetch()) {
				$userID = $userID_result;
				$name = $name_result;
				$surname = $surname_result;
				$type = $type_result;
				$salt = $salt_result;
				$hash_pass_stored = $hash_pass_result;
		}
	} else {
		$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	//Generate hash_pass values
	$hash_pass = hash('sha256', $password.$salt);

	//Compare hash_pass values
	if ($hash_pass == $hash_pass_stored) {

		$data = time()."_".$email;
		$token = createToken($data);

		//Store token to the database
		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("UPDATE users SET token=? WHERE email=?");
		$stmt->bind_param("ss",
		$token,
		$email);

		if ($stmt->execute()) {
			$responce = "Login is successful.";
			$resp = "true";
			$respdata = array('token' => $token, 'userID' => $userID, 'name' => $name, 'surname' => $surname, 'type' => $type);
		} else {
			$responce = "Login failed: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

	} else {
		$responce = "Login failed.";
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

function email_send($to, $subject, $message) {

	// Always set content-type when sending HTML email
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
	$header = "From:admin@roomier.gr \r\n";
	mail($to,$subject,$message,$headers);

}

function random_password( $length = 8 ) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
    $password = substr( str_shuffle( $chars ), 0, $length );
		//for ($i = 0; $i < $length; $i++) {
			//$password = $chars[mt_rand(0, strlen($chars) – 1)];
		//}

		return $password;
}


function createSalt(){
    $text = md5(uniqid(rand(), true));
    return substr($text, 0, 3);
}


function createToken($data)
{
    /* Create a part of token using secretKey and other stuff */
    $tokenGeneric = SECRET_KEY.$_SERVER["SERVER_NAME"];

    /* Encoding token */
    $token = hash('sha256', $tokenGeneric.$data);

    return $token;
}

?>
