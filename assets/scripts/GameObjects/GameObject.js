import * as THREE from '../../../../../assets/threejs/build/three.module.js';

export class GameObject
{
    constructor(object)
    {
        this.object = object;
    }
    
    onMouseStartHover()
    {
        console.log("hover start");
    }
    
    onMouseStopHover()
    {
        console.log("hover stop");
    }
}