<?php
session_start();
setcookie("identifier",$identifier, 1); //-1 Stunde Gültigkeit
setcookie("securitytoken",$securitytoken, 1); //-1 Stunde Gültigkeit
session_destroy();
echo'<script type="text/javascript">alert("Du wurdest erfolgreich ausgeloggt!");document.location="http://pcscout24.w4f.eu/tee/";</script>';
?>