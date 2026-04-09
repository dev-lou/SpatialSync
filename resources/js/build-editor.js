// ConstructHub - Three.js Build Editor
// Bloxburg 2026 Build Mode — Full Feature Set
// UPDATED: 2026-04-09

const DEBUG_MODE = false;

class BuildEditor {
    constructor(container, buildId, csrfToken) {
        if (DEBUG_MODE) console.log('[Editor] Initializing with build ID:', buildId);
        
        this.buildId = buildId;
        this.csrfToken = csrfToken;
        this.container = container;
        
        // State
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.controls = null;
        this.minimap = null;
        
        // Parts
        this.parts = new Map();
        this.currentPreset = null;
        this.selectedPart = null;
        this.selectionOutline = null;
        this.currentFloor = 1;
        this.maxFloors = 10;
        this.roofVisible = true;
        
        // Undo/Redo
        this.undoStack = [];
        this.redoStack = [];
        this.maxUndoSteps = 50;
        
        // Raycasting
        this.raycaster = new THREE.Raycaster();
        this.mouse = new THREE.Vector2();
        this.gridHelper = null;
        this.previewMesh = null;
        this.previewMarker = null;
        
        // UI State
        this.isPlacing = false;
        this.previewRotation = 0;
        
        // EDITOR MODES
        this.paintMode = false;
        this.paintColor = '#6B7280';
        this.materialMode = false;
        this.selectedMaterial = 'default';
        
        // TOOLS (Bloxburg 2026)
        this.currentTool = 'select'; // select, delete, move, clone
        this.isMoving = false;
        this.movingPartId = null;
        
        // Camera movement
        this.cameraSpeed = 0.5;
        this.keysPressed = {};
        
        // Bird's eye view
        this.birdsEyeActive = false;
        this.savedCameraState = null;
        
        // Day/Night
        this.isNightMode = false;
        this.ambientLight = null;
        this.directionalLight = null;
        
        // Dirty flag
        this.hasUnsavedChanges = false;
        
        // Grid settings
        this.gridSize = 1;
        this.gridSizes = [1, 0.5, 0.25];
        this.gridSizeIndex = 0;
        this.snapToGrid = true;
        this.gridUnits = 20;
        this.gridHalfSize = 10;
        this.minBound = 0.5;
        this.maxBound = 19.5;
        
        // Platform mesh
        this.platform = null;
        this.platformEdges = null;
        
        // Object types
        this.EDGE_PLACED_TYPES = ['wall'];
        this.CENTER_PLACED_TYPES = ['floor', 'roof', 'stairs'];
        this.WALL_ATTACHED_TYPES = ['door', 'window'];
        
        // API endpoints
        this.api = {
            parts: `/api/builds/${buildId}/parts`,
            build: `/api/builds/${buildId}`,
        };
        
        if (DEBUG_MODE) console.log('[Editor] Calling init...');
        this.init();
    }
    
    async init() {
        if (DEBUG_MODE) console.log('[Editor] init() called');
        
        await this.setupScene();
        this.setupEventListeners();
        
        if (DEBUG_MODE) console.log('[Editor] Loading parts...');
        await this.loadParts();
        
        if (DEBUG_MODE) console.log('[Editor] Creating minimap...');
        this.createMinimap();
        
        this.animate();
        
        this.updateDebugInfo('Ready — Select a part to start building!');
        
        if (DEBUG_MODE) console.log('[Editor] Initialization complete');
    }
    
    // ============ DISPOSAL HELPERS ============
    
    disposeGroup(obj) {
        if (!obj) return;
        
        // If it's a group, recurse into children
        if (obj.children && obj.children.length > 0) {
            // Copy array since we modify it
            const children = [...obj.children];
            children.forEach(child => this.disposeGroup(child));
        }
        
        // Dispose geometry
        if (obj.geometry) {
            obj.geometry.dispose();
        }
        
        // Dispose material(s)
        if (obj.material) {
            if (Array.isArray(obj.material)) {
                obj.material.forEach(m => {
                    if (m.map) m.map.dispose();
                    m.dispose();
                });
            } else {
                if (obj.material.map) obj.material.map.dispose();
                obj.material.dispose();
            }
        }
    }
    
    // ============ GRID HELPERS ============
    
