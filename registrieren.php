<?php 
include ("password.php"); 
include ("dbinfo.php"); 
include_once ("r_string.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require('PHPMailer/src/PHPMailer.php');
require('PHPMailer/language/phpmailer.lang-de.php');
require('PHPMailer/src/SMTP.php');
require('PHPMailer/src/Exception.php');

	$email = htmlspecialchars($_POST['email']);
	$passwort = $_POST['passwort'];
	$passwort2 = $_POST['passwort2'];
  
	if($passwort != $passwort2) {
		echo 'Die Passwörter müssen übereinstimmen<br>';
		$error = false;
	}else{
	$error = true;
	}

	//Überprüfe, dass die E-Mail-Adresse noch nicht registriert wurde
	if($error == true) {
		try {
		$conn = new PDO("mysql:host=$servername;dbname=Skinplaner", $username, $password);
    		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
		$sql = $conn->prepare("SELECT * FROM Benutzerinfo WHERE Email = '$email'");
		$sql->execute();
		$user = $sql->fetch();
		}
        	catch(PDOException $e) {
                echo "Error: " . $e->getMessage();
           	}
      		$conn->null;
		if($user !== false) {
			echo '<script type="text/javascript">alert("Diese E-Mail-Adresse ist bereits vergeben ");document.location="http://deniz.bounceme.net/mert/";</script>';
			$error = false;
		}
	}
	//Keine Fehler, wir können den Nutzer registrieren
	if($error == true) {
		$verifykey = random_string();
		$passwort_hash = password_hash($passwort, PASSWORD_DEFAULT); //Passwort wird verschlüsselt
	try {
        	$conn = new PDO("mysql:host=$servername;dbname=Skinplaner", $username, $password);
        	// set the PDO error mode to exception
        	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        	$sql = "INSERT INTO Benutzerinfo (Email, passwort, verifykey) VALUES ('$email', '$passwort_hash', '$verifykey')"; //Datenbankbefehl wird als Variable gespeichert
        	$conn->exec($sql);
    	    }
	catch(PDOException $e)
    	    {
        	echo "Error: " . $e->getMessage();
    	    }
	$conn->null;
	try {
		$mail = new PHPMailer(true);
		//Server setting
		//$mail->SMTPDebug = 2;                                 // Enable verbose debug output
    		$mail->isSMTP();                                      // Set mailer to use SMTP
    		$mail->Host = 'xxxx';		      // Specify main and backup SMTP servers
    		$mail->SMTPAuth = true;                               // Enable SMTP authentication
    		$mail->Username = 'xxxx';                // SMTP username
   		$mail->Password = 'xxxx';                    // SMTP password
   		$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    		$mail->Port = 587;
    		//Recipients
    		$mail->setFrom('email', 'Skinplaner');
		$mail->addAddress($email); 
		//Content
    		$mail->isHTML(true);                                  // Set email format to HTML
    		$mail->Subject = 'Bitte best&auml;tigen sie ihre Registrierung auf Skinplaner';
    		$mail->Body    = 'Guten Tag,<br><br>
                                  Vielen Dank f&uuml;r ihre Registrierung auf der Website SkinPlaner. Damit Sie unser Angebot nutzen k&ouml;nnen, m&uuml;ssen Sie ihre E-Mail-Adresse best&auml;tigen.
				  Klicken sie hierf&uuml;r auf den unten stehenden Link.<br><br>
                                  http://deniz.bounceme.net/mert/verify.php?email='.$email.'&verifykey='.$verifykey.' 
				  <br><br><strong>Falls Sie sich nicht auf dieser Website registriert haben, k&ouml;nnen Sie diese Nachricht ignorieren.</strong>
				  <br><br>Vielen Dank<br>SkinPlaner-Team';
		$mail->send();
	} catch (Exception $e) {
    		echo 'Message could not be sent.';
   		echo 'Mailer Error: ' . $mail->ErrorInfo;
	}
/*
		$subject   = "Bitte bestätigen sie ihre Registrierung auf Skinplaner";
		$subject   = "=?utf-8?b?".base64_encode($subject)."?=";
		$mailtext = "Guten Tag,<br>";
		$mailtext .= "<br> Vielen Dank f&uuml;r ihre Registrierung auf der Website SkinPlaner. Damit Sie unser Angebot nutzen k&ouml;nnen, müssen Sie ihre E-Mail-Adresse best&auml;tigen. Klicken sie hierf&uuml;r auf den unten stehenden Link.<br><br>";
		$mailtext .= "http://deniz.bounceme.net/mert/verify.php?email=".$email."&verifykey=".$verifykey;
		$mailtext .= "<br><br><strong>Falls Sie sich nicht auf dieser Website registriert haben, k&ouml;nnen Sie diese Nachricht ignorieren.</strong>";
		$mailtext .= "<br><br>Vielen Dank<br>SkinPlaner-Team";
		$reply  = "info@pcscout24.w4f.eu";
		$headers   = array();
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-type: text/html; charset=iso-8859-1";
		$headers[] = "From: $from\nReply-To: $reply";
		$headers[] = "X-Mailer: PHP/".phpversion();
		$headers[] = "Subject: {$subject}";
		mail($to,$subject,$mailtext,implode("\r\n", $headers)); */
	    echo '<script type="text/javascript">alert("Du wurdest erfolgreich registriert!");document.location="http://deniz.bounceme.net/mert/";</script>';
	}
?>
