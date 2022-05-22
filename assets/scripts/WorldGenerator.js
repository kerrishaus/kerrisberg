import * as THREE from '../../../../assets/threejs/build/three.module.js';

import { GameObject } from './GameObjects/GameObject.js'; 
import { Grass } from './GameObjects/Grass.js';

function getRandomInt(min, max) 
{
    min = Math.ceil(min);
    max = Math.floor(max);
    return Math.floor(Math.random() * (max - min) + min); //The maximum is exclusive and the minimum is inclusive
}

function getRandomArbitrary(min, max) 
{
    return Math.random() * (max - min) + min;
}

var worldTerrain = [];

export function generateFloor(width, height)
{
    console.log("generating floor terrain");
    
    var rotation = Math.PI / 2;
    
    var terrain = [];
    
    for (var i = 0; i < width; i++)
    {
        for (var j = 0; j < height; j++)
        {
            var bit = getRandomInt(0, 100);
            
            // 0 - dead grass
            // 1 - normal grass
            // 2 - fertile grass
            // 3 - VERY fertile grass
            
            var color = 0xFFFFFFF;
            
            if (bit > 90)
            {
                // very fertile
                color = 0x49a358;
                bit = 3;
            }
            else if (bit > 60)
            {
                // fertile grass
                color = 0x70a173;
                bit = 2;
            }
            else if (bit > 30)
            {
                // regular grass
                color = 0x81a170;
                bit = 1;
            }
            else if (bit >= 0)
            {
                // dead grass
                color = 0x948f62;
                bit = 0;
            }
            
            const grassObject = new Grass(bit);
            
    		grassObject.object.position.x = i;
    		grassObject.object.position.y = -0.35;
    		grassObject.object.position.z = j;
    		grassObject.object.rotation.x = -rotation;
            
    		terrain.push(grassObject);
		}
    }
    
    return terrain;
}

export function generateResources(width, height, depth)
{
    let resources = [];

    for (var x = 0; x < width; x++)
    {
        for (var z = 0; z < height; z++)
        {
            var bit = getRandomInt(0, 100);
            
            if (bit < 90)
                continue;

			var geometry = new THREE.CylinderGeometry( 0, .4, .7, 4, 1 );
			var material = new THREE.MeshPhongMaterial( { color: 0x077336, flatShading: true } );
			var mesh = new THREE.Mesh( geometry, material );
			mesh.position.x = x;
			mesh.position.y = depth;
			mesh.position.z = z;
			mesh.userData.gameObject = true;
			mesh.userData.type = "tree";
			
			resources.push(mesh);
        }
    }
    
    for (var x = 0; x < width; x++)
    {
        for (var z = 0; z < height; z++)
        {
            var bit = getRandomInt(0, 100);
            
            if (bit < 99)
                continue;

			var geometry = new THREE.BoxGeometry( .2, .3, .4 );
			var material = new THREE.MeshPhongMaterial( { color: 0xcccccc, flatShading: true } );
			var mesh = new THREE.Mesh( geometry, material );
			mesh.position.x = x;
			mesh.position.y = depth;
			mesh.position.z = z;
			mesh.userData.gameObject = true;
			mesh.userData.type = "rock";
			
			resources.push(mesh);
        }
    }
    
    return resources;
}

export function generateWorld(width, height)
{
    console.log("generating world");

    let floor     = generateFloor(width, height);
    //let resources = generateResources(width, height, 0);
    
    //worldTerrain = worldTerrain.concat(floor, resources);
    
    console.log("world is ready");
    return floor;
}

export function addWorldToScene(scene, world)
{
    for (var i = 0; i < world.length; i++)
        scene.add(world[i].object);
}