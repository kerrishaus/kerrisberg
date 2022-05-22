import * as THREE from '../../../../../assets/threejs/build/three.module.js';

import { GameObject } from './GameObject.js'; 

function getRandomInt(min, max)
{
    min = Math.ceil(min);
    max = Math.floor(max);
    return Math.floor(Math.random() * (max - min) + min); //The maximum is exclusive and the minimum is inclusive
}

export class Grass extends GameObject
{
    constructor(fertility)
    {
        var color = 0xFFFFFFF;
        
        if (fertility > 90)
        {
            // very fertile
            color = 0x49a358;
            fertility = 3;
        }
        else if (fertility > 60)
        {
            // fertile grass
            color = 0x70a173;
            fertility = 2;
        }
        else if (fertility > 30)
        {
            // regular grass
            color = 0x81a170;
            fertility = 1;
        }
        else if (fertility >= 0)
        {
            // dead grass
            color = 0x948f62;
            fertility = 0;
        }
    
		const geometry = new THREE.PlaneGeometry(1, 1);
		const material = new THREE.MeshLambertMaterial( { color: color } );
		const cube = new THREE.Mesh( geometry, material );
		
		super(cube);
		
        this.fertility = fertility;
    }

    getFertility()
    {
        return this.fertility;
    }

    onMouseStartHover()
    {
        console.log("Mouse hover grass start.");
    }
    
    onMouseStopHover()
    {
        console.log("Mouse hover grass stop.");    
    }
}