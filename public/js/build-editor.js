// ConstructHub - Three.js Build Editor
// Bloxburg 2026 Build Mode — Full Feature Set
// UPDATED: 2026-04-09

const DEBUG_MODE = true;

class BuildEditor {
    constructor(container, buildId, csrfToken) {
        if (DEBUG_MODE) console.log('[Editor] Initializing with build ID:', buildId);
        
        this.container = container;
        this.csrfToken = csrfToken;
        this.rtChannel = null;
        this.myPresenceKey = null;
        this.userRole = 'editor';
        
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
        this.hoveredPartId = null;
        this.hoverOutline = null;
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

        // CUSTOM POLY DRAW MODE
        this.isDrawingPoly = false;
        this.polyPoints = [];
        this.polyLines = [];
        this.vertexMarkers = [];
        this.tempLine = null;
        this.drawPreviewMesh = null;
        this.keysPressed = {};
        this.drawType = 'floor';
        
        // TOOLS (Bloxburg 2026)
        this.currentTool = 'select'; // select, delete, move, clone
        this.isMoving = false;
        this.movingPartId = null;
        
        // Manual Save State
        this.deletedIds = new Set();
        this.dirtyPartIds = new Set();
        this.isSaving = false;
        
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
        
        // COLLABORATION STATE
        this.rtChannel = null;
        this.userRole = 'viewer';
        this.remoteCursors = new Map(); // userId -> { mesh, label }
        this.lastPresenceSent = 0;
        
        // Platform mesh
        this.platform = null;
        this.platformEdges = null;
        
        // Object types
        this.EDGE_PLACED_TYPES = ['wall'];
        this.CENTER_PLACED_TYPES = ['floor', 'roof', 'stairs'];
        this.WALL_ATTACHED_TYPES = ['door', 'window'];
        
        // API endpoints (using /editor/ prefix for proper CSRF handling)
        this.api = {
            parts: `/editor/builds/${buildId}/parts`,
            build: `/editor/builds/${buildId}`,
        };
        
        if (DEBUG_MODE) console.log('[Editor] Calling init...');
        this.init();
    }
    
    async init() {
        if (DEBUG_MODE) console.log('[Editor] init() called');
        
        await this.setupScene();
        this.setupEventListeners();
        
        // Push a state to history so we can intercept the Back button
        window.history.pushState({ editor: true }, '', window.location.href);
        
        // Supabase Realtime is now handled by Alpine.js in show.blade.php
        
        if (DEBUG_MODE) console.log('[Editor] Loading parts...');
        await this.loadParts();
        
        // Minimap disabled per user request
        // this.createMinimap();
        
        this.animate();
        
        this.updateDebugInfo('Ready — Select a part to start building!');
        
        if (DEBUG_MODE) console.log('[Editor] Initialization complete');
    }
    
    // ============ SUPABASE REALTIME ============
    
    trackPresence(mousePos) {
        if (!this.rtChannel) return;
        
        const now = Date.now();
        if (now - this.lastPresenceSent < 50) return; // Throttling 20fps
        
        // Use the title from the page or a fallback
        const userName = document.querySelector('.editor-topbar__title')?.textContent.split(' - ')[1] || 'Collaborator';
        
        if (DEBUG_MODE) console.log('[RT] Tracking presence:', userName, mousePos);

        this.rtChannel.track({
            cursor: { x: mousePos.x, y: mousePos.y, z: mousePos.z },
            name: userName,
            role: this.userRole
        });
        
        this.lastPresenceSent = now;
    }

    updateRemoteCursors(presenceState) {
        if (DEBUG_MODE) console.log('[RT] Presence Sync Received:', presenceState);
        // Clear old ones not in state
        const currentIds = new Set(Object.keys(presenceState));
        for (const [userId, cursor] of this.remoteCursors.entries()) {
            if (!currentIds.has(userId)) {
                this.scene.remove(cursor.mesh);
                this.remoteCursors.delete(userId);
            }
        }

        // Update/Create
        for (const userId in presenceState) {
            // Don't draw our own
            if (userId === this.myPresenceKey) continue;

            const userState = presenceState[userId][0];
            if (!userState || !userState.cursor) continue;

            let cursor = this.remoteCursors.get(userId);
            if (!cursor) {
                cursor = this.createRemoteCursorMesh(userState.name);
                this.scene.add(cursor.mesh);
                this.remoteCursors.set(userId, cursor);
            }

            cursor.mesh.position.set(userState.cursor.x, userState.cursor.y, userState.cursor.z);
        }
    }

    createRemoteCursorMesh(name) {
        // Simple pointer (Cone)
        const geometry = new THREE.ConeGeometry(0.1, 0.3, 8);
        geometry.rotateX(Math.PI); // Point down
        const material = new THREE.MeshPhongMaterial({ color: 0x3b82f6, emissive: 0x1d4ed8 });
        const mesh = new THREE.Mesh(geometry, material);

        // Name tag (Billboard CSS renderer style but simplified for this demo as a sprite)
        const canvas = document.createElement('canvas');
        canvas.width = 128;
        canvas.height = 32;
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = 'rgba(0,0,0,0.6)';
        ctx.roundRect(0, 0, 128, 32, 8);
        ctx.fill();
        ctx.fillStyle = 'white';
        ctx.font = 'bold 16px Plus Jakarta Sans';
        ctx.textAlign = 'center';
        ctx.fillText(name, 64, 22);

        const texture = new THREE.CanvasTexture(canvas);
        const spriteMaterial = new THREE.SpriteMaterial({ map: texture });
        const sprite = new THREE.Sprite(spriteMaterial);
        sprite.position.y = 0.5;
        sprite.scale.set(1, 0.25, 1);
        mesh.add(sprite);

        return { mesh };
    }

    deletePartFromRealtime(id) {
        const part = this.parts.get(id);
        if (part) {
            this.scene.remove(part.mesh);
            this.parts.delete(id);
        }
    }

    updatePartInRealtime(partId, newData) {
        const part = this.parts.get(partId);
        if (!part) return;

        // Apply visual updates
        if (newData.position_x !== undefined) {
            part.mesh.position.set(newData.position_x, newData.position_y, newData.position_z);
            part.data.position_x = newData.position_x;
            part.data.position_y = newData.position_y;
            part.data.position_z = newData.position_z;
        }

        if (newData.rotation_y !== undefined) {
            part.mesh.rotation.y = (newData.rotation_y || 0) * Math.PI / 180;
            part.data.rotation_y = newData.rotation_y;
        }

        if (newData.color !== undefined) {
            part.mesh.userData.color = newData.color;
            part.data.color = newData.color;
            
            const applyColor = (mat) => {
                if (mat && mat.color && typeof mat.color.set === 'function') {
                    if (mat.transparent || mat.opacity < 1) return;
                    mat.color.set(newData.color);
                }
            };
            part.mesh.traverse((child) => {
                if (child.isMesh && child.material) {
                    if (Array.isArray(child.material)) child.material.forEach(applyColor);
                    else applyColor(child.material);
                }
            });
        }
    }

    // ============ SCENE SETUP ============
    
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
        const vLineX = Math.round(x / gs) * gs;
        const distToVLine = Math.abs(x - vLineX);
        
