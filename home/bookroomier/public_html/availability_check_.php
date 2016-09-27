<?php

include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
//print_r($data);

$result;
$responce = "";
$resp = "false";
$respdata = array();

$action = $data["action"];

$propertyID = $data["propertyID"];


if ($action == "check_available"){

		$arrival_date = $data["arrival_date"];
		$departure_date = $data["departure_date"];
		$date1 = new DateTime($arrival_date);
		$date2 = new DateTime($departure_date);
		$nights = $date2->diff($date1)->format("%a");

		$rooms=$data["rooms"];

		$max_adults = 0;
		$min_adults = 6;
		$max_children = 0;
		$min_children = 6;

		foreach($rooms as $item)
		{
		    if($item["adults"] > $max_adults)
					$max_adults = $item["adults"];

				if($item["adults"] < $min_adults)
					$min_adults = $item["adults"];

				if($item["children"] > $max_children)
					$max_children = $item["children"];

				if($item["children"] < $min_children)
					$min_children = $item["children"];
		}

		$rooms_num = count($data["rooms"]);

		// echo "MAX_Adults:$max_adults\n";
		// echo "MIN_Adults:$min_adults\n";
		// echo "MAX_Children:$max_children\n";
		// echo "MIN_Children:$min_children\n";
		// echo "ROOM_Number:$rooms_num\n";

		//Select roomtypes that matches criteria
		//Returns the roomtypesIDs of matching roomtypes
		$roomtypes_suitable = get_roomtypes($propertyID, $nights, $min_adults, $min_children);

		// echo "Suitable roomtypes\n";
		// print_r($roomtypes_suitable);

		$roomstypeID_avail = array();
		$roomtypeCount = array();

		foreach($roomtypes_suitable as $roomtype_suitable) {

			//Check if selected room types are available from the onDuty option
			//Returns the roomsIDs that are included in the matching roomtypes
			$roomsIdentify_onDuty = array();
			$roomsIdentify_onDuty = get_roomsID_onDuty($roomtype_suitable,$arrival_date,$departure_date);
			array_push($roomtypeCount,count($roomsIdentify_onDuty));

			if (count($roomsIdentify_onDuty) == 0) {
				continue;
			}

			//Check if selected room types are available from the booking option
			//Cleanup previous lists from rooms that are allready booked
			$roomsIdentify_free = array();
			$roomsIdentify_free = get_roomsID_free($roomsIdentify_onDuty, $propertyID, $arrival_date,$departure_date);
			if (count($roomsIdentify_free) == 0) {
				continue;
			} else {
				array_push($roomstypeID_avail,$roomtype_suitable);
			}

		}

		// echo "Available roomtypes\n";
		// print_r($roomstypeID_avail);

		$roomtypes_details = array();

		//The array $roomstypeID_avail includes all roomtypesID that are suitable and free.

		//Get details for each roomtype
		$i=0;
		foreach($roomstypeID_avail as $roomtypeID) {

			$quantity = $roomtypeCount[$i];
			$i++;
			$roomtype_data = get_roomtype_details($roomtypeID, $quantity, $arrival_date,$departure_date,$propertyID);

			//Create responce
			array_push($roomtypes_details,$roomtype_data);

		}

		$responce = "Available rooms";
		$resp = "true";
		$respdata = $roomtypes_details;
		//print_r($roomtypes_details);
		//exit;


}

//Return result
$result = array('responce' => $responce, 'resp' => $resp, 'data' => $respdata);
echo json_encode($result);


////////////////////////////////////////////////////////////////////////////
////////////////////      FUNCTIONS       //////////////////////////////////
////////////////////////////////////////////////////////////////////////////

