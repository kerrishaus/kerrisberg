<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Kerrisberg</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
		<!--<link type="text/css" rel="stylesheet" href="assets.css">-->
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@800&display=swap" rel="stylesheet">
		<style>
		    html, body
		    {
		        margin: 0px;
		        padding: 0px;
		    }
		
			body {
				background-color: #ccc;
				color: #000;
				font-family: 'Montserrat';
			}

			a {
				color: #f00;
			}
			
			#loadingCover
			{
			    position: absolute;
			    z-index: 10;
			    width: 100%;
			    height: 100%;
			    top: 0px;
			    left: 0px;
			    
			    background: rgb(46, 46, 46) center;
			    background-size: cover;
			        
			    backdrop-filter: blur(3px);
			    -webkit-backdrop-filter: blur(3px);
			    
			    display: flex;
			    justify-content: center;
			    align-items: center;
			    flex-direction: column;
			    
			    color: white;
			    font-size: 72px;
			    
			    opacity: 1;
			    transition: all 1s;
			}
			
			#loadingCover > #status
			{
			    display: flex;
			    align-items: center;
			    gap: 20px;
			}
			
			#kerris
			{
			    width: 30vw;
			    height: 3vw;
			}
			
			#threejs
			{
			    width: 10vw;
			    height: 10vw;
			}
			
			#webgl
			{
			    width: 30vw;
			    height: 10vw;
			}
			
			#loadingCover > .help
			{
			    position: absolute;
			    
			    width: 100%;
			    height: 100%;
			    
			    display: flex;
			    justify-content: center;
			    align-items: flex-end;
			    
			    color: white;
			    font-size: 20px;
			    
			    margin-bottom: 2em;
			}
			
			.interface
			{
			    position: absolute;
			    z-index: 99;
			    bottom: 0;
			    
			    background: black;
			}
		</style>
	</head>

	<body>
	    <div id='loadingCover'>
	        <div id='status'>
    	        <img id='kerris' src='https://kerrishaus.com/assets/logo/text-big.png'></img>
	            <img id='threejs' src='https://bachasoftware.com/wp-content/uploads/2020/07/icon_2-1.png'></img>
    	        <img id='webgl' src='https://upload.wikimedia.org/wikipedia/commons/thumb/2/25/WebGL_Logo.svg/1024px-WebGL_Logo.svg.png'></img>
	        </div>
	        <div class='help'>
	            Copyright &copy; <?php echo date('Y'); ?> Kerris Haus
	        </div>
	    </div>
	    
		<script type="module">
		    import Stats from '../../assets/threejs/examples/jsm/libs/stats.module.js';
		    import GUI from '../../assets/threejs/examples/jsm/libs/lil-gui.module.min.js';
		
			import * as THREE from '../../assets/threejs/build/three.module.js';
			
			import { OrbitControls } from '../../assets/threejs/examples/jsm/controls/OrbitControls.js';
			import { GLTFLoader } from '../../assets/threejs/examples/jsm/loaders/GLTFLoader.js';
			import { FontLoader } from '../../assets/threejs/examples/jsm/loaders/FontLoader.js';
			import { TextGeometry } from '../../assets/threejs/examples/jsm/geometries/TextGeometry.js';
			
			import { generateWorld, addWorldToScene } from './assets/scripts/WorldGenerator.js';
			
			let camera, controls, scene, renderer, raycaster, INTERSECTED = null, frameCounter, dirLight1, holdingObject, heldObject, selectedObject, selectedGui, lastMouseClickTime;
			
			let statusText = document.getElementById("status");
			
			var buildingDropAudio;
			
			const mouse = new THREE.Vector2();
			
			// for moving objects around
			var plane = new THREE.Plane(new THREE.Vector3(0, 1, 0), 0);
            var intersects = new THREE.Vector3();
            
            var selectedItemInfoPanelGroup = null;
            var selectedItemInfoPanel = null, selectedItemInfoPanel2 = null, selectedItemInfoPanelTitleText = null, selectedItemInfoPanelTitleBar, selectedItemInfoPanelDeleteButton = null;
            
            var textFont = "fucking shit";
            
            var selectedItemInfoPanelButtons = [];
            
            const gui = new GUI();
            
            setTimeout(function() {
			    init();
			    animate();
            }, 500);
			//render(); // remove when using next line for animation loop (requestAnimationFrame)
			
			function prepareCameraControls()
			{
				const controls = new OrbitControls( camera, renderer.domElement );
				controls.listenToKeyEvents( window ); // optional

				controls.enableDamping = true; // an animation loop is required when either damping or auto-rotation are enabled
				controls.dampingFactor = 0.3;

				controls.screenSpacePanning = false;

				controls.minDistance = 4;
				controls.maxDistance = 40;

				controls.maxPolarAngle = Math.PI / 2.5;
				
				controls.keys = {
                	LEFT: 'KeyA', //left arrow
                	UP: 'KeyW', // up arrow
                	RIGHT: 'KeyD', // right arrow
                	BOTTOM: 'KeyS' // down arrow
                }
                
                return controls;
			}
			
			function prepareRenderer()
			{
				const renderer = new THREE.WebGLRenderer( { antialias: true } );
				renderer.setPixelRatio( window.devicePixelRatio );
				renderer.setSize( window.innerWidth, window.innerHeight );
				renderer.setClearColor(0x1c6e91);
				document.body.appendChild( renderer.domElement );
				
				return renderer;
			}

			function init()
			{
                function setStatusText(text)
                {
                    statusText.innerHTML = text;
                }

                setStatusText("Creating scene");
				scene = new THREE.Scene();
				scene.background = new THREE.Color( 0xcccccc );
				scene.fog = new THREE.FogExp2( 0xcccccc, 0.0015 );
				
				raycaster = new THREE.Raycaster();

                setStatusText("Creating renderer");
                renderer = prepareRenderer();
                
                setStatusText("Setting up camera");
				camera = new THREE.PerspectiveCamera( 60, window.innerWidth / window.innerHeight, 0.1, 1000 );

				// controls

                controls = prepareCameraControls();

				// world

                setStatusText("Generating World");
                
				let world = generateWorld(50, 50);
				addWorldToScene(scene, world);
				
				// lights

				dirLight1 = new THREE.DirectionalLight( 0xffffff );
				dirLight1.position.set( 1, 1, 1 );
				scene.add( dirLight1 );

				const ambientLight = new THREE.AmbientLight( 0x222222 );
				scene.add( ambientLight );

                setStatusText("Setting up GUI");
				window.addEventListener( 'resize', onWindowResize );
				document.addEventListener( 'mousemove', onDocumentMouseMove );
				document.addEventListener( 'keydown', onKeyDown );
				document.addEventListener( 'mousedown', onMouseDown );
				document.addEventListener( 'mouseup', onMouseUp );
				
				holdingObject = false;
				heldObject = null;
				
				frameCounter = new Stats();
                frameCounter.showPanel( 0 ); // 0: fps, 1: ms, 2: mb, 3+: custom
                document.body.appendChild( frameCounter.dom );
                
                gui.title("Construction");
                
                const myObject = {
                	house: function() { startBuilding(0) },
                	tavern: function() { startBuilding(1) },
                };
                
                gui.add( myObject, 'house' );
                gui.add( myObject, 'tavern' );
                
                buildingDropAudio = new Audio('https://kerrishaus.com/games/kerrisberg/assets/audio/bang_1.wav')
                
                selectedItemInfoPanelGroup = new THREE.Group();
                
	    		const geometry344 = new THREE.PlaneGeometry(1, 2);
    		    const material344 = new THREE.MeshBasicMaterial( { color: 0x000000 } );
    		    selectedItemInfoPanel = new THREE.Mesh( geometry344, material344 );
    		    selectedItemInfoPanel.userData.interfaceObject = true;
                selectedItemInfoPanelGroup.add(selectedItemInfoPanel);
                
                const geometry34 = new THREE.PlaneGeometry(.9, 1.9);
    		    const material34 = new THREE.MeshBasicMaterial( { color: 0xFFFFFF } );
    		    selectedItemInfoPanel2 = new THREE.Mesh( geometry34, material34 );
    		    selectedItemInfoPanel2.userData.interfaceObject = true;
                selectedItemInfoPanelGroup.add(selectedItemInfoPanel2);
                
                const selectedItemInfoPanelTitleBarGeometry = new THREE.PlaneGeometry(.9, .2);
    		    const selectedItemInfoPanelTitleBarMaterial = new THREE.MeshBasicMaterial( { color: 0x000000 } );
    		    selectedItemInfoPanelTitleBar = new THREE.Mesh( selectedItemInfoPanelTitleBarGeometry, selectedItemInfoPanelTitleBarMaterial );
    		    selectedItemInfoPanelTitleBar.userData.interfaceObject = true;
    		    selectedItemInfoPanelTitleBar.position.y = .9;
                selectedItemInfoPanelGroup.add(selectedItemInfoPanelTitleBar);
                
                const selectedItemInfoPanelDeleteButtonGeometry = new THREE.PlaneGeometry(.8, .2);
    		    const selectedItemInfoPanelDeleteButtonMaterial = new THREE.MeshBasicMaterial( { color: 0xff001e } );
    		    selectedItemInfoPanelDeleteButton = new THREE.Mesh( selectedItemInfoPanelDeleteButtonGeometry, selectedItemInfoPanelDeleteButtonMaterial );
    		    selectedItemInfoPanelDeleteButton.userData.interfaceObject = true;
    		    selectedItemInfoPanelDeleteButton.position.y = .65;
                selectedItemInfoPanelGroup.add(selectedItemInfoPanelDeleteButton);
                
				loadFont();
				
				setTimeout(function() {
				    if (textFont == "fucking shit")
				        throw new Error("Couldn't load the dang font.");
				}, 200);
				
				setStatusText("Ready!");
				setTimeout(function() {
    				document.getElementById("loadingCover").style.opacity = "0";
    				setTimeout(function() {
        				document.getElementById("loadingCover").remove();
    				}, 1000);
				}, 0);
			}
			
			function changeInfoPanelTitleText(string)
			{
			    selectedItemInfoPanelGroup.remove(selectedItemInfoPanelTitleText);
			    disposeObject(selectedItemInfoPanelTitleText);
			    
			    selectedItemInfoPanelTitleText = createText(string);
			    selectedItemInfoPanelTitleText.userData.interfaceObject = true;
			    
			    selectedItemInfoPanelGroup.add(selectedItemInfoPanelTitleText);
			}
			
			function createText(string)
			{
			    const textGeo = new TextGeometry( string, {
            		font: textFont,
            		size: .1,
            		height: 0,
            		curveSegments: 12
            	} );

				textGeo.computeBoundingBox();

				const centerOffset = - 0.5 * ( textGeo.boundingBox.max.x - textGeo.boundingBox.min.x );

				const materials = [
					new THREE.MeshBasicMaterial( { color: 0xFFFFFF } ), // front
					new THREE.MeshBasicMaterial( { color: 0x000000 } ) // side, even though it's flat text
				];

				const textMesh = new THREE.Mesh( textGeo, materials );
                
				return textMesh;
			}
			
			function buildInfoPanelForGameObject(object)
			{
			    var objectData = object.userData;
			    
			    scene.add(selectedItemInfoPanelGroup);
    
                selectedItemInfoPanelGroup.position.copy(object.position);
                selectedItemInfoPanelGroup.position.y += 2;
                
                changeInfoPanelTitleText(objectData.type);
                selectedItemInfoPanelTitleText.position.copy(selectedItemInfoPanelTitleBar.position);
			}
			
			function addInfoPanelButton(text, yPosOffset, callback = null)
			{
			    const buttonText = createText(text);
			    
                const selectedItemInfoPanelButtonGeometry = new THREE.PlaneGeometry(.8, .2);
    		    const selectedItemInfoPanelButtonMaterial = new THREE.MeshBasicMaterial( { color: 0xff001e } );
    		    selectedItemInfoPanelButton = new THREE.Mesh( selectedItemInfoPanelButtonGeometry, selectedItemInfoPanelButtonMaterial );
    		    selectedItemInfoPanelButton.userData.interfaceObject = true;
    		    selectedItemInfoPanelButton.position.y = yPosOffset;
                selectedItemInfoPanelGroup.add(selectedItemInfoPanelButton);
			}
			
            function loadFont()
            {
                const fontLoad = new FontLoader();
                
				fontLoad.load( 'https://raw.githubusercontent.com/mrdoob/three.js/master/examples/fonts/helvetiker_regular.typeface.json', 
				
				function (response)
				{
				    textFont = response;
				    
				    console.log("font is ready");
				});
			}
			
			function startBuilding(code)
			{
			    console.log("building " + code);
			    
			    if (code == 1) // tavern
			    {
    			    const loader = new GLTFLoader();
    
                    loader.load( './assets/models/CartoonTavern.glb', function ( gltf ) {
                    	scene.add( gltf.scene );
                    	heldObject = gltf.scene;
                    	heldObject.scale.x = .15;
                    	heldObject.scale.y = .15;
                    	heldObject.scale.z = .15;
                    	heldObject.position.y = 0;
                    	heldObject.userData.gameObject = true;
                    	heldObject.userData.type = "building";
                    	heldObject.userData.buildingType = "tavern";
                    	holdingObject = true;
                    }, undefined, function ( error ) {
                    	console.error( error );
                    } );
			    }
			}
			
			function disposeObject(objectUuid)
			{
                var o = scene.getObjectByProperty('uuid', objectUuid);
                
                if (!o)
                    return;
                
                if (o.geometry) {
                    o.geometry.dispose()
                }
    
                if (o.material) {
                    if (o.material.length) {
                        for (let i = 0; i < o.material.length; ++i) {
                            o.material[i].dispose()
                        }
                    }
                    else {
                        o.material.dispose()
                    }
                }
                
                scene.remove( o );
            };
            
            function disposeSelectedObject()
            {
                if (selectedObject != null)
                {
                    disposeObject(selectedObject.uuid);
                }
            }

			function onMouseDown(event)
			{
			    lastMouseClickTime = Date.now();
			    
			    // left click
			    if (event.button == 0)
			    {
			        if (holdingObject)
			        {
	    	            heldObject.position.y = -0.35;
    		            holdingObject = false;
	    	            heldObject = null;
	    	            
	    	            buildingDropAudio.currentTime = 100;
                        buildingDropAudio.play();
			        }
			    }
			}
			
			function onMouseUp(event)
			{
			    // left click
			    if (event.button == 0)
			    {
			        // if last mouse click was less than 175ms ago
			        // this is so that we don't move the menu if the user is rotating the camera
			        if (Date.now() - lastMouseClickTime < 175)
			        {
    			        if (!holdingObject && INTERSECTED != null) // if not moving an object, and hovering an object
    			        {
    			            // make sure it's a real object
    			            if (INTERSECTED.hasOwnProperty("userData") && INTERSECTED.userData.hasOwnProperty("gameObject"))
    			            {
    			                buildInfoPanelForGameObject(INTERSECTED);
    			            }
    			        }
			        }
			    }
			}
			
			function onKeyDown(event)
			{
			    if (event.key == "Escape")
			    {
			        if (holdingObject)
			        {
			            disposeObject(heldObject.uuid)
    		            holdingObject = false;
	    	            heldObject = null;
			        }
			        
			        if (selectedItemInfoPanelGroup)
			            scene.remove(selectedItemInfoPanelGroup)
			        
			        if (selectedGui)
			            selectedGui.destroy();
			            
		            if (selectedObject != null)
			            selectedObject = null;
			    }
                else if (event.key == "r")// rotate held object
                {
                    if (holdingObject)
                        heldObject.rotation.y += Math.PI / 2;
                }
			}

			function onWindowResize()
			{
				camera.aspect = window.innerWidth / window.innerHeight;
				camera.updateProjectionMatrix();

				renderer.setSize( window.innerWidth, window.innerHeight );
			}
			
			function onDocumentMouseMove( event )
			{
                // Update the mouse variable
                event.preventDefault();
                mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
                mouse.y = - (event.clientY / window.innerHeight) * 2 + 1;
                
			    if (holdingObject && heldObject != null && INTERSECTED != null)
                {
                    heldObject.position.x = INTERSECTED.position.x;
                    heldObject.position.z = INTERSECTED.position.z;
                    
                    /*
                    // follow mouse position exactly
                    raycaster.setFromCamera(mouse, camera);
                    raycaster.ray.intersectPlane(plane, intersects);
                    heldObject.position.set(intersects.x, heldObject.position.y, intersects.z);
                    */
                }
			}

			function animate() 
			{
			    frameCounter.begin();

				requestAnimationFrame(animate);

				controls.update(); // only required if controls.enableDamping = true, or if controls.autoRotate = true

				raycaster.setFromCamera(mouse, camera);

				const intersects2 = raycaster.intersectObjects(scene.children, true);
				
				if (intersects2.length > 0)
				{
					const targetDistance = intersects2[ 0 ].distance;

					if (INTERSECTED != intersects2[ 0 ].object)
					{
					    /*
						if (INTERSECTED && INTERSECTED.material.hasOwnProperty("emissive"))
						    INTERSECTED.material.emissive.setHex( INTERSECTED.currentHex );
					    */
					      
						INTERSECTED = intersects2[ 0 ].object;
						
						INTERSECTED.onMouseStartHover();
						
						/*
						if (INTERSECTED.material.hasOwnProperty("emissive"))
						{
    						INTERSECTED.currentHex = INTERSECTED.material.emissive.getHex();
    						INTERSECTED.material.emissive.setHex( 0xff0000 );
						}
						*/
					}
				}
				else // nothing is hovered
				{
				    /*
					if (INTERSECTED != null && INTERSECTED.material.hasOwnProperty("emissive"))
					    INTERSECTED.material.emissive.setHex( INTERSECTED.currentHex );
				    */
				    
				    INTERSECTED.onMouseStopHover();

					INTERSECTED = null;
				}
				
				if (selectedItemInfoPanelGroup != null)
				{
				    selectedItemInfoPanelGroup.quaternion.copy(camera.quaternion);
				}
				
				render();
				
				frameCounter.end();
			}

			function render() 
			{
				renderer.render( scene, camera );
			}
		</script>
	</body>
</html>