<?php
	function random_string() {
		if(function_exists('random_bytes')) {
			$bytes = random_bytes(16);
			$str = bin2hex($bytes); 
		} else if(function_exists('openssl_random_pseudo_bytes')) {
			$bytes = openssl_random_pseudo_bytes(16);
			$str = bin2hex($bytes); 
		} else if(function_exists('mcrypt_create_iv')) {
			$bytes = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);
			$str = bin2hex($bytes); 
		} else {
			//In uniqid einen zufälligen String mit >12 Zeichen einsetzten
			$str = md5(uniqid('G4M3R4L1V3.&:322', true));
		}	
		return $str;
	}
?>