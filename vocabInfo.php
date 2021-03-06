<?php
	if(!isset($_COOKIE["email"])){
		header("Location: login.php");
		exit;
	}
	
	$content = $_GET["content"];
	if($content == ""){
		header("Location: index.php");
		exit;
	}
	
	if(isset($_GET["hasVideo"])){
		$hasVideo = true;
	}else{
		$hasVideo = false;
	}
	if(isset($_GET["onlyApproved"])){
		$onlyApproved = true;
	}else{
		$onlyApproved = false;
	}
	
	require_once 'connect_db.php';
	mysqli_select_db($db,$dbName);
	
	$sql0 = $db->prepare("SELECT * FROM permission WHERE title=?");
	$sql0->bind_param("s",$_COOKIE["title"]);
	$sql0->execute();
	$result0 = $sql0->get_result();
	$row0 = mysqli_fetch_array($result0, MYSQLI_ASSOC);
	
	if($row0["readVocab"]==0){
		mysqli_close($db);
		echo "<script>alert('Your trial period has expired, please subscribe a plan to continue visit this website.');
		window.location.href='index.php';</script>";
	}
	
	$sql_string = "SELECT * FROM vocabulary WHERE vocabName=?";
	
	if($hasVideo){
		$sql_string = $sql_string . " AND videoSource!=''";
	}
	if($onlyApproved){
		$sql_string = $sql_string . " AND status='approved'";
	}
	
	$sql = $db->prepare($sql_string);
	$sql->bind_param("s",$content);
	$sql->execute();	
	$result = $sql->get_result();
	$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	if($row!=false){
		$vocabId = $row['vocabId'];
    	$submitter = $row['submitter'];
		$approver = $row['approver'];
		$vocabName = $row['vocabName'];
		$description = $row['description'];
		$videoSource = $row['videoSource'];
		$checkTotal = $row['checkTotal'];
		
		$sql2 = "UPDATE vocabulary SET checkTotal=checkTotal+1 WHERE vocabId='" . $vocabId . "'";
		mysqli_query($db,$sql2) or die("SQL error!<br>");
		$sql3 = "INSERT INTO checkinghistory(email,vocabId,vocabName,checkTime) VALUES('" . $_COOKIE['email'] . "','" . 
				$vocabId . "','" . $vocabName . "','" . date('Y-m-d') . "')";
		mysqli_query($db,$sql3) or die("SQL error!<br>");	
		$sql4 = "SELECT * FROM addingtoglossaryhistory WHERE vocabId='" . $vocabId . "' AND email='" . $_COOKIE['email'] . "'";
		$result2 = mysqli_query($db,$sql4) or die("SQL error!<br>");
		$row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC);
		if($row2==false){
			$alreadyAddToGlossary = false;
		}else{
			$alreadyAddToGlossary = true;
		}
	}
	else{
		header("Location: noResult.php");
	}
	mysqli_close($db);
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Sign Language Station</title>
<link rel="stylesheet" type="text/css" href="css/general.css"/>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
$(document).ready(function(){
	$("input").on("input",function(){
		$("datalist").empty();
		$.post("scripts/searchVocabWithInitials.php",
        	{content:$("input").val()},
        	function(data,status){
        		$("datalist").append("<option value=" + data + ">");
        	});
	});
})
function DropDown(){
	$(".dropDownContent").slideToggle("fast");
};
function add(){
	$.post("scripts/addToGlossary.php",
        {
			vocabId:<?php echo $vocabId?>,
			vocabName: "<?php echo $vocabName?>"
		},
        function(data,status){
			$("#addToGlossary").text("Added");
		});
}
</script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
		
		h1{
			font-size: 40px;
			font-family: "Lucida Sans Unicode", "Lucida Grande", sans-serif;
		}
		p{
			color: darkblue;
			font-family: "Lucida Sans Unicode", "Lucida Grande", sans-serif;
			font-size:16px;
		}
		#addToGlossary{
			width:600px;
			margin-left: auto;
			margin-right: auto;
			font-size: 16px;
		}
		
		.video{
			width:600px;
			margin-left: auto;
			margin-right: auto;
		}
	</style>
</head>

<body>
	<?php require('header.php');?>

	<div class="mainFrame">
		
		
		
    	<h1 align="center"><?php echo $vocabName;?></h1>
    	<p align="center">Description:<?php echo $description;?></p>
    	
    	<div id="addToGlossary">
    		<img src = "img/click.png">
    			<span>
    				<?php 
			if($alreadyAddToGlossary){
				echo "Added";
			}else{
				echo "<a href='javascript:add()'>Add to my vocabulary list!</a>";
			}
		?>
    			</span>
        
        </div>
    	<div class = "video">
    		<video width="600" height="600"controls>
    			
 		 <source src="<?php echo $videoSource;?>" type="video/mp4">
		</video><br><br>
			
		</div>
    	
    	<table class="defaultTable">
			<tr>
				<td>Uploader:</td>
          		<td width="2000"><?php echo $submitter;?></td>
			</tr>

			<tr>
				<td>auditor:</td>
       			<td><?php echo $approver;?></td>
			</tr>

			<tr>
				<td>checkTotal:</td>
       			<td><?php echo $checkTotal;?></td>
			</tr>
		</table>
            
        
        
        
	</div>
	
	<?php require('footer.php');?>

</body>
</html>