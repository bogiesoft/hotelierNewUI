<?php

include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
//print_r($data);

$result;
$responce = "";
$resp = "false";
$respdata = array();

$action = $data["action"];
//$token = $data["token"];
//$email = $data["email"];

$propertyID = $data["propertyID"];

//$userID = checkToken($token,$email);

// if ($userID == "error") {
// 	$responce = "Authentication failed";
// 	$resp = "false";
// 	$result = array('responce' => $responce, 'resp' => $resp, 'data' => $respdata);
// 	echo json_encode($result);
// 	exit;
// }

if ($action == "check_available"){

		$arrival_date = $data["arrival_date"];
		$departure_date = $data["departure_date"];
		$nights = 2;
		//$rooms_num = $data["rooms"];
		$adults = $data["adults"];
		$children = $data["children"];

		//Select roomtypes that matches criteria
		//Returns the roomtypesIDs of matching roomtypes
		$roomtypes_suitable = get_roomtypes($propertyID, $nights, $adults, $children);

		$roomstypeID_avail = array();

		foreach($roomtypes_suitable as $roomtype_suitable) {

			//Check if selected room types are available from the onDuty option
			//Returns the roomsIDs that are included in the matching roomtypes
			$roomsID_onDuty = get_roomsID_onDuty($roomtype_suitable);

			//Check if selected room types are available from the booking option
			//Cleanup previous lists from rooms that are allready booked
			$roomsID_free = get_roomsID_free($roomsID_onDuty, $roomtype_suitable);


			//if (count($roomsID_free) >= $rooms_num) {
				array_push($roomstypeID_avail,$roomtype_suitable);
			//} else
				//Probably we should also return the suitable roomtypesID with a notice that less rooms are available
			//}
		}

		$roomtypes_details = array();

		//The array $roomstypeID_avail includes all roomtypesID that are suitable and free.

		//Get details for each roomtype
		foreach($roomstypeID_avail as $roomtypeID) {

			$roomtype_data = get_roomtype_details($roomtypeID,$arrival_date,$departure_date);

			//Create responce
			array_push($roomtypes_details,$roomtype_data);

		}

		$responce = "Available rooms";
		$resp = "true";
		$respdata = $roomtypes_details;
		//print_r($roomtypes_details);
		//exit;


} elseif ($action == "select_room") {



} elseif ($action == "book_room") {



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

function get_roomsID_free($roomsID_onDuty) {

}


function get_roomsID_onDuty($roomtypes_suitable) {


}


function get_roomtype_details($roomtypeID,$arrival_date,$departure_date) {

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT roomtype_name,
	roomtype_descr,
	price,
	currency FROM room_types WHERE roomtypeID=?");
	$stmt->bind_param("i", $roomtypeID);

	$responce = "Error";
	$resp = "false";

	if ($stmt->execute()) {

		$stmt->bind_result(
		$roomtype_name_result,
		$roomtype_descr_result,
		$price_result,
		$currency_result);


		while ($stmt->fetch()) {
			$roomtype_name = $roomtype_name_result;
			$roomtype_descr = $roomtype_descr_result;
			$price = $price_result;
			$currency = $currency_result;
		}

		$services = show_services($roomtypeID);
		$prices = get_prices($roomtypeID,$price,$arrival_date,$departure_date);
		$totalcost = calculate_total_cost($prices,$arrival_date);
		$images = get_images($roomtypeID);

		$responce = "Room type data.";
		$resp = "true";
		$respdata = array('roomtypeID' => $roomtypeID
		, 'roomtype_name' => $roomtype_name
		, 'roomtype_descr' => $roomtype_descr
		, 'price' => $price
		, 'currency' => $currency
		, 'services' => $services
		, 'prices' => $prices
		, 'total_cost' => $totalcost
		, 'images' => $images);

	} else {
		$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	$conn->close();

	return $respdata;

}


function show_services($roomtypeID) {

	$output = array();
	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT service_name, service_descr, price, currency, daily FROM services WHERE roomtypeID=?");
	$stmt->bind_param("i", $roomtypeID);

	$responce = "Error";
	$resp = "false";
	if ($stmt->execute()) {

		$stmt->bind_result($service_name_result,$service_descr_result,
		$price_result,$currency_result, $daily_result);

		while ($stmt->fetch()) {
			$output[]=array("service_name" => $service_name_result,
											"service_descr" => $service_descr_result,
											"price" => $price_result,
											"currency" => $currency_result,
											"daily" => $daily_result);
		}

	} else {
		$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	$conn->close();
	return $output;

}

function get_prices($roomtypeID,$price,$arrival_date,$departure_date) {

	$date1 = new DateTime($arrival_date);
	$date2 = new DateTime($departure_date);

	$night = $date2->diff($date1)->format("%a");

	$current_night = date('Y-m-d', strtotime($arrival_date .' -1 day'));
	//$prices = array();

	if (!has_roomtype_specialprice ($roomtypeID)) {

			//Calculate all prices based on default prices
			for ($i=0; $i < $night; $i++) {
					$current_night = date('Y-m-d', strtotime($current_night .' +1 day'));
					//array_push($prices,"$current_night:$price");
					$prices[]=array($current_night=>$price);
			}

	} else {

			//Check each date seperatly
			for ($i=0; $i < $night; $i++) {
				$current_night = date('Y-m-d', strtotime($current_night .' +1 day'));
				$current_price = get_roomtype_specialprice ($roomtypeID,$current_night,$price);
				//array_push($prices,"$current_night:$current_price");
				$prices[]=array($current_night=>$current_price);
			}

	}

	return $prices;

}


function has_roomtype_specialprice ($roomtypeID) {

	$has_special_price = false;

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT spriceID FROM special_prices WHERE roomtypeID=?");
	$stmt->bind_param("i", $roomtypeID);


	if ($stmt->execute()) {

		$stmt->bind_result($spriceID_result);

		while ($stmt->fetch()) {
			$has_special_price = true;
		}

	}

	$conn->close();


	return $has_special_price;

}


function get_roomtype_specialprice ($roomtypeID,$current_date,$price) {

	$current_price = "";

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT price FROM special_prices WHERE roomtypeID=? AND startDate <=? AND endDate >=?");
	$stmt->bind_param("iss", $roomtypeID,$current_date,$current_date);

	if ($stmt->execute()) {

		$stmt->bind_result($price_result);

		while ($stmt->fetch()) {
			$current_price =  $price_result;
		}

	}

	$conn->close();

	if ($current_price == "") {
		return $price;
	} else {
		return $current_price;
	}

}


function calculate_total_cost($prices,$arrival_date) {

	$totalcost = 0;

 	$current_date = date('Y-m-d', strtotime($arrival_date .' -1 day'));

	for ($i=0; $i < count($prices); $i++) {
		$current_price = $prices[$i];
		$current_date = date('Y-m-d', strtotime($current_date .' +1 day'));
		$totalcost += $current_price[$current_date];
	}

	return $totalcost;
}


function get_images($roomtypeID) {
	$directory = $_SERVER["DOCUMENT_ROOT"]."/".basename(__DIR__) ."/uploads/files/roomtypes/" . $roomtypeID;
	if (file_exists($directory)) {
		$scanned_directory = array_diff(scandir($directory), array('..', '.','thumbnail'));
	} else {
		$scanned_directory = "";
	}
	return $scanned_directory;

}


function get_roomtypes($propertyID, $nights, $adults, $children) {

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT roomtypeID FROM room_types WHERE capacity_min <=? AND capacity_max >=? AND child_min <=? AND child_max >=? AND minimum_stay <=? AND propertyID = ?");
	$stmt->bind_param("iiiiii", $adults, $adults, $children, $children, $nights, $propertyID);

$output;


	if ($stmt->execute()) {

		$stmt->bind_result($roomtypeID_result);

		$output = array();

		while ($stmt->fetch()) {
				//$output[]=array($roomtypeID_result);
				array_push($output,$roomtypeID_result);
		}
	}

	$conn->close();

	return $output;


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


/*
* This function returns a list of room ids for specific category that have no reservasion
*/
function CheckFreeRooms($firstNight, $lastNight, $roomQty, $category_rooms, $conn, $prefix) {

  $rooms_in_booking = array();
  $free_rooms = array();
  $rooms_to_check = array();

  $sql = "SELECT * FROM " . $prefix . "apb_availability";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {

      while($row = $result->fetch_assoc()) {

        if (!in_array($row["unit_id"], $rooms_in_booking)) {
          $rooms_in_booking[] = $row["unit_id"];
        }

        $calendar[] = $row;

      }

  }

  foreach ($category_rooms as &$room) {

    if (!in_array($room, $rooms_in_booking)) {

      $free_rooms[] = $room;

      // if (count($free_rooms) == $roomQty) {
      //   return $free_rooms;
      // }

    } else {

      $rooms_to_check[] = $room;

    }

  }

	$firstD = explode("-", $firstNight);
	$firstD_year = $firstD[0];
	$firstD_month = $firstD[1];
	$firstD_day = $firstD[2];

	$lastD = explode("-", $lastNight);
	$lastD_year = $lastD[0];
	$lastD_month = $lastD[1];
	$lastD_day = $lastD[2];

	$date1 = new DateTime($firstNight);
	$date2 = new DateTime($lastNight);

	$diff = $date2->diff($date1)->format("%a");

  //Loop for the rooms that should be checked
  foreach ($rooms_to_check as &$room_ch) {

    $new_firstD_day = "";
    $new_firstD_month = "";
    $new_firstD_year = "";

    $j=0;
    $room_status = "free";

    //Loop for all records in calendar
    for ($j = 0; $j < count($calendar); $j++) {

      //Same room under check with the room_id in calendar
      if($calendar[$j]["unit_id"] == $room_ch && $calendar[$j]["year"] == $firstD_year && $calendar[$j]["month"] == $firstD_month){

          $room_status = "to_update";

          //Check if selected dates exist in bookings table
          $i=0;
          $k=$diff+1;
          while($i<$diff+1) {

            //Dates excide months days (e.g. 28/12-2/1)
            if ($firstD_day+$i == cal_days_in_month (CAL_GREGORIAN, $firstD_month, $firstD_year)) {
                $new_firstD_day =1;
                if ($firstD_month == 12) {
                  $new_firstD_month = 1;
                  $new_firstD_year = $firstD_year + 1;
                } else {
                  $new_firstD_month = $firstD_month + 1;
                  $new_firstD_year = $firstD_year;

                }

                //Continue checks on next month
                break;

            }

            $ch_day = "d" . ($firstD_day + $i);
      			if ($calendar[$j][$ch_day] != "2") {
      				$room_status = "booked";
      			}

      			$i++;
            $k--;

    		  }

    	}

    }

    //Continue checking on next month
    if ($new_firstD_day == "1") {

      $m=0;
      for ($m = 0; $m < count($calendar); $m++) {

        if($calendar[$m]["unit_id"] == $room_ch && $calendar[$m]["year"] == $new_firstD_year && $calendar[$m]["month"] == $new_firstD_month){
            $room_status = "to_update";
            $l=0;

            while($l<$diff+1) {
              $ch_day = "d" . ($new_firstD_day + $l);

        			if ($calendar[$m][$ch_day] != "2") {
        				$room_status = "booked";
        			}
        			$l++;
      		  }

        }

      }

    }

    if ($firstD_month != $lastD_month) {

      for ($p = 0; $p < count($calendar); $p++) {

        if($calendar[$p]["unit_id"] == $room_ch && $calendar[$p]["year"] == $lastD_year && $calendar[$p]["month"] == $lastD_month){

            $room_status = "to_update";

            for ($n=0; $n < $lastD_day; $n++) {

              $ch_day = "d" . ($n + 1);
        			if ($calendar[$p][$ch_day] != "2") {
        				$room_status = "booked";
        			}

            }

        }

      }

    }

    if ($room_status == "free") {
      $free_rooms[] = $room_ch;
      if (count($free_rooms) == $roomQty) {
        return $free_rooms;
      }
    } elseif ($room_status == "to_update") {
      $par_free_rooms[] = $room_ch;
    }

  }

	return array($free_rooms,$par_free_rooms);

}



/*
* This function creates a new booking (Data related to the )
*/
function createNewBooking($firstNight,$lastNight,$fr_room,$conn,$prefix) {

  $firstD = explode("-", $firstNight);
	$firstD_year = $firstD[0];
	$firstD_month = $firstD[1];
	$firstD_day = $firstD[2];

	$lastD = explode("-", $lastNight);
	$lastD_year = $lastD[0];
	$lastD_month = $lastD[1];
	$lastD_day = $lastD[2];

	$date1 = new DateTime($firstNight);
	$date2 = new DateTime($lastNight);

	$diff = $date2->diff($date1)->format("%a");

  $new_firstD_day = "";
  $new_firstD_month = "";
  $new_firstD_year = "";

  $mydaycolumns = "";
  $mydayvalues = "";

  $i=0;
  $k=$diff+1;
  while($i<$diff+1) {

    $ch_day = "d" . ($firstD_day + $i);
    $mydaycolumns .= ", " . $ch_day;
    $mydayvalues .= ", '0'";

    //Dates excide months days (e.g. 28/12-2/1)
    if ($firstD_day+$i == cal_days_in_month (CAL_GREGORIAN, $firstD_month, $firstD_year)) {
        $new_firstD_day =1;
        if ($firstD_month == 12) {
          $new_firstD_month = 1;
          $new_firstD_year = $firstD_year + 1;
        } else {
          $new_firstD_month = $firstD_month + 1;
          $new_firstD_year = $firstD_year;
        }
        //Continue booking on next month
        break;
    }

    $i++;
    $k--;

  }

  $sql = "INSERT INTO " . $prefix . "apb_availability (unit_id, year, month" . $mydaycolumns . ")";
  $sql .= "VALUES ('" . $fr_room . "', '" . $firstD_year . "', '" . $firstD_month . "'" .$mydayvalues . ")";

  if ($conn->query($sql) === TRUE) {
      echo "Booking Added!!!";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  if ($new_firstD_day == "1") {

    $mydaycolumns = "";
    $mydayvalues = "";

    $j=0;
    while($j<$k -1) {

      $ch_day = "d" . ($new_firstD_day + $j);
      $mydaycolumns .= ", " . $ch_day;
      $mydayvalues .= ", '0'";

      $j++;

    }

    $sql2 = "INSERT INTO " . $prefix . "apb_availability (unit_id, year, month" . $mydaycolumns . ")";
    $sql2 .= "VALUES ('" . $fr_room . "', '" . $new_firstD_year . "', '" . $new_firstD_month . "'" .$mydayvalues . ")";

    if ($conn->query($sql2) === TRUE) {
        echo "Booking Added!!!";
    } else {
        echo "Error: " . $sql2 . "<br>" . $conn->error;
    }

  }

}


/*
*
*/
function updateBooking($firstNight,$lastNight,$b_room,$conn,$prefix) {

  $firstD = explode("-", $firstNight);
  $firstD_year = $firstD[0];
  $firstD_month = $firstD[1];
  $firstD_day = $firstD[2];

  $lastD = explode("-", $lastNight);
  $lastD_year = $lastD[0];
  $lastD_month = $lastD[1];
  $lastD_day = $lastD[2];

  $date1 = new DateTime($firstNight);
  $date2 = new DateTime($lastNight);

  $diff = $date2->diff($date1)->format("%a");

  $new_firstD_day = "";
  $new_firstD_month = "";
  $new_firstD_year = "";

  $mydaycolumns = "";

  $i=0;
  $k=$diff+1;
  while($i<$diff+1) {

    $ch_day = "d" . ($firstD_day + $i);
    $mydaycolumns .= $ch_day . "='0'";

    if ($i < $diff) {
      $mydaycolumns .= ",";
    }

    //Dates excide months days (e.g. 28/12-2/1)
    if ($firstD_day+$i == cal_days_in_month (CAL_GREGORIAN, $firstD_month, $firstD_year)) {
        $new_firstD_day =1;
        if ($firstD_month == 12) {
          $new_firstD_month = 1;
          $new_firstD_year = $firstD_year + 1;
        } else {
          $new_firstD_month = $firstD_month + 1;
          $new_firstD_year = $firstD_year;
        }
        //Continue booking on next month
        break;
    }
    $i++;
    $k--;

  }

  $sql = "UPDATE " . $prefix . "apb_availability SET " . $mydaycolumns . " WHERE unit_id =" . $b_room . " AND year=" . $firstD_year . " AND month=" . $firstD_month . " ";

  if ($conn->query($sql) === TRUE) {
      echo "Record updated successfully";
  } else {
      echo "Error updating record: " . $conn->error;
  }

}


?>
