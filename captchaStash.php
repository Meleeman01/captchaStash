<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	session_start();
	error_log('made it here.');
	if(!empty($_GET['new'])){
		$_SESSION["newCaptcha"] = true;
		error_log(json_encode(gd_info()));
		$pngFile = imagecreatefrompng('captchaAssets/japaneseGirlAd.png');  
		$imageWidth = imagesx($pngFile);  
		$imageHeight = imagesy($pngFile);
		error_log($imageWidth);
		error_log($imageHeight);
		$_SESSION['topBoundary'] = 217;
		$_SESSION['bottomBoundary'] = 243;
		$_SESSION['rightBoundary'] = 203;
		$_SESSION['leftBoundary'] = 153;
		
		// we can carve out a hitbox foreach image manually
		// send points and if they are within the checks foreach image, send a win condition
		// else send a try again pic
		//same size as current wetfish captcha. 420X240 spray paint can for funzies 
		

		// Output or save the modified image
		header('Content-Type: image/png');
		imagepng($pngFile);


	}
	
}

?>