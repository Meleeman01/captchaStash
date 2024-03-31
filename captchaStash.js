function captcha(){
	console.log('ready.');
	init();
}
function restartCaptcha() {
    document.getElementById('captcha').innerHTML = ``;
    captcha();
}
async function init(){
	//make a call to the server to get a challenge.
	const canvas = document.createElement('canvas');
	const button = document.createElement('button');
    const h3 = document.createElement('h3');
    h3.innerText = "Draw a moustache on that face!";
    button.innerText = "Submit";
    button.style.marginTop = "1rem";
    const SprayLength = 50;
	const pointsData = [];
	// button.addEventListener('')
	let result = await fetch('http://localhost:8000/captchaStash.php?new=true');
	result = await result.blob();
	const objectURL = URL.createObjectURL(result);
	const img = new Image();
	// Set the source of the image to the object URL
    img.src = objectURL;

	console.log(img);
    // Set attributes for the canvas (width, height, and id)
    canvas.width = 420;
    canvas.height = 240;
    canvas.id = 'canvasID';

	
	// Create an image element
    document.getElementById('captcha').appendChild(h3)
    document.getElementById('captcha').appendChild(canvas)
    document.getElementById('captcha').appendChild(button)



    // Set up variables to track drawing state
    let isDrawing = false;
    let lastX = 0;
    let lastY = 0;

    // Event listeners for mouse and touch events
    button.addEventListener('click',submitData);
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseleave', stopDrawing);
    //canvas.addEventListener('mousemove', checkPoints);
    canvas.addEventListener('touchstart', startDrawing);
    canvas.addEventListener('touchmove', draw);
    canvas.addEventListener('touchend', stopDrawing);



	const canvasHandler = document.getElementById('canvasID');
    const context = canvasHandler.getContext('2d');
    
    
    // When the image is loaded, draw it onto the canvas
    img.onload = () => {
        // Draw the image on the canvas at coordinates (0, 0)
        context.drawImage(img, 0, 0, canvasHandler.width, canvasHandler.height);
    };
    async function submitData(e) {
        //submit points data
        
        console.log('submitData!',e.target);
        let response = await fetch('captchaStash.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(pointsData)
        });
        let result = await response.json();
        console.log(result);
        if (result.message == 'success') {
            let img = new Image();
            img.src = 'data:image/jpeg;base64,' + result.image;
            // When the image has loaded, draw it on the canvas
            img.onload = function() {
                context.drawImage(img, 0, 0, canvas.width, canvas.height);
            };
        }
        if (result.message == 'captcha failed') {
            let img = new Image();
            img.src = 'data:image/jpeg;base64,' + result.image;
            // When the image has loaded, draw it on the canvas
            img.onload = function() {
                context.drawImage(img, 0, 0, canvas.width, canvas.height);
            };
            canvas.addEventListener('click', restartCaptcha);
        }
    }

    // Function to start drawing
    function startDrawing(e) {
        if (pointsData.length >= SprayLength) return;
        isDrawing = true;
        [lastX, lastY] = getMousePosition(e);
		//when starting a new stroke
		pointsData.push([lastX,lastY]);
        console.log(pointsData);
    }
    function render(e,x,y) {
    	// Set up drawing styles (you can customize these)
        context.fillStyle = '#f0f'; // Stroke color
        const r = 5;
        for (let i = 0; i < 5; i++) {
            const rx = (Math.random() * 2 - 1) * r;
            const ry = (Math.random() * 2 - 1) * r;
            const d = rx * rx + ry * ry;
            if (d <= r * r) {
            // Apply supersampling
                for (let sx = -1; sx <= 1; sx++) {
                    for (let sy = -1; sy <= 1; sy++) {
                        const subX = x + ~~rx + sx / 3;
                        const subY = y + ~~ry + sy / 3;
                        const subD = (sx / 3) * (sx / 3) + (sy / 3) * (sy / 3);
                        if (subD <= 1 && subX >= 0 && subX < canvas.width && subY >= 0 && subY < canvas.height) {
                            context.fillRect(subX, subY, 1, 1);
                        }
                    }
                }
            }
        }
    }

    // Function to draw on the canvas
    function draw(e) {
        if (!isDrawing || pointsData.length >= SprayLength) return;

        const [x, y] = getMousePosition(e);
        console.log(x,y);
        

        render(e,x,y);

        pointsData.push([x,y]);
		console.log(pointsData);
        [lastX, lastY] = [x, y];
        if (true) {}
        
    }
    function checkPoints(e) {
    	const [x,y] = getMousePosition(e);
    	console.log(x,y);
    }
    // Function to stop drawing
    function stopDrawing() {
        isDrawing = false;
        console.log(pointsData)
    }
    function getRandomInt(min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }

    // Function to get mouse position relative to the canvas
    function getMousePosition(e) {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;

        let x, y;

        if (e.touches && e.touches[0]) {
            x = e.touches[0].clientX - rect.left;
            y = e.touches[0].clientY - rect.top;
        } else {
            x = e.clientX - rect.left;
            y = e.clientY - rect.top;
        }

        return [x * scaleX, y * scaleY];
    }


          
    

	//document.getElementById('test').src = objectURL;
	//render the picture to the client and create an image on the canvas

}