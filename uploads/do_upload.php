<?php
header('Content-type: application/json');

if($_GET['action'] == 'background') {
	//echo $_POST['propertyID'];
	move_uploaded_file($_FILES['img']['tmp_name'], "files/backgrounds/background_".$_POST['propertyID'].".png");
	echo json_encode(array('code' => 0, 'url' => "uploads/files/backgrounds/background_".$_POST['propertyID'].".png"));
}

if($_GET['action'] == 'logo') {
	move_uploaded_file($_FILES['img']['tmp_name'], "files/logos/logo_".$_GET['property'].".png");
	echo json_encode(array('code' => 0, 'url' => "uploads/files/logos/logo_".$_GET['property'].".png"));
}

