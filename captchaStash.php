<?php
//global variables
session_start();
class captchaStash {
	private static $instance = null;
	private function __construct(){}
	public $horizonTypes = ['low', 'middle', 'high'];
	public $horizonType = "";
	public $roadTypes = ['dual','center','left','right'];
	public $roadType = "";
	public $billboardPlacement = ['top','middle','bottom'];
	public $billboardType = "";
	public $paths = [
		"billboardTowerTop"=>"captchaAssets/billboards/billboardTowerTop.png",
		"billboardTowerMiddle"=>"captchaAssets/billboards/billboardTowerMiddle.png",
		"billboardBottom"=>"captchaAssets/billboards/billboardBottom.png",
		"billboardImage" =>"captchaAssets/billboards/bilboardImage.png",
		"distantCityBg"=>"captchaAssets/distantCity.png",
		"lowHorizon"=>"captchaAssets/lowHorizon/",
		"middleHorizon"=>"captchaAssets/middleHorizon/",
		"highHorizon"=>"captchaAssets/highHorizon/",
	];
	public static function createNewCaptcha()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
//===end of globals===//
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	
	error_log('made it here.');

	if(!empty($_GET['new'])) {
		$_SESSION["newCaptcha"] = true;
		//error_log(json_encode(gd_info()));
		

		$captchaData = captchaStash::createNewCaptcha();
		//error_log(json_encode($captchaData));
		$im = @imagecreatetruecolor(420, 240)
		    or die("Cannot Initialize new GD image stream");
		    //these are here to test individual cases.
		$captchaData->horizonType = 'high';
		$captchaData->roadType = 'dual';
		$captchaData->billboardType = 'top';

		$im = createBackground($im, $captchaData);
		$im = generateRoads($im, $captchaData);
		//decide billboard height
		decideBillBoardHeight($captchaData);
		$im = generateBillboard($im, $captchaData);

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

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	error_log('posting coords');
	if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
		 $jsonData = file_get_contents('php://input');

        // Decode the JSON data into a PHP array
        $data = json_decode($jsonData, true);
        error_log(json_encode($_SESSION));
        $validEntries = array_filter($data, function($v) {
        	if($v[0] > $_SESSION['leftBoundary'] and $v[0] < $_SESSION['rightBoundary'] and
        		$v[1] > $_SESSION['topBoundary'] and $v[1] < $_SESSION['bottomBoundary']) { 
        		return $v;
        	}
        });

        if (!empty($validEntries)) {
        	error_log('lol win condition!!!!');
        	$imageData = file_get_contents('captchaAssets/success.png');
    		$base64Image = base64_encode($imageData);
    		$response = ['message' => 'success','image' => $base64Image];
	        header('Content-Type: application/json');
	        echo json_encode($response);
        }
        else {
        	error_log('FAIL!');
        	$imageData = file_get_contents('captchaAssets/failure.png');
    		$base64Image = base64_encode($imageData);
    		error_log(json_encode($imageData));
        	$response = ['message' => 'captcha failed','image' => $base64Image];
	        header('Content-Type: application/json');
	        echo json_encode($response);
			die();
        }

        error_log(json_encode($validEntries));
        

        

	}
}
function generateBoundaries($imageXoffset,$imageYoffset,$image) {
	$imageWidth = $image['x'];
	$imageHeight = $image['y'];
	$_SESSION['topBoundary'] = $imageYoffset;
	$_SESSION['bottomBoundary'] = ($imageYoffset+$image['y']);
	$_SESSION['leftBoundary'] = $imageXoffset;
	$_SESSION['rightBoundary'] = ($imageXoffset+$image['x']);
	error_log(json_encode($_SESSION));
}
function importImage($url,$getChallenge = false) {
	
	$image = imagecreatefrompng($url);
	if ($image == false) {
		error_log('no image!');
	}

	$baseWidth = imagesx($image);
	$baseHeight = imagesy($image);
	$clippoints = null;
	if ($getChallenge) {
		$clippoints = getimagesize($url);
		error_log(json_encode($clippoints));
	}
	return ["x"=> $baseWidth,"y"=>$baseHeight,"image"=>$image];
}
function getRandomInt($min = null, $max = null) {
    // Generate and return a random integer
    if ($min > $max) {
    	return mt_rand($max, $min);
    }else return mt_rand($min, $max);
    
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
	// $captchaData->horizonType = $captchaData->
	// horizonTypes[array_rand($captchaData->horizonTypes)];

	// Fill the green rectangle based on the horizon type
	$distantCityBg = importImage($captchaData->paths['distantCityBg']);
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
	// $captchaData->roadType = $captchaData->
	// roadTypes[array_rand($captchaData->roadTypes)];

	error_log($captchaData->roadType);
	switch ($captchaData->horizonType) {
	    case 'high':
	    	//error_log($captchaData->horizonType);
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
	// error_log(json_encode($captchaData));
	// $captchaData->billboardType = $captchaData->
	// billboardPlacement[array_rand($captchaData->billboardPlacement)];
	// error_log('billboardPlacement high,middle,low: '.$captchaData->billboardType);
	return $captchaData;
}

function generateBuildings($image, $captchaData) {
	return $image;
}

function generateBillboard($image, $captchaData) {
	//check horizon height status

	

	switch ($captchaData->horizonType) {
	    case 'high':
	    	switch ($captchaData->roadType){
	    		case 'dual':
	    		//anywhere on the bottom if using road bilboard. anywhere middle with tower,
	    		//anywhere top with tower
	    			switch($captchaData->billboardType){
	    				case "top":
	    					$randomInt = mt_rand(-140,140);
	    					$bilboardImageData = importImage($captchaData->paths['billboardTowerTop']);
	    					$bilboardSubImageData = importImage($captchaData->paths['billboardImage'],true);

	    					
	    					imagecopy($image, $bilboardImageData['image'], $randomInt, 0, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    					imagecopy($image, $bilboardSubImageData['image'], ($randomInt+160), 3, 0, 0, $bilboardSubImageData['x'], $bilboardSubImageData['y']);
	    					generateBoundaries(($randomInt+160),3,$bilboardSubImageData);
	    				break;
	    				case 'middle':
	    					$bilboardImageData = importImage($captchaData->paths['billboardTowerMiddle']);
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-140,140), 0, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    				case 'bottom':

	    					$bilboardImageData = importImage($captchaData->paths['billboardBottom']);
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-155,159), mt_rand(40,80), 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    			}
	    		break;
	    		case 'center':
	    		//availible positions, bottom right, middle right, middle left,
	    		//top right if using tower.
	    			switch($captchaData->billboardType){
	    				case "top":
	    					$bilboardImageData = importImage($captchaData->paths['billboardTowerTop']);
	    					imagecopy($image, $bilboardImageData['image'], 160, 0, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    				case 'middle':
	    					$isTower = mt_rand(0,1);
	    					if ($isTower == 0) {
	    						$leftorRight = mt_rand(0,1);
	    						$billboardX = 0;
	    						if ($leftorRight == 0) {
	    							$billboardX = -155;
	    						}else $billboardX = 159;

	    						$bilboardImageData = importImage($captchaData->paths['billboardBottom']);
	    						imagecopy($image, $bilboardImageData['image'], $billboardX, mt_rand(-10,40), 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    					}
	    					else {
	    						$bilboardImageData = importImage($captchaData->paths['billboardTowerMiddle']);
	    						imagecopy($image, $bilboardImageData['image'], 160, mt_rand(-10,0), 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    					}
	    					
	    				break;
	    				case 'bottom':
	    					$bilboardImageData = importImage($captchaData->paths['billboardBottom']);
	    					imagecopy($image, $bilboardImageData['image'], 159, 80, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    			}
	    		break;
	    		case 'left':
	    		case 'right':
	    		//anywhere on the bottom if using road bilboard. anywhere middle with tower but with 80 - 100px away from the left road, same with top tower.
	    			switch($captchaData->billboardType){
	    				case "top":
	    				$bilboardImageData = importImage($captchaData->paths['billboardTowerTop']);
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-140,140), mt_rand(0,10), 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    				case 'middle':
	    				$bilboardImageData = importImage($captchaData->paths['billboardTowerMiddle']);
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-140,140), mt_rand(0,10), 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    				case 'bottom':
	    				$bilboardImageData = importImage('captchaAssets/billboards/billboardBottom.png');
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-150,150),mt_rand(70,80), 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    			}
	    		break;
	    	}
	    break;
	    case 'middle':
	    	switch ($captchaData->roadType){
	    		case 'dual':
	    		//bottom road bilboard 60px away from either side. billboard towers can go anywhere
	    			switch($captchaData->billboardType){
	    				case "top":
	    				$bilboardImageData = importImage($captchaData->paths['billboardTowerTop']);
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-155,159), 0, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    				case 'middle':
	    				$bilboardImageData = importImage($captchaData->paths['billboardTowerMiddle']);
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-140,140), 0, 0, 0, 
										    		$bilboardImageData['x'],
										    		$bilboardImageData['y']);
	    				break;
	    				case 'bottom':
	    				$bilboardImageData = importImage('captchaAssets/billboards/billboardBottom.png');
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-120,120), 75, 0, 0, 
										    		$bilboardImageData['x'],
										    		$bilboardImageData['y']);
	    				break;
	    			}
	    		break;
	    		case 'center':
	    		//all billboards can appear on the right or left side but not the center. height doesn't matter
		    		$leftorRight = mt_rand(0,1);
		    		if ($leftorRight == 0) {
	    				$leftorRight = -1;
	    			}else $leftorRight = 1;

	    			switch($captchaData->billboardType){
	    				case "top":
	    				$bilboardImageData = importImage($captchaData->paths['billboardTowerTop']);
	    					imagecopy($image, $bilboardImageData['image'], (155*$leftorRight), 0, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    				case 'middle':
	    				$bilboardImageData = importImage($captchaData->paths['billboardTowerMiddle']);
	    					imagecopy($image, $bilboardImageData['image'], (155*$leftorRight), 0, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    				case 'bottom':
	    				$bilboardImageData = importImage('captchaAssets/billboards/billboardBottom.png');
	    					imagecopy($image, $bilboardImageData['image'], (155*$leftorRight), mt_rand(0,75), 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    			}
	    		break;
	    		case 'left':
	    		//all billboards must be 60px away from the left side
	    			switch($captchaData->billboardType){
	    				case "top":
	    				$bilboardImageData = importImage($captchaData->paths['billboardTowerTop']);
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-120,159), 0, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    				case 'middle':
	    				$bilboardImageData = importImage($captchaData->paths['billboardTowerMiddle']);
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-120,159), 0, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    				case 'bottom':
	    				$bilboardImageData = importImage('captchaAssets/billboards/billboardBottom.png');
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-120,159), mt_rand(30,75), 0, 0, 
										    		$bilboardImageData['x'],
										    		$bilboardImageData['y']);
	    				break;
	    			}
	    		break;
	    		case 'right':
	    		//all billboards must be 60px away from the right side
	    			switch($captchaData->billboardType){
	    				case "top":
	    				$bilboardImageData = importImage($captchaData->paths['billboardTowerTop']);
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-155,110), 0, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    				case 'middle':
	    				$bilboardImageData = importImage($captchaData->paths['billboardTowerMiddle']);
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-155,110), 0, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    				case 'bottom':
	    				$bilboardImageData = importImage($captchaData->paths['billboardBottom']);
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-155,110), 75, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    			}
	    		break;
	    	}
	    break;
	    case 'low':
	    	switch ($captchaData->roadType){
	    		case 'dual':
	    		//all billboards stay 100px + away from either side
	    			switch($captchaData->billboardType){
	    				case "top":
	    				$bilboardImageData = importImage($captchaData->paths['billboardTowerTop']);
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-60,60), 0, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    				case 'middle':
	    				$bilboardImageData = importImage($captchaData->paths['billboardTowerMiddle']);
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-60,60), 0, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    				case 'bottom':
	    				$bilboardImageData = importImage($captchaData->paths['billboardBottom']);
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-60,60), 75, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    			}
	    		break;
	    		case 'center':
	    			$leftorRight = mt_rand(0,1);
	    			$minRand = 100;
	    			$maxRand = 159;
	    			if ($leftorRight == 0) {
	    				//left
	    				$minRand = $minRand*-1;
	    				$maxRand = $maxRand*-1;
	    			}
	    			switch($captchaData->billboardType){
	    				case "top":
	    				$bilboardImageData = importImage($captchaData->paths['billboardTowerTop']);
	    					imagecopy($image, $bilboardImageData['image'], getRandomInt($minRand,$maxRand), 0, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    				case 'middle':
	    				$bilboardImageData = importImage($captchaData->paths['billboardTowerMiddle']);
	    					imagecopy($image, $bilboardImageData['image'], getRandomInt($minRand,$maxRand), 0, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    				case 'bottom':
	    				$bilboardImageData = importImage($captchaData->paths['billboardBottom']);
	    					imagecopy($image, $bilboardImageData['image'], getRandomInt($minRand,$maxRand), 75, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    			}
	    		break;
	    		case 'left':
	    		//all billboards stay 100px + away from left side
	    			switch($captchaData->billboardType){
	    				case "top":
	    				$bilboardImageData = importImage($captchaData->paths['billboardTowerTop']);
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-55,159), 0, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    				case 'middle':
	    				$bilboardImageData = importImage($captchaData->paths['billboardTowerMiddle']);
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-55,159), 0, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    				case 'bottom':
	    				$bilboardImageData = importImage($captchaData->paths['billboardBottom']);
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-50,159), 75, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    			}
	    		break;
	    		case 'right':
	    		//all billboards stay 100px + away from right side
	    		switch($captchaData->billboardType){
	    				case "top":
	    				$bilboardImageData = importImage($captchaData->paths['billboardTowerTop']);
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-157,40), 0, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    				case 'middle':
	    				$bilboardImageData = importImage($captchaData->paths['billboardTowerMiddle']);
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-157,40), 0, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    				case 'bottom':
	    				$bilboardImageData = importImage($captchaData->paths['billboardBottom']);
	    					imagecopy($image, $bilboardImageData['image'], mt_rand(-157,40), 75, 0, 0, 
										    		$bilboardImageData['x'], 
										    		$bilboardImageData['y']);
	    				break;
	    			}
	    		break;
	    	}
	    break;
	}
	return $image;
}



?>