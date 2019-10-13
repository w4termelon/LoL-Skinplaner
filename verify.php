<?php
include_once ("dbinfo.php"); 
if ((isset($_GET['verifykey'])) && isset($_GET['email'])) {
	$verifykey = $_GET['verifykey'];
	$email = $_GET['email'];
	try {
                $conn = new PDO("mysql:host=$servername;dbname=Skinplaner", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $sql = $conn->prepare("SELECT AccountStatus FROM Benutzerinfo WHERE Email = '$email' AND verifykey = '$verifykey'");
                $sql->execute();
                $Verify = $sql->fetch();
                if ($Verify[0] == "0"){
                	$Verified = "1";
			$sql = "UPDATE Benutzerinfo SET AccountStatus = '$Verified'";
                	$conn->exec($sql);
			echo '<script type="text/javascript">alert("Ihre E-Mail-Adresse wurde erfolgreich best\u00e4tigt!");document.location="http://deniz.bounceme.net/mert/";</script>';
           	} else if ($Verify[0] == "1"){
                	echo '<script type="text/javascript">alert("Diese E-Mail-Adresse wurde bereits best\u00e4tigt!");document.location="http://deniz.bounceme.net/,ert/";</script>';
        	} else {
                	die("Ein unerwarteter Fehler ist aufgetreten, bitte wenden Sie sich an den Support!");
        	}

 	    }
                catch(PDOException $e) {
                echo "Error: " . $e->getMessage();
                }
   	        $conn->null;
}
?>
