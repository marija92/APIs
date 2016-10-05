<?php
$link = mysqli_connect ( "localhost", "root", "", "elearning" );
$link->set_charset ( "utf8" );
if (! $link)
	die ( "Error:" . mysql_connect_error () );
function parseUdacity($link, $course) {
	$courseUrl = $course ["homepage"];
	$id = $course ["key"];
	$name = mysqli_real_escape_string ( $link, $course ["title"] );
	//echo $name."<br />";
	$description = mysqli_real_escape_string ( $link, $course ["summary"] );
	$provider = "UDACITY";
	$institutions = $course ["affiliates"];
	$instID = "";
	
	foreach ( $institutions as $inst ) {
		$instID = "INST" . substr ( $inst ["name"], 0, 4 );
		insertUdacityInstitution ( $link, $instID, mysqli_real_escape_string ( $link, $inst ["name"] ) );
	}
	
	if (empty ( $instID )) {
		insertUdacityCourse ( $link, $id, $name, $description, $provider, $instID, true );
	}
	else{
	insertUdacityCourse ( $link, $id, $name, $description, $provider, $instID, false );
	}
}


function getUdacityCoruses($link) {
	$query1 = "INSERT INTO provder(id,name, description) VALUES('UDACITY','Udacity', 'Be in demand.Get a new job or advance your career with courses built by industry leaders like Google, Amazon and Facebook.') ";
	$row = mysqli_query ( $link, $query1 );
	
	$resp = file_get_contents ( "https://www.udacity.com/public-api/v0/courses" );
	$json_response = json_decode ( $resp, true );
	
	foreach ( $json_response ["courses"] as $course ) {
		parseUdacity ( $link, $course );
	}	
	
	
	 foreach ($json_response["degrees"] as $degree) {
	 	parseUdacity($link, $degree); 
	 }
	 
	 foreach ($json_response["tracks"] as $keyword){
	 	$courses=$keyword['courses'];
	 	$value=$keyword['name'];
	 	foreach ($courses as $course){
	 		insertKeyWord($link, $value,$course);
	 	}
	 }
	 	
}


function insertUdacityCourse($link, $id, $title, $desc, $provider, $inst, $flag) {
	$query = "SELECT * FROM course WHERE id LIKE '$id'";
	$result = mysqli_query ( $link, $query );
	
	if (mysqli_num_rows ( $result ) <= 0) {
		if (! $flag) {
			$query1 = "INSERT INTO course(id,title, description, provder,institution) VALUES('$id','$title','$desc','$provider','$inst') ";
			if (mysqli_query ( $link, $query1 )) {
				//echo 'Course success <br />';
			} else {
				echo "COURSE: " . mysqli_error ( $link ) . "<br />";
			}
		} else {
			$query1 = "INSERT INTO course(id,title, description, provder) VALUES('$id','$title','$desc','$provider') ";
			if (mysqli_query ( $link, $query1 )) {
				//echo 'Course success <br />';
			} else {
				echo "COURSE: " . mysqli_error ( $link ) . "<br />";
			}
		}
	}
}


function insertUdacityInstitution($link, $instID, $institution) {
	$query = "SELECT * FROM institution WHERE id LIKE '$instID'";
	$result = mysqli_query ( $link, $query );
	// $instID="INST".substr($institution, 0, 4);
	
	if (mysqli_num_rows ( $result ) <= 0) {
		$query1 = "INSERT INTO institution(id,name) VALUES('$instID','$institution') ";
		if (mysqli_query ( $link, $query1 )) {
			//echo 'Institution success <br />';
		} else {
			
			echo "INSTITUTION: " . mysqli_error ( $link ) . "<br />";
		}
	}
	
	return $instID;
}

function insertKeyWord($link, $value,$course){
	
	$query = "SELECT * FROM keyword WHERE value LIKE '$value' and course LIKE '$course'";
	$result = mysqli_query ( $link, $query );
	
	if (mysqli_num_rows ( $result ) <= 0) {
		$query1 = "INSERT INTO keyword(value,course) VALUES('$value','$course') ";
		if(mysqli_query($link, $query1)) {
				echo 'Keyword success';
		} else {
			 echo "KEYWORD: ". mysqli_error($link)."<br />";
		}
	}
}

getUdacityCoruses ( $link );

?>