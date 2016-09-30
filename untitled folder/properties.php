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

if ($action == "add_property"){

		$property_name = $data["property_name"];
		$eponymia = $data["eponymia"];
		$contact = $data["contact"];
		$phone = $data["phone"];
		$fax = $data["fax"];
		$email = $data["emailprop"];
		$website = $data["website"];
		$address = $data["address"];
		$town = $data["town"];
		$postcode = $data["postcode"];
		$country = $data["country"];
		$geotag = $data["geotag"];
		$logo = $data["logo"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		// prepare and bind
		$stmt = $conn->prepare("INSERT INTO properties (userID, property_name, eponymia, contact, phone, fax, email, website, address, town, postcode, country, geotag, logo)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("isssssssssssss",
		$userID,
		$property_name,
		$eponymia,
		$contact,
		$phone,
		$fax,
		$email,
		$website,
		$address,
		$town,
		$postcode,
		$country,
		$geotag,
		$logo);

		if ($stmt->execute()) {

			//Return status
			$responce = "Property added sucessfully.";
			$resp = "true";

		} else {
			$responce = "Property add failed: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


} elseif ($action == "update_property") {

		$propertyID = $data["propertyID"];

		$property_name = $data["property_name"];
		$eponymia = $data["eponymia"];
		$contact = $data["contact"];
		$phone = $data["phone"];
		$fax = $data["fax"];
		$email = $data["emailprop"];
		$website = $data["website"];
		$address = $data["address"];
		$town = $data["town"];
		$postcode = $data["postcode"];
		$country = $data["country"];
		$geotag = $data["geotag"];
		$logo = $data["logo"];
		$services = $data["services"];

		$usertype = user_type($token,$email);

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("UPDATE properties SET property_name=?, eponymia=?, contact=?, phone=?, fax=?, email=?, website=?, address=?, town=?, postcode=?, country=?, geotag=?, logo=? WHERE propertyID=? AND userID=?");
		$stmt->bind_param("sssssssssssssss",
		$property_name,
		$eponymia,
		$contact,
		$phone,
		$fax,
		$email,
		$website,
		$address,
		$town,
		$postcode,
		$country,
		$geotag,
		$logo,
		$propertyID,
		$userID);

		if ($stmt->execute()) {
			$responce = "Property updated sucessfully";
			$resp = "true";

			delete_services($propertyID);
			foreach ($services as $service) {
				add_service($service["service_name"],$service["service_descr"],$service["price"],htmlentities($service["currency"], ENT_QUOTES),$service["type"],$propertyID,$service["roomtypeID"],$service["daily"]);
			}

		} else {
			$responce = "Update failed(" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();



} elseif ($action == "update_property_alias") {

		$propertyID = $data["propertyID"];
		$alias = $data["alias"];

		$alias_exist = checkalias($alias);

		if ($alias_exist) {

			$responce = "Update failed. Alias already in use";
			$resp = "false";

		} else {

			$old_alias = getPropertyAlias($propertyID);

			// Create connection
			$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

			// Check connection
			if ($conn->connect_error) {
				die("Connection failed: " . $conn->connect_error);
			}

			$stmt = $conn->prepare("UPDATE properties SET alias=? WHERE propertyID=?");
			$stmt->bind_param("ss",
			$alias,
			$propertyID);

			if ($stmt->execute()) {
				$responce = "Property updated sucessfully";
				$resp = "true";

				$usertype = user_type($token,$email);

				//Copy client template
				if ($usertype == "admin") {

					$dest = "./hotels/$alias";
					mkdir($dest);

					if ($old_alias == "") {

						$filename = "./tpl/booking/config.php";
						$key = "PROPERTYID";
						$new_val = $propertyID;
						updateConfigValue($filename, $key,$new_val);


						$src = "./tpl/booking/";
						//xcopy($src,$dest,"755");
						recurse_copy($src,$dest);

					} else {
						$responce = $src;

						$src = "./hotels/$old_alias/";
						//xcopy($src,$dest,"755");
						recurse_copy($src,$dest);

						//Delete old folder
						delete_dir($src);
					}

				}

			} else {
				$responce = "Update failed(" . $stmt->errno . ") " . $stmt->error;
				$resp = "false";
			}

			$conn->close();


		}

	} elseif ($action == "update_property_langs") {

			$propertyID = $data["propertyID"];
			$langs = $data["langs"];


			// Create connection
			$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

			// Check connection
			if ($conn->connect_error) {
				die("Connection failed: " . $conn->connect_error);
			}

			$stmt = $conn->prepare("UPDATE properties SET langs=? WHERE propertyID=?");
			$stmt->bind_param("si",
			$langs,
			$propertyID);

			if ($stmt->execute()) {
				$responce = "Property updated sucessfully";
				$resp = "true";

				$usertype = user_type($token,$email);

				//Copy client template
				if ($usertype == "admin") {
					//Create or update the cinfig.php file

				}

			} else {
				$responce = "Update failed(" . $stmt->errno . ") " . $stmt->error;
				$resp = "false";
			}

			$conn->close();

} elseif ($action == "show_property"){

		$propertyID = $data["propertyID"];

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("SELECT property_name,alias,eponymia,contact,
		phone,fax,email,website,address,town,
		postcode,country,geotag,logo FROM properties WHERE propertyID=?");
		$stmt->bind_param("i", $propertyID);

		$responce = "Error";
		$resp = "false";

		if ($stmt->execute()) {

			$stmt->bind_result($property_name_result,$alias_result,$eponymia_result,$contact_result,
			$phone_result,$fax_result,$email_result,$website_result,$address_result,$town_result,
			$postcode_result,$country_result,$geotag_result,$logo_result);

			$services = show_services($propertyID);

			while ($stmt->fetch()) {
				$property_name = $property_name_result;
				$alias = $alias_result;
				$eponymia = $eponymia_result;
				$contact = $contact_result;
				$phone = $phone_result;
				$fax = $fax_result;
				$email = $email_result;
				$website = $website_result;
				$address = $address_result;
				$town = $town_result;
				$postcode = $postcode_result;
				$country = $country_result;
				$geotag = $geotag_result;
				$logo = $logo_result;
			}

			$responce = "Property data.";
			$resp = "true";
			$respdata = array('userID' => $userID
			, 'property_name' => $property_name
			, 'alias' => $alias
			, 'eponymia' => $eponymia
			, 'contact' => $contact
			, 'phone' => $phone
			, 'fax' => $fax
			, 'email' => $email
			, 'website' => $website
			, 'address' => $address
			, 'town' => $town
			, 'postcode' => $postcode
			, 'country' => $country
			, 'geotag' => $geotag
			, 'logo' => $logo
			, 'services' => $services);

		} else {
			$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
			$resp = "false";
		}

		$conn->close();


} elseif ($action == "show_properties"){

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$responce = "Error";
		$resp = "false";

		$usertype = user_type($token,$email);

		if ($usertype == "admin") {

			$stmt = $conn->prepare("SELECT propertyID,
				users.name,
				users.surname,
				property_name,
				alias,
				langs,
				eponymia,
				contact,
				properties.phone,
				fax,
				properties.email,
				website,
				properties.address,
				properties.town,
				properties.postcode,
				properties.country,
				geotag,
				logo FROM properties INNER JOIN users ON properties.userID=users.userID");

			//$stmt->bind_param("i", $userID);


			if ($stmt->execute()) {

				$stmt->bind_result(
				$propertyID_result,
				$name_result,
				$surname_result,
				$property_name_result,
				$alias_result,
				$langs_result,
				$eponymia_result,
				$contact_result,
				$phone_result,
				$fax_result,
				$email_result,
				$website_result,
				$address_result,
				$town_result,
				$postcode_result,
				$country_result,
				$geotag_result,
				$logo_result);

				//$properties = array();
				while ($stmt->fetch()) {

					$output[]=array("propertyID" => $propertyID_result,
													"name" => $name_result." ". $surname_result,
													"property_name" => $property_name_result,
													"alias" => $alias_result,
													"_alias_class" => "_alias_class_editable",
													"_alias_id" => "_alias_id" . $propertyID_result . "_editable",
													"langs" => $langs_result,
													"_langs_class" => "_langs_class_editable",
													"_langs_id" => "_langs_id" . $propertyID_result . "_editable",
													"eponymia" => $eponymia_result,
													"contact" => $contact_result,
													"phone" => $phone_result,
													"fax" => $fax_result,
													"email" => $email_result,
													"website" => $website_result,
													"address" => $address_result,
													"town" => $town_result,
													"postcode" => $postcode_result,
													"country" => $country_result,
													"geotag" => $geotag_result,
													"logo" => $logo_result);
				}

				$responce = "Properties data.";
				$resp = "true";
				$respdata = $output;

			} else {
				$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
				$resp = "false";
			}


		} elseif ($usertype == "hotelier") {


			$stmt = $conn->prepare("SELECT propertyID, property_name FROM properties WHERE userID=?");
			$stmt->bind_param("i", $userID);

			if ($stmt->execute()) {

				$stmt->bind_result($propertyID_result,$property_name_result);

				//$properties = array();
				while ($stmt->fetch()) {

					$output[]=array("propertyID" => $propertyID_result,
												"property_name" => $property_name_result,
												"_property_name_class" => "_property_name_class_editable",
												"_property_name_id" => "_property_name_id" . $propertyID_result . "_editable");
				}

				$responce = "Properties data.";
				$resp = "true";
				//$respdata = array('userID' => $userID, 'properties' => $properties);
				$respdata = $output;

			} else {
				$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
				$resp = "false";
			}


		}

		$conn->close();


} elseif ($action == "delete_property"){

	$propertyID = $data["propertyID"];

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("DELETE FROM properties WHERE propertyID=?");
	$stmt->bind_param("i",$propertyID);

	if ($stmt->execute()) {
		$responce = "Property deleted.";
		$resp = "true";
	} else {
		$responce = "Delete failed: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}


	///DELETE ALL RELATED DATA TO OTHER TABLES>>>> TO BE ADDED!!!!!
	// delete_roomservices($propertyID);
	// delete_specialprices($propertyID)
	// delete_roomtypes($propertyID);




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

function updateConfigValue($filename, $key,$new_val) {

  $filedata = file($filename);
  $newdata = array();
  $lookfor = $key;

  $new_val = "\$pref['" . $key . "'] = '" . $new_val . "';";

  foreach ($filedata as $filerow) {
    if (strstr($filerow, $lookfor) !== false)
      $filerow = $new_val;
    $newdata[] = $filerow;
  }

  file_put_contents($filename,$newdata) ;


}




function checkalias($alias) {

	$propertyID = 0;

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT propertyID FROM properties WHERE alias=?");
	$stmt->bind_param("s", $alias);

	if ($stmt->execute()) {

		$stmt->bind_result($propertyID_result);

		while ($stmt->fetch()) {
				$propertyID = $propertyID_result;
		}
	}

	$conn->close();

	if ($propertyID == 0) {
		return false;
	} else {
		return true;
	}

}


function getPropertyAlias($propertyID)
{
    $old_alias= "error";

		// Create connection
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$stmt = $conn->prepare("SELECT alias FROM properties WHERE propertyID=?");
		$stmt->bind_param("i", $propertyID);

		if ($stmt->execute()) {

			$stmt->bind_result($alias_result);

			while ($stmt->fetch()) {
					$old_alias = $alias_result;
			}
		}

		$conn->close();

    return $old_alias;
}


function user_type($token, $email) {

	$usertype= "ghost";

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT type FROM users WHERE email=? AND token = ?");
	$stmt->bind_param("ss", $email, $token);

	if ($stmt->execute()) {

		$stmt->bind_result($usertype_result);

		while ($stmt->fetch()) {
				$usertype = $usertype_result;
		}
	}

	$conn->close();

	return $usertype;


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

function show_services($propertyID) {
	$output = array();
	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT service_name, service_descr, price, currency, type, daily, propertyID, roomtypeID FROM services WHERE propertyID=?");
	$stmt->bind_param("i", $propertyID);

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

function delete_services($propertyID){


	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("DELETE FROM services WHERE propertyID=?");
	$stmt->bind_param("i",$propertyID);

	if ($stmt->execute()) {
		$responce = "Service deleted.";
		$resp = "true";
	} else {
		$responce = "Delete failed: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	//$conn->close();

}

// function delete_specialprices($propertyID){
//
// 	// Create connection
// 	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//
// 	// Check connection
// 	if ($conn->connect_error) {
// 	    die("Connection failed: " . $conn->connect_error);
// 	}
//
// 	$stmt = $conn->prepare("DELETE FROM special_prices WHERE propertyID=?");
// 	$stmt->bind_param("i",$roomtypeID);
//
// 	if ($stmt->execute()) {
// 		$responce = "Special price deleted.";
// 		$resp = "true";
// 	} else {
// 		$responce = "Delete failed: (" . $stmt->errno . ") " . $stmt->error;
// 		$resp = "false";
// 	}
//
// 	$conn->close();
//
// }
//
// function delete_roomservices($propertyID){
//
// 	// Create connection
// 	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//
// 	// Check connection
// 	if ($conn->connect_error) {
// 	    die("Connection failed: " . $conn->connect_error);
// 	}
//
// 	$stmt = $conn->prepare("DELETE FROM services WHERE propertyID=?");
// 	$stmt->bind_param("i",$roomtypeID);
//
// 	if ($stmt->execute()) {
// 		$responce = "Service deleted.";
// 		$resp = "true";
// 	} else {
// 		$responce = "Delete failed: (" . $stmt->errno . ") " . $stmt->error;
// 		$resp = "false";
// 	}
//
// 	$conn->close();
//
// }
//
// function delete_roomtypes($propertyID) {
// 	// Create connection
// 	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//
// 	// Check connection
// 	if ($conn->connect_error) {
// 	    die("Connection failed: " . $conn->connect_error);
// 	}
//
// 	$stmt = $conn->prepare("DELETE FROM room_types WHERE propertyID=?");
// 	$stmt->bind_param("i",$roomtypeID);
//
// 	if ($stmt->execute()) {
// 		$responce = "Service deleted.";
// 		$resp = "true";
// 	} else {
// 		$responce = "Delete failed: (" . $stmt->errno . ") " . $stmt->error;
// 		$resp = "false";
// 	}
//
// 	$conn->close();
// }

/**
 * Copy a file, or recursively copy a folder and its contents
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.1
 * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
 * @param       string   $source    Source path
 * @param       string   $dest      Destination path
 * @param       int      $permissions New folder creation permissions
 * @return      bool     Returns true on success, false on failure
 */
function xcopy($source, $dest, $permissions = 0755)
{
    // Check for symlinks
    if (is_link($source)) {
        return symlink(readlink($source), $dest);
    }

    // Simple copy for a file
    if (is_file($source)) {
        return copy($source, $dest);
    }

    // Make destination directory
    if (!is_dir($dest)) {
        mkdir($dest, $permissions);
    }

    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep copy directories
        xcopy("$source/$entry", "$dest/$entry", $permissions);
    }

    // Clean up
    $dir->close();
    return true;
}

function copyr($source, $dest)
{
    // Check for symlinks
    if (is_link($source)) {
        return symlink(readlink($source), $dest);
    }

    // Simple copy for a file
    if (is_file($source)) {
        return copy($source, $dest);
    }

    // Make destination directory
    if (!is_dir($dest)) {
        mkdir($dest);
    }

    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep copy directories
        copyr("$source/$entry", "$dest/$entry");
    }

    // Clean up
    $dir->close();
    return true;
}


function recurse_copy($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}


function delete_dir($src) {
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                delete_dir($src . '/' . $file);
            }
            else {
                unlink($src . '/' . $file);
            }
        }
    }
    closedir($dir);
    rmdir($src);

}


?>