function get_roomsID_onDuty($roomtype_suitable,$arrival_date,$departure_date) {

	$output = array();
	$rooms_onDuty = array();


	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT roomID, room_identify, available, startDate, endDate FROM rooms WHERE roomtypeID=?");
	$stmt->bind_param("i", $roomtype_suitable);


	if ($stmt->execute()) {

		$stmt->bind_result($roomID_result,$room_identify_result,
		$available_result,$startDate_result, $endDate_result);

		$i =0;
		while ($stmt->fetch()) {

											if ($available_result == 1) {
												array_push($rooms_onDuty,$room_identify_result);
											}	else {
												// echo "START" . $startDate_result . "\n";
												// echo "END" . $endDate_result . "\n";
												// echo "ARRIVAL" . $arrival_date . "\n";
												// echo "DEPARTURE" . $departure_date . "\n";

												if($arrival_date >= $startDate_result && $arrival_date <= $endDate_result) {
												        //echo "arrival between\n";
																continue;
												} else {
																//echo "out\n";
																if($departure_date >= $startDate_result && $departure_date <= $endDate_result) {
																        //echo "departure between\n";
																				continue;
																} else {
																	//echo "out\n";
																	array_push($rooms_onDuty,$room_identify_result);
																}
												}
											}
		}

	} else {
		$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	//print_r($rooms_onDuty);
	$conn->close();
	return $rooms_onDuty;
}


function get_roomsID_free($roomsIdentify_onDuty,$propertyID, $arrival_date,$departure_date) { //$firstNight, $lastNight, $roomQty, $category_rooms, $conn, $prefix

	$rooms_in_booking = array();
	$free_rooms = array();
	$rooms_to_check = array();

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$sql = "SELECT * FROM availability WHERE propertyID='" . $propertyID . "'"; //to add property id
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {

			while($row = $result->fetch_assoc()) {

				if (!in_array($row["roomID"], $rooms_in_booking)) {
					$rooms_in_booking[] = $row["roomID"];
				}

				$calendar[] = $row;

			}

	}


	foreach ($roomsIdentify_onDuty as &$room) {

		if (!in_array($room, $rooms_in_booking)) {

			$free_rooms[] = $room;

			// if (count($free_rooms) == $roomQty) {
			//   return $free_rooms;
			// }

		} else {

			$rooms_to_check[] = $room;

		}

	}

	$firstD = explode("-", $arrival_date);
	$firstD_year = $firstD[0];
	$firstD_month = $firstD[1];
	$firstD_day = $firstD[2];

	$lastD = explode("-", $departure_date);
	$lastD_year = $lastD[0];
	$lastD_month = $lastD[1];
	$lastD_day = $lastD[2];

	$date1 = new DateTime($arrival_date);
	$date2 = new DateTime($departure_date);

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
			if($calendar[$j]["roomID"] == $room_ch && $calendar[$j]["year"] == $firstD_year && $calendar[$j]["month"] == $firstD_month){

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

				if($calendar[$m]["roomID"] == $room_ch && $calendar[$m]["year"] == $new_firstD_year && $calendar[$m]["month"] == $new_firstD_month){
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

				if($calendar[$p]["roomID"] == $room_ch && $calendar[$p]["year"] == $lastD_year && $calendar[$p]["month"] == $lastD_month){

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

	return array($free_rooms);

}


function get_roomtype_details($roomtypeID,$quantity,$arrival_date,$departure_date,$propertyID) {

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT roomtype_name,
	roomtype_descr,
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
		$roomtype_name_result,
		$roomtype_descr_result,
		$price_result,
		$currency_result,
		$capacity_min_result,
		$capacity_max_result,
		$child_min_result,
		$child_max_result,
		$minimum_stay_result);


		while ($stmt->fetch()) {
			$roomtype_name = $roomtype_name_result;
			$roomtype_descr = $roomtype_descr_result;
			$price = $price_result;
			$currency = $currency_result;
			$capacity_min = $capacity_min_result;
			$capacity_max = $capacity_max_result;
			$child_min = $child_min_result;
			$child_max = $child_max_result;
			$minimum_stay = $minimum_stay_result;
		}

		$services = show_services($roomtypeID);
		$prices = get_prices($roomtypeID,$price,$arrival_date,$departure_date);
		$totalcost = calculate_total_cost($prices,$arrival_date);
		$images = get_images($roomtypeID);
		$availability = get_roomtype_availability($roomtypeID,$arrival_date,$departure_date,$propertyID);

		$responce = "Room type data.";
		$resp = "true";
		$respdata = array('roomtypeID' => $roomtypeID
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
		, 'prices' => $prices
		, 'total_cost' => $totalcost
		, 'availability' => $availability
		, 'images' => $images);

	} else {
		$responce = "Error: (" . $stmt->errno . ") " . $stmt->error;
		$resp = "false";
	}

	$conn->close();

	return $respdata;

}


function get_roomtype_availability($roomtypeID,$arrival_date,$departure_date,$propertyID) {

	$start_date = date('Y-m-d', strtotime($arrival_date. ' - 5 days'));
	$end_date = date('Y-m-d', strtotime($departure_date. ' + 5 days'));

	//echo  $start_date;
	//echo  $end_date;

	$date1 = new DateTime($start_date);
	$date2 = new DateTime($end_date);
	$diff = $date2->diff($date1)->format("%a");

	//echo $diff + 1;

	$x = 0;

	$check_date = date('Y-m-d', strtotime($start_date. ' - 1 days'));
	while($x <= $diff ) {


			$check_date = date('Y-m-d', strtotime($check_date. ' + 1 days'));
			//echo "$check_date\n";
			$avail_status = check_date_availability ($check_date,$roomtypeID,$propertyID);
			$availability[]=array($check_date=>$avail_status);

			$x++;
	}

	return $availability;

}


function check_date_availability ($check_date,$roomtypeID,$propertyID) {

	$checkD = explode("-", $check_date);
	$checkD_year = $checkD[0];
	$checkD_month = $checkD[1];
	$checkD_day = $checkD[2];

	if ($checkD_month != 10) {
		$checkD_month = str_replace("0","",$checkD_month);
	}

	if ($checkD_day == "01" || $checkD_day == "02" ||$checkD_day == "03" || $checkD_day == "04" || $checkD_day == "05" || $checkD_day == "06" || $checkD_day == "07" || $checkD_day == "08" || $checkD_day == "09") {
		$checkD_day = str_replace("0","",$checkD_day);
	}

	$rooms_identify = array();

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT roomID, room_identify FROM rooms WHERE roomtypeID=?");
	$stmt->bind_param("i", $roomtypeID);


	if ($stmt->execute()) {

		$stmt->bind_result($roomID_result,$room_identify_result);

		while ($stmt->fetch()) {
				array_push($rooms_identify,$room_identify_result);
		}

	}

	$num_room_ident = count($rooms_identify);
	$i = 0;

	if ($num_room_ident == 0) {
		$conn->close();
		return 1;
	}
	$room_status = "free";

	foreach ($rooms_identify as $room_ident) {
		$i++;

		$sql = "SELECT * FROM availability WHERE roomID='" . $room_ident . "' AND propertyID='" . $propertyID . "' AND year='" . $checkD_year . "' AND month='" . $checkD_month . "'";
		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					unset($calendar);
					$calendar[] = $row;
				}

				$ch_day = "d" . $checkD_day;

				$DAY_status = $calendar[0][$ch_day];
				if ($DAY_status == "0") {
					$conn->close();
					return 0;
				} else {

					if($i == $num_room_ident) {
						$conn->close();
		    		return 1;
					}
		  	}
		} else {
			$conn->close();
			return 0;
		}

	}

	$conn->close();

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
	$directory = "./uploads/files/roomtypes/" . $roomtypeID;

	$scanned_directory = @array_diff(@scandir($directory), array('..', '.','thumbnail'));

	return @$scanned_directory;

}


