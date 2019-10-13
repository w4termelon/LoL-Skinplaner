<?php
 include("dbinfo.php"); //Datenbankinformation
	$url = "https://extraction.import.io/query/extractor/eda158f1-9eca-4404-8080-4c8a7efcd38a?_apikey=0cf720d4011d44c5bc0a2c67434af6aebb3835e283842f4715e46963ba0c9447e2c9c4a86ef7dd5f0d564510243b04a1202ada65a27f7b2bc25ffc8a0bd95623e3a655515039bdf1098408cb2f0b2224&url=http%3A%2F%2Feuw.leagueoflegends.com%2Fen%2Fnews%2Fstore%2Fsales%2F";
	$news_contents = file_get_contents($url); //String des Codes der URL
	$news_contents = utf8_encode($news_contents); //Zeichensatz wird auf UTF8 gesetzt	
	$results = json_decode($news_contents); //JSON Datei wird als PHP Array/Object "gespeichert"
	$news_n = count($results->extractorData->data[0]->group)-1; //Anzahl aller News
	$search = "/Champion/i"; // Suchstring für die Sales 
	
	for ($i = 0; $i <= $news_n; $i++){
		$href = $results->extractorData->data[0]->group[$i]->{'Default link'}[0]->href ;
		$href_txt = $results->extractorData->data[0]->group[$i]->{'Default link'}[0]->text ;
		$n = 0;
	    preg_match_all("/\d+/",$href_txt,$time);
		$date_s = $time[0][0]."-".$time[0][1]."-".date('Y')."";
	    $date_e = $time[0][2]."-".$time[0][3]."-".date('Y')."";
		$today = date('d-M-Y');

		if (preg_match($search, $href_txt) && $today>=$date_s && $today<=$date_e) {
			$url="https://extraction.import.io/query/extractor/3f052e43-c202-47cd-ab9f-0dc6f62bdb80?_apikey=0cf720d4011d44c5bc0a2c67434af6aebb3835e283842f4715e46963ba0c9447e2c9c4a86ef7dd5f0d564510243b04a1202ada65a27f7b2bc25ffc8a0bd95623e3a655515039bdf1098408cb2f0b2224&url=".htmlspecialchars($href);
			$sale_contents = file_get_contents($url); //String des Codes der URL
			$sale_contents = utf8_encode($sale_contents); 	//Zeichensatz wird auf UTF8 gesetzt
			$result = json_decode($sale_contents); //JSON Datei wird als PHP Array/Object "gespeichert"
			$skins = ''; //array für die Skinnamen (im Angebot)
			do{
				$sales_n = count($result->extractorData->data[0]->group); //Anzahl aller Sonderangebote
				$skin = $result->extractorData->data[0]->group[$n]->{'content'}[0]->text;
				$fullPrice = $result->extractorData->data[0]->group[$n]->{'fullPrice'}[0]->text;
				$reducedPrice = $result->extractorData->data[0]->group[$n]->{'reducedPrice'}[0]->text;
				$skinimage = $result->extractorData->data[0]->group[$n]->{'content_image'}[0]->src;

				$skins .='"'.$skin.'", '; //Skinnamen werden in das Array eingefügt
				$n++;
			} while ($n < $sales_n );
		}
	}
	$skins = substr($skins, 0, -2);  //entfernt das letzt Kommata
	print_r($skins);
	echo "<br />";
	if(isset($skins)){
		$sql = 'SELECT ID FROM Login WHERE receivemail ="1" ';
		$ID = array();
		foreach($conn->query($sql) as $D){
		$ID[] = $D[0];
		}
		print_r($ID);
		echo "<br />";
		$cross = array();
		foreach($ID as $id){
		$statement = $conn->prepare("SELECT u_id FROM Wishlist WHERE Skinname IN (".$skins.") AND mailstatus='0' AND ID = :ID");
		$statement->execute(array('ID' =>$id ));
		$cross[$id] = $statement->fetchAll();
		}
		$cross = array_filter($cross);
		print_r($cross);
		$ID_n = count($ID);
		for ($l = 0; $l <= $ID_n; $l++){
			$statement = $conn->prepare("SELECT Email FROM Login WHERE ID = :ID");
			$statement->execute(array('ID' =>$ID[$l]));
			$email = $statement->fetch();
			echo $email[0];
			$x=0;
			if (isset($cross[$ID[$l]])){
				$to =  $email[0]; //Mailadresse
				$from   = "no-reply@pcscout24.w4f.eu";
				$subject   = "Skins aus ihrer Wunschliste sind im Angebot!";
				$mailtext = "Guten Tag,<br>";
				$mailtext .= "<br> ein oder mehrere Skins aus ihrer Wunschliste sind aktuell im Angebot.<br><br>";
				$reply  = "info@pcscout24.w4f.eu";
				$headers   = array();
				$headers[] = "MIME-Version: 1.0";
				$headers[] = "Content-type: text/html; charset=iso-8859-1";
				$headers[] = "From: $from\nReply-To: $reply";
				$headers[] = "X-Mailer: PHP/".phpversion();
				$headers[] = "Subject: {$subject}";
				do {
					$cross_n = count($cross[$ID[$l]]);
					$mailtext .= "Der Skin ".$cross[$ID[$l]][$x][0]." ist aktuell um 50% reduziert.<br>";
					$x++;
				} while ($x < $cross_n);
				$mailtext .= "<br> Mit freundlichen Gr&uuml;&szlig;en <br /> Das SkinPlaner Team :)";
				//Email Versand an den Benutzer
				mail($to,$subject,$mailtext,implode("\r\n", $headers));
			} 
		}
	} else {
		echo"Bitte <a href='pcscout24.w4f.eu/tee/'>pcscout24.w4f.eu/tee/</a> laden";
	}
?>