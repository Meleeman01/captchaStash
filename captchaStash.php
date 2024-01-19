<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	error_log('made it here.');
	if(!empty($_GET['new'])){
		$response = ['lol'=>'success'];
		  // Set the content type to application/json
    	header('Content-Type: application/json');

		echo json_encode($response);
	}
	
}

?>