function get_roomtypes($propertyID, $nights, $adults, $children) {

	// Create connection
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	//$stmt = $conn->prepare("SELECT roomtypeID FROM room_types WHERE capacity_min <=? AND capacity_max >=? AND child_min <=? AND child_max >=? AND minimum_stay <=? AND propertyID = ?");
	$stmt = $conn->prepare("SELECT roomtypeID FROM room_types WHERE capacity_max >=? AND child_max >=? AND minimum_stay <=? AND propertyID = ?");
	$stmt->bind_param("iiii", $adults, $children, $nights, $propertyID);

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


/*
* Functions from awebooking solution
*/
function CheckFreeRooms($firstNight, $lastNight, $roomQty, $category_rooms, $conn, $prefix) {

  $rooms_in_booking = array();
  $free_rooms = array();
  $rooms_to_check = array();

  $sql = "SELECT * FROM " . $prefix . "apb_availability";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {

      while($row = $result->fetch_assoc()) {

        if (!in_array($row["roomID"], $rooms_in_booking)) {
          $rooms_in_booking[] = $row["roomID"];
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
      if($calendar[$j]["roomID"] == $room_ch && $calendar[$j]["year"] == $firstD_year && $calendar[$j]["month"] == $firstD_month){

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

        if($calendar[$m]["roomID"] == $room_ch && $calendar[$m]["year"] == $new_firstD_year && $calendar[$m]["month"] == $new_firstD_month){
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

        if($calendar[$p]["roomID"] == $room_ch && $calendar[$p]["year"] == $lastD_year && $calendar[$p]["month"] == $lastD_month){

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

  $sql = "INSERT INTO " . $prefix . "apb_availability (roomID, year, month" . $mydaycolumns . ")";
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

    $sql2 = "INSERT INTO " . $prefix . "apb_availability (roomID, year, month" . $mydaycolumns . ")";
    $sql2 .= "VALUES ('" . $fr_room . "', '" . $new_firstD_year . "', '" . $new_firstD_month . "'" .$mydayvalues . ")";

    if ($conn->query($sql2) === TRUE) {
        echo "Booking Added!!!";
    } else {
        echo "Error: " . $sql2 . "<br>" . $conn->error;
    }

  }

}


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

  $sql = "UPDATE " . $prefix . "apb_availability SET " . $mydaycolumns . " WHERE roomID =" . $b_room . " AND year=" . $firstD_year . " AND month=" . $firstD_month . " ";

  if ($conn->query($sql) === TRUE) {
      echo "Record updated successfully";
  } else {
      echo "Error updating record: " . $conn->error;
  }

}


?>
