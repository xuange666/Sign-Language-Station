<?php
$token = $_GET["token"];
$email = $_COOKIE["email"];

session_start();
$newEmail = $_SESSION["newEmail"];

if($token == $_SESSION["token"]){
	$db = mysqli_connect("sdmysql.comp.polyu.edu.hk","18012633x","sqgqcbvd");
	mysqli_select_db($db,"18012633x");
	
	$sql = $db->prepare("UPDATE member SET email=? WHERE email=?");
	$sql->bind_param("ss",$newEmail,$email);
	$sql->execute();
	
	mysqli_close($db);
	setcookie("email", $newEmail, time() + 3600);
	session_destroy();
	echo "<script>alert('Email has been successfully rebinded!');
			window.location.href='../profile.php';</script>";
}else{
	echo "<script>alert('授权失败!')</script>";
	session_destroy();
}
?>