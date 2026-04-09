class BuildEditor {
    constructor() {
        this.gridSize = 1;
        this.gridUnits = 20;
        this.currentFloor = 1;
        this.previewRotation = 0;
        this.parts = new Map();
    }
    
    snapToEdge(x, z) {
        const gs = this.gridSize;
        const snapXLine = Math.round(x / gs) * gs;
        const distToVertEdge = Math.abs(x - snapXLine);
        const snapZLine = Math.round(z / gs) * gs;
        const distToHorizEdge = Math.abs(z - snapZLine);
        let snapX, snapZ, isVertical;
        
        if (distToVertEdge <= distToHorizEdge) {
            snapX = snapXLine;
            snapZ = Math.floor(z / gs) * gs + gs / 2;
            isVertical = true;
        } else {
            snapX = Math.floor(x / gs) * gs + gs / 2;
            snapZ = snapZLine;
            isVertical = false;
        }
        return { x: snapX, z: snapZ, isVertical };
    }

    snapToCenter(x, z) {
        const gs = this.gridSize;
        let snapX = Math.floor(x / gs) * gs + gs / 2;
        let snapZ = Math.floor(z / gs) * gs + gs / 2;
        snapX = Math.max(gs / 2, Math.min(this.gridUnits - gs / 2, snapX));
        snapZ = Math.max(gs / 2, Math.min(this.gridUnits - gs / 2, snapZ));
        return { x: snapX, z: snapZ };
    }

    isCellOccupied(x, z, floorNumber) {
        return false;
    }

    isWithinBounds(x, z) {
        return x >= 0 && x <= this.gridUnits && z >= 0 && z <= this.gridUnits;
    }

    calculatePlacementPosition(preset, rawPoint) {
        const floorHeight = (this.currentFloor - 1) * 3;
        let x = rawPoint.x;
        let z = rawPoint.z;
        let y = 0;
        let rotation = this.previewRotation;
        let isValid = true;
        let wallFound = null;
        
        if (preset.type === 'wall') {
            const snapped = this.snapToEdge(x, z);
            x = snapped.x;
            z = snapped.z;
            y = floorHeight + preset.default_height / 2;
            
            if (this.previewRotation % 90 === 0) {
                if (snapped.isVertical) {
                    rotation = 90;
                } else {
                    rotation = 0;
                }
            }
            if (this.isCellOccupied(x, z, this.currentFloor)) isValid = false;
        } else if (preset.type === 'floor') {
            const snapped = this.snapToCenter(x, z);
            x = snapped.x;
            z = snapped.z;
            y = floorHeight + preset.default_height / 2;
            if (this.isCellOccupied(x, z, this.currentFloor)) isValid = false;
        }
        
        if (!this.isWithinBounds(x, z)) isValid = false;
        
        return { x, y, z, rotation, isValid, wallFound };
    }
}

const editor = new BuildEditor();
const presetWall = { type: 'wall', default_height: 3, default_width: 1, default_depth: 0.2 };
console.log("WALL 1:", editor.calculatePlacementPosition(presetWall, {x: 10.4, y: 0, z: 10.1}));
console.log("WALL 2:", editor.calculatePlacementPosition(presetWall, {x: 0, y: 0, z: 0.5}));
console.log("WALL 3:", editor.calculatePlacementPosition(presetWall, {x: 20, y: 0, z: 19.5}));
console.log("WALL 4:", editor.calculatePlacementPosition(presetWall, {x: 20, y: 0, z: 19.9}));

const presetFloor = { type: 'floor', default_height: 0.05, default_width: 1, default_depth: 1 };
console.log("FLOOR 1:", editor.calculatePlacementPosition(presetFloor, {x: 10.4, y: 0, z: 10.1}));
