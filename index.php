 <!Doctype html>
<?php session_start();
 include("dbinfo.php"); //Datenbankinformation
 include_once("password.php"); //hierdurch kann man password_hash() in PHP 5.4 nutzen
 include_once ("r_string.php"); ?>
<html>
<head>
	<title>SkinPlaner</title>
	<script type="text/javascript" src="details-shim.js"></script>
	<link rel="stylesheet" href="details-shim.css" />
	<link rel="stylesheet" href="style.css"/>
</head>
<body>
<main class="width100">
<?php if (isset($_SESSION['Email'])) {
	echo'<section class="width20">
	<details>
	<summary class="h1">Profil</summary>
	<p>Testsdsadsal</p>
	</details>
</section>';}
?>
	<section class="width70" >
	<details open="open">
	<summary class="h1">Weekly-Sales</summary>
	<?php 												//Ubersicht aktuelle Sales
	$csv = fopen("/home/pi/mert/text.csv","r");
	while(!feof($csv))
  	 {
 	 $tearr = fgetcsv($csv);
	 $skin = $tearr[0];
	 $fullPrice = $tearr[1];
	 $reducedPrice = $tearr[2];
	 $skinimage = $tearr[3];
	 if (isset($skin)) {
	  echo "<div class='skinblock' >
	  <h3 name='skinname'>".$skin."</h3>
	  <img alt='".$skin."' src='".$skinimage."' />
	  <p>Full Price: ".$fullPrice." RP <br> Reduced Prize: ".$reducedPrice."</p>
	  </div>";
	  }
	 }
        fclose($csv);
	?>
	</details>
	</section>
	<section class="width30" >
		<h1>Wunschliste</h1>
	<?php												//Registrieren oder Wunschliste
		if (!isset($_SESSION['Email'])) {
			echo "Sie m&uuml;ssen sich <details><summary><strong>Registrieren</strong></summary> 
				<form  action='registrieren.php' method='post'>
					E-Mail:<br>
					<input class='formular_log' type='email' size='40' maxlength='250' name='email' required >
					<br>
					<br>
					Dein Passwort:<br>
					<input class='formular_log' type='password' size='40'  maxlength='250' name='passwort' required>
					<br>
					Passwort wiederholen:<br>
					<input class='formular_log' type='password' size='40' maxlength='250' name='passwort2' required>
					<br>
					<div style='text-align:center;'>
						<input class='loginb width90' type='submit' value='Registrieren'>
					</div>
					<br>
				</form></details>
				und anschlie&szlig;end zum<details><summary><strong>Login</strong></summary>
				<form action='?login=1' method='post'>
					E-Mail:<br>
					<input class='formular_log' type='email' size='40' maxlength='250' name='email'><br>
					<br>
					Dein Passwort:<br>
					<input class='formular_log' type='password' size='40'  maxlength='250' name='passwort'><br>
					<label><input type='checkbox' name='angemeldet_bleiben' value='1'> Angemeldet bleiben</label><br>
					<div style='text-align:center;'>
						<input class='loginb width90' type='submit' value='Login'>
					</div>
					<br>
				</form></details> damit Sie die Wunschliste nutzen k&ouml;nnen.";
		} else {
		$statement = $conn->prepare("SELECT Championname FROM Champion");
		$result = $statement->execute();
		$champions = $statement->fetch();
		echo"<form action='?skin=1' method='post' > <select id='championlist' onchange='championName(options[this.selectedIndex].text)'>";
		foreach($champions as $champion){
			echo"<option>".$champion."</option>";
		}
		echo"</select>
		<select id='skinlist' name='skinlist' >
			  <option></option>
		</select>";
		$statement = $conn->prepare("SELECT * FROM Login WHERE Email = :Email");
		$result = $statement->execute(array('Email' => $_SESSION['Email']));
		$ID = $statement->fetch();
		$sql ="SELECT Skinname FROM Wishlist WHERE ID = $ID[0]";
		$_SESSION['wishlist'] = array();  //array für die Skinnamen (in der Wunschliste)
		echo "<button value='skin'>Hinzuf&uuml;gen</button></form><ul>";
		foreach($conn->query($sql) as $skins){
			echo"<li id='".$skins[0]."'>".$skins[0]."</li>";
			$_SESSION['wishlist'][] = $skins[0]; //Skinnamen werden in das Array $_SESSION['wishlist'] eingefügt
		}
		echo "</ul> ";
		}
		if(isset($_GET['skin'])) {
		$skin_a = $_POST['skinlist'];
		$sql ="INSERT INTO `Wishlist` (ID, Skinname) VALUES ('$ID[0]','$skin_a')";
		$insert = ($conn->query ($sql));
		die('<script type="text/javascript">document.location="http://pcscout24.w4f.eu/tee/";</script>');
		}
		//Markierung von Skins auf der Wunschliste, welche im Angebot sind
		$cross = array_intersect($_SESSION['skins'], $_SESSION['wishlist']); //Überprüft ob Einträge der aktuellen Angebote und Wunschliste übereinstimmen und speichert diese in einem Array
		$cross_n = count($cross)-1; //Zähler 
		$cross = array_values($cross); //setzt den Start der Indizierung auf 0
		if ($cross == true){ //Wenn Übereinstimmungen:
			echo "<script>";
			for($i=0; $i<=$cross_n; $i++) { //Schleife für alle Übereinstimmungen
				echo 'document.getElementById("'.$cross[$i].'").style.background = "green";'; //Markiert Übereinstimmungen grün
				}
			echo "</script>";
	}
	?>
	<?php												//Login-Script
	if(isset($_GET['login'])) {
	$email = htmlspecialchars($_POST['email']);
	$passwort = $_POST['passwort'];
	try {
                $conn = new PDO("mysql:host=$servername;dbname=Skinplaner", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $sql = $conn->prepare("SELECT * FROM Benutzerinfo WHERE Email = '$email'");
                $sql->execute();
                $user = $sql->fetch();
                if ($user[4] !== "1"){
		die("Bitte Email vor dem Login Best&auml;tigen")
		}
		if ($user != false && password_verify($passwort, $user['Password'])) {
                $_SESSION['Email'] = $user['Email'];
                //Möchte der Nutzer angemeldet beleiben?
                if(isset($_POST['angemeldet_bleiben'])) {
                                $identifier = random_string();
                                $securitytoken = random_string();
                                $securitytoken = sha1($securitytoken);
                                $sql = "UPDATE securitytokens SET securitytoken = '$securitytoken', identifier = '$identifier' WHERE BenutzerID = '$user[0]'";
                		$conn->exec($sql);
                                setcookie("identifier",$identifier,time()+(3600*24*365)); //1 Jahr Gültigkeit
                                setcookie("securitytoken",$securitytoken,time()+(3600*24*365)); //1 Jahr Gültigkeit
                        } else {

		
	    }
         catch(PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
         $conn->null;


$statement = $conn->prepare("SELECT Verify FROM verifyemail WHERE Email = :Email");
	$result = $statement->execute(array('Email' => $email));
	$verify = $statement->fetch();
	If ($verify[0] == "1"){
	$statement = $conn->prepare("SELECT * FROM Login WHERE Email = :Email");
	$result = $statement->execute(array('Email' => $email));
	$user = $statement->fetch();
	} else {
		die("Bitte Email vor dem Login Best&auml;tigen");
	}

	//Überprüfung des Passworts
	if ($user != false && password_verify($passwort, $user['Password'])) {
		$_SESSION['Email'] = $user['Email'];
		//Möchte der Nutzer angemeldet beleiben?
		if(isset($_POST['angemeldet_bleiben'])) {
			$statement = $conn->prepare("SELECT * FROM securitytokens WHERE Email = :Email");
			$result = $statement->execute(array('Email' => $_SESSION['Email']));
			$token = $statement->fetch();
			if(isset($token[1])){
				$identifier = random_string();
				$securitytoken = random_string();
				$securitytoken = sha1($securitytoken);
				$insert = $conn->prepare("UPDATE securitytokens SET securitytoken = :securitytoken, identifier = :identifier WHERE Email = :Email");
				$insert->execute(array('Email' => $_SESSION['Email'], 'identifier' => $identifier, 'securitytoken' => $securitytoken));
				setcookie("identifier",$identifier,time()+(3600*24*365)); //1 Jahr Gültigkeit
				setcookie("securitytoken",$securitytoken,time()+(3600*24*365)); //1 Jahr Gültigkeit
			} else {
				$identifier = random_string();
				$securitytoken = random_string();
				$securitytoken = sha1($securitytoken);
				$insert = $conn->prepare("INSERT INTO securitytokens (Email, identifier, securitytoken) VALUES (:Email, :identifier, :securitytoken)");
				$insert->execute(array('Email' => $user['Email'], 'identifier' => $identifier, 'securitytoken' => $securitytoken));
				setcookie("identifier",$identifier,time()+(3600*24*365)); //1 Jahr Gültigkeit
				setcookie("securitytoken",$securitytoken,time()+(3600*24*365)); //1 Jahr Gültigkeit
			}
		}
		die('<script type="text/javascript">alert("Du wurdest erfolgreich eingeloggt!");document.location="http://pcscout24.w4f.eu/tee/";</script>');
	} else {
		echo "E-Mail oder Passwort war ungültig<br>";
	}
}
	?>
	</section>
</main>

	<footer>
	<?php if (isset($_SESSION['Email'])) {
	echo'<form action="logout.php" method="post">
	<button type="submit" >Logout</button>
	</form>';}?>
	<a href="impressum.php">Impressum</a>
	<script>
		function championName(str) {
		  var xhttp;
		  xhttp = new XMLHttpRequest();
		  xhttp.onreadystatechange = function() {
			if (xhttp.readyState == 4 && xhttp.status == 200) {
			  document.getElementById("skinlist").innerHTML = xhttp.responseText;
			}
		  };
		  xhttp.open("GET", "skins.php?q="+str, true);
		  xhttp.send();
		}
	</script>
	</footer>
</body>
	<?php												//"Angemeldet Bleiben" - Script 
	//Überprüfe auf den 'Angemeldet bleiben'-Cookie
	if(!isset($_SESSION['Email']) && isset($_COOKIE['identifier']) && isset($_COOKIE['securitytoken'])) {
		//Überprüft ob die Session nicht besteht und Cookies gesetzt sind
		$identifier = $_COOKIE['identifier']; //Cookie wird als Variable gespeichert
		$securitytoken = $_COOKIE['securitytoken'];//Cookie wird als Variable gespeichert
		$statement = $conn->prepare("SELECT * FROM securitytokens WHERE identifier = :identifier");//sucht in der Datenbank
		$result = $statement->execute(array('identifier' => $identifier));// nach dem Identifier, anschließend werden
		$securitytoken_row = $statement->fetch(); //der dazugehörige securitytoken und die Email in ein Object geladen

		if($securitytoken !== $securitytoken_row['securitytoken']) { //überprüft ob der securitytoken aus dem Cookie mit einem aus dem Array übereinstimmt
		//Token ist inkorrekt = beende alles und Sende eine Fehlermeldung
			die('Ein vermutlich gestohlener Security Token wurde identifiziert');
		} else {
			//Token war korrekt = Setze neuen Token
			$neuer_securitytoken = random_string();
			$neuer_securitytoken = sha1($neuer_securitytoken);
			$insert = $conn->prepare("UPDATE securitytokens SET securitytoken = :securitytoken WHERE identifier = :identifier");
			$insert->execute(array('securitytoken' => $neuer_securitytoken, 'identifier' => $identifier));
			setcookie("identifier",$identifier,time()+(3600*24*365)); //1 Jahr Gültigkeit
			setcookie("securitytoken",$neuer_securitytoken,time()+(3600*24*365)); //1 Jahr Gültigkeit
			//Logge den Benutzer ein
			$_SESSION['Email'] = $securitytoken_row['Email'];
		}
	}
	//$userid = $_SESSION['Email'];
	?>
</html>
