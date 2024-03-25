<?php
//global variables

class captchaStash {
	private static $instance = null;
	private function __construct(){}
	public $horizonTypes = ['low', 'middle', 'high'];
	public $horizonType = "";
	public $roadTypes = ['dual','center','left','right'];
	public $roadType = "";
	public $billboardPlacement = ['top','middle','bottom'];
	public $billboardType = "";
	public static function createNewCaptcha()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
//===end of globals===//

function importImage($url) {

	$image = imagecreatefrompng($url);
	if ($image == false) {
		error_log('no image!');
	}
	$baseWidth = imagesx($image);
	$baseHeight = imagesy($image);

	return ["x"=> $baseWidth,"y"=>$baseHeight,"image"=>$image];
}

function createBackground($image, $captchaData) {
	// Define colors
	$green = imagecolorallocate($image, 0, 255, 0); // Green
	$skyBlue = imagecolorallocate($image, 135, 206, 235); // Sky Blue

	// Define heights for the green and blue rectangles
	$lowHorizonHeight = 80; // 1/3 of the total height or 39px
	$middleHorizonHeight = 120; // 1/2 of the total height or 
	$highHorizonHeight = 200; // 80ish% of the total height
	
	//get random horizon type
	$captchaData->horizonType = $captchaData->
	horizonTypes[array_rand($captchaData->horizonTypes)];

	// Fill the green rectangle based on the horizon type
	$distantCityBg = importImage('captchaAssets/distantCity.png');
	switch ($captchaData->horizonType) {
	    case 'high':
	    	imagefilledrectangle($image, 0, $lowHorizonHeight, 420, 240, $green);
	        imagefilledrectangle($image, 0, 0, 420, $lowHorizonHeight, $skyBlue);
	        imagecopy($image, $distantCityBg['image'], 0, -120, 0, 0, 
	    		$distantCityBg['x'], 
	    		$distantCityBg['y']);
	        break;
	    case 'middle':
	    	imagefilledrectangle($image, 0, $middleHorizonHeight, 420, 240, $green);
	        imagefilledrectangle($image, 0, 0, 420, $middleHorizonHeight, $skyBlue);
	        imagecopy($image, $distantCityBg['image'], 0, -80, 0, 0, 
	    		$distantCityBg['x'], 
	    		$distantCityBg['y']);
	        break;
	    case 'low':
	    	imagefilledrectangle($image, 0, $highHorizonHeight, 420, 240, $green);
	        imagefilledrectangle($image, 0, 0, 420, $highHorizonHeight, $skyBlue);
	        imagecopy($image, $distantCityBg['image'], 0, 0, 0, 0, 
	    		$distantCityBg['x'], 
	    		$distantCityBg['y']);
	        break;
	}
	return $image;
}

function generateRoads($image, $captchaData) {
	
	//get random road type
	$captchaData->roadType = $captchaData->
	roadTypes[array_rand($captchaData->roadTypes)];

	error_log($captchaData->roadType);
	switch ($captchaData->horizonType) {
	    case 'high':
	    	error_log($captchaData->horizonType);
	    	$roadImageData = importImage('captchaAssets/highHorizon/'.$captchaData->roadType.'.png');
	    	imagecopy($image, $roadImageData['image'], 0, 0, 0, 0, 
	    		$roadImageData['x'], 
	    		$roadImageData['y']);
	    break;
	    case 'middle':
	    	error_log($captchaData->horizonType);
	    	$roadImageData = importImage('captchaAssets/middleHorizon/'.$captchaData->roadType.'.png');
	    	imagecopy($image, $roadImageData['image'], 0, 0, 0, 0, 
	    		$roadImageData['x'], 
	    		$roadImageData['y']);
	    break;
	    case 'low':
	    	error_log($captchaData->horizonType);
	    	$roadImageData = importImage('captchaAssets/lowHorizon/'.$captchaData->roadType.'.png');
	    	imagecopy($image, $roadImageData['image'], 0, 0, 0, 0, 
	    		$roadImageData['x'], 
	    		$roadImageData['y']);
	    break;
	}
	return $image;
}

function decideBillBoardHeight($captchaData) {
	
}

function generateBuildings($image, $captchaData) {
	return $image;
}

function generateBillboard($image, $captchaData) {
	return $image;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	session_start();
	error_log('made it here.');

	if(!empty($_GET['new'])){
		$_SESSION["newCaptcha"] = true;
		error_log(json_encode(gd_info()));
		

		$captchaData = captchaStash::createNewCaptcha();
		error_log(json_encode($captchaData));
		$im = @imagecreatetruecolor(420, 240)
		    or die("Cannot Initialize new GD image stream");
		
		$im = createBackground($im, $captchaData);
		$im = generateRoads($im, $captchaData);
		//decide billboard height
		
		$captchaData->billboardType = $captchaData->billboardPlacement[array_rand($captchaData->billboardPlacement)];


		// $background_color = imagecolorallocate($im, 0, 0, 0);
		// $text_color = imagecolorallocate($im, 233, 14, 91);
		// imagestring($im, 1, 5, 5,  "A Simple Text String", $text_color);


		
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
		imagepng($im);
		// Clean up
		//imagedestroy($image);

	}
	
}

?>