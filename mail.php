<?php
/*
	print ("###");
	$n="\n";
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Origin: *');
    */

	//$SET_EMAIL_HERE = "gmanias@hellobusiness.gr";
	$SET_EMAIL_HERE = "xrkatsenos@gmail.com";

    $_NGPOST=json_decode(file_get_contents('php://input'),true);
	$date=new DateTime();
	$currentTimeStamp=$date->getTimestamp(); $df=($currentTimeStamp-$_NGPOST['timestamp']);
    // previous method | DEPRECATED
    /*
    $subject  = 'Reception message by Villa Teresa mobile app';
    $img='<img src="http://villateresa.gr/images/logo.png">';
    $message  = '<div style="font-size: 11px; padding: 20px; display: table; color:#DDDDDD; background: #333333;">This message was sent from villateresa mobile app:\n\n';
    $message .= $n.$n;
	$message .= '<div style="padding: 20px; background: #EEEEEE; color: #222222;">';
	$message .= "Επώνυμο:".$_NGPOST['surname'].$n;
	$message .= "Ονομα:".$_NGPOST['name'].$n;
$message .= "Email:".$_NGPOST['emailadr'].$n;
	$message .= "Δωμάτιο:".$_NGPOST['room'].$n;
	$message .= "Μύνημα:".$_NGPOST['message'];
    //$_NGPOST['surname'].$_NGPOST['name'].$_NGPOST['message'];
    $message .= '</div">';
    //$to = "info@villateresa.gr"; //"info@villateresa.gr";
    $to = "delcoderweb@gmail.com"; //"info@villateresa.gr";
    mail($to, $subject, $message);
    */
	function check_email_address($email) {

			// First, we check that there's one @ symbol, and that the lengths are right
		if (!preg_match("/^[^@]{1,64}@[^@]{1,255}$/", $email)) {

		    // Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
		    return false;
		}
		// Split it into sections to make life easier
		$email_array = explode("@", $email);
		$local_array = explode(".", $email_array[0]);
		for ($i = 0; $i < sizeof($local_array); $i++) {

		    if (!preg_match("/^(([A-Za-z0-9!#$%&'*+\/=?^_`{|}~-][A-Za-z0-9!#$%&'*+\/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$/", $local_array[$i])) {

		        return false;
		    }
		}

		if (!preg_match("/^\[?[0-9\.]+\]?$/", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name

		    $domain_array = explode(".", $email_array[1]);
		    if (sizeof($domain_array) < 2) {

		        return false; // Not enough parts to domain
		    }
		    for ($i = 0; $i < sizeof($domain_array); $i++) {

		        if (!preg_match("/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/", $domain_array[$i])) {

		            return false;
		        }
		    }
		}
		return true;
	}
	$name = $_POST['name']; //strip_tags($_NGPOST['name']);
	$surname = $_POST['surname']; //strip_tags($_NGPOST['surname']);
  $emailadr = $_POST['emailadr']; //strip_tags($_NGPOST['emailadr']);
	$text = $_POST['message']; //strip_tags($_NGPOST['message']);
	$room = $_POST['room']; //strip_tags($_NGPOST['room']);
	if ( strlen($name)===0 )
	{
		echo 1;
		exit();
	}
	if ( strlen($text)===0 )
	{
		echo 1;
		exit();
	}
	if ( strlen($room)===0 )
	{
		echo 1;
		exit();
	}
	if ( strlen($surname)===0 )
	{
		echo 1;
		exit();
	}
if ( strlen($emailadr)===0 )
	{
		echo 1;
		exit();
	}
	if ( strlen($name) > 100 || strlen($text) > 1000 || strlen($surname) > 500 || strlen($emailadr) > 500 || strlen($room) > 500 )
	{
		echo 2;
		exit();
	}
	$emailSubject='Reception message by Villa Teresa mobile app';
	$subject  = 'Reception message from mobile app: ';
	//$emailTo="delcoderweb@gmail.com";
	$emailTo=$SET_EMAIL_HERE;
	//$img='<img src="http://villateresa.gr/images/logo.png">';
	$do="<div>";
	$dc="</div>";
	$br = "<br>";
	$message  = '<div style="font-size: 11px; padding: 20px; display: table; color:#DDDDDD; background: #333333;">This message was sent from villateresa mobile app</div>';
	$message .= $n.$n;
	$message .= '<div style="padding: 20px; background: #EEEEEE; color: #222222;">';
	$message .= $do."Επώνυμο:".$surname.$dc.$br;
	$message .= $do."Ονομα:".$name.$dc.$br;
$message .= $do."Email:".$emailadr.$dc.$br;
	$message .= $do."Δωμάτιο:".$room.$dc.$br;
	$message .= $do."Μύνημα:".$text.$dc.$br;
	$message .= '</div>';
	//$_NGPOST['surname'].$_NGPOST['name'].$_NGPOST['message'];
	//$text .= "Name: ".$name;
	//$text .= $text."\n";
	/*echo 'everything seems ok.';*/
	//$headers = 'From: '."\r\n".'Reply-To: '.$emailFrom."\r\n" .'X-Mailer: PHP/' . phpversion();
	//$headers = "From: " . strip_tags($_POST['req-email']) . "\r\n";
	//$headers .= "Reply-To: ". strip_tags($_POST['req-email']) . "\r\n";
	//$headers .= "CC: susan@example.com\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	sleep(2);
	mail( $emailTo, $emailSubject, $message, $headers) or die("Message send failed!");
?>