    snapToEdge(x, z) {
        const gs = this.gridSize;
        
        // Find distance to nearest vertical line (multiples of gs)
        const snapXLine = Math.round(x / gs) * gs;
        const distToVertEdge = Math.abs(x - snapXLine);
        
        // Find distance to nearest horizontal line (multiples of gs)
        const snapZLine = Math.round(z / gs) * gs;
        const distToHorizEdge = Math.abs(z - snapZLine);
        
        let snapX, snapZ, isVertical;
        
        if (distToVertEdge <= distToHorizEdge) {
            // Snap to vertical edge: X is exactly on the line, Z is halfway between lines
            snapX = snapXLine;
            snapZ = Math.floor(z / gs) * gs + gs / 2;
            isVertical = true;
        } else {
            // Snap to horizontal edge: Z is exactly on the line, X is halfway between lines
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
    
    isWithinBounds(x, z) {
        return x >= 0 && x <= this.gridUnits && z >= 0 && z <= this.gridUnits;
    }
    
    isCellOccupied(x, z, floorNumber, excludePartId = null) {
        for (const [partId, partData] of this.parts) {
            if (excludePartId && partId === excludePartId) continue;
            if (partData.data.floor_number !== floorNumber) continue;
            
            const partX = partData.data.position_x;
            const partZ = partData.data.position_z;
            const partType = partData.data.type;
            
            if (partType === 'wall') {
                if (Math.abs(partX - x) < 0.1 && Math.abs(partZ - z) < 0.1) {
                    return true;
                }
            } else if (partType === 'door' || partType === 'window') {
                if (Math.abs(partX - x) < 0.1 && Math.abs(partZ - z) < 0.1) {
                    return true;
                }
            } else {
                if (Math.abs(partX - x) < this.gridSize * 0.9 && 
                    Math.abs(partZ - z) < this.gridSize * 0.9) {
                    return true;
                }
            }
        }
        return false;
    }
    
    findNearestWall(x, z, floorNumber, maxDistance = 2) {
        let nearestWall = null;
        let nearestDistance = maxDistance;
        
        for (const [partId, partData] of this.parts) {
            if (partData.data.type !== 'wall') continue;
            if (partData.data.floor_number !== floorNumber) continue;
            
            const wallX = partData.data.position_x;
            const wallZ = partData.data.position_z;
            
            const distance = Math.sqrt(
                Math.pow(wallX - x, 2) + Math.pow(wallZ - z, 2)
            );
            
            if (distance < nearestDistance) {
                nearestDistance = distance;
                nearestWall = partData;
            }
        }
        
        return nearestWall;
    }
    
    findWallAtGridEdge(gridX, gridZ, floorNumber) {
        const tolerance = 0.3;
        
        for (const [partId, partData] of this.parts) {
            if (partData.data.type !== 'wall') continue;
            if (partData.data.floor_number !== floorNumber) continue;
            
            const wallX = partData.data.position_x;
            const wallZ = partData.data.position_z;
            
            if (Math.abs(wallX - gridX) < tolerance && Math.abs(wallZ - gridZ) < tolerance) {
                return partData;
            }
        }
        return null;
    }
    
    hasOpeningAtPosition(x, z, floorNumber) {
        const tolerance = 0.3;
        
        for (const [partId, partData] of this.parts) {
            if (partData.data.floor_number !== floorNumber) continue;
            
            const type = partData.data.type;
            if (type !== 'door' && type !== 'window') continue;
            
            if (Math.abs(partData.data.position_x - x) < tolerance &&
                Math.abs(partData.data.position_z - z) < tolerance) {
                return { type, part: partData };
            }
        }
        return null;
    }
    
    // ============ SCENE SETUP ============
    
    async setupScene() {
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0x1e293b);
        
        const aspect = this.container.clientWidth / this.container.clientHeight;
        this.camera = new THREE.PerspectiveCamera(45, aspect, 0.1, 1000);
        this.camera.position.set(25, 22, 25);
        this.camera.lookAt(10, 0, 10);
        
        this.renderer = new THREE.WebGLRenderer({ 
            antialias: true,
            powerPreference: 'high-performance',
        });
        this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        this.renderer.shadowMap.enabled = true;
        this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        
        this.container.appendChild(this.renderer.domElement);
        
        // Lights
        this.ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        this.scene.add(this.ambientLight);
        
        this.directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
        this.directionalLight.position.set(10, 20, 10);
        this.directionalLight.castShadow = true;
        this.directionalLight.shadow.mapSize.width = 1024;
        this.directionalLight.shadow.mapSize.height = 1024;
        this.directionalLight.shadow.camera.near = 0.5;
        this.directionalLight.shadow.camera.far = 50;
        this.directionalLight.shadow.camera.left = -30;
        this.directionalLight.shadow.camera.right = 30;
        this.directionalLight.shadow.camera.top = 30;
        this.directionalLight.shadow.camera.bottom = -30;
        this.scene.add(this.directionalLight);
        
        // Ground plane (invisible, for raycasting)
        const groundGeometry = new THREE.PlaneGeometry(100, 100);
        const groundMaterial = new THREE.MeshBasicMaterial({ visible: false });
        this.ground = new THREE.Mesh(groundGeometry, groundMaterial);
        this.ground.rotation.x = -Math.PI / 2;
        this.ground.position.y = 0;
        this.ground.name = 'ground';
        this.scene.add(this.ground);
        
        // Platform
        this.createPlatform();
        
        // Grid helper
        this.rebuildGrid();
        
        // OrbitControls
        if (typeof THREE.OrbitControls !== 'undefined') {
            this.controls = new THREE.OrbitControls(this.camera, this.renderer.domElement);
            this.controls.enableDamping = true;
            this.controls.dampingFactor = 0.05;
            this.controls.minDistance = 5;
            this.controls.maxDistance = 50;
            this.controls.maxPolarAngle = Math.PI / 2 - 0.05;
            this.controls.target.set(10, 0, 10);
            this.controls.enablePan = true;
            this.controls.mouseButtons = {
                LEFT: null, // We handle left click ourselves
                MIDDLE: THREE.MOUSE.DOLLY,
                RIGHT: THREE.MOUSE.PAN
            };
        }
        
        window.addEventListener('resize', () => this.onWindowResize());
    }
    
    rebuildGrid() {
        if (this.gridHelper) {
            this.scene.remove(this.gridHelper);
            this.gridHelper.geometry.dispose();
            this.gridHelper.material.dispose();
        }
        
        const divisions = this.gridUnits / this.gridSize;
        this.gridHelper = new THREE.GridHelper(this.gridUnits, divisions, 0x64748B, 0x475569);
        this.gridHelper.position.set(10, 0.16, 10);
        this.scene.add(this.gridHelper);
    }
    
    createPlatform() {
        if (this.platform) {
            this.scene.remove(this.platform);
            this.platform.geometry.dispose();
            this.platform.material.dispose();
        }
        
        const platformGeometry = new THREE.BoxGeometry(this.gridUnits, 0.3, this.gridUnits);
        const platformMaterial = new THREE.MeshStandardMaterial({
            color: 0xffffff,
            transparent: true,
            opacity: 0,
        });
        this.platform = new THREE.Mesh(platformGeometry, platformMaterial);
        this.platform.position.set(10, 0, 10);
        this.platform.name = 'platform';
        this.scene.add(this.platform);
    }
    
    // ============ PARTS LOADING ============
    
    async loadParts() {
        try {
            const response = await fetch(this.api.parts, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const parts = await response.json();
            
            parts.forEach(partData => {
                this.addPartToScene(partData, false);
            });
            
            this.updateDebugInfo(`Loaded ${parts.length} parts — Click a part to start building`);
            this.emitPartCount();
        } catch (error) {
            console.error('[Editor] Error loading parts:', error);
            this.updateDebugInfo('Error loading parts — check console');
            this.showToastEvent('Could not load parts. Try refreshing.', 'error');
        }
    }
    
    // ============ PART CREATION (MESH FACTORY) ============
    
    createWallMesh(width, height, depth, colorFront) {
        const wallGeo = new THREE.BoxGeometry(width, height, depth);
        const wallMat = new THREE.MeshStandardMaterial({ 
            color: colorFront, 
            roughness: 0.7,
            metalness: 0.1,
            side: THREE.DoubleSide
        });
        const wall = new THREE.Mesh(wallGeo, wallMat);
        wall.castShadow = true;
        wall.receiveShadow = true;
        return wall;
    }
    
    createFloorMesh(width, height, depth, color, variant) {
        const geo = new THREE.BoxGeometry(width, height, depth);
        const matProps = { color, roughness: 0.7, metalness: 0.1 };
        
        switch (variant) {
            case 'tile': matProps.roughness = 0.3; break;
            case 'hardwood': matProps.roughness = 0.9; matProps.metalness = 0; break;
            case 'carpet': matProps.roughness = 1.0; matProps.metalness = 0; break;
            case 'concrete': matProps.roughness = 0.85; matProps.metalness = 0; break;
            case 'marble': matProps.roughness = 0.2; matProps.metalness = 0.1; break;
        }
        
        const mesh = new THREE.Mesh(geo, new THREE.MeshStandardMaterial(matProps));
        mesh.castShadow = true;
        mesh.receiveShadow = true;
        return mesh;
    }
    
    createDoorMesh(width, height, depth, color) {
        const group = new THREE.Group();
        const doorDepth = 0.3;
        
        const frameMat = new THREE.MeshStandardMaterial({ color: 0x5c4033, roughness: 0.6 });
        const doorMat = new THREE.MeshStandardMaterial({ color, roughness: 0.7 });
        const handleMat = new THREE.MeshStandardMaterial({ color: 0xc0c0c0, metalness: 0.8, roughness: 0.2 });
        
        const frameWidth = 0.1;
        const leftFrame = new THREE.Mesh(new THREE.BoxGeometry(frameWidth, height, doorDepth), frameMat);
        leftFrame.position.x = -width / 2 + frameWidth / 2;
        leftFrame.castShadow = true;
        group.add(leftFrame);
        
        const rightFrame = new THREE.Mesh(new THREE.BoxGeometry(frameWidth, height, doorDepth), frameMat);
        rightFrame.position.x = width / 2 - frameWidth / 2;
        rightFrame.castShadow = true;
        group.add(rightFrame);
        
        const topFrame = new THREE.Mesh(new THREE.BoxGeometry(width, frameWidth, doorDepth), frameMat);
        topFrame.position.y = height / 2 - frameWidth / 2;
        topFrame.castShadow = true;
        group.add(topFrame);
        
        const door = new THREE.Mesh(
            new THREE.BoxGeometry(width - frameWidth * 2 - 0.02, height - frameWidth - 0.02, doorDepth - 0.05),
            doorMat
        );
        door.castShadow = true;
        door.receiveShadow = true;
        group.add(door);
        
        const handleFront = new THREE.Mesh(new THREE.SphereGeometry(0.05, 8, 8), handleMat);
        handleFront.position.set(width / 4, 0, doorDepth / 2 + 0.02);
        group.add(handleFront);
        
        const handleBack = new THREE.Mesh(new THREE.SphereGeometry(0.05, 8, 8), handleMat);
        handleBack.position.set(width / 4, 0, -doorDepth / 2 - 0.02);
        group.add(handleBack);
        
        return group;
    }
    
    createWindowMesh(width, height, depth, color) {
        const group = new THREE.Group();
        const windowDepth = 0.3;
        
        const frameMat = new THREE.MeshStandardMaterial({ color: 0xffffff, roughness: 0.5 });
        const frame = new THREE.Mesh(new THREE.BoxGeometry(width, height, windowDepth), frameMat);
        frame.castShadow = true;
        group.add(frame);
        
        const glassMat = new THREE.MeshPhysicalMaterial({
            color: 0x87ceeb,
            transparent: true,
            opacity: 0.4,
            roughness: 0.1,
            metalness: 0.1,
        });
        const glass = new THREE.Mesh(
            new THREE.BoxGeometry(width - 0.16, height - 0.16, windowDepth - 0.05),
            glassMat
        );
        glass.receiveShadow = true;
        group.add(glass);
        
        return group;
    }
    
    createRoofMesh(width, height, depth, color, variant) {
        const group = new THREE.Group();
        
        if (variant === 'peaked') {
            const shape = new THREE.Shape();
            shape.moveTo(-width / 2, 0);
            shape.lineTo(0, height);
            shape.lineTo(width / 2, 0);
            shape.lineTo(-width / 2, 0);
            
            const geometry = new THREE.ExtrudeGeometry(shape, {
                steps: 1, depth: depth, bevelEnabled: false,
            });
            const material = new THREE.MeshStandardMaterial({ color, roughness: 0.8 });
            const roof = new THREE.Mesh(geometry, material);
            roof.rotation.y = Math.PI / 2;
            roof.position.z = depth / 2;
            roof.castShadow = true;
            roof.receiveShadow = true;
            group.add(roof);
        } else {
            const roof = new THREE.Mesh(
                new THREE.BoxGeometry(width, height, depth),
                new THREE.MeshStandardMaterial({ color, roughness: 0.7 })
            );
            roof.castShadow = true;
            roof.receiveShadow = true;
            group.add(roof);
        }
        
        return group;
    }
    
    createStairsMesh(width, height, depth, color) {
        const group = new THREE.Group();
        
        const stepCount = 6;
        const stepHeight = height / stepCount;
        const stepDepth = depth / stepCount;
        const stepWidth = width - 0.1;
        
        const stepMat = new THREE.MeshStandardMaterial({ color, roughness: 0.7 });
        const sideMat = new THREE.MeshStandardMaterial({ color: 0x4a4a4a, roughness: 0.8 });
        
        for (let i = 0; i < stepCount; i++) {
            const step = new THREE.Mesh(
                new THREE.BoxGeometry(stepWidth, stepHeight * 0.9, stepDepth - 0.02), stepMat
            );
            step.position.x = 0.05;
            step.position.y = -height / 2 + stepHeight * (i + 0.5);
            step.position.z = -depth / 2 + stepDepth * (i + 0.5);
            step.castShadow = true;
            step.receiveShadow = true;
            group.add(step);
        }
        
        const railGeo = new THREE.BoxGeometry(0.1, height + 0.5, depth);
        const leftRail = new THREE.Mesh(railGeo, sideMat);
        leftRail.position.set(-width / 2 + 0.05, 0.25, 0);
        leftRail.castShadow = true;
        group.add(leftRail);
        
        const rightRail = new THREE.Mesh(railGeo.clone(), sideMat);
        rightRail.position.set(width / 2 - 0.05, 0.25, 0);
        rightRail.castShadow = true;
        group.add(rightRail);
        
        return group;
    }
    
    createPartMesh(partData) {
        const { type, variant, width, height, depth } = partData;
        const color = partData.color || '#6B7280';
        const colorFront = partData.color_front || color;
        
        switch (type) {
            case 'wall': return this.createWallMesh(width, height, depth, colorFront);
            case 'door': return this.createDoorMesh(width, height, depth, color);
            case 'window': return this.createWindowMesh(width, height, depth, color);
            case 'roof': return this.createRoofMesh(width, height, depth, color, variant);
            case 'stairs': return this.createStairsMesh(width, height, depth, color);
            default: return this.createFloorMesh(width, height, depth, color, variant);
        }
    }
    
    // ============ ADD PART TO SCENE ============
    
    addPartToScene(partData, save = true) {
        const tempId = partData.id || `temp_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        partData.id = tempId;
        
        const mesh = this.createPartMesh(partData);
        
        mesh.position.set(partData.position_x, partData.position_y, partData.position_z);
        mesh.rotation.y = (partData.rotation_y || 0) * Math.PI / 180;
        
        mesh.userData = {
            id: tempId,
            type: partData.type,
            variant: partData.variant,
            width: partData.width,
            height: partData.height,
            depth: partData.depth,
            color: partData.color,
            color_front: partData.color_front || partData.color,
            color_back: partData.color_back || partData.color,
            material: partData.material || 'default',
            floor_number: partData.floor_number || 1,
        };
        
        this.scene.add(mesh);
        this.parts.set(tempId, { mesh, data: partData });
        
        if (save) {
            this.hasUnsavedChanges = true;
            this.savePartToAPI(mesh, tempId);
        }
        
        return mesh;
    }
    
    async savePartToAPI(mesh, tempId) {
        const data = {
            type: mesh.userData.type,
            variant: mesh.userData.variant,
            position_x: mesh.position.x,
            position_y: mesh.position.y,
            position_z: mesh.position.z,
            width: mesh.userData.width,
            height: mesh.userData.height,
            depth: mesh.userData.depth,
            rotation_y: Math.round(mesh.rotation.y * 180 / Math.PI),
            color: mesh.userData.color,
            color_front: mesh.userData.color_front,
            color_back: mesh.userData.color_back,
            material: mesh.userData.material,
            floor_number: mesh.userData.floor_number,
        };
        
        try {
            const response = await fetch(this.api.parts, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify(data),
            });
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('[Editor] API Error:', response.status, errorText);
                this.showToastEvent(`Save failed (${response.status})`, 'error');
                return null;
            }
            
            const savedPart = await response.json();
            
            if (tempId && this.parts.has(tempId)) {
                this.parts.delete(tempId);
            }
            
            mesh.userData.id = savedPart.id;
            savedPart.position_x = mesh.position.x;
            savedPart.position_y = mesh.position.y;
            savedPart.position_z = mesh.position.z;
            savedPart.rotation_y = Math.round(mesh.rotation.y * 180 / Math.PI);
            this.parts.set(savedPart.id, { mesh, data: savedPart });
            
            return savedPart;
        } catch (error) {
            console.error('[Editor] Network error saving part:', error);
            this.showToastEvent('Network error — part not saved', 'error');
            return null;
        }
    }
    
    async updatePartAPI(partId, data) {
        try {
            const response = await fetch(`${this.api.parts}/${partId}`, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify(data),
            });
            if (!response.ok) {
                console.error('[Editor] Update failed:', response.status);
            }
        } catch (error) {
            console.error('[Editor] Error updating part:', error);
        }
    }
    
    async deletePartAPI(partId) {
        try {
            const response = await fetch(`${this.api.parts}/${partId}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
            });
            if (!response.ok) {
                console.error('[Editor] Delete failed:', response.status);
            }
        } catch (error) {
            console.error('[Editor] Error deleting part:', error);
        }
    }
    
    deletePartFromScene(partId) {
        const partData = this.parts.get(partId);
        if (!partData) return false;
        
        if (partData.mesh) {
            this.scene.remove(partData.mesh);
            this.disposeGroup(partData.mesh);
        }
        
        this.parts.delete(partId);
        return true;
    }
    
    // ============ TOAST HELPER ============
    
    showToastEvent(message, type = 'success') {
        window.dispatchEvent(new CustomEvent('toast', { 
            detail: { message, type } 
        }));
    }
    
    emitPartCount() {
        window.dispatchEvent(new CustomEvent('part-placed', { detail: { count: this.parts.size } }));
    }
    
    // ============ SELECT PRESET (from panel) ============
    
    selectPreset(preset) {
        // Exit any edit modes
        this.exitPaintMode();
        this.exitMaterialMode();
        this.deselectPart();
        this.setTool('select');
        
        this.currentPreset = preset;
        this.isPlacing = true;
        this.previewRotation = 0;
        
        this.container.style.cursor = 'crosshair';
        this.updateDebugInfo(`Placing: ${preset.name} — Click to place, R rotate, Q cancel`);
        
        window.dispatchEvent(new CustomEvent('preset-selected', { detail: { preset } }));
    }
    
    // ============ CALCULATE PLACEMENT POSITION ============
    // Shared between showPreview() and placePart() to guarantee they match
    
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
            
            // Auto-rotate based on nearest edge
            if (this.previewRotation % 90 === 0) {
                if (snapped.isVertical) {
                    rotation = (this.previewRotation === 90 || this.previewRotation === 270) ? this.previewRotation : 90;
                } else {
                    rotation = (this.previewRotation === 0 || this.previewRotation === 180) ? this.previewRotation : 0;
                }
            }
            
            if (this.isCellOccupied(x, z, this.currentFloor)) {
                isValid = false;
            }
            
        } else if (preset.type === 'floor') {
            const snapped = this.snapToCenter(x, z);
            x = snapped.x;
            z = snapped.z;
            y = floorHeight + preset.default_height / 2;
            if (this.isCellOccupied(x, z, this.currentFloor)) isValid = false;
            
        } else if (preset.type === 'roof') {
            const snapped = this.snapToCenter(x, z);
            x = snapped.x;
            z = snapped.z;
            y = floorHeight + 2.8 + preset.default_height / 2;
            if (this.isCellOccupied(x, z, this.currentFloor)) isValid = false;
            
        } else if (preset.type === 'stairs') {
            const snapped = this.snapToCenter(x, z);
            x = snapped.x;
            z = snapped.z;
            y = floorHeight + preset.default_height / 2;
            if (this.isCellOccupied(x, z, this.currentFloor)) isValid = false;
            
        } else if (preset.type === 'door' || preset.type === 'window') {
            const snapped = this.snapToEdge(x, z);
            wallFound = this.findWallAtGridEdge(snapped.x, snapped.z, this.currentFloor);
            
            if (!wallFound) {
                const snapped = this.snapToEdge(x, z); // Use standard edge
                x = snapped.x;
                z = snapped.z;
                
                // Doors/windows should face outward, so rotation is perpendicular to wall
                if (this.previewRotation % 90 === 0) {
                    if (snapped.isVertical) {
                        rotation = (this.previewRotation === 90 || this.previewRotation === 270) ? this.previewRotation : 90;
                    } else {
                        rotation = (this.previewRotation === 0 || this.previewRotation === 180) ? this.previewRotation : 0;
                    }
                }
                
                isValid = false;
                
                if (preset.type === 'door') {
                    y = floorHeight + preset.default_height / 2;
                } else {
                    y = floorHeight + 2.2;
                }
            } else {
                x = wallFound.data.position_x;
                z = wallFound.data.position_z;
                rotation = wallFound.data.rotation_y || 0;
                
                const existingOpening = this.hasOpeningAtPosition(x, z, this.currentFloor);
                if (existingOpening) isValid = false;
                
                if (preset.type === 'door') {
                    y = floorHeight + preset.default_height / 2;
                } else {
                    y = floorHeight + 2.2;
                }
            }
        }
        
        if (!this.isWithinBounds(x, z)) isValid = false;
        
        return { x, y, z, rotation, isValid, wallFound };
    }
    
    // ============ PLACE PART ============
    
    placePart(point) {
        if (!this.currentPreset) return null;
        
        const preset = this.currentPreset;
        const pos = this.calculatePlacementPosition(preset, point);
        
        if (!pos.isValid) {
            if (this.WALL_ATTACHED_TYPES.includes(preset.type) && !pos.wallFound) {
                this.showToastEvent('No wall here! Build a wall first.', 'error');
            }
            return null;
        }
        
        let width = preset.default_width;
        let depth = preset.default_depth;
        
        if (['wall', 'floor', 'roof', 'stairs'].includes(preset.type)) {
            width *= this.gridSize;
            if (preset.type !== 'wall') depth *= this.gridSize;
        }
        
        const partData = {
            type: preset.type,
            variant: preset.variant,
            position_x: pos.x,
            position_y: pos.y,
            position_z: pos.z,
            width: width,
            height: preset.default_height,
            depth: depth,
            rotation_y: pos.rotation,
            color: preset.default_color,
            color_front: preset.default_color,
            color_back: preset.default_color,
            material: 'default',
            floor_number: this.currentFloor,
        };
        
        const mesh = this.addPartToScene(partData);
        
        if (mesh && mesh.userData.id) {
            this.saveUndoState('add', { id: mesh.userData.id, ...partData });
        }
        
        this.emitPartCount();
        this.updateDebugInfo(`Placed ${preset.name}! Click again or Q to cancel.`);
        
        return mesh;
    }
    
    // ============ PREVIEW SYSTEM ============
    
    showPreview(preset, intersectPoint) {
        if (!intersectPoint) return;
        this.hidePreview();
        
        const pos = this.calculatePlacementPosition(preset, intersectPoint);
        
        let previewColor = pos.isValid ? 0x22C55E : 0xEF4444;
        
        if (pos.isValid && preset.default_color) {
            if (typeof preset.default_color === 'string') {
                previewColor = parseInt(preset.default_color.replace('#', ''), 16);
            }
        }
        
        let width = preset.default_width || 1;
        const height = preset.default_height || 3;
        let depth = preset.default_depth || 0.2;
        
        if (['wall', 'floor', 'roof', 'stairs'].includes(preset.type)) {
            width *= this.gridSize;
            if (preset.type !== 'wall') depth *= this.gridSize;
        }
        
        const geometry = new THREE.BoxGeometry(width, height, depth);
        const material = new THREE.MeshBasicMaterial({
            color: previewColor,
            transparent: true,
            opacity: 0.6,
            side: THREE.DoubleSide,
            depthWrite: false
        });
        
        this.previewMesh = new THREE.Mesh(geometry, material);
        this.previewMesh.position.set(pos.x, pos.y, pos.z);
        this.previewMesh.rotation.y = pos.rotation * (Math.PI / 180);
        this.previewMesh.name = 'preview';
        this.previewMesh.userData.isValid = pos.isValid;
        this.scene.add(this.previewMesh);
        
        // Ground marker
        const markerGeo = new THREE.CylinderGeometry(0.2, 0.2, 0.05, 16);
        const markerMat = new THREE.MeshBasicMaterial({ 
            color: pos.isValid ? 0x22C55E : 0xEF4444,
            transparent: true, opacity: 0.9
        });
        this.previewMarker = new THREE.Mesh(markerGeo, markerMat);
        this.previewMarker.position.set(pos.x, 0.025, pos.z);
        this.scene.add(this.previewMarker);
    }
    
    hidePreview() {
        if (this.previewMesh) {
            this.scene.remove(this.previewMesh);
            this.previewMesh.geometry.dispose();
            this.previewMesh.material.dispose();
            this.previewMesh = null;
        }
        if (this.previewMarker) {
            this.scene.remove(this.previewMarker);
            this.previewMarker.geometry.dispose();
            this.previewMarker.material.dispose();
            this.previewMarker = null;
        }
    }
    
    // ============ SELECTION SYSTEM ============
    
    selectPart(partId) {
        this.deselectPart();
        
        const partData = this.parts.get(partId);
        if (!partData) return;
        
        this.selectedPart = partId;
        
        // Create selection outline using EdgesGeometry
        const mesh = partData.mesh;
        let targetGeo;
        
        if (mesh.geometry) {
            targetGeo = mesh.geometry;
        } else if (mesh.children && mesh.children.length > 0) {
            // For groups, create a bounding box outline
            const box = new THREE.Box3().setFromObject(mesh);
            const size = new THREE.Vector3();
            box.getSize(size);
            targetGeo = new THREE.BoxGeometry(size.x + 0.05, size.y + 0.05, size.z + 0.05);
        }
        
        if (targetGeo) {
            const edges = new THREE.EdgesGeometry(targetGeo);
            const lineMat = new THREE.LineBasicMaterial({ color: 0x3B82F6, linewidth: 2 });
            this.selectionOutline = new THREE.LineSegments(edges, lineMat);
            this.selectionOutline.position.copy(mesh.position);
            this.selectionOutline.rotation.copy(mesh.rotation);
            this.selectionOutline.name = 'selection-outline';
            this.scene.add(this.selectionOutline);
        }
        
        // Show properties panel event
        window.dispatchEvent(new CustomEvent('part-selected', { 
            detail: { 
                id: partId, 
                type: partData.data.type, 
                variant: partData.data.variant,
                color: partData.data.color,
            } 
        }));
        
        this.updateDebugInfo(`Selected: ${partData.data.type} — G delete, T move, C clone`);
    }
    
    deselectPart() {
        this.selectedPart = null;
        
        if (this.selectionOutline) {
            this.scene.remove(this.selectionOutline);
            this.selectionOutline.geometry.dispose();
            this.selectionOutline.material.dispose();
            this.selectionOutline = null;
        }
        
        window.dispatchEvent(new CustomEvent('part-deselected'));
    }
    
    // ============ TOOLS (Bloxburg 2026) ============
    
    setTool(tool) {
        this.currentTool = tool;
        this.isMoving = false;
        this.movingPartId = null;
        
        const cursors = {
            select: 'default',
            delete: 'crosshair',
            move: 'grab',
            clone: 'copy',
        };
        
        this.container.style.cursor = cursors[tool] || 'default';
        
        window.dispatchEvent(new CustomEvent('tool-changed', { detail: { tool } }));
        
        const labels = { select: 'Select', delete: 'Delete', move: 'Move', clone: 'Clone' };
        this.updateDebugInfo(`Tool: ${labels[tool] || tool} — Click on a part`);
    }
    
    // DELETE TOOL
    deletePart(partId) {
        const partData = this.parts.get(partId);
        if (!partData) return;
        
        // Save for undo
        this.saveUndoState('delete', { ...partData.data });
        
        // Remove from scene
        this.deletePartFromScene(partId);
        
        // Remove from API
        if (typeof partId === 'number') {
            this.deletePartAPI(partId);
        }
        
        this.deselectPart();
        this.hasUnsavedChanges = true;
        this.emitPartCount();
        this.showToastEvent('Part deleted', 'info');
        this.updateDebugInfo('Part deleted — Ctrl+Z to undo');
    }
    
    // MOVE TOOL
    startMove(partId) {
        const partData = this.parts.get(partId);
        if (!partData) return;
        
        this.isMoving = true;
        this.movingPartId = partId;
        this.container.style.cursor = 'grabbing';
        
        // Make part semi-transparent while moving
        const setOpacity = (obj) => {
            if (obj.material) {
                obj.material.transparent = true;
                obj.material.opacity = 0.5;
            }
            if (obj.children) obj.children.forEach(setOpacity);
        };
        setOpacity(partData.mesh);
        
        this.updateDebugInfo('Moving — Click to place, Q to cancel');
    }
    
    finishMove(point) {
        if (!this.isMoving || !this.movingPartId) return;
        
        const partData = this.parts.get(this.movingPartId);
        if (!partData) return;
        
        const preset = {
            type: partData.data.type,
            variant: partData.data.variant,
            default_width: partData.data.width,
            default_height: partData.data.height,
            default_depth: partData.data.depth,
            default_color: partData.data.color,
        };
        
        const pos = this.calculatePlacementPosition(preset, point);
        
        if (!pos.isValid) {
            this.showToastEvent('Can\'t place here', 'error');
            return;
        }
        
        // Save old position for undo
        const oldData = { ...partData.data };
        
        // Update position
        partData.mesh.position.set(pos.x, pos.y, pos.z);
        partData.mesh.rotation.y = pos.rotation * Math.PI / 180;
        partData.data.position_x = pos.x;
        partData.data.position_y = pos.y;
        partData.data.position_z = pos.z;
        partData.data.rotation_y = pos.rotation;
        
        // Restore opacity
        const restoreOpacity = (obj) => {
            if (obj.material) {
                obj.material.transparent = false;
                obj.material.opacity = 1;
            }
            if (obj.children) obj.children.forEach(restoreOpacity);
        };
        restoreOpacity(partData.mesh);
        
        // Update API
        if (typeof this.movingPartId === 'number') {
            this.updatePartAPI(this.movingPartId, {
                position_x: pos.x,
                position_y: pos.y,
                position_z: pos.z,
                rotation_y: pos.rotation,
            });
        }
        
        this.saveUndoState('move', { id: this.movingPartId, oldData, newData: { ...partData.data } });
        
        this.isMoving = false;
        this.movingPartId = null;
        this.container.style.cursor = 'grab';
        this.hasUnsavedChanges = true;
        this.showToastEvent('Part moved', 'success');
        this.updateDebugInfo('Part moved');
    }
    
    cancelMove() {
        if (!this.isMoving || !this.movingPartId) return;
        
        const partData = this.parts.get(this.movingPartId);
        if (partData) {
            const restoreOpacity = (obj) => {
                if (obj.material) {
                    obj.material.transparent = false;
                    obj.material.opacity = 1;
                }
                if (obj.children) obj.children.forEach(restoreOpacity);
            };
            restoreOpacity(partData.mesh);
        }
        
        this.isMoving = false;
        this.movingPartId = null;
        this.container.style.cursor = 'grab';
        this.updateDebugInfo('Move cancelled');
    }
    
    // CLONE TOOL
    clonePart(partId) {
        const partData = this.parts.get(partId);
        if (!partData) return;
        
        // Create a clone preset
        this.currentPreset = {
            id: null,
            name: `${partData.data.type} (clone)`,
            type: partData.data.type,
            variant: partData.data.variant,
            default_width: partData.data.width,
            default_height: partData.data.height,
            default_depth: partData.data.depth,
            default_color: partData.data.color,
        };
        this.isPlacing = true;
        this.previewRotation = partData.data.rotation_y || 0;
        
        this.deselectPart();
        this.container.style.cursor = 'crosshair';
        this.updateDebugInfo(`Cloning ${partData.data.type} — Click to place`);
    }
    
    // ============ PAINT MODE ============
    
    enterPaintMode() {
        this.isPlacing = false;
        this.currentPreset = null;
        this.hidePreview();
        this.exitMaterialMode();
        this.setTool('select');
        
        this.paintMode = true;
        this.container.style.cursor = 'pointer';
        this.updateDebugInfo('PAINT MODE — Click to paint, Exit to stop');
        
        window.dispatchEvent(new CustomEvent('paint-mode-changed', { detail: { active: true } }));
    }
    
    exitPaintMode() {
        if (!this.paintMode) return;
        this.paintMode = false;
        this.paintColor = '#6B7280';
        this.container.style.cursor = 'default';
        this.updateDebugInfo('Paint mode exited');
        window.dispatchEvent(new CustomEvent('paint-mode-changed', { detail: { active: false } }));
    }
    
    setPaintColor(color) {
        this.paintColor = color;
    }
    
    // ============ MATERIAL MODE ============
    
    enterMaterialMode() {
        this.isPlacing = false;
        this.currentPreset = null;
        this.hidePreview();
        this.exitPaintMode();
        this.setTool('select');
        
        this.materialMode = true;
        this.container.style.cursor = 'pointer';
        this.updateDebugInfo('MATERIAL MODE — Click to apply, Exit to stop');
        window.dispatchEvent(new CustomEvent('material-mode-changed', { detail: { active: true } }));
    }
    
    exitMaterialMode() {
        if (!this.materialMode) return;
        this.materialMode = false;
        this.selectedMaterial = 'default';
        this.container.style.cursor = 'default';
        this.updateDebugInfo('Material mode exited');
        window.dispatchEvent(new CustomEvent('material-mode-changed', { detail: { active: false } }));
    }
    
    setMaterial(material) {
        this.selectedMaterial = material;
    }
    
    // ============ APPLY PAINT/MATERIAL ============
    
    applyPaintToPart(mesh, color) {
        mesh.userData.color = color;
        mesh.userData.color_front = color;
        mesh.userData.color_back = color;
        
        this.updatePartAPI(mesh.userData.id, { color, color_front: color, color_back: color });
        this.hasUnsavedChanges = true;
        this.showToastEvent('Painted!', 'success');
    }
    
    applyMaterialToPart(mesh, material) {
        mesh.userData.material = material;
        this.updatePartAPI(mesh.userData.id, { material });
        this.hasUnsavedChanges = true;
        this.showToastEvent(`Material: ${material}`, 'success');
    }
    
    // ============ CANCEL / EXIT ============
    
    cancelSelection() {
        if (this.isMoving) {
            this.cancelMove();
            return;
        }
        
        if (this.isPlacing) {
            this.isPlacing = false;
            this.currentPreset = null;
            this.previewRotation = 0;
            this.hidePreview();
            this.container.style.cursor = 'default';
            this.updateDebugInfo('Placement cancelled');
            window.dispatchEvent(new CustomEvent('preset-deselected'));
            return;
        }
        
        if (this.paintMode) { this.exitPaintMode(); return; }
        if (this.materialMode) { this.exitMaterialMode(); return; }
        if (this.selectedPart) { this.deselectPart(); return; }
        if (this.currentTool !== 'select') { this.setTool('select'); return; }
    }
    
    // ============ EVENT LISTENERS ============
    
    setupEventListeners() {
        const canvas = this.renderer.domElement;
        
        canvas.addEventListener('mousemove', (e) => this.onMouseMove(e));
        canvas.addEventListener('mousedown', (e) => this.onMouseDown(e));
        canvas.addEventListener('contextmenu', (e) => this.onContextMenu(e));
        
        document.addEventListener('keydown', (e) => this.onKeyDown(e));
        document.addEventListener('keyup', (e) => this.onKeyUp(e));
        
        window.addEventListener('beforeunload', (e) => {
            if (this.hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes.';
                return e.returnValue;
            }
        });
    }
    
    updateMouseCoords(event) {
        const rect = this.renderer.domElement.getBoundingClientRect();
        this.mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
        this.mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
    }
    
    onMouseMove(event) {
        this.updateMouseCoords(event);
        
        // Preview while placing
        if (this.isPlacing && this.currentPreset && !this.isMoving) {
            this.raycaster.setFromCamera(this.mouse, this.camera);
            const intersects = this.raycaster.intersectObject(this.ground);
            
            if (intersects.length > 0) {
                this.showPreview(this.currentPreset, intersects[0].point);
            } else {
                this.hidePreview();
            }
        }
        
        // Preview while moving
        if (this.isMoving && this.movingPartId) {
            this.raycaster.setFromCamera(this.mouse, this.camera);
            const intersects = this.raycaster.intersectObject(this.ground);
            
            if (intersects.length > 0) {
                const partData = this.parts.get(this.movingPartId);
                if (partData) {
                    const preset = {
                        type: partData.data.type,
                        default_width: partData.data.width,
                        default_height: partData.data.height,
                        default_depth: partData.data.depth,
                        default_color: partData.data.color,
                    };
                    const pos = this.calculatePlacementPosition(preset, intersects[0].point);
                    partData.mesh.position.set(pos.x, pos.y, pos.z);
                }
            }
        }
    }
    
    onMouseDown(event) {
        if (event.button !== 0) return; // Left click only
        
        this.updateMouseCoords(event);
        
        // PLACEMENT MODE
        if (this.isPlacing && this.currentPreset && !this.isMoving) {
            this.raycaster.setFromCamera(this.mouse, this.camera);
            const intersects = this.raycaster.intersectObject(this.ground);
            
            if (intersects.length > 0) {
                this.placePart(intersects[0].point);
            }
            return;
        }
        
        // MOVING MODE — finish move
        if (this.isMoving) {
            this.raycaster.setFromCamera(this.mouse, this.camera);
            const intersects = this.raycaster.intersectObject(this.ground);
            if (intersects.length > 0) {
                this.finishMove(intersects[0].point);
            }
            return;
        }
        
        // PAINT MODE
        if (this.paintMode) {
            this.handlePaintClick();
            return;
        }
        
        // MATERIAL MODE
        if (this.materialMode) {
            this.handleMaterialClick();
            return;
        }
        
        // TOOL MODES (select, delete, move, clone)
        this.handleToolClick();
    }
    
    onContextMenu(event) {
        event.preventDefault();
        
        // Right-click delete (Bloxburg quick-delete)
        this.updateMouseCoords(event);
        const hitPart = this.raycastParts();
        if (hitPart) {
            this.deletePart(hitPart.userData.id);
        }
    }
    
    handlePaintClick() {
        const hitPart = this.raycastParts();
        if (hitPart && hitPart.userData.id) {
            this.applyPaintToPart(hitPart, this.paintColor);
        }
    }
    
    handleMaterialClick() {
        const hitPart = this.raycastParts();
        if (hitPart && hitPart.userData.id) {
            this.applyMaterialToPart(hitPart, this.selectedMaterial);
        }
    }
    
    handleToolClick() {
        const hitPart = this.raycastParts();
        
        if (!hitPart) {
            this.deselectPart();
            return;
        }
        
        const partId = hitPart.userData.id;
        
        switch (this.currentTool) {
            case 'delete':
                this.deletePart(partId);
                break;
            case 'move':
                this.selectPart(partId);
                this.startMove(partId);
                break;
            case 'clone':
                this.clonePart(partId);
                break;
            case 'select':
            default:
                this.selectPart(partId);
                break;
        }
    }
    
    raycastParts() {
        const partMeshes = [];
        this.parts.forEach(({ mesh }) => {
            if (mesh.visible) partMeshes.push(mesh);
        });
        
        this.raycaster.setFromCamera(this.mouse, this.camera);
        const intersects = this.raycaster.intersectObjects(partMeshes, true);
        
        if (intersects.length > 0) {
            let target = intersects[0].object;
            while (target.parent && !target.userData.id) {
                target = target.parent;
            }
            if (target.userData.id) return target;
        }
        return null;
    }
    
    onKeyDown(event) {
        if (document.activeElement.tagName === 'INPUT' || 
            document.activeElement.tagName === 'TEXTAREA') {
            return;
        }
        
        const key = event.key.toLowerCase();
        this.keysPressed[key] = true;
        
        // ===== BLOXBURG 2026 HOTKEYS =====
        
        // Q / Escape — cancel / exit
        if (key === 'q' || key === 'escape') {
            event.preventDefault();
            this.cancelSelection();
        }
        
        // R — rotate preview
        if (key === 'r' && this.isPlacing) {
            event.preventDefault();
            this.previewRotation = (this.previewRotation + 45) % 360;
            this.updateDebugInfo(`Rotation: ${this.previewRotation}°`);
        }
        
        // G or Delete — delete tool / delete selected
        if (key === 'g' || key === 'delete') {
            event.preventDefault();
            if (this.selectedPart) {
                this.deletePart(this.selectedPart);
            } else {
                this.setTool(this.currentTool === 'delete' ? 'select' : 'delete');
            }
        }
        
        // T — move/transform tool
        if (key === 't') {
            event.preventDefault();
            if (this.selectedPart) {
                this.startMove(this.selectedPart);
            } else {
                this.setTool(this.currentTool === 'move' ? 'select' : 'move');
            }
        }
        
        // C — clone tool
        if (key === 'c' && !event.ctrlKey && !event.metaKey) {
            event.preventDefault();
            if (this.selectedPart) {
                this.clonePart(this.selectedPart);
            } else {
                this.setTool(this.currentTool === 'clone' ? 'select' : 'clone');
            }
        }
        
        // F — paint mode
        if (key === 'f') {
            event.preventDefault();
            if (this.paintMode) {
                this.exitPaintMode();
            } else {
                this.enterPaintMode();
            }
        }
        
        // B — day/night toggle
        if (key === 'b') {
            event.preventDefault();
            this.toggleDayNight();
        }
        
        // J — grid size toggle
        if (key === 'j') {
            event.preventDefault();
            this.cycleGridSize();
        }
        
        // H — toggle grid visibility
        if (key === 'h') {
            event.preventDefault();
            this.toggleGrid();
        }
        
        // Space — bird's eye view (hold)
        if (key === ' ' && !this.birdsEyeActive) {
            event.preventDefault();
            this.enterBirdsEye();
        }
        
        // Ctrl shortcuts
        if (event.ctrlKey || event.metaKey) {
            switch (key) {
                case 's': event.preventDefault(); this.saveBuild(); break;
                case 'z': event.preventDefault(); this.undo(); break;
                case 'y': event.preventDefault(); this.redo(); break;
            }
        }
    }
    
    onKeyUp(event) {
        const key = event.key.toLowerCase();
        this.keysPressed[key] = false;
        
        // Space release — exit bird's eye
        if (key === ' ' && this.birdsEyeActive) {
            this.exitBirdsEye();
        }
    }
    
    // ============ BIRD'S EYE VIEW (Spacebar) ============
    
    enterBirdsEye() {
        this.birdsEyeActive = true;
        this.savedCameraState = {
            position: this.camera.position.clone(),
            target: this.controls ? this.controls.target.clone() : new THREE.Vector3(10, 0, 10),
        };
        
        const floorY = (this.currentFloor - 1) * 3;
        this.camera.position.set(10, 40 + floorY, 10);
        this.camera.lookAt(10, floorY, 10);
        
        if (this.controls) {
            this.controls.target.set(10, floorY, 10);
            this.controls.enabled = false;
        }
        
        this.updateDebugInfo('Bird\'s Eye View — Release Space to return');
    }
    
    exitBirdsEye() {
        this.birdsEyeActive = false;
        
        if (this.savedCameraState) {
            this.camera.position.copy(this.savedCameraState.position);
            if (this.controls) {
                this.controls.target.copy(this.savedCameraState.target);
                this.controls.enabled = true;
            }
            this.savedCameraState = null;
        }
        
        this.updateDebugInfo('Returned from Bird\'s Eye View');
    }
    
    // ============ DAY/NIGHT TOGGLE (B key) ============
    
    toggleDayNight() {
        this.isNightMode = !this.isNightMode;
        
        if (this.isNightMode) {
            this.scene.background = new THREE.Color(0x0a0f1a);
            this.ambientLight.intensity = 0.15;
            this.directionalLight.intensity = 0.1;
            this.directionalLight.color.setHex(0x4466aa);
            
            // Add a dim blue fill light
            if (!this.moonLight) {
                this.moonLight = new THREE.PointLight(0x4466cc, 0.3, 60);
                this.moonLight.position.set(10, 25, 10);
                this.scene.add(this.moonLight);
            }
        } else {
            this.scene.background = new THREE.Color(0x1e293b);
            this.ambientLight.intensity = 0.6;
            this.directionalLight.intensity = 0.8;
            this.directionalLight.color.setHex(0xffffff);
            
            if (this.moonLight) {
                this.scene.remove(this.moonLight);
                this.moonLight = null;
            }
        }
        
        window.dispatchEvent(new CustomEvent('daynight-changed', { 
            detail: { night: this.isNightMode } 
        }));
        
        this.showToastEvent(this.isNightMode ? 'Night Mode' : 'Day Mode', 'info');
    }
    
    // ============ GRID SIZE TOGGLE (J key) ============
    
    cycleGridSize() {
        this.gridSizeIndex = (this.gridSizeIndex + 1) % this.gridSizes.length;
        this.gridSize = this.gridSizes[this.gridSizeIndex];
        
        this.rebuildGrid();
        
        window.dispatchEvent(new CustomEvent('gridsize-changed', { 
            detail: { size: this.gridSize } 
        }));
        
        this.showToastEvent(`Grid: ${this.gridSize}x`, 'info');
        this.updateDebugInfo(`Grid size: ${this.gridSize}`);
    }
    
    // ============ CAMERA MOVEMENT ============
    
    updateCameraMovement() {
        if (!this.controls || this.birdsEyeActive) return;
        
        const moveSpeed = this.cameraSpeed;
        const target = this.controls.target;
        const camera = this.camera;
        
        const forward = new THREE.Vector3();
        camera.getWorldDirection(forward);
        forward.y = 0;
        forward.normalize();
        
        const right = new THREE.Vector3();
        right.crossVectors(forward, new THREE.Vector3(0, 1, 0));
        right.normalize();
        
        if (this.keysPressed['w']) {
            camera.position.add(forward.clone().multiplyScalar(moveSpeed));
            target.add(forward.clone().multiplyScalar(moveSpeed));
        }
        if (this.keysPressed['s'] && !this.keysPressed['control']) {
            camera.position.add(forward.clone().multiplyScalar(-moveSpeed));
            target.add(forward.clone().multiplyScalar(-moveSpeed));
        }
        if (this.keysPressed['a']) {
            camera.position.add(right.clone().multiplyScalar(-moveSpeed));
            target.add(right.clone().multiplyScalar(-moveSpeed));
        }
        if (this.keysPressed['d']) {
            camera.position.add(right.clone().multiplyScalar(moveSpeed));
            target.add(right.clone().multiplyScalar(moveSpeed));
        }
        
        const orbitSpeed = 0.02;
        const offset = camera.position.clone().sub(target);
        const spherical = new THREE.Spherical().setFromVector3(offset);
        
        if (this.keysPressed['arrowleft']) spherical.theta += orbitSpeed;
        if (this.keysPressed['arrowright']) spherical.theta -= orbitSpeed;
        if (this.keysPressed['arrowup']) spherical.phi = Math.max(0.1, spherical.phi - orbitSpeed);
        if (this.keysPressed['arrowdown']) spherical.phi = Math.min(Math.PI - 0.1, spherical.phi + orbitSpeed);
        
        if (this.keysPressed['arrowleft'] || this.keysPressed['arrowright'] || 
            this.keysPressed['arrowup'] || this.keysPressed['arrowdown']) {
            offset.setFromSpherical(spherical);
            camera.position.copy(target).add(offset);
            camera.lookAt(target);
        }
        
        this.cameraSpeed = this.keysPressed['shift'] ? 1.0 : 0.5;
    }
    
    // ============ FLOOR SYSTEM ============
    
    setFloor(floor) {
        this.currentFloor = floor;
        
        this.parts.forEach(({ mesh }) => {
            mesh.visible = mesh.userData.floor_number === floor;
        });
        
        const targetY = (floor - 1) * 3;
        this.camera.position.y = 12 + targetY;
        if (this.controls) {
            this.controls.target.y = targetY;
        }
        
        this.updateDebugInfo(`Floor ${floor}`);
        window.dispatchEvent(new CustomEvent('floor-changed', { detail: { floor } }));
    }
    
    addFloor() {
        if (this.currentFloor >= this.maxFloors) return;
        this.currentFloor++;
        this.setFloor(this.currentFloor);
        window.dispatchEvent(new CustomEvent('floor-added', { detail: { floor: this.currentFloor } }));
        this.updateDebugInfo(`Added Floor ${this.currentFloor}!`);
    }
    
    toggleRoof() {
        this.roofVisible = !this.roofVisible;
        this.parts.forEach(({ mesh }) => {
            if (mesh.userData.type === 'roof') mesh.visible = this.roofVisible;
        });
        window.dispatchEvent(new CustomEvent('roof-toggled', { detail: { visible: this.roofVisible } }));
    }
    
    toggleGrid() {
        this.gridHelper.visible = !this.gridHelper.visible;
    }
    
    // ============ UNDO/REDO ============
    
    saveUndoState(action, data) {
        this.undoStack.push({ action, data, timestamp: Date.now() });
        if (this.undoStack.length > this.maxUndoSteps) this.undoStack.shift();
        this.redoStack = [];
    }
    
    undo() {
        if (this.undoStack.length === 0) return;
        
        const state = this.undoStack.pop();
        this.redoStack.push(state);
        
        switch (state.action) {
            case 'add': {
                const part = this.parts.get(state.data.id);
                if (part) {
                    this.scene.remove(part.mesh);
                    this.disposeGroup(part.mesh);
                    this.parts.delete(state.data.id);
                    this.deletePartAPI(state.data.id);
                }
                break;
            }
            case 'delete': {
                // Re-add the deleted part
                this.addPartToScene(state.data, true);
                break;
            }
            case 'move': {
                const part = this.parts.get(state.data.id);
                if (part) {
                    const old = state.data.oldData;
                    part.mesh.position.set(old.position_x, old.position_y, old.position_z);
                    part.mesh.rotation.y = (old.rotation_y || 0) * Math.PI / 180;
                    part.data = { ...old };
                    this.updatePartAPI(state.data.id, {
                        position_x: old.position_x,
                        position_y: old.position_y,
                        position_z: old.position_z,
                        rotation_y: old.rotation_y,
                    });
                }
                break;
            }
        }
        
        this.deselectPart();
        this.emitPartCount();
        this.showToastEvent('Undone', 'info');
    }
    
    redo() {
        if (this.redoStack.length === 0) return;
        
        const state = this.redoStack.pop();
        this.undoStack.push(state);
        
        switch (state.action) {
            case 'add':
                this.addPartToScene(state.data, false);
                break;
            case 'delete': {
                const part = this.parts.get(state.data.id);
                if (part) {
                    this.scene.remove(part.mesh);
                    this.disposeGroup(part.mesh);
                    this.parts.delete(state.data.id);
                }
                break;
            }
            case 'move': {
                const part = this.parts.get(state.data.id);
                if (part) {
                    const newD = state.data.newData;
                    part.mesh.position.set(newD.position_x, newD.position_y, newD.position_z);
                    part.mesh.rotation.y = (newD.rotation_y || 0) * Math.PI / 180;
                    part.data = { ...newD };
                    this.updatePartAPI(state.data.id, {
                        position_x: newD.position_x,
                        position_y: newD.position_y,
                        position_z: newD.position_z,
                        rotation_y: newD.rotation_y,
                    });
                }
                break;
            }
        }
        
        this.emitPartCount();
        this.showToastEvent('Redone', 'info');
    }
    
    // ============ SAVE ============
    
    saveBuild(showNotification = true) {
        this.hasUnsavedChanges = false;
        if (showNotification) {
            this.showToastEvent('Build saved!', 'success');
        }
    }
    
    exportPNG() {
        const gridVisible = this.gridHelper.visible;
        this.gridHelper.visible = false;
        this.renderer.render(this.scene, this.camera);
        
        const dataURL = this.renderer.domElement.toDataURL('image/png');
        const link = document.createElement('a');
        link.download = `build-${Date.now()}.png`;
        link.href = dataURL;
        link.click();
        
        this.gridHelper.visible = gridVisible;
        this.showToastEvent('PNG exported!', 'success');
    }
    
    // ============ MINIMAP ============
    
    createMinimap() {
        const minimapContainer = document.getElementById('minimap');
        if (!minimapContainer) return;
        
        const minimapScene = new THREE.Scene();
        minimapScene.background = new THREE.Color(0x1e293b);
        
        const minimapCamera = new THREE.OrthographicCamera(-5, 25, 25, -5, 0.1, 100);
        minimapCamera.position.set(10, 20, 10);
        minimapCamera.lookAt(10, 0, 10);
        
        const minimapRenderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        minimapRenderer.setSize(150, 150);
        minimapContainer.appendChild(minimapRenderer.domElement);
        
        const light = new THREE.AmbientLight(0xffffff, 0.5);
        minimapScene.add(light);
        
        const groundGeo = new THREE.PlaneGeometry(20, 20);
        const groundMat = new THREE.MeshBasicMaterial({ color: 0x334155 });
        const ground = new THREE.Mesh(groundGeo, groundMat);
        ground.rotation.x = -Math.PI / 2;
        minimapScene.add(ground);
        
        this.minimap = { scene: minimapScene, camera: minimapCamera, renderer: minimapRenderer };
    }
    
    updateMinimap() {
        if (!this.minimap) return;
        
        const toRemove = [];
        this.minimap.scene.children.forEach(child => {
            if (child.userData.isMinimapPart) toRemove.push(child);
        });
        toRemove.forEach(child => this.minimap.scene.remove(child));
        
        this.parts.forEach(({ mesh }) => {
            if (!mesh.visible) return;
            const color = mesh.userData.type === 'roof' ? 0x94a3b8 : 
                          mesh.userData.type === 'window' ? 0x93c5fd : 
                          mesh.userData.type === 'door' ? 0x78350f : 0x64748b;
            
            const geo = new THREE.BoxGeometry(mesh.userData.width, 0.1, mesh.userData.depth);
            const mat = new THREE.MeshBasicMaterial({ color });
            const indicator = new THREE.Mesh(geo, mat);
            indicator.position.set(mesh.position.x, 0.05, mesh.position.z);
            indicator.rotation.y = mesh.rotation.y;
            indicator.userData.isMinimapPart = true;
            this.minimap.scene.add(indicator);
        });
        
        this.minimap.renderer.render(this.minimap.scene, this.minimap.camera);
    }
    
    // ============ DEBUG ============
    
    updateDebugInfo(message) {
        const debugInfo = document.getElementById('debug-info');
        if (debugInfo) debugInfo.textContent = message;
    }
    
    // ============ RESIZE ============
    
    onWindowResize() {
        const width = this.container.clientWidth;
        const height = this.container.clientHeight;
        this.camera.aspect = width / height;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(width, height);
    }
    
    // ============ ANIMATE ============
    
    animate() {
        requestAnimationFrame(() => this.animate());
        
        this.updateCameraMovement();
        
        if (this.controls && !this.birdsEyeActive) {
            this.controls.update();
        }
        
        // Pulse selection outline
        if (this.selectionOutline) {
            const t = Date.now() * 0.003;
            this.selectionOutline.material.opacity = 0.5 + 0.5 * Math.sin(t);
            this.selectionOutline.material.transparent = true;
        }
        
        this.renderer.render(this.scene, this.camera);
        
        if (!this.frameCount) this.frameCount = 0;
        this.frameCount++;
        if (this.frameCount >= 10) {
            this.updateMinimap();
            this.frameCount = 0;
        }
    }
}

// Initialize editor
let editor;

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('editor-canvas');
    const buildId = container?.dataset.buildId;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    if (container && buildId && csrfToken) {
        editor = new BuildEditor(container, buildId, csrfToken);
    } else {
        console.error('[Editor] Missing required elements!');
    }
});
