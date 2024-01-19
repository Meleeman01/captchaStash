function captcha(){
	console.log('ready.');
	init();
}


async function init(){
	//make a call to the server to get a challenge.
	let result = await fetch('http://localhost:8000/captchaStash.php?new=true');
	console.log(await result.json());
	//render the picture to the client and create an image on the canvas

}