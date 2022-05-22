var selectedItemInfoPanelGroup = null;
var selectedItemInfoPanel = null, selectedItemInfoPanel2 = null, selectedItemInfoPanelTitleText = null, selectedItemInfoPanelTitleBar, selectedItemInfoPanelDeleteButton = null;

var textFont = "fucking shit";

var selectedItemInfoPanelButtons = [];

const geometry344 = new THREE.PlaneGeometry(1, 2);
const material344 = new THREE.MeshBasicMaterial( { color: 0x000000 } );
selectedItemInfoPanel = new THREE.Mesh( geometry344, material344 );
selectedItemInfoPanelGroup.add(selectedItemInfoPanel);

const geometry34 = new THREE.PlaneGeometry(.9, 1.9);
const material34 = new THREE.MeshBasicMaterial( { color: 0xFFFFFF } );
selectedItemInfoPanel2 = new THREE.Mesh( geometry34, material34 );
selectedItemInfoPanelGroup.add(selectedItemInfoPanel2);

const selectedItemInfoPanelTitleBarGeometry = new THREE.PlaneGeometry(.9, .2);
const selectedItemInfoPanelTitleBarMaterial = new THREE.MeshBasicMaterial( { color: 0x000000 } );
selectedItemInfoPanelTitleBar = new THREE.Mesh( selectedItemInfoPanelTitleBarGeometry, selectedItemInfoPanelTitleBarMaterial );
selectedItemInfoPanelTitleBar.position.y = .9;
selectedItemInfoPanelGroup.add(selectedItemInfoPanelTitleBar);

const selectedItemInfoPanelDeleteButtonGeometry = new THREE.PlaneGeometry(.8, .2);
const selectedItemInfoPanelDeleteButtonMaterial = new THREE.MeshBasicMaterial( { color: 0xff001e } );
selectedItemInfoPanelDeleteButton = new THREE.Mesh( selectedItemInfoPanelDeleteButtonGeometry, selectedItemInfoPanelDeleteButtonMaterial );
selectedItemInfoPanelDeleteButton.position.y = .65;
selectedItemInfoPanelGroup.add(selectedItemInfoPanelDeleteButton);

loadFont();

setTimeout(function() {
    if (textFont == "fucking shit")
        throw new Error("Couldn't load the dang font.");
}, 200);
	
function changeInfoPanelTitleText(string)
{
    selectedItemInfoPanelGroup.remove(selectedItemInfoPanelTitleText);
    disposeObject(selectedItemInfoPanelTitleText);
    selectedItemInfoPanelTitleText = createText(string);
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