<?php
session_start();
header('Cache-control: private'); // IE 6 FIX

if(isSet($_GET['lang'])) {
	$lang = $_GET['lang'];

	// register the session and set the cookie
	$_SESSION['lang'] = $lang;

	setcookie('lang', $lang, time() + (3600 * 24 * 30));
} else if(isSet($_SESSION['lang'])) {
	$lang = $_SESSION['lang'];
} else if(isSet($_COOKIE['lang'])) {
	$lang = $_COOKIE['lang'];
} else {
	$lang = 'en';
}

switch ($lang) {
  case 'en':
	$lang_file = 'lang.en.php';
	break;

  case 'de':
	$lang_file = 'lang.de.php';
	break;

  case 'es':
	  $lang_file = 'lang.es.php';
	  break;

 case 'el':
	  $lang_file = 'lang.el.php';
	  break;

  default:
		$lang_file = 'lang.en.php';

}

include_once 'languages/'.$lang_file;
@include_once '../../config.php';



//////////////////////////////////////////////////////////////////////////////
///////////////////  FUNCTIONS  //////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////


function getLangs($propertyID) {

  $langs= "error";

  // Create connection
  $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $stmt = $conn->prepare("SELECT langs FROM properties WHERE propertyID=?");
  $stmt->bind_param("i", $propertyID);

  if ($stmt->execute()) {

    $stmt->bind_result($langs_result);

    while ($stmt->fetch()) {
        $langs = $langs_result;
    }
  }

  $conn->close();

  return $langs;

}






?>