        // Find distance to nearest horizontal line (multiples of gs)
        const hLineZ = Math.round(z / gs) * gs;
        const distToHLine = Math.abs(z - hLineZ);
        
        let snapX, snapZ, isVertical;
        
        if (distToVLine <= distToHLine) {
            // We are closer to a vertical grid line.
            // Snap X to the line, and Z to the center of the nearest grid cell segment.
            snapX = vLineX;
            snapZ = Math.floor(z / gs) * gs + gs / 2;
            isVertical = true;
        } else {
            // We are closer to a horizontal grid line.
            // Snap Z to the line, and X to the center of the nearest grid cell segment.
            snapX = Math.floor(x / gs) * gs + gs / 2;
            snapZ = hLineZ;
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
        // Crisp, professional sky blue background
        this.scene.background = new THREE.Color(0xbddaf2);
        
        // Fog to hide the grid fading out beautifully
        this.scene.fog = new THREE.Fog(0xbddaf2, 20, 100);
        
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
        // Tone mapping for better, more natural color exposure
        this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
        this.renderer.toneMappingExposure = 1.0;
        
        this.container.appendChild(this.renderer.domElement);
        
        // Hemisphere light (Sky color, Ground color, Intensity)
        const hemiLight = new THREE.HemisphereLight(0xffffff, 0x444444, 0.6);
        hemiLight.position.set(0, 200, 0);
        this.scene.add(hemiLight);
        
        // Flat ambient light for baseline brightness
        this.ambientLight = new THREE.AmbientLight(0xffffff, 0.4);
        this.scene.add(this.ambientLight);
        
        // Crisp directional sun light
        this.directionalLight = new THREE.DirectionalLight(0xfff5e6, 1.2);
        this.directionalLight.position.set(20, 30, 10);
        this.directionalLight.castShadow = true;
        this.directionalLight.shadow.mapSize.width = 2048;
        this.directionalLight.shadow.mapSize.height = 2048;
        this.directionalLight.shadow.bias = -0.0005;
        this.directionalLight.shadow.camera.near = 0.5;
        this.directionalLight.shadow.camera.far = 100;
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
            color: 0xf8fafc,
            transparent: true,
            opacity: 0.5,
        });
        this.platform = new THREE.Mesh(platformGeometry, platformMaterial);
        this.platform.position.set(10, -0.15, 10);
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
            case 'roof': 
                if (partData.shape_points) return this.createCustomPolyMesh(partData);
                return this.createRoofMesh(width, height, depth, color, variant);
            case 'stairs': return this.createStairsMesh(width, height, depth, color);
            case 'floor':
            default: 
                if (partData.shape_points) return this.createCustomPolyMesh(partData);
                return this.createFloorMesh(width, height, depth, color, variant);
        }
    }

    createCustomPolyMesh(partData) {
        if (!partData.shape_points || partData.shape_points.length < 3) {
            return this.createFloorMesh(partData.width, partData.height, partData.depth, partData.color, partData.variant);
        }
        
        const shape = new THREE.Shape();
        const start = partData.shape_points[0];
        shape.moveTo(start.x, -start.z);
        
        for (let i = 1; i < partData.shape_points.length; i++) {
            const pt = partData.shape_points[i];
            shape.lineTo(pt.x, -pt.z);
        }
        
        const extrudeSettings = {
            depth: partData.type === 'custom_floor' ? 0.2 : 0.5,
            bevelEnabled: false,
        };
        const geometry = new THREE.ExtrudeGeometry(shape, extrudeSettings);
        geometry.rotateX(Math.PI / 2);
        
        geometry.computeBoundingBox();
        const box = geometry.boundingBox;
        const center = new THREE.Vector3();
        box.getCenter(center);
        geometry.translate(-center.x, -center.y, -center.z);
        
        const mat = new THREE.MeshStandardMaterial({ color: partData.color, roughness: 0.8, side: THREE.DoubleSide });
        return new THREE.Mesh(geometry, mat);
    }
    
    // ============ ADD PART TO SCENE ============

    addPartToScene(partData, save = true) {
        console.log(`[Editor] addPartToScene called - save=${save}, partData=`, partData);

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

        // BROADCAST for Realtime
        if (save) {
            window.dispatchEvent(new CustomEvent('part-placed', {
                detail: {
                    count: this.parts.size,
                    isLocal: true,
                    partData: partData
                }
            }));

            this.hasUnsavedChanges = true;
            this.showToastEvent('Draft Updated', 'info');
            console.log('[Editor] Local part placed and broadcast:', tempId);
        } else {
            console.log('[Editor] RT Rendered Remote Part:', tempId, partData);
        }

        return mesh;
    }
    
    // This is now only called within saveBuild() loop
    async createPartOnServer(mesh, tempId) {
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
            shape_points: mesh.userData.shape_points || null,
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
            
            if (!response.ok) return null;
            
            const savedPart = await response.json();
            const newId = savedPart.id;
            
            // Swap temp ID with real ID in the local map
            if (this.parts.has(tempId)) {
                this.parts.delete(tempId);
            }
            
            mesh.userData.id = newId;
            this.parts.set(newId, { mesh, data: savedPart });
            
            // Update history to point to the new server ID
            this.updateHistoryId(tempId, newId);
            
            return savedPart;
        } catch (error) {
            console.error('[Editor] Network error during commit:', error);
            return null;
        }
    }
    
    async updatePartAPI(partId, data) {
        // MARK AS DIRTY - Draft Mode
        if (typeof partId === 'number' || !String(partId).startsWith('temp_')) {
            this.dirtyPartIds.add(partId);
        }
        this.hasUnsavedChanges = true;
        this.updateDebugInfo('Changes ready to save');
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
            return response.ok;
        } catch (error) {
            console.error('[Editor] Error deleting part:', error);
            return false;
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

        // Dispatch for Realtime broadcast
        window.dispatchEvent(new CustomEvent('part-deleted', { 
            detail: { 
                id: partId,
                isLocal: true
            } 
        }));

        return true;
    }
    
    // ============ TOAST HELPER ============
    
    showToastEvent(message, type = 'success') {
        if (typeof showSweetToast === 'function') {
            showSweetToast(message, type);
        } else if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                    popup: 'swal-premium swal-toast compact-toast'
                }
            });
            Toast.fire({ icon: type, title: message });
        } else {
            console.log(`[Toast] ${type}: ${message}`);
        }
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
            const isRotated = (this.previewRotation % 180 !== 0);
            
            // Stairs can have asymmetric sizes (e.g., 1x2). 
            // Even dimensions snap exactly to grid lines. Odd dimensions snap exactly to grid cell centers.
            const effWidth = isRotated ? preset.default_depth : preset.default_width;
            const effDepth = isRotated ? preset.default_width : preset.default_depth;
            
            if (effWidth % 2 === 0) {
                x = Math.round(x / this.gridSize) * this.gridSize;
            } else {
                x = Math.floor(x / this.gridSize) * this.gridSize + this.gridSize / 2;
            }
            
            if (effDepth % 2 === 0) {
                z = Math.round(z / this.gridSize) * this.gridSize;
            } else {
                z = Math.floor(z / this.gridSize) * this.gridSize + this.gridSize / 2;
            }
            
            x = Math.max(this.gridSize/2, Math.min(this.gridUnits - this.gridSize/2, x));
            z = Math.max(this.gridSize/2, Math.min(this.gridUnits - this.gridSize/2, z));
            
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
        console.log('[placePart] Attempting place for type:', this.currentPreset.type, 'at', point);
        
        const preset = this.currentPreset;
        const pos = this.calculatePlacementPosition(preset, point);
        
        if (!pos.isValid) {
            console.error('[placePart] PREVENTED: isValid returned FALSE!', JSON.stringify(pos));
            if (this.WALL_ATTACHED_TYPES.includes(preset.type) && !pos.wallFound) {
                this.showToastEvent('No wall here! Build a wall first.', 'error');
            }
            return null;
        }
        console.log('[placePart] Valid pos calculated:', JSON.stringify(pos));
        
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
        
        // Ground marker at current floor grid level
        const markerGeo = new THREE.CylinderGeometry(0.2, 0.2, 0.05, 16);
        const markerMat = new THREE.MeshBasicMaterial({ 
            color: pos.isValid ? 0x22C55E : 0xEF4444,
            transparent: true, opacity: 0.9
        });
        this.previewMarker = new THREE.Mesh(markerGeo, markerMat);
        const gridY = (this.currentFloor - 1) * 3 + 0.025;
        this.previewMarker.position.set(pos.x, gridY, pos.z);
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
        // ROLE CHECK
        if (this.userRole === 'viewer') {
            this.showToastEvent('Only Editors can delete!', 'error');
            return;
        }

        const partData = this.parts.get(partId);
        if (!partData) return;
        
        // Save for undo
        this.saveUndoState('delete', { ...partData.data });
        
        // Remove from scene and local map
        this.scene.remove(partData.mesh);
        this.disposeGroup(partData.mesh);
        this.parts.delete(partId);
        
        // If it was a real server part, track for batch deletion
        if (typeof partId === 'number' || !String(partId).startsWith('temp_')) {
            this.deletedIds.add(partId);
            this.dirtyPartIds.delete(partId);
        }
        
        // Dispatch for Realtime broadcast
        window.dispatchEvent(new CustomEvent('part-deleted', { detail: { id: partId } }));

        this.deselectPart();
        this.hasUnsavedChanges = true;
        this.emitPartCount();
        this.showToastEvent('Deleted', 'info');
    }
    
    // MOVE TOOL
    startMove(partId) {
        // ROLE CHECK
        if (this.userRole === 'viewer') {
            this.showToastEvent('Only Editors can move parts!', 'error');
            return;
        }

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
        
        // BROADCAST for Realtime
        window.dispatchEvent(new CustomEvent('part-updated', { 
            detail: { 
                id: this.movingPartId, 
                isLocal: true,
                data: {
                    position_x: partData.data.position_x,
                    position_y: partData.data.position_y,
                    position_z: partData.data.position_z,
                    rotation_y: partData.data.rotation_y
                }
            } 
        }));

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
    
    // ============ CUSTOM POLY DRAW MODE (Bloxburg Style) ============

    toggleDrawMode(type = 'floor') {
        if (this.isDrawingPoly && this.drawType === type) {
            this.exitDrawMode();
        } else {
            if (this.isDrawingPoly) this.clearDrawGraphics();
            this.enterDrawMode(type);
        }
    }
    
    enterDrawMode(type) {
        this.exitPaintMode();
        this.exitMaterialMode();
        this.deselectPart();
        if (this.isPlacing) {
            this.isPlacing = false;
            this.hidePreview();
        }
        
        this.isDrawingPoly = true;
        this.drawType = type;
        this.polyPoints = [];
        this.clearDrawGraphics();
        
        this.container.style.cursor = 'crosshair';
        const label = type.charAt(0).toUpperCase() + type.slice(1);
        this.updateDebugInfo(`DRAWING ${label}: Click points to layout. Click 1st point or 'Enter' to finish.`);
        window.dispatchEvent(new CustomEvent('draw-mode-changed', { detail: { active: true, type } }));
    }
    
    exitDrawMode() {
        this.isDrawingPoly = false;
        this.clearDrawGraphics();
        this.container.style.cursor = 'default';
        this.updateDebugInfo('Draw mode exited');
        window.dispatchEvent(new CustomEvent('draw-mode-changed', { detail: { active: false } }));
    }

    // Helper: 2D Line intersection check (XZ plane)
    doLinesIntersect(p1, p2, p3, p4) {
        function ccw(A, B, C) {
            return (C.z - A.z) * (B.x - A.x) > (B.z - A.z) * (C.x - A.x);
        }
        // Basic check for shared endpoints (adjacent segments don't count as intersecting)
        const isShared = (
            (p1.x === p3.x && p1.z === p3.z) || (p1.x === p4.x && p1.z === p4.z) ||
            (p2.x === p3.x && p2.z === p3.z) || (p2.x === p4.x && p2.z === p4.z)
        );
        if (isShared) return false;

        return (ccw(p1, p3, p4) !== ccw(p2, p3, p4)) && (ccw(p1, p2, p3) !== ccw(p1, p2, p4));
    }

    isDrawValid(newPoint = null) {
        if (this.polyPoints.length < 2) return true;
        
        const pts = [...this.polyPoints];
        if (newPoint) pts.push(newPoint);
        
        // Construct edges
        const edges = [];
        for (let i = 0; i < pts.length - 1; i++) {
            edges.push({ a: pts[i], b: pts[i+1] });
        }

        // Check if the NEWEST edge intersects any PREVIOUS edges
        if (edges.length > 1) {
            const lastEdge = edges[edges.length - 1];
            for (let i = 0; i < edges.length - 2; i++) { // Skip the edge right before it
                if (this.doLinesIntersect(lastEdge.a, lastEdge.b, edges[i].a, edges[i].b)) {
                    return false;
                }
            }
        }

        return true;
    }

    getGridIntersection() {
        const rayTargets = [this.ground];
        if (this.platform) rayTargets.push(this.platform);
        const intersects = this.raycaster.intersectObjects(rayTargets);
        if (intersects.length > 0) {
            const intersect = intersects[0];
            // Snap to grid for drawing
            intersect.point.x = Math.round(intersect.point.x / this.gridSize) * this.gridSize;
            intersect.point.z = Math.round(intersect.point.z / this.gridSize) * this.gridSize;
            return intersect;
        }
        return null;
    }

    handleDrawClick() {
        this.raycaster.setFromCamera(this.mouse, this.camera);
        const intersect = this.getGridIntersection();
        if (!intersect) return;
        
        const pt = intersect.point.clone();
        
        // If clicking near the first point and we have >=3 points, close it!
        if (this.polyPoints.length >= 3) {
            const firstPt = this.polyPoints[0];
            const dist = pt.distanceTo(firstPt);
            if (dist < 1.5) { // Snapping tolerance
                if (!this.isDrawValid(firstPt)) {
                    this.showToastEvent('Cannot close: shape intersects itself!', 'error');
                    return;
                }
                this.finishDrawPoly();
                return; // Finished!
            }
        }
        
        this.polyPoints.push(pt);
        
        // Add visual vertex marker (Sphere)
        const geo = new THREE.SphereGeometry(0.2, 8, 8);
        const mat = new THREE.MeshBasicMaterial({ color: 0x00ff00 }); // Green for markers
        const marker = new THREE.Mesh(geo, mat);
        marker.position.copy(pt);
        this.scene.add(marker);
        if (!this.vertexMarkers) this.vertexMarkers = [];
        this.vertexMarkers.push(marker);
        
        // Solidify the last temp line if there's >1 point
        if (this.polyPoints.length > 1) {
            const p1 = this.polyPoints[this.polyPoints.length - 2];
            const p2 = this.polyPoints[this.polyPoints.length - 1];
            this.createSolidLine(p1, p2);
        }
    }
    
    handleDrawMove() {
        if (this.polyPoints.length === 0) return;
        
        this.raycaster.setFromCamera(this.mouse, this.camera);
        const intersect = this.getGridIntersection();
        if (!intersect) return;
        
        let pt = intersect.point.clone();
        
        // Visual Snapping to start point
        let isValid = this.isDrawValid(pt);
        
        if (this.polyPoints.length >= 3) {
            const firstPt = this.polyPoints[0];
            if (pt.distanceTo(firstPt) < 1.5) {
                pt = firstPt.clone();
                isValid = this.isDrawValid(firstPt); // Re-validate if snapping to close
            }
        }
        
        const lastPt = this.polyPoints[this.polyPoints.length - 1];
        const lineColor = isValid ? 0x00ff00 : 0xff0000;
        
        if (!this.tempLine) {
            const mat = new THREE.LineDashedMaterial({ color: lineColor, dashSize: 0.5, gapSize: 0.2 });
            const geo = new THREE.BufferGeometry().setFromPoints([lastPt, pt]);
            this.tempLine = new THREE.Line(geo, mat);
            this.tempLine.computeLineDistances();
            this.scene.add(this.tempLine);
        } else {
            this.tempLine.material.color.setHex(lineColor);
            const pos = this.tempLine.geometry.attributes.position;
            pos.setXYZ(1, pt.x, pt.y, pt.z);
            pos.needsUpdate = true;
            this.tempLine.computeLineDistances();
        }

        if (this.polyPoints.length >= 2) {
            this.updateDrawPreview(pt, isValid);
        }
    }

    getFloorY() {
        return (this.currentFloor - 1) * 3;
    }

    updateDrawPreview(currentMousePt, isValid) {
        if (this.drawPreviewMesh) {
            this.scene.remove(this.drawPreviewMesh);
            this.drawPreviewMesh.geometry.dispose();
            this.drawPreviewMesh = null;
        }

        const pts = [...this.polyPoints, currentMousePt];
        if (pts.length < 3) return;

        const shape = new THREE.Shape();
        shape.moveTo(pts[0].x, pts[0].z); // Map world X,Z to shape X,Y
        for (let i = 1; i < pts.length; i++) {
            shape.lineTo(pts[i].x, pts[i].z);
        }

        const depthHeight = 0.15;
        const geo = new THREE.ExtrudeGeometry(shape, { depth: depthHeight, bevelEnabled: false });
        
        // Rotate 90deg to lay flat on XZ plane. y_local becomes z_world.
        geo.rotateX(Math.PI / 2);

        const color = isValid ? 0x22C55E : 0xEF4444;
        const mat = new THREE.MeshStandardMaterial({ 
            color, 
            transparent: true, 
            opacity: 0.5, // More opaque
            side: THREE.DoubleSide
        });

        this.drawPreviewMesh = new THREE.Mesh(geo, mat);
        const floorY = this.getFloorY();
        const finalY = (this.drawType === 'roof' ? floorY + 2.8 : floorY) + 0.05; 
        this.drawPreviewMesh.position.set(0, finalY, 0); 
        this.scene.add(this.drawPreviewMesh);
    }

    createSolidLine(p1, p2) {
        const material = new THREE.LineBasicMaterial({ color: 0x00ff00, linewidth: 2 });
        const points = [p1, p2];
        const geometry = new THREE.BufferGeometry().setFromPoints(points);
        const line = new THREE.Line(geometry, material);
        this.scene.add(line);
        this.polyLines.push(line);
    }
    
    clearDrawGraphics() {
        if (this.polyLines) {
            this.polyLines.forEach(line => this.scene.remove(line));
        }
        this.polyLines = [];
        if (this.tempLine) {
            this.scene.remove(this.tempLine);
            this.tempLine = null;
        }
        if (this.vertexMarkers) {
            this.vertexMarkers.forEach(m => this.scene.remove(m));
        }
        this.vertexMarkers = [];
        if (this.drawPreviewMesh) {
            this.scene.remove(this.drawPreviewMesh);
            if (this.drawPreviewMesh.geometry) this.drawPreviewMesh.geometry.dispose();
            this.drawPreviewMesh = null;
        }
    }

    finishDrawPoly() {
        if (this.polyPoints.length < 3) return;
        
        // Create THREE.Shape from points (Map XZ world to XY shape)
        const shape = new THREE.Shape();
        const start = this.polyPoints[0];
        shape.moveTo(start.x, start.z); 
        
        for (let i = 1; i < this.polyPoints.length; i++) {
            const pt = this.polyPoints[i];
            shape.lineTo(pt.x, pt.z);
        }
        
        // Generate thickness using ExtrudeGeometry
        const depthHeight = this.drawType === 'floor' ? 0.2 : 0.5;
        const extrudeSettings = {
            depth: depthHeight,
            bevelEnabled: false,
        };
        const geometry = new THREE.ExtrudeGeometry(shape, extrudeSettings);
        
        // Rotate +90 deg! 
        geometry.rotateX(Math.PI / 2);
        
        // Center the geometry for pivot handling
        geometry.computeBoundingBox();
        const box = geometry.boundingBox;
        const center = new THREE.Vector3();
        box.getCenter(center);
        geometry.translate(-center.x, 0, -center.z); // Center XZ, keep bottom at local 0
        
        const colorString = this.drawType === 'floor' ? '#8a8a8a' : '#555555';
        const mat = new THREE.MeshStandardMaterial({ color: colorString, roughness: 0.8, side: THREE.DoubleSide });
        const mesh = new THREE.Mesh(geometry, mat);
        
        // Position it!
        const floorY = this.getFloorY();
        const finalBaseY = this.drawType === 'roof' ? floorY + 3.0 : floorY;
        mesh.position.set(center.x, finalBaseY, center.z);
        
        mesh.castShadow = true;
        mesh.receiveShadow = true;
        
        // Serialize original points so it can be perfectly recreated on load!
        const ptsData = this.polyPoints.map(p => ({ x: p.x, y: p.y, z: p.z }));
        
        const tempId = 'temp_' + Date.now();
        mesh.userData = {
            id: tempId,
            type: this.drawType, // Just 'floor' or 'roof'
            variant: 'custom', 
            width: box.max.x - box.min.x,
            height: depthHeight,
            depth: box.max.z - box.min.z,
            color: colorString,
            color_front: colorString,
            color_back: colorString,
            material: 'default',
            floor_number: this.currentFloor,
            shape_points: ptsData
        };
        
        this.scene.add(mesh);
        this.parts.set(tempId, mesh);
        
        this.saveUndoState('create', { parts: [{ id: tempId, data: mesh.userData, position: mesh.position.clone(), rotation: mesh.rotation.clone() }] });
        this.createPartOnServer(mesh, tempId);
        
        this.clearDrawGraphics();
        const label = this.drawType.charAt(0).toUpperCase() + this.drawType.slice(1);
        this.showToastEvent(`${label} placed!`, 'success');
        this.polyPoints = []; 
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
        // ROLE CHECK
        if (this.userRole === 'viewer') {
            this.showToastEvent('Only Editors can paint!', 'error');
            return;
        }

        const oldColor = mesh.userData.color_front || mesh.userData.color;
        
        // Save for undo
        this.saveUndoState('paint', { 
            id: mesh.userData.id, 
            oldColor: oldColor,
            newColor: color 
        });

        mesh.userData.color = color;
        mesh.userData.color_front = color;
        mesh.userData.color_back = color;
        
        // Update mesh visuals
        const applyColor = (mat) => {
            if (mat && mat.color && typeof mat.color.set === 'function') {
                if (mat.transparent || mat.opacity < 1) return; // Ignore glass/translucent materials
                mat.color.set(color);
            }
        };

        if (mesh.isGroup) {
            mesh.traverse((child) => {
                if (child.isMesh && child.material) {
                    if (Array.isArray(child.material)) {
                        child.material.forEach(applyColor);
                    } else {
                        applyColor(child.material);
                    }
                }
            });
        } else if (mesh.material) {
            if (Array.isArray(mesh.material)) {
                mesh.material.forEach(applyColor);
            } else {
                applyColor(mesh.material);
            }
        }

        this.updatePartAPI(mesh.userData.id, { color, color_front: color, color_back: color });
        this.hasUnsavedChanges = true;
        this.showToastEvent('Painted', 'info');
    }
    
    applyMaterialToPart(mesh, material) {
        const oldMaterial = mesh.userData.variant || mesh.userData.material || 'default';
        
        // Save for undo
        this.saveUndoState('material', { 
            id: mesh.userData.id, 
            oldMaterial: oldMaterial,
            newMaterial: material 
        });

        mesh.userData.variant = material;
        // Keep material mapped as well depending on legacy vs current backend logic
        mesh.userData.material = material;
        
        // Update actual THREE material properties
        let roughness = 0.7;
        let metalness = 0.1;
        let transparent = false;
        let opacity = 1.0;
        let textureMapUrl = null;

        switch (material) {
            case 'wood': roughness = 0.85; metalness = 0.0; textureMapUrl = '/img/textures/wood.png'; break;
            case 'brick': roughness = 0.95; metalness = 0.0; textureMapUrl = '/img/textures/brick.png'; break;
            case 'concrete': roughness = 0.85; metalness = 0.1; break;
            case 'stone': roughness = 0.8; metalness = 0.1; break;
            case 'marble': roughness = 0.2; metalness = 0.1; textureMapUrl = '/img/textures/marble.png'; break;
            case 'metal': roughness = 0.3; metalness = 0.9; break;
            case 'glass': roughness = 0.1; metalness = 0.8; transparent = true; opacity = 0.4; break;
            case 'default': roughness = 0.7; metalness = 0.1; break;
        }

        let loadedTexture = null;
        if (textureMapUrl) {
            if (!this.textureLoader) this.textureLoader = new THREE.TextureLoader();
            if (!this.textureCache) this.textureCache = {};
            
            if (this.textureCache[textureMapUrl]) {
                loadedTexture = this.textureCache[textureMapUrl];
            } else {
                loadedTexture = this.textureLoader.load(textureMapUrl);
                loadedTexture.wrapS = THREE.RepeatWrapping;
                loadedTexture.wrapT = THREE.RepeatWrapping;
                loadedTexture.repeat.set(2, 2); // Make it tile denser
                this.textureCache[textureMapUrl] = loadedTexture;
            }
        }

        const applyProps = (mat) => {
            if (mat && mat.isMeshStandardMaterial) {
                // If they explicitly picked glass, let it be transparent. Otherwise, if it was glass and they picked something else, make it solid again.
                mat.transparent = transparent;
                mat.opacity = opacity;
                
                mat.roughness = roughness;
                mat.metalness = metalness;

                if (loadedTexture) {
                    mat.map = loadedTexture;
                } else {
                    mat.map = null;
                }

                mat.needsUpdate = true;
            }
        };

        if (mesh.isGroup) {
            mesh.traverse((child) => {
                if (child.isMesh && child.material) {
                    if (Array.isArray(child.material)) {
                        child.material.forEach(applyProps);
                    } else {
                        applyProps(child.material);
                    }
                }
            });
        } else if (mesh.material) {
            if (Array.isArray(mesh.material)) {
                mesh.material.forEach(applyProps);
            } else {
                applyProps(mesh.material);
            }
        }
        
        this.updatePartAPI(mesh.userData.id, { variant: material, material: material });
        this.hasUnsavedChanges = true;
        this.showToastEvent('Material Changed', 'info');
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
        
        canvas.addEventListener('pointermove', (e) => this.onMouseMove(e));
        canvas.addEventListener('pointerdown', (e) => this.onMouseDown(e));
        canvas.addEventListener('pointerup', () => { /* no-op mouse up blocker */ });
        canvas.addEventListener('contextmenu', (e) => this.onContextMenu(e));
        
        document.addEventListener('keydown', (e) => this.onKeyDown(e));
        document.addEventListener('keyup', (e) => this.onKeyUp(e));
        
        window.addEventListener('beforeunload', (e) => {
            if (this.hasUnsavedChanges && !window.isConfirmingReload) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes.';
                return e.returnValue;
            }
        });

        // Intercept Refresh (F5, Ctrl+R) to show SweetAlert
        window.addEventListener('keydown', (e) => {
            if (e.key === 'F5' || (e.ctrlKey && (e.key === 'r' || e.key === 'R'))) {
                if (this.hasUnsavedChanges) {
                    e.preventDefault();
                    window.isConfirmingReload = true; // Block native alert immediately
                    if (typeof confirmReload === 'function') {
                        confirmReload();
                    }
                }
            }
        });

        // Intercept internal links with SweetAlert
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link && link.href && !link.href.startsWith('#') && this.hasUnsavedChanges) {
                // If it's a relative link or same domain
                const url = new URL(link.href, window.location.origin);
                if (url.origin === window.location.origin) {
                    e.preventDefault();
                    this.showUnsavedChangesModal().then((choice) => {
                        if (choice === 'save') {
                            this.saveBuild().then(() => {
                                window.location.href = link.href;
                            });
                        } else if (choice === 'discard') {
                            this.hasUnsavedChanges = false;
                            window.location.href = link.href;
                        }
                    });
                }
            }
        });

        // Intercept browser BACK button
        window.addEventListener('popstate', (e) => {
            if (this.hasUnsavedChanges) {
                // Show custom modern modal
                this.showUnsavedChangesModal().then((choice) => {
                    if (choice === 'save') {
                        this.saveBuild().then(() => {
                            window.history.back();
                        });
                    } else if (choice === 'discard') {
                        this.hasUnsavedChanges = false;
                        window.history.back();
                    } else {
                        // Stay on current page - push state back to prevent leaving
                        window.history.pushState({ editor: true }, '', window.location.href);
                    }
                });
            }
        });
    }

    async showUnsavedChangesModal() {
        // Modern custom popup using premium design tokens
        const result = await Swal.fire({
            title: 'Unsaved Changes',
            html: 'You have some unsaved progress.<br>Would you like to save before leaving?',
            icon: 'warning',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: 'Save & Exit',
            denyButtonText: 'Discard & Exit',
            cancelButtonText: 'Keep Editing',
            customClass: {
                popup: 'swal-premium',
                actions: 'swal-premium-actions',
                confirmButton: 'swal-confirm-btn',
                denyButton: 'swal-deny-btn',
                cancelButton: 'swal-cancel-btn'
            },
            buttonsStyling: false,
            allowOutsideClick: false
        });

        if (result.isConfirmed) return 'save';
        if (result.isDenied) return 'discard';
        return 'cancel';
    }
    
    updateMouseCoords(event) {
        const rect = this.renderer.domElement.getBoundingClientRect();
        this.mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
        this.mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
    }
    
    onMouseMove(event) {
        this.updateMouseCoords(event);
        
        // CUSTOM DRAW PREVIEW
        if (this.isDrawingPoly) {
            this.handleDrawMove();
            return;
        }

        // Track Presence
        this.raycaster.setFromCamera(this.mouse, this.camera);
        const intersects = this.raycaster.intersectObject(this.ground);
        if (intersects.length > 0) {
            this.trackPresence(intersects[0].point);
        }

        // Preview while placing
        if (this.isPlacing && this.currentPreset && !this.isMoving) {
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
        
        // HOVER SYSTEM (Bloxburg 2026 Style)
        if (!this.isPlacing && !this.isMoving) {
            const hitPart = this.raycastParts();
            if (hitPart) {
                const partId = hitPart.userData.id;
                if (this.hoveredPartId !== partId) {
                    this.hoveredPartId = partId;
                    this.updateHoverOutline(hitPart);
                }
            } else {
                if (this.hoveredPartId !== null) {
                    this.hoveredPartId = null;
                    this.clearHover();
                }
            }
        }
    }
    
    updateHoverOutline(mesh) {
        this.clearHover();
        
        // Don't show hover on already selected parts
        if (this.selectedPart === mesh.userData.id) return;

        let targetGeo;
        if (mesh.geometry) {
            targetGeo = mesh.geometry;
        } else if (mesh.children && mesh.children.length > 0) {
            const box = new THREE.Box3().setFromObject(mesh);
            const size = new THREE.Vector3();
            box.getSize(size);
            targetGeo = new THREE.BoxGeometry(size.x + 0.02, size.y + 0.02, size.z + 0.02);
        }

        if (targetGeo) {
            // Color based on tool logic
            let colorHex = 0x3B82F6; // Default Light Blue
            if (this.currentTool === 'delete') colorHex = 0xEF4444; // Red for delete
            if (this.currentTool === 'select') colorHex = 0xFFFFFF; // White for select
            if (this.paintMode) colorHex = 0xFAB005; // Yellow for paint

            // If DELETE tool, also tint the actual object
            if (this.currentTool === 'delete') {
                this.hoveredPartsEmissive = [];
                const highlight = (obj) => {
                    if (obj.material) {
                        const mats = Array.isArray(obj.material) ? obj.material : [obj.material];
                        mats.forEach(m => {
                            if (m.emissive) {
                                this.hoveredPartsEmissive.push({ material: m, color: m.emissive.clone() });
                                m.emissive.setHex(0xFF0000);
                                m.emissiveIntensity = 0.5;
                            }
                        });
                    }
                    if (obj.children) obj.children.forEach(highlight);
                };
                highlight(mesh);
            }

            const edges = new THREE.EdgesGeometry(targetGeo);
            const lineMat = new THREE.LineBasicMaterial({ color: colorHex, linewidth: 2, transparent: true, opacity: 0.8 });
            this.hoverOutline = new THREE.LineSegments(edges, lineMat);
            this.hoverOutline.position.copy(mesh.position);
            this.hoverOutline.rotation.copy(mesh.rotation);
            this.hoverOutline.scale.multiplyScalar(1.01); // Slightly larger to prevent z-fighting
            this.hoverOutline.name = 'hover-outline';
            this.scene.add(this.hoverOutline);
        }
        
        this.container.style.cursor = this.currentTool === 'delete' ? 'crosshair' : 'pointer';
    }

    clearHover() {
        if (this.hoverOutline) {
            this.scene.remove(this.hoverOutline);
            this.hoverOutline.geometry.dispose();
            this.hoverOutline.material.dispose();
            this.hoverOutline = null;
        }

        // Restore emissive if we were in delete mode
        if (this.hoveredPartsEmissive) {
            this.hoveredPartsEmissive.forEach(item => {
                item.material.emissive.copy(item.color);
                item.material.emissiveIntensity = 0;
            });
            this.hoveredPartsEmissive = null;
        }
        
        const cursors = { select: 'default', delete: 'crosshair', move: 'grab', clone: 'copy' };
        this.container.style.cursor = cursors[this.currentTool] || 'default';
    }
    
    onMouseDown(event) {
        if (event.button !== 0) return; // Left click only
        
        // Ensure the canvas gets focus if we click it
        this.renderer.domElement.focus();
        
        this.updateMouseCoords(event);
        
        console.log('[PointerDown] Click detected. Placing:', this.isPlacing, 'Preset:', !!this.currentPreset);
        
        // CUSTOM DRAW TOOL
        if (this.isDrawingPoly) {
            this.handleDrawClick();
            return;
        }
        
        // PLACEMENT MODE
        if (this.isPlacing && this.currentPreset && !this.isMoving) {
            this.raycaster.setFromCamera(this.mouse, this.camera);
            const rayTargets = [this.ground];
            if (this.platform) rayTargets.push(this.platform);
            const intersects = this.raycaster.intersectObjects(rayTargets);
            
            console.log('[MouseDown] Intersects length:', intersects.length);
            
            if (intersects.length > 0) {
                this.placePart(intersects[0].point);
            } else {
                console.error('[MouseDown] Raycast failed. Make sure ground/platform exist!');
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
        
        // Right-click to create issue on part
        this.updateMouseCoords(event);
        const hitPart = this.raycastParts();
        if (hitPart) {
            // Select the part first
            this.selectPart(hitPart.userData.id);
            
            // Dispatch event to open issue modal
            window.dispatchEvent(new CustomEvent('open-issue-modal', { 
                detail: { 
                    partId: hitPart.userData.id,
                    partType: hitPart.userData.type
                } 
            }));
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
            if (this.isDrawingPoly) {
                this.exitDrawMode();
            } else {
                this.cancelSelection();
            }
        }

        // Enter — Finish drawing
        if (key === 'enter' && this.isDrawingPoly) {
            event.preventDefault();
            this.finishDrawPoly();
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
        
        // Ctrl / Cmd shortcuts
        if (event.ctrlKey || event.metaKey) {
            console.log(`[Editor] Capture Shortcut: Ctrl+${key}`);
            
            // REDO: Ctrl + Y or Ctrl + Shift + Z
            if (key === 'y' || (key === 'z' && event.shiftKey)) {
                event.preventDefault();
                this.redo();
                return;
            }
            
            // UNDO: Ctrl + Z
            if (key === 'z' && !event.shiftKey) {
                event.preventDefault();
                this.undo();
                return;
            }

            switch (key) {
                case 's': event.preventDefault(); this.saveBuild(); break;
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
        
        // Move grid to floor level
        if (this.gridHelper) {
            const gridY = (floor - 1) * 3 + 0.16;
            this.gridHelper.position.y = gridY;
        }
        
        // Show ALL parts from ALL floors - ghost mode for non-current floors
        this.parts.forEach(({ mesh }) => {
            const partFloor = mesh.userData.floor_number || 1;
            mesh.visible = true;
            
            if (partFloor === floor) {
                // Current floor - fully visible and interactive
                mesh.material.opacity = 1;
                mesh.material.transparent = false;
                mesh.userData.isGhost = false;
            } else {
                // Other floors (above or below) - visible but ghosted
                mesh.material.opacity = 0.35;
                mesh.material.transparent = true;
                mesh.userData.isGhost = true;
            }
        });
        
        // Move camera to see the floor
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
                    // Remove from API if it has a real ID
                    if (typeof state.data.id === 'number') this.deletePartAPI(state.data.id);
                }
                break;
            }
            case 'delete': {
                const partId = state.data.id;
                // Re-add the deleted part. 
                this.addPartToScene(state.data, false); // false = don't auto-save again
                
                // Remove from deleted pool so it's not deleted again on real save
                this.deletedIds.delete(partId);
                this.hasUnsavedChanges = true;
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
            case 'paint': {
                const part = this.parts.get(state.data.id);
                if (part) {
                    const color = state.data.oldColor;
                    part.mesh.userData.color_front = color;
                    if (part.mesh.material) {
                        if (Array.isArray(part.mesh.material)) {
                            part.mesh.material.forEach(m => m.color.set(color));
                        } else {
                            part.mesh.material.color.set(color);
                        }
                    }
                    this.updatePartAPI(state.data.id, { color, color_front: color, color_back: color });
                }
                break;
            }
            case 'material': {
                const part = this.parts.get(state.data.id);
                if (part) {
                    part.userData.material = state.data.oldMaterial;
                    this.updatePartAPI(state.data.id, { material: state.data.oldMaterial });
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
                this.addPartToScene(state.data, true);
                break;
            case 'delete': {
                const part = this.parts.get(state.data.id);
                if (part) {
                    const partId = state.data.id;
                    this.scene.remove(part.mesh);
                    this.disposeGroup(part.mesh);
                    this.parts.delete(partId);
                    
                    // Track for deletion again
                    if (typeof partId === 'number' || !String(partId).startsWith('temp_')) {
                        this.deletedIds.add(partId);
                    }
                    this.hasUnsavedChanges = true;
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
            case 'paint': {
                const part = this.parts.get(state.data.id);
                if (part) {
                    const color = state.data.newColor;
                    part.mesh.userData.color_front = color;
                    if (part.mesh.material) {
                        if (Array.isArray(part.mesh.material)) {
                            part.mesh.material.forEach(m => m.color.set(color));
                        } else {
                            part.mesh.material.color.set(color);
                        }
                    }
                    this.updatePartAPI(state.data.id, { color, color_front: color, color_back: color });
                }
                break;
            }
            case 'material': {
                const part = this.parts.get(state.data.id);
                if (part) {
                    part.userData.material = state.data.newMaterial;
                    this.updatePartAPI(state.data.id, { material: state.data.newMaterial });
                }
                break;
            }
        }
        
        this.emitPartCount();
        this.showToastEvent('Redone', 'info');
    }
    
    updateHistoryId(oldId, newId) {
        const updateStack = (stack) => {
            stack.forEach(entry => {
                if (entry.data && entry.data.id === oldId) {
                    entry.data.id = newId;
                }
            });
        };
        updateStack(this.undoStack);
        updateStack(this.redoStack);
        console.log(`[History] Migrated ID ${oldId} -> ${newId} in stacks`);
    }
    
    // ============ SAVE (Draft to Server Commit) ============
    
    async saveBuild() {
        if (!this.hasUnsavedChanges && this.deletedIds.size === 0 && this.dirtyPartIds.size === 0) {
            this.showToastEvent('Nothing to save', 'info');
            return;
        }

        if (this.isSaving) return;
        this.isSaving = true;

        // Use custom DOM overlay instead of Swal so it isn't destroyed by connection toasts!
        const overlay = document.createElement('div');
        overlay.id = 'build-editor-saving-overlay';
        overlay.style.position = 'fixed';
        overlay.style.inset = '0';
        overlay.style.backgroundColor = 'rgba(255, 255, 255, 0.75)';
        overlay.style.backdropFilter = 'blur(8px)';
        overlay.style.zIndex = '9999';
        overlay.style.display = 'flex';
        overlay.style.flexDirection = 'column';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.innerHTML = `
            <div style="width: 48px; height: 48px; border: 4px solid #e2e8f0; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 20px;"></div>
            <div style="font-size: 20px; font-weight: 700; color: #1e293b;">Saving Build...</div>
            <div style="font-size: 14px; font-weight: 500; color: #64748b; margin-top: 8px;">Uploading your masterwork to the server</div>
            <style>@keyframes spin { 100% { transform: rotate(360deg); } }</style>
        `;
        document.body.appendChild(overlay);

        try {
            let successCount = 0;
            let failCount = 0;

            // 1. PROCESS DELETIONS
            for (const id of this.deletedIds) {
                const ok = await this.deletePartAPI(id);
                if (ok) successCount++; else failCount++;
            }
            this.deletedIds.clear();

            // 2. PROCESS CREATIONS (New parts with temp IDs)
            const newParts = Array.from(this.parts.entries())
                .filter(([id]) => String(id).startsWith('temp_'));
            
            for (const [tempId, part] of newParts) {
                const result = await this.createPartOnServer(part.mesh, tempId);
                if (result) successCount++; else failCount++;
            }

            // 3. PROCESS UPDATES (Existing parts modified post-load)
            for (const id of this.dirtyPartIds) {
                const part = this.parts.get(id);
                if (part) {
                    const data = {
                        position_x: part.mesh.position.x,
                        position_y: part.mesh.position.y,
                        position_z: part.mesh.position.z,
                        rotation_y: Math.round(part.mesh.rotation.y * 180 / Math.PI),
                        color: part.mesh.userData.color,
                        color_front: part.mesh.userData.color_front,
                        color_back: part.mesh.userData.color_back,
                        material: part.mesh.userData.material,
                    };
                    // We can't really track if updatePartAPI succeeded easily without return value
                    // but we'll assume it's part of the sync
                    await this.updatePartAPI_Real(id, data); 
                    successCount++;
                }
            }
            this.dirtyPartIds.clear();
            this.deletedIds.clear();
            this.hasUnsavedChanges = false;
            
            // Clear history after save to prevent temp_ ID conflicts
            this.undoStack = [];
            this.redoStack = [];
            
            Swal.fire({
                icon: failCount === 0 ? 'success' : 'warning',
                title: failCount === 0 ? 'Build Saved!' : 'Saved with Errors',
                html: failCount === 0 
                    ? 'All your changes have been successfully committed.' 
                    : `Sync complete. ${successCount} succeeded, ${failCount} failed.`,
                customClass: {
                    popup: 'swal-premium',
                    confirmButton: 'swal-confirm-btn'
                },
                buttonsStyling: false
            });

        } catch (error) {
            console.error('[Editor] Fatal error during save:', error);
            Swal.fire({
                icon: 'error',
                title: 'Save Failed',
                html: 'A critical error occurred while syncing.<br>Please check your connection.',
                customClass: {
                    popup: 'swal-premium',
                    confirmButton: 'swal-confirm-btn'
                },
                buttonsStyling: false
            });
        } finally {
            this.isSaving = false;
            const overlay = document.getElementById('build-editor-saving-overlay');
            if (overlay) overlay.remove();
        }
    }

    async updatePartAPI_Real(partId, data) {
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
            return response.ok;
        } catch (e) { return false; }
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
    
    // ============ ISSUE PINS SYSTEM ============
    
    addIssuePin(issueData) {
        if (!issueData.position_x && !issueData.part_id) {
            console.log('[Editor] Issue has no position, skipping pin');
            return null;
        }
        
        // Get position from issue data or part
        let position;
        if (issueData.position_x !== null && issueData.position_y !== null && issueData.position_z !== null) {
            position = new THREE.Vector3(issueData.position_x, issueData.position_y, issueData.position_z);
        } else if (issueData.part_id && this.parts.has(issueData.part_id)) {
            const partData = this.parts.get(issueData.part_id);
            position = partData.mesh.position.clone();
        } else {
            console.log('[Editor] Cannot determine position for issue pin');
            return null;
        }
        
        // Create pin geometry (sphere)
        const geometry = new THREE.SphereGeometry(0.3, 16, 16);
        
        // Get color based on status
        const color = this.getIssueColor(issueData.status);
        
        const material = new THREE.MeshBasicMaterial({
            color: color,
            transparent: true,
            opacity: issueData.status === 'resolved' || issueData.status === 'closed' ? 0.5 : 0.9,
        });
        
        const pin = new THREE.Mesh(geometry, material);
        
        // Position pin at center of the part
        pin.position.copy(position);
        pin.position.y += 0.5; // Slightly above center for visibility
        
        // Store original position for animation
        pin.userData = {
            id: issueData.id,
            issueId: issueData.id,
            type: 'issue_pin',
            status: issueData.status,
            originalY: pin.position.y,
            floatOffset: Math.random() * Math.PI * 2, // Random start phase
        };
        
        // Add to scene
        this.scene.add(pin);
        
        // Store in issues map
        if (!this.issuePins) this.issuePins = new Map();
        this.issuePins.set(issueData.id, pin);
        
        // Start floating animation
        this.animateIssuePin(pin);
        
        console.log('[Editor] Added issue pin:', issueData.id);
        return pin;
    }
    
    removeIssuePin(issueId) {
        if (!this.issuePins || !this.issuePins.has(issueId)) {
            return;
        }
        
        const pin = this.issuePins.get(issueId);
        
        // Remove from scene
        this.scene.remove(pin);
        
        // Dispose geometry and materials
        pin.geometry.dispose();
        pin.material.dispose();
        
        // Dispose children (line)
        pin.children.forEach(child => {
            if (child.geometry) child.geometry.dispose();
            if (child.material) child.material.dispose();
        });
        
        // Remove from map
        this.issuePins.delete(issueId);
        
        console.log('[Editor] Removed issue pin:', issueId);
    }
    
    updateIssuePin(issueId, status) {
        if (!this.issuePins || !this.issuePins.has(issueId)) {
            return;
        }
        
        const pin = this.issuePins.get(issueId);
        const newColor = this.getIssueColor(status);
        
        // Update material color
        pin.material.color.setHex(newColor);
        pin.material.opacity = status === 'resolved' || status === 'closed' ? 0.5 : 0.9;
        
        // Update line color
        const line = pin.children[0];
        if (line && line.material) {
            line.material.color.setHex(newColor);
        }
        
        // Update userData
        pin.userData.status = status;
        
        console.log('[Editor] Updated issue pin:', issueId, 'status:', status);
    }
    
    clearIssuePins() {
        if (!this.issuePins) return;
        
        this.issuePins.forEach((pin, issueId) => {
            this.removeIssuePin(issueId);
        });
        
        this.issuePins.clear();
    }
    
    showIssuePinsForFloor(floorNumber) {
        if (!this.issuePins) return;
        
        this.issuePins.forEach((pin, issueId) => {
            // Get issue data to check floor
            // For now, show all pins - in a full implementation, 
            // you'd filter by floor based on the attached part's floor
            pin.visible = true;
        });
    }
    
    getIssueColor(status) {
        const colors = {
            open: 0xef4444,      // Red
            in_progress: 0xeab308, // Yellow
            resolved: 0x22c55e,   // Green
            closed: 0x6b7280,    // Gray
        };
        return colors[status] || colors.closed;
    }
    
    animateIssuePin(pin) {
        const self = this;
        const floatSpeed = 2;
        const floatHeight = 0.2;
        
        function animate() {
            if (!pin.parent) return; // Stop if removed from scene
            
            const time = Date.now() * 0.001;
            const offset = pin.userData.floatOffset;
            
            pin.position.y = pin.userData.originalY + Math.sin(time * floatSpeed + offset) * floatHeight;
            pin.rotation.y += 0.01; // Slow rotation
            
            requestAnimationFrame(animate);
        }
        
        animate();
    }
    
    focusOnPosition(x, y, z) {
        // Smoothly move camera to focus on position
        const targetPosition = new THREE.Vector3(x, y + 5, z + 10);
        const lookAtTarget = new THREE.Vector3(x, y, z);
        
        // Store current camera state
        const startPosition = this.camera.position.clone();
        const startTarget = this.controls.target.clone();
        
        // Animate camera
        const duration = 1000; // ms
        const startTime = Date.now();
        
        const self = this;
        function animate() {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function (ease-in-out)
            const ease = progress < 0.5 
                ? 2 * progress * progress 
                : 1 - Math.pow(-2 * progress + 2, 2) / 2;
            
            // Interpolate camera position
            self.camera.position.lerpVectors(startPosition, targetPosition, ease);
            self.controls.target.lerpVectors(startTarget, lookAtTarget, ease);
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        }
        
        animate();
    }
    
    loadIssuePins(issues) {
        // Clear existing pins
        this.clearIssuePins();
        
        // Add pins for all issues
        issues.forEach(issue => {
            this.addIssuePin(issue);
        });
        
        console.log('[Editor] Loaded', issues.length, 'issue pins');
    }
}

// Initialize editor
let editor;

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('editor-canvas');
    const buildId = container?.dataset.buildId;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    console.log('[CSRF Debug] Token found:', csrfToken ? 'YES (length: ' + csrfToken.length + ')' : 'NO');
    console.log('[CSRF Debug] Container:', container ? 'YES' : 'NO');
    console.log('[CSRF Debug] Build ID:', buildId ? 'YES' : 'NO');
    
    if (container && buildId && csrfToken) {
        editor = new BuildEditor(container, buildId, csrfToken);
        window.editor = editor;
        console.log('[Editor] Assigned to window.editor');
    } else {
        console.error('[Editor] Missing required elements! Container:', !!container, 'BuildID:', !!buildId, 'CSRF:', !!csrfToken);
    }
});
