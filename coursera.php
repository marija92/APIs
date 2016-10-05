<?php
$link = mysqli_connect ( "localhost", "root", "", "elearning" );
$link->set_charset ( "utf8" );
if (! $link)
	die ( "Error:" . mysql_connect_error () );

$query1 = "INSERT INTO provder(id,name, description) VALUES('COURSERA','Coursera','Take the worlds best courses online.') ";
$row = mysqli_query ( $link, $query1 );

insertInstitutions ( $link );

for($i = 0, $j = 100; $i <= 2000; $i += 100, $j += 100) {
	$resp = file_get_contents ( "https://api.coursera.org/api/courses.v1?start=" . $i . "&limit=" . $j . "&fields=primaryLanguages,description,partnerIds,domainTypes" );
	$json_response = json_decode ( $resp, true );
	
	foreach ( $json_response ["elements"] as $course ) {
		$id = $course ["id"];
		$name = mysqli_real_escape_string($link, $course ["name"]);
		$desc = mysqli_real_escape_string($link, $course ["description"]);
		$provider = "COURSERA";
		$institution = $course ["partnerIds"];
		$categories = $course ["domainTypes"];
		
		$language = $course ["primaryLanguages"];
		
	if ($language [0] == 'en' || $language [0] == 'de' || $language [0] == 'es' || $language [0] == 'fr') {
			
				
			insertCourseraCourse ( $link, $id, $name, $desc, $provider, $institution [0] );
			insertCourseraKeyword ( $link, $categories, $id );
			//echo $name . "<br />";
		}
	}
}



function insertCourseraCourse($link, $id, $name, $desc, $provider, $institution) {
	$query = "SELECT * FROM course WHERE id LIKE '$id'";
	$result = mysqli_query ( $link, $query );

	if (mysqli_num_rows ( $result ) <= 0) {
		//echo "vleze";
		$query1 = "INSERT INTO course (id, title, description, provder,institution) VALUES('{$id}','{$name}', '{$desc}', '${provider}', '${institution}') ";
		if(mysqli_query($link, $query1)) {
		//	echo 'Course success';
		} else {
			echo "COURSE: ". mysqli_error($link). "<br />";
		}
	}
}

function insertInstitutions($link) {
	for($i = 0, $j = 100; $i <= 300; $i += 100, $j += 100) {
		$resp = file_get_contents ( "https://api.coursera.org/api/partners.v1?start=" . $i . "&limit=" . $j."&fields=description,location" );
		$json_response = json_decode ( $resp, true );
		
		foreach ( $json_response ["elements"] as $partner ) {
			$id = $partner ["id"];
			$name =mysqli_real_escape_string($link, $partner ["name"]);
			$desc = mysqli_real_escape_string($link,$partner ["description"]);
			if (preg_match('/[\'^â€™$%&*()}{@#~?><>,|=_+¬-]/', $desc))
			{
				$desc="";
			}
			$location = $partner ["location"];
			$locationInsert = $location['country'];
			
			//echo "Institution: " . $name;
			
			$query = "SELECT * FROM institution WHERE id LIKE '$id'";
			$result = mysqli_query ( $link, $query );
			
			if (mysqli_num_rows ( $result ) <= 0) {
				$query1 = "INSERT INTO institution(id, name,country, description) VALUES('$id','$name','$locationInsert','$desc')";
				if(mysqli_query($link, $query1)) {
					//echo 'Institution success';
				} else {
					echo "INSTITUTION: ". mysqli_error($link). "<br />";
				}
			}
		}
	}
}

function insertCourseraKeyword($link, $arrTypes, $id) {
	foreach ( $arrTypes as $type ) {
		
		$subdom = $type ["subdomainId"];
		$dom = $type ["domainId"];
		
		// echo $subdom.", ".$dom."<br />";
		
	//	echo "sub:" . $subdom . "<br />";
		$query = "SELECT * FROM keyword WHERE value LIKE '$subdom' and course LIKE '$id'";
		$result = mysqli_query ( $link, $query );
		
		if (mysqli_num_rows ( $result ) <= 0) {
			$query1 = "INSERT INTO keyword(value,course) VALUES('$subdom','$id') ";
			if(mysqli_query($link, $query1)) {
			//	echo 'Keyword sub success';
			} else {
			//	echo "Keyword sub: ". mysqli_error($link)."<br />";
			}
		}
		
		//echo "dom:" . $dom . "<br />";
		
		$query = "SELECT * FROM keyword WHERE value LIKE '$dom' and course LIKE '$id'";
		$result = mysqli_query ( $link, $query );
		
		if (mysqli_num_rows ( $result ) <= 0) {
			$query1 = "INSERT INTO keyword(value,course) VALUES('$dom','$id') ";
			if(mysqli_query($link, $query1)) {
				//echo 'Keyword dom success';
			} else {
				//echo "Keyword dom: ". mysqli_error($link). "<br />";
			}
		}
	}
}

?>
