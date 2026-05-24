// SpatialSync - Three.js Build Editor
// Bloxburg 2026 Build Mode - Full Feature Set
// UPDATED: 2026-04-09

const DEBUG_MODE = typeof window !== 'undefined' && window.DEBUG_MODE === true;

class BuildEditor {
  constructor(container, buildId, csrfToken) {
    if (DEBUG_MODE) {
      console.log('[Editor] Initializing with build ID:', buildId);
    }

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

    // Mobile / Touch Detection
    this.isMobile =
      /Android|iPhone|iPad|iPod/i.test(navigator.userAgent) || window.innerWidth <= 768;
    this.isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
    this.longPressTimer = null;
    this.longPressTriggered = false;
    this.longPressDuration = 500; // ms
    this.tapFeedbackEl = null;
    this.lastTapTime = 0;
    this.doubleTapThreshold = 300; // ms

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

    // Auto-Save State
    this.autoSaveTimer = null;
    this.autoSaveDelay = 2000; // 2 seconds debounce
    this.lastAutoSave = 0;

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
    this.gridUnits = 30;
    this.gridHalfSize = 15;
    this.minBound = 0.5;
    this.maxBound = 29.5;

    // COLLABORATION STATE
    this.rtChannel = null;
    this.userRole = 'viewer';
    this.remoteCursors = new Map(); // userId -> { mesh, label }
    this.lastPresenceSent = 0;

    // Platform mesh
    this.platform = null;
    this.platformEdges = null;

    // Object types
    this.EDGE_PLACED_TYPES = ['wall', 'fence'];
    this.CENTER_PLACED_TYPES = ['floor', 'roof', 'stairs', 'furniture', 'landscape'];
    this.WALL_ATTACHED_TYPES = ['door', 'window'];

    // API endpoints (using /editor/ prefix for proper CSRF handling)
    this.api = {
      parts: `/editor/builds/${buildId}/parts`,
      build: `/editor/builds/${buildId}`,
    };

    if (DEBUG_MODE) {
      console.log('[Editor] Calling init...');
    }
    this.init();
  }

  async init() {
    if (DEBUG_MODE) {
      console.log('[Editor] init() called');
    }

    await this.setupScene();
    this.setupEventListeners();

    // Push a state to history so we can intercept the Back button
    window.history.pushState({ editor: true }, '', window.location.href);

    // Supabase Realtime is now handled by Alpine.js in show.blade.php

    if (DEBUG_MODE) {
      console.log('[Editor] Loading parts...');
    }
    await this.loadParts();

    // Show mobile action bar on touch devices
    if (this.isTouchDevice) {
      const actionBar = document.getElementById('mobile-action-bar');
      if (actionBar) {
        actionBar.style.display = 'flex';
      }
    }

    // Minimap disabled per user request
    // this.createMinimap();

    this.animate();

    this.updateDebugInfo('Ready - Select a part to start building!');

    if (DEBUG_MODE) {
      console.log('[Editor] Initialization complete');
    }
  }

  // ============ SUPABASE REALTIME ============

  trackPresence(mousePos) {
    if (!this.rtChannel) {
      return;
    }

    const now = Date.now();
    if (now - this.lastPresenceSent < 500) {
      return;
    }

    if (this._lastCursorPos) {
      const dx = mousePos.x - this._lastCursorPos.x;
      const dy = mousePos.y - this._lastCursorPos.y;
      const dz = mousePos.z - this._lastCursorPos.z;
      const dist = Math.sqrt(dx * dx + dy * dy + dz * dz);
      if (dist < 0.5) {
        return;
      }
    }
    this._lastCursorPos = { x: mousePos.x, y: mousePos.y, z: mousePos.z };

    const userName =
      document.querySelector('.editor-topbar__title')?.textContent.split(' - ')[1] ||
      'Collaborator';

    this.rtChannel.track({
      cursor: { x: mousePos.x, y: mousePos.y, z: mousePos.z },
      name: userName,
      role: this.userRole,
      ts: now,
    });

    this.lastPresenceSent = now;
  }

  updateRemoteCursors(presenceState) {
    if (DEBUG_MODE && Object.keys(presenceState).length > 0) {
      console.log('[RT] Presence Sync:', Object.keys(presenceState).length, 'user(s)');
    }
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
      if (userId === this.myPresenceKey) {
        continue;
      }

      const userState = presenceState[userId][0];
      if (!userState || !userState.cursor) {
        continue;
      }

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
    if (!part) {
      return;
    }

    // Apply visual updates
    if (newData.position_x !== undefined) {
      part.mesh.position.set(newData.position_x, newData.position_y, newData.position_z);
      part.data.position_x = newData.position_x;
      part.data.position_y = newData.position_y;
      part.data.position_z = newData.position_z;
    }

    if (newData.rotation_y !== undefined) {
      part.mesh.rotation.y = ((newData.rotation_y || 0) * Math.PI) / 180;
      part.data.rotation_y = newData.rotation_y;
    }

    if (newData.color !== undefined) {
      part.mesh.userData.color = newData.color;
      part.data.color = newData.color;

      const applyColor = (mat) => {
        if (mat && mat.color && typeof mat.color.set === 'function') {
          if (mat.transparent || mat.opacity < 1) {
            return;
          }
          mat.color.set(newData.color);
        }
      };
      part.mesh.traverse((child) => {
        if (child.isMesh && child.material) {
          if (Array.isArray(child.material)) {
            child.material.forEach(applyColor);
          } else {
            applyColor(child.material);
          }
        }
      });
    }
  }

  // ============ SCENE SETUP ============

  // ============ DISPOSAL HELPERS ============

  disposeGroup(obj) {
    if (!obj) {
      return;
    }

    // If it's a group, recurse into children
    if (obj.children && obj.children.length > 0) {
      // Copy array since we modify it
      const children = [...obj.children];
      children.forEach((child) => this.disposeGroup(child));
    }

    // Dispose geometry
    if (obj.geometry) {
      obj.geometry.dispose();
    }

    // Dispose material(s)
    if (obj.material) {
      if (Array.isArray(obj.material)) {
        obj.material.forEach((m) => {
          if (m.map) {
            m.map.dispose();
          }
          m.dispose();
        });
      } else {
        if (obj.material.map) {
          obj.material.map.dispose();
        }
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
      if (excludePartId && partId === excludePartId) {
        continue;
      }
      if (partData.data.floor_number !== floorNumber) {
        continue;
      }

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
        if (
          Math.abs(partX - x) < this.gridSize * 0.9 &&
          Math.abs(partZ - z) < this.gridSize * 0.9
        ) {
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
      if (partData.data.type !== 'wall') {
        continue;
      }
      if (partData.data.floor_number !== floorNumber) {
        continue;
      }

      const wallX = partData.data.position_x;
      const wallZ = partData.data.position_z;

      const distance = Math.sqrt(Math.pow(wallX - x, 2) + Math.pow(wallZ - z, 2));

      if (distance < nearestDistance) {
        nearestDistance = distance;
        nearestWall = partData;
      }
    }

    return nearestWall;
  }

  findWallAtGridEdge(gridX, gridZ, floorNumber) {
    const tolerance = 0.5; // half a grid unit — covers any snap rounding

    for (const [partId, partData] of this.parts) {
      if (partData.data.type !== 'wall') {
        continue;
      }
      if (partData.data.floor_number !== floorNumber) {
        continue;
      }

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
      if (partData.data.floor_number !== floorNumber) {
        continue;
      }

      const type = partData.data.type;
      if (type !== 'door' && type !== 'window') {
        continue;
      }

      if (
        Math.abs(partData.data.position_x - x) < tolerance &&
        Math.abs(partData.data.position_z - z) < tolerance
      ) {
        return { type, part: partData };
      }
    }
    return null;
  }

  // ============ SCENE SETUP ============

  async setupScene() {
    this.scene = new THREE.Scene();
    // Crisp, professional sky blue background
    this.scene.background = new THREE.Color(0x87ceeb);

    // Fog — hides scenery edges, creates depth
    this.scene.fog = new THREE.Fog(0x87ceeb, 60, 280);

    const aspect = this.container.clientWidth / this.container.clientHeight;
    this.camera = new THREE.PerspectiveCamera(45, aspect, 0.1, 1000);
    this.camera.position.set(35, 28, 35);
    this.camera.lookAt(15, 0, 15);

    this.renderer = new THREE.WebGLRenderer({
      antialias: true,
      powerPreference: 'high-performance',
    });
    this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
    this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    this.renderer.shadowMap.enabled = true;
    this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    // ACES tone mapping gives realistic exposure — needed for PBR clearcoat
    this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
    this.renderer.toneMappingExposure = 1.1;

    this.container.appendChild(this.renderer.domElement);

    // Sky hemisphere light — warm sky top, cool ground bounce
    const hemiLight = new THREE.HemisphereLight(0xddeeff, 0x8899aa, 0.8);
    hemiLight.position.set(0, 200, 0);
    this.scene.add(hemiLight);

    // Flat ambient for baseline fill
    this.ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
    this.scene.add(this.ambientLight);

    // Primary sun — warm afternoon light
    this.directionalLight = new THREE.DirectionalLight(0xfff0d0, 1.4);
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

    // Soft fill light from opposite side — removes harsh shadows, studio look
    const fillLight = new THREE.DirectionalLight(0xcce8ff, 0.4);
    fillLight.position.set(-15, 20, -10);
    this.scene.add(fillLight);

    // PMREM environment map — gives clearcoat and metalness realistic reflections
    // Note: RoomEnvironment available in r137+. For r128, we build a simple gradient env.
    try {
      const pmremGenerator = new THREE.PMREMGenerator(this.renderer);
      pmremGenerator.compileEquirectangularShader();
      // Create a simple gradient sky sphere as env source
      const envGeo = new THREE.SphereGeometry(50, 32, 16);
      const envMat = new THREE.MeshBasicMaterial({ side: THREE.BackSide });
      envMat.color.set(0xddeeff);
      const envSphere = new THREE.Mesh(envGeo, envMat);
      // Render a quick scene for the env map
      const envScene = new THREE.Scene();
      envScene.add(envSphere);
      envScene.add(new THREE.AmbientLight(0xffffff, 1));
      const envLight = new THREE.DirectionalLight(0xfff0d0, 1);
      envLight.position.set(1, 2, 1);
      envScene.add(envLight);
      const renderTarget = pmremGenerator.fromScene(envScene, 0.04);
      this.scene.environment = renderTarget.texture;
      pmremGenerator.dispose();
    } catch (e) {
      // Silently skip if PMREM not supported in this build
      console.log('[Editor] PMREM env skip:', e.message);
    }

    // Ground plane (invisible, for raycasting)
    const groundGeometry = new THREE.PlaneGeometry(500, 500);
    const groundMaterial = new THREE.MeshBasicMaterial({ visible: false });
    this.ground = new THREE.Mesh(groundGeometry, groundMaterial);
    this.ground.rotation.x = -Math.PI / 2;
    this.ground.position.y = 0;
    this.ground.name = 'ground';
    this.scene.add(this.ground);

    // Platform
    this.createPlatform();

    // Background scenery
    this.createEnvironment();

    // Grid helper
    this.rebuildGrid();

    // OrbitControls
    if (typeof OrbitControls !== 'undefined') {
      this.controls = new OrbitControls(this.camera, this.renderer.domElement);
      this.controls.enableDamping = true;
      this.controls.dampingFactor = 0.05;
      this.controls.minDistance = 5;
      this.controls.maxDistance = 120;
      this.controls.maxPolarAngle = Math.PI / 2 - 0.05;
      this.controls.target.set(15, 0, 15);
      this.controls.enablePan = true;
      this.controls.mouseButtons = {
        LEFT: null, // We handle left click ourselves
        MIDDLE: THREE.MOUSE.DOLLY,
        RIGHT: THREE.MOUSE.PAN,
      };

      // Mobile touch configuration — free up one-finger for editor raycasting
      if (this.isTouchDevice) {
        this.controls.touches = {
          ONE: THREE.TOUCH.ROTATE, // One finger rotate (only when NOT placing)
          TWO: THREE.TOUCH.DOLLY_PAN, // Two fingers zoom + pan
        };
      }
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
    // 2026 Industry Standard: ultra-thin, barely-there grid lines
    this.gridHelper = new THREE.GridHelper(this.gridUnits, divisions, 0x94a3b8, 0xcbd5e1);
    this.gridHelper.material.transparent = true;
    this.gridHelper.material.opacity = 0.25;
    this.gridHelper.position.set(this.gridUnits / 2, 0.002, this.gridUnits / 2);
    this.scene.add(this.gridHelper);
  }

  createPlatform() {
    if (this.platform) {
      this.scene.remove(this.platform);
      this.platform.geometry.dispose();
      if (Array.isArray(this.platform.material)) {
        this.platform.material.forEach((m) => m.dispose());
      } else {
        this.platform.material.dispose();
      }
    }

    // Procedural checkerboard / grid texture for the platform
    const canvas = document.createElement('canvas');
    canvas.width = 512;
    canvas.height = 512;
    const ctx = canvas.getContext('2d');

    // Base: off-white
    ctx.fillStyle = '#f8fafc';
    ctx.fillRect(0, 0, 512, 512);

    // Subtle checker pattern (every 2 cells)
    const cell = 64; // 8 cells across 512px
    for (let row = 0; row < 8; row++) {
      for (let col = 0; col < 8; col++) {
        if ((row + col) % 2 === 0) {
          ctx.fillStyle = '#f1f5f9';
          ctx.fillRect(col * cell, row * cell, cell, cell);
        }
      }
    }

    // Main grid lines
    ctx.strokeStyle = '#e2e8f0';
    ctx.lineWidth = 2;
    ctx.beginPath();
    for (let i = 0; i <= 512; i += 128) {
      ctx.moveTo(i, 0);
      ctx.lineTo(i, 512);
      ctx.moveTo(0, i);
      ctx.lineTo(512, i);
    }
    ctx.stroke();

    const texture = new THREE.CanvasTexture(canvas);
    texture.wrapS = THREE.RepeatWrapping;
    texture.wrapT = THREE.RepeatWrapping;
    texture.repeat.set(this.gridUnits / 4, this.gridUnits / 4);

    const platformGeometry = new THREE.BoxGeometry(this.gridUnits, 0.3, this.gridUnits);
    const platformMaterial = new THREE.MeshStandardMaterial({
      map: texture,
      roughness: 0.4,
      metalness: 0.0,
    });
    this.platform = new THREE.Mesh(platformGeometry, platformMaterial);
    this.platform.position.set(this.gridUnits / 2, -0.15, this.gridUnits / 2);
    this.platform.receiveShadow = true;
    this.platform.name = 'platform';
    this.scene.add(this.platform);
  }

  createEnvironment() {
    this.sceneryObjects = [];
    this.setScenery(this.currentScenery || 'neighborhood');
  }

  setScenery(theme) {
    // Clear all existing scenery objects
    if (this.sceneryObjects) {
      this.sceneryObjects.forEach((obj) => {
        this.scene.remove(obj);
        if (obj.geometry) {
          obj.geometry.dispose();
        }
        if (obj.material) {
          if (Array.isArray(obj.material)) {
            obj.material.forEach((m) => m.dispose());
          } else {
            obj.material.dispose();
          }
        }
      });
    }
    this.sceneryObjects = [];
    this.currentScenery = theme;
    const cx = this.gridUnits / 2;
    const half = this.gridUnits / 2;

    const themes = {
      neighborhood: {
        skyDay: 0x8a929e,
        fog: 0x8a929e, // Darker overcast
        label: 'Modern Neighborhood',
      },
      nature: {
        skyDay: 0x4a5869,
        fog: 0x4a5869, // Darker slate
        label: 'Nature & Mountains',
      },
      urban: {
        skyDay: 0x87cefa,
        fog: 0xa0d8ef, // Bright sky blue (like reference image)
        label: 'Urban City',
      },
      desert: {
        skyDay: 0x4b3a5a,
        fog: 0x4b3a5a, // Moody Dusk / Twilight
        label: 'Desert Oasis',
      },
    };

    const t = themes[theme] || themes.neighborhood;

    // Update sky + fog
    if (!this.isNightMode) {
      this.scene.background = new THREE.Color(t.skyDay);
      this.scene.fog = new THREE.Fog(t.fog, 80, 400);
    }

    // Helper: Create InstancedMesh
    const createInstanced = (geometry, material, count, positionFn, scaleFn, rotationFn) => {
      const instancedMesh = new THREE.InstancedMesh(geometry, material, count);
      const dummy = new THREE.Object3D();
      for (let i = 0; i < count; i++) {
        const pos = positionFn(i);
        dummy.position.copy(pos);
        if (scaleFn) {
          const s = scaleFn(i);
          dummy.scale.set(s.x, s.y, s.z);
        }
        if (rotationFn) {
          const r = rotationFn(i);
          dummy.rotation.set(r.x, r.y, r.z);
        }
        dummy.updateMatrix();
        instancedMesh.setMatrixAt(i, dummy.matrix);
      }
      instancedMesh.instanceMatrix.needsUpdate = true;
      instancedMesh.castShadow = true;
      instancedMesh.receiveShadow = true;
      this.scene.add(instancedMesh);
      this.sceneryObjects.push(instancedMesh);
      return instancedMesh;
    };

    // Base Terrain Generation - High res for smooth ArchViz curves
    const terrainGeo = new THREE.PlaneGeometry(800, 800, 128, 128);
    terrainGeo.rotateX(-Math.PI / 2);
    const posAttr = terrainGeo.attributes.position;
    const centerVec = new THREE.Vector2(cx, cx);

    for (let i = 0; i < posAttr.count; i++) {
      const px = posAttr.getX(i);
      const pz = posAttr.getZ(i);
      const distToCenter = centerVec.distanceTo(new THREE.Vector2(px, pz));

      if (distToCenter > this.gridUnits + 10) {
        const blend = Math.min(1, (distToCenter - (this.gridUnits + 10)) / 40);
        if (theme === 'nature') {
          // Dramatic rocky foothills
          const h1 = Math.sin(px * 0.04) * Math.cos(pz * 0.04) * 8;
          const h2 = Math.sin(px * 0.015) * Math.cos(pz * 0.015) * 20;
          posAttr.setY(i, (h1 + h2) * blend - 0.31);
        } else if (theme === 'desert') {
          // Deep sweeping dunes
          const dune1 = Math.sin(px * 0.02 + pz * 0.01) * 12;
          const dune2 = Math.cos(px * 0.01 - pz * 0.02) * 18;
          posAttr.setY(i, (dune1 + dune2) * blend - 0.31);
        } else if (theme === 'neighborhood') {
          // Very subtle manicured slopes
          const wave = Math.sin(px * 0.02) * Math.cos(pz * 0.02) * 3;
          posAttr.setY(i, wave * blend - 0.31);
        } else {
          posAttr.setY(i, -0.31); // Urban flat
        }
      } else {
        posAttr.setY(i, -0.31);
      }
    }
    terrainGeo.computeVertexNormals();

    // High-End Texturing via Canvas (Archviz realism without massive image payloads)
    const generateTexture = (type) => {
      const canvas = document.createElement('canvas');
      canvas.width = 1024;
      canvas.height = 1024;
      const ctx = canvas.getContext('2d');

      if (type === 'grass') {
        ctx.fillStyle = '#2A3B2C'; // Dark rich base
        ctx.fillRect(0, 0, 1024, 1024);
        for (let i = 0; i < 40000; i++) {
          ctx.fillStyle = Math.random() > 0.5 ? '#314434' : '#233024';
          ctx.fillRect(Math.random() * 1024, Math.random() * 1024, 2, 2);
        }
      } else if (type === 'sand') {
        ctx.fillStyle = '#3A2818'; // Darker cooler twilight sand
        ctx.fillRect(0, 0, 1024, 1024);
        for (let i = 0; i < 30000; i++) {
          ctx.fillStyle = Math.random() > 0.5 ? '#4D3624' : '#2A1C12';
          ctx.fillRect(Math.random() * 1024, Math.random() * 1024, 1, 1);
        }
      } else if (type === 'asphalt') {
        ctx.fillStyle = '#1C1E21';
        ctx.fillRect(0, 0, 1024, 1024);
        for (let i = 0; i < 20000; i++) {
          ctx.fillStyle = Math.random() > 0.5 ? '#24272B' : '#141517';
          ctx.fillRect(Math.random() * 1024, Math.random() * 1024, 2, 2);
        }
      }
      const tex = new THREE.CanvasTexture(canvas);
      tex.wrapS = THREE.RepeatWrapping;
      tex.wrapT = THREE.RepeatWrapping;
      tex.repeat.set(40, 40);
      return tex;
    };

    let terrainMat;
    if (theme === 'desert') {
      terrainMat = new THREE.MeshStandardMaterial({ map: generateTexture('sand'), roughness: 0.9 });
    } else if (theme === 'nature') {
      terrainMat = new THREE.MeshStandardMaterial({ color: 0x1f2e22, roughness: 0.95 }); // Darker mossy ground for nature
    } else {
      // Urban and Neighborhood both use grass/park base now
      terrainMat = new THREE.MeshStandardMaterial({
        map: generateTexture('grass'),
        roughness: 0.95,
      });
    }

    const terrain = new THREE.Mesh(terrainGeo, terrainMat);
    terrain.position.set(cx, 0, cx);
    // Offset geometry to center
    terrainGeo.translate(-cx, 0, -cx);
    terrain.receiveShadow = true;
    this.scene.add(terrain);
    this.sceneryObjects.push(terrain);

    // ================== THEME SPECIFIC DETAILS ==================

    if (theme === 'neighborhood') {
      // Elegant Concrete Sidewalks
      const swMat = new THREE.MeshStandardMaterial({ color: 0x8c9299, roughness: 0.9 });
      [
        [cx, cx + half + 8, this.gridUnits + 6, 2],
        [cx, cx - half - 8, this.gridUnits + 6, 2],
        [cx + half + 8, cx, 2, this.gridUnits + 6],
        [cx - half - 8, cx, 2, this.gridUnits + 6],
      ].forEach(([bx, bz, bw, bd]) => {
        const s = new THREE.Mesh(new THREE.BoxGeometry(bw, 0.15, bd), swMat);
        s.position.set(bx, -0.23, bz);
        s.receiveShadow = true;
        this.scene.add(s);
        this.sceneryObjects.push(s);
      });

      // Archviz Dark Asphalt Roads
      const roadMat = new THREE.MeshStandardMaterial({ color: 0x24262a, roughness: 0.8 });
      const r1 = new THREE.Mesh(new THREE.PlaneGeometry(8, 400), roadMat);
      r1.rotation.x = -Math.PI / 2;
      r1.position.set(cx + half + 14, -0.28, cx);
      this.scene.add(r1);
      this.sceneryObjects.push(r1);
      const r2 = new THREE.Mesh(new THREE.PlaneGeometry(400, 8), roadMat);
      r2.rotation.x = -Math.PI / 2;
      r2.position.set(cx, -0.28, cx + half + 14);
      this.scene.add(r2);
      this.sceneryObjects.push(r2);

      // Reduced Trees for less clutter
      const treeCount = 40;
      // Detailed tree crown (Icosahedron looks sophisticated)
      const treeGeo = new THREE.IcosahedronGeometry(2.5, 1);
      treeGeo.translate(0, 4, 0);
      const treeMat = new THREE.MeshStandardMaterial({ color: 0x2a3e2d, roughness: 0.9 });

      createInstanced(
        treeGeo,
        treeMat,
        treeCount,
        () => {
          let tx, tz;
          do {
            tx = cx + (Math.random() - 0.5) * 200;
            tz = cx + (Math.random() - 0.5) * 200;
          } while (
            Math.abs(tx - cx) < this.gridUnits + 12 &&
            Math.abs(tz - cx) < this.gridUnits + 12
          );
          return new THREE.Vector3(tx, -0.3, tz);
        },
        () => {
          const s = 0.8 + Math.random() * 0.7;
          return new THREE.Vector3(s, s * 1.2, s);
        },
        () => new THREE.Vector3(0, Math.random() * Math.PI, 0),
      );

      // Tall Dark Trunks
      const trunkGeo = new THREE.CylinderGeometry(0.2, 0.3, 4.5);
      trunkGeo.translate(0, 2, 0);
      const trunkMat = new THREE.MeshStandardMaterial({ color: 0x1f1a17, roughness: 1.0 });
      const trees = this.sceneryObjects[this.sceneryObjects.length - 1];

      const trunks = createInstanced(
        trunkGeo,
        trunkMat,
        treeCount,
        () => new THREE.Vector3(),
        () => new THREE.Vector3(1, 1, 1),
      );

      const mat4 = new THREE.Matrix4();
      const pos = new THREE.Vector3();
      const quat = new THREE.Quaternion();
      const scale = new THREE.Vector3();
      for (let i = 0; i < treeCount; i++) {
        trees.getMatrixAt(i, mat4);
        mat4.decompose(pos, quat, scale);
        // Reset trunk rotation to straight up
        mat4.compose(pos, new THREE.Quaternion(), scale);
        trunks.setMatrixAt(i, mat4);
      }
      trunks.instanceMatrix.needsUpdate = true;

      // Streetlamps
      const lampCount = 16;
      const lampGeo = new THREE.CylinderGeometry(0.05, 0.1, 6);
      lampGeo.translate(0, 3, 0);
      const lampMat = new THREE.MeshStandardMaterial({
        color: 0x111111,
        metalness: 0.8,
        roughness: 0.2,
      });
      createInstanced(
        lampGeo,
        lampMat,
        lampCount,
        (i) => {
          const angle = (i / lampCount) * Math.PI * 2;
          const r = this.gridUnits + 18;
          return new THREE.Vector3(cx + Math.cos(angle) * r, -0.3, cx + Math.sin(angle) * r);
        },
        () => new THREE.Vector3(1, 1, 1),
      );
    } else if (theme === 'nature') {
      // Reduced pine forest for less clutter
      const pineCount = 150;
      const pineGeo = new THREE.ConeGeometry(1.5, 7, 6);
      pineGeo.translate(0, 3.5, 0);
      const pineMat = new THREE.MeshStandardMaterial({ color: 0x18281b, roughness: 1.0 });

      createInstanced(
        pineGeo,
        pineMat,
        pineCount,
        () => {
          let tx, tz;
          do {
            tx = cx + (Math.random() - 0.5) * 350;
            tz = cx + (Math.random() - 0.5) * 350;
          } while (
            Math.abs(tx - cx) < this.gridUnits + 15 &&
            Math.abs(tz - cx) < this.gridUnits + 15
          );
          const distToCenter = Math.sqrt(Math.pow(tx - cx, 2) + Math.pow(tz - cx, 2));
          let y = -0.31;
          if (distToCenter > this.gridUnits + 10) {
            const blend = Math.min(1, (distToCenter - (this.gridUnits + 10)) / 40);
            const h1 = Math.sin(tx * 0.04) * Math.cos(tz * 0.04) * 8;
            const h2 = Math.sin(tx * 0.015) * Math.cos(tz * 0.015) * 20;
            y = (h1 + h2) * blend - 0.31;
          }
          return new THREE.Vector3(tx, y, tz);
        },
        () => {
          const s = 0.8 + Math.random() * 1.5;
          return new THREE.Vector3(s, s * 1.2, s);
        },
        () => new THREE.Vector3(0, Math.random() * Math.PI, 0),
      );

      // Rocky, dramatic mountains
      const mtMat = new THREE.MeshStandardMaterial({ color: 0x3a4045, roughness: 0.9 });
      const peaks = [
        [cx - 180, cx - 120, 100, 120],
        [cx + 160, cx - 160, 140, 150],
        [cx - 150, cx + 160, 110, 130],
        [cx + 190, cx + 140, 160, 160],
      ];
      peaks.forEach(([x, z, h, r]) => {
        const geo = new THREE.ConeGeometry(r, h, 12);
        const pos = geo.attributes.position;
        for (let i = 0; i < pos.count; i++) {
          if (pos.getY(i) < h / 2 - 10) {
            pos.setX(i, pos.getX(i) + (Math.random() - 0.5) * 15);
            pos.setZ(i, pos.getZ(i) + (Math.random() - 0.5) * 15);
          }
        }
        geo.computeVertexNormals();
        const mt = new THREE.Mesh(geo, mtMat);
        mt.position.set(x, h / 2 - 10, z);
        this.scene.add(mt);
        this.sceneryObjects.push(mt);
      });
    } else if (theme === 'urban') {
      // Concrete Sidewalks
      const swMat = new THREE.MeshStandardMaterial({ color: 0x888c91, roughness: 0.8 });
      [
        [cx, cx + half + 8, this.gridUnits + 6, 2],
        [cx, cx - half - 8, this.gridUnits + 6, 2],
        [cx + half + 8, cx, 2, this.gridUnits + 6],
        [cx - half - 8, cx, 2, this.gridUnits + 6],
      ].forEach(([bx, bz, bw, bd]) => {
        const s = new THREE.Mesh(new THREE.BoxGeometry(bw, 0.2, bd), swMat);
        s.position.set(bx, -0.2, bz);
        this.scene.add(s);
        this.sceneryObjects.push(s);
      });

      // Road Grid
      const roadMat = new THREE.MeshStandardMaterial({ color: 0x1a1c1e, roughness: 0.7 });
      for (let i = -4; i <= 4; i++) {
        const r1 = new THREE.Mesh(new THREE.PlaneGeometry(400, 8), roadMat);
        r1.rotation.x = -Math.PI / 2;
        r1.position.set(cx, -0.28, cx + i * 40 + (i > 0 ? 14 : i < 0 ? -14 : 0));
        this.scene.add(r1);
        this.sceneryObjects.push(r1);

        const r2 = new THREE.Mesh(new THREE.PlaneGeometry(8, 400), roadMat);
        r2.rotation.x = -Math.PI / 2;
        r2.position.set(cx + i * 40 + (i > 0 ? 14 : i < 0 ? -14 : 0), -0.28, cx);
        this.scene.add(r2);
        this.sceneryObjects.push(r2);
      }

      // Detailed Archviz City Blocks (Hong Kong Residential Style)
      const bldCount = 120;
      const bldGeo = new THREE.BoxGeometry(1, 1, 1);
      bldGeo.translate(0, 0.5, 0);

      // Procedural Residential Facade Texture
      const generateFacadeTexture = () => {
        const canvas = document.createElement('canvas');
        canvas.width = 512;
        canvas.height = 512;
        const ctx = canvas.getContext('2d');

        // Base building wall color (Off-white)
        ctx.fillStyle = '#E2E8F0';
        ctx.fillRect(0, 0, 512, 512);

        const cols = 12; // Dense balconies
        const rows = 32; // Many floors
        const w = 512 / cols;
        const h = 512 / rows;
        const marginX = w * 0.15;
        const marginY = h * 0.15;

        for (let r = 0; r < rows; r++) {
          for (let c = 0; c < cols; c++) {
            // Window / Shadow interior
            ctx.fillStyle = '#334155';
            ctx.fillRect(c * w + marginX, r * h + marginY, w - marginX * 2, h - marginY * 2);

            // Balcony railing (light cyan/glass)
            ctx.fillStyle = '#BAE6FD';
            ctx.fillRect(c * w + marginX, r * h + h - marginY * 3, w - marginX * 2, marginY * 2);

            // Occasional AC unit or clothes line accent
            if (Math.random() > 0.7) {
              ctx.fillStyle = '#FFFFFF';
              ctx.fillRect(
                c * w + w - marginX * 2,
                r * h + marginY * 2,
                marginX * 1.5,
                marginY * 2,
              );
            }
          }
        }

        const tex = new THREE.CanvasTexture(canvas);
        tex.wrapS = THREE.RepeatWrapping;
        tex.wrapT = THREE.RepeatWrapping;
        return tex;
      };

      const bldTex = generateFacadeTexture();

      // Residential material (matte paint, not highly reflective metal)
      const bldMat = new THREE.MeshStandardMaterial({
        color: 0xf8fafc,
        map: bldTex,
        roughness: 0.8,
        metalness: 0.1,
      });

      const instancedBld = createInstanced(
        bldGeo,
        bldMat,
        bldCount,
        (i) => {
          let tx, tz;
          do {
            const blockX = Math.floor((Math.random() - 0.5) * 8);
            const blockZ = Math.floor((Math.random() - 0.5) * 8);
            tx = cx + blockX * 40 + (Math.random() - 0.5) * 20;
            tz = cx + blockZ * 40 + (Math.random() - 0.5) * 20;
          } while (
            Math.abs(tx - cx) < this.gridUnits + 15 &&
            Math.abs(tz - cx) < this.gridUnits + 15
          );
          return new THREE.Vector3(tx, -0.3, tz);
        },
        () => {
          // Taller, thinner proportions like HK apartments
          const w = 10 + Math.random() * 8;
          const d = 10 + Math.random() * 8;
          const h = 60 + Math.random() * 100 + (Math.random() > 0.8 ? 120 : 0);

          // Adjust texture repeat to maintain square windows
          bldTex.repeat.set(Math.round(w / 10), Math.round(h / 10));

          return new THREE.Vector3(w, h, d);
        },
      );

      // Subtle hue variations for different apartment blocks (Pale blue, pale green, white)
      const color = new THREE.Color();
      const palettes = [0xf8fafc, 0xe0f2fe, 0xdcfce7, 0xf1f5f9];
      for (let i = 0; i < bldCount; i++) {
        color.setHex(palettes[Math.floor(Math.random() * palettes.length)]);
        instancedBld.setColorAt(i, color);
      }
      instancedBld.instanceColor.needsUpdate = true;

      // Detailed Park Trees (Icosahedron + Trunks)
      const treeGeo = new THREE.IcosahedronGeometry(2.5, 1);
      treeGeo.translate(0, 3, 0);
      const treeMat = new THREE.MeshStandardMaterial({ color: 0x4ade80, roughness: 0.9 });

      const urbanTrees = createInstanced(
        treeGeo,
        treeMat,
        80,
        () => {
          let tx, tz;
          do {
            tx = cx + (Math.random() - 0.5) * 150;
            tz = cx + (Math.random() - 0.5) * 150;
          } while (
            Math.abs(tx - cx) < this.gridUnits + 10 &&
            Math.abs(tz - cx) < this.gridUnits + 10
          );
          return new THREE.Vector3(tx, -0.3, tz);
        },
        () => {
          const s = 0.5 + Math.random() * 0.8;
          return new THREE.Vector3(s, s * 1.2, s);
        },
        () => new THREE.Vector3(0, Math.random() * Math.PI, 0),
      );

      // Tree Trunks
      const trunkGeo = new THREE.CylinderGeometry(0.15, 0.25, 4);
      trunkGeo.translate(0, 1.5, 0);
      const trunkMat = new THREE.MeshStandardMaterial({ color: 0x3e2723, roughness: 1.0 });

      const trunks = createInstanced(
        trunkGeo,
        trunkMat,
        80,
        () => new THREE.Vector3(),
        () => new THREE.Vector3(1, 1, 1),
      );

      const mat4 = new THREE.Matrix4();
      const pos = new THREE.Vector3();
      const quat = new THREE.Quaternion();
      const scale = new THREE.Vector3();
      for (let i = 0; i < 80; i++) {
        urbanTrees.getMatrixAt(i, mat4);
        mat4.decompose(pos, quat, scale);
        // Reset trunk rotation to straight up
        mat4.compose(pos, new THREE.Quaternion(), scale);
        trunks.setMatrixAt(i, mat4);
      }
      trunks.instanceMatrix.needsUpdate = true;
    } else if (theme === 'desert') {
      // Smooth, massive sweeping dunes
      const duneMat = new THREE.MeshStandardMaterial({ color: 0xc18a62, roughness: 1.0 });
      const peaks = [
        [cx - 120, cx - 100, 30, 80],
        [cx + 140, cx - 120, 45, 100],
        [cx - 100, cx + 140, 25, 70],
        [cx + 130, cx + 130, 50, 110],
      ];
      peaks.forEach(([x, z, h, r]) => {
        const geo = new THREE.SphereGeometry(r, 32, 32, 0, Math.PI * 2, 0, Math.PI / 2);
        const mt = new THREE.Mesh(geo, duneMat);
        mt.scale.setY(h / r);
        mt.position.set(x, -2, z);
        this.scene.add(mt);
        this.sceneryObjects.push(mt);
      });

      // Archviz Water Oasis
      const waterGeo = new THREE.PlaneGeometry(60, 40, 16, 16);
      waterGeo.rotateX(-Math.PI / 2);
      const waterMat = new THREE.MeshStandardMaterial({
        color: 0x457885,
        roughness: 0.05,
        metalness: 0.9,
        transparent: true,
        opacity: 0.9,
      });
      const water = new THREE.Mesh(waterGeo, waterMat);
      water.position.set(cx + 80, -0.2, cx + 50);
      this.scene.add(water);
      this.sceneryObjects.push(water);

      // Palm Trees
      const palmGeo = new THREE.CylinderGeometry(0.1, 0.4, 7, 6);
      palmGeo.translate(0, 3.5, 0);
      const palmMat = new THREE.MeshStandardMaterial({ color: 0x3a2e24, roughness: 0.9 });
      createInstanced(
        palmGeo,
        palmMat,
        12,
        () => {
          const angle = Math.random() * Math.PI * 2;
          const r = 25 + Math.random() * 10;
          return new THREE.Vector3(
            cx + 80 + Math.cos(angle) * r,
            -0.3,
            cx + 50 + Math.sin(angle) * r,
          );
        },
        () => new THREE.Vector3(1, 1 + Math.random() * 0.5, 1),
        () =>
          new THREE.Vector3(
            (Math.random() - 0.5) * 0.3,
            Math.random() * Math.PI,
            (Math.random() - 0.5) * 0.3,
          ),
      );
    }

    this.showToastEvent(`Scenery: ${t.label}`, 'info');
  }

  async loadParts() {
    try {
      const response = await fetch(this.api.parts, {
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
          'X-CSRF-TOKEN': this.csrfToken,
        },
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const parts = await response.json();

      parts.forEach((partData) => {
        this.addPartToScene(partData, false);
      });

      this.updateDebugInfo(`Loaded ${parts.length} parts - Click a part to start building`);
      this.emitPartCount();
    } catch (error) {
      console.error('[Editor] Error loading parts:', error);
      this.updateDebugInfo('Error loading parts - check console');
      this.showToastEvent('Could not load parts. Try refreshing.', 'error');
    }
  }

  // ============ PART CREATION (MESH FACTORY) ============

  // ============ PBR MATERIAL HELPER ============
  createPBRMaterial(type, color) {
    const mat = new THREE.MeshPhysicalMaterial({ color: color || '#6B7280' });
    switch (type) {
      case 'wood':
        mat.roughness = 0.6;
        mat.metalness = 0.0;
        mat.clearcoat = 0.15;
        mat.clearcoatRoughness = 0.4;
        break;
      case 'metal':
        mat.roughness = 0.15;
        mat.metalness = 0.95;
        mat.clearcoat = 0.5;
        mat.reflectivity = 1.0;
        break;
      case 'concrete':
        mat.roughness = 0.9;
        mat.metalness = 0.0;
        break;
      case 'ceramic':
        mat.roughness = 0.1;
        mat.metalness = 0.0;
        mat.clearcoat = 0.8;
        mat.clearcoatRoughness = 0.1;
        break;
      case 'fabric':
        mat.roughness = 1.0;
        mat.metalness = 0.0;
        break;
      case 'glass':
        mat.color.set(0x99ccff);
        mat.roughness = 0.05;
        mat.metalness = 0.1;
        mat.transparent = true;
        mat.opacity = 0.25;
        mat.side = THREE.DoubleSide;
        mat.depthWrite = false;
        break;
      default:
        mat.roughness = 0.7;
        mat.metalness = 0.1;
    }
    return mat;
  }

  createWallMesh(width, height, depth, colorFront, variant) {
    // Full glass wall variant
    if (variant === 'glass' || variant === 'curtain') {
      const group = new THREE.Group();
      const frameMat = this.createPBRMaterial('metal', '#94A3B8');
      // Top + bottom metal rails
      const topBar = new THREE.Mesh(new THREE.BoxGeometry(width, 0.06, depth + 0.04), frameMat);
      topBar.position.y = height / 2 - 0.03;
      group.add(topBar);
      const botBar = new THREE.Mesh(new THREE.BoxGeometry(width, 0.06, depth + 0.04), frameMat);
      botBar.position.y = -height / 2 + 0.03;
      group.add(botBar);
      // Vertical mullions every 1m
      const mullionCount = Math.max(2, Math.ceil(width));
      for (let i = 0; i <= mullionCount; i++) {
        const m = new THREE.Mesh(new THREE.BoxGeometry(0.04, height, depth + 0.04), frameMat);
        m.position.x = -width / 2 + i * (width / mullionCount);
        group.add(m);
      }
      // Glass pane
      const glassMat = this.createPBRMaterial('glass');
      const glass = new THREE.Mesh(
        new THREE.BoxGeometry(width - 0.04, height - 0.12, depth),
        glassMat,
      );
      glass.castShadow = false;
      glass.renderOrder = 1;
      group.add(glass);
      return group;
    }

    // Half-height wall with glass panel on top
    if (variant === 'half' || variant === 'half_glass') {
      const group = new THREE.Group();
      const solidH = variant === 'half_glass' ? height * 0.45 : height;
      const solidMat = this.createPBRMaterial('concrete', colorFront);
      solidMat.side = THREE.DoubleSide;
      const solid = new THREE.Mesh(new THREE.BoxGeometry(width, solidH, depth), solidMat);
      solid.position.y = -height / 2 + solidH / 2;
      solid.castShadow = true;
      solid.receiveShadow = true;
      group.add(solid);
      if (variant === 'half_glass') {
        const glassH = height - solidH - 0.05;
        const glassMat = this.createPBRMaterial('glass');
        const glass = new THREE.Mesh(new THREE.BoxGeometry(width, glassH, depth * 0.5), glassMat);
        glass.position.y = -height / 2 + solidH + glassH / 2 + 0.025;
        glass.renderOrder = 1;
        group.add(glass);
      }
      return group;
    }

    // Standard solid wall
    const wallGeo = new THREE.BoxGeometry(width, height, depth);
    const wallMat = this.createPBRMaterial('concrete', colorFront);
    wallMat.side = THREE.DoubleSide;
    const wall = new THREE.Mesh(wallGeo, wallMat);
    wall.castShadow = true;
    wall.receiveShadow = true;
    return wall;
  }

  createFloorMesh(width, height, depth, color, variant) {
    const geo = new THREE.BoxGeometry(width, height, depth);
    const matProps = { color, roughness: 0.7, metalness: 0.1 };

    switch (variant) {
      case 'tile':
        matProps.roughness = 0.3;
        break;
      case 'hardwood':
        matProps.roughness = 0.9;
        matProps.metalness = 0;
        break;
      case 'carpet':
        matProps.roughness = 1.0;
        matProps.metalness = 0;
        break;
      case 'concrete':
        matProps.roughness = 0.85;
        matProps.metalness = 0;
        break;
      case 'marble':
        matProps.roughness = 0.2;
        matProps.metalness = 0.1;
        break;
    }

    const mesh = new THREE.Mesh(geo, new THREE.MeshStandardMaterial(matProps));
    mesh.castShadow = true;
    mesh.receiveShadow = true;
    return mesh;
  }

  createDoorMesh(width, height, depth, color, variant) {
    const group = new THREE.Group();
    const doorDepth = 0.25;
    const fw = 0.08; // frame width

    const frameMat = this.createPBRMaterial('wood', '#5C4033');
    const handleMat = this.createPBRMaterial('metal', '#C0C0C0');

    // Frame: 3 bars (left, right, top)
    const leftFrame = new THREE.Mesh(new THREE.BoxGeometry(fw, height, doorDepth), frameMat);
    leftFrame.position.x = -width / 2 + fw / 2;
    group.add(leftFrame);
    const rightFrame = new THREE.Mesh(new THREE.BoxGeometry(fw, height, doorDepth), frameMat);
    rightFrame.position.x = width / 2 - fw / 2;
    group.add(rightFrame);
    const topFrame = new THREE.Mesh(new THREE.BoxGeometry(width, fw, doorDepth), frameMat);
    topFrame.position.y = height / 2 - fw / 2;
    group.add(topFrame);

    // Door panel — glass or solid
    if (variant === 'glass' || variant === 'sliding_glass') {
      const glassMat = this.createPBRMaterial('glass');
      const glass = new THREE.Mesh(
        new THREE.BoxGeometry(width - fw * 2 - 0.02, height - fw - 0.02, doorDepth * 0.4),
        glassMat,
      );
      glass.renderOrder = 1;
      group.add(glass);
    } else {
      const doorMat = this.createPBRMaterial('wood', color);
      const door = new THREE.Mesh(
        new THREE.BoxGeometry(width - fw * 2 - 0.02, height - fw - 0.02, doorDepth * 0.7),
        doorMat,
      );
      door.castShadow = true;
      group.add(door);
    }

    // Handle
    const handle = new THREE.Mesh(new THREE.SphereGeometry(0.05, 8, 8), handleMat);
    handle.position.set(width / 4, 0, doorDepth / 2 + 0.02);
    group.add(handle);

    return group;
  }

  createWindowMesh(width, height, depth, color, variant) {
    const group = new THREE.Group();
    const wd = 0.15; // window depth (thin)
    const fw = 0.06; // frame bar thickness

    const frameMat = this.createPBRMaterial('metal', '#D4D4D8');

    // Frame: 4 thin bars around the edge — NOT a solid filled box
    const topBar = new THREE.Mesh(new THREE.BoxGeometry(width, fw, wd), frameMat);
    topBar.position.y = height / 2 - fw / 2;
    group.add(topBar);
    const botBar = new THREE.Mesh(new THREE.BoxGeometry(width, fw, wd), frameMat);
    botBar.position.y = -height / 2 + fw / 2;
    group.add(botBar);
    const leftBar = new THREE.Mesh(new THREE.BoxGeometry(fw, height - fw * 2, wd), frameMat);
    leftBar.position.x = -width / 2 + fw / 2;
    group.add(leftBar);
    const rightBar = new THREE.Mesh(new THREE.BoxGeometry(fw, height - fw * 2, wd), frameMat);
    rightBar.position.x = width / 2 - fw / 2;
    group.add(rightBar);

    // Cross dividers for multi-pane look
    if (variant === 'pane' || variant === 'double_hung') {
      const divMat = this.createPBRMaterial('metal', '#A1A1AA');
      const hDiv = new THREE.Mesh(new THREE.BoxGeometry(width - fw * 2, 0.03, wd + 0.01), divMat);
      group.add(hDiv);
      const vDiv = new THREE.Mesh(new THREE.BoxGeometry(0.03, height - fw * 2, wd + 0.01), divMat);
      group.add(vDiv);
    }

    // GLASS PANE — the key: transparent + depthWrite:false
    const glassMat = new THREE.MeshPhysicalMaterial({
      color: 0x88bbee,
      transparent: true,
      opacity: 0.2,
      roughness: 0.02,
      metalness: 0.05,
      side: THREE.DoubleSide,
      depthWrite: false,
    });
    const glass = new THREE.Mesh(
      new THREE.BoxGeometry(width - fw * 2 - 0.01, height - fw * 2 - 0.01, wd * 0.3),
      glassMat,
    );
    glass.renderOrder = 1;
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
        steps: 1,
        depth: depth,
        bevelEnabled: false,
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
        new THREE.MeshStandardMaterial({ color, roughness: 0.7 }),
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
        new THREE.BoxGeometry(stepWidth, stepHeight * 0.9, stepDepth - 0.02),
        stepMat,
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

  createFurnitureMesh(width, height, depth, color, variant) {
    const group = new THREE.Group();
    // Two-tone: preset color for surfaces, dark slate for frames/legs/structure
    const dark = '#1E293B';
    const matSurface = this.createPBRMaterial('fabric', color);
    const matWood = this.createPBRMaterial('wood', color);
    const matDark = this.createPBRMaterial('wood', dark);

    if (variant === 'table') {
      // Tabletop: light (preset color)
      const top = new THREE.Mesh(new THREE.BoxGeometry(width, 0.07, depth), matWood);
      top.position.y = height / 2 - 0.035;
      top.castShadow = true;
      group.add(top);
      // Legs: dark
      const legGeo = new THREE.BoxGeometry(0.07, height - 0.07, 0.07);
      [
        [-1, -1],
        [1, -1],
        [1, 1],
        [-1, 1],
      ].forEach(([sx, sz]) => {
        const leg = new THREE.Mesh(legGeo, matDark);
        leg.position.set(sx * (width / 2 - 0.06), -0.035, sz * (depth / 2 - 0.06));
        leg.castShadow = true;
        group.add(leg);
      });
    } else if (variant === 'chair') {
      // Seat: light fabric
      const seat = new THREE.Mesh(new THREE.BoxGeometry(width, 0.08, depth), matSurface);
      seat.position.y = height * 0.4;
      seat.castShadow = true;
      group.add(seat);
      // Backrest: light fabric
      const back = new THREE.Mesh(new THREE.BoxGeometry(width, height * 0.55, 0.08), matSurface);
      back.position.set(0, height * 0.68, -depth / 2 + 0.04);
      back.castShadow = true;
      group.add(back);
      // Legs: dark
      const legGeo = new THREE.CylinderGeometry(0.025, 0.025, height * 0.4, 6);
      [
        [-1, -1],
        [1, -1],
        [1, 1],
        [-1, 1],
      ].forEach(([sx, sz]) => {
        const leg = new THREE.Mesh(legGeo, matDark);
        leg.position.set(
          sx * (width / 2 - 0.06),
          (height * 0.4) / 2 - height / 2,
          sz * (depth / 2 - 0.06),
        );
        group.add(leg);
      });
    } else if (variant === 'sofa') {
      // Cushion base: light
      const base = new THREE.Mesh(
        new THREE.BoxGeometry(width - 0.2, height * 0.32, depth - 0.25),
        matSurface,
      );
      base.position.y = -height / 2 + height * 0.16;
      base.castShadow = true;
      group.add(base);
      // Back cushion: light
      const backRest = new THREE.Mesh(
        new THREE.BoxGeometry(width - 0.2, height * 0.5, depth * 0.22),
        matSurface,
      );
      backRest.position.set(0, 0.05, -depth / 2 + depth * 0.12);
      backRest.castShadow = true;
      group.add(backRest);
      // Dark frame/base
      const frame = new THREE.Mesh(new THREE.BoxGeometry(width, height * 0.12, depth), matDark);
      frame.position.y = -height / 2 + height * 0.06;
      group.add(frame);
      // Arms: dark
      const armGeo = new THREE.BoxGeometry(depth * 0.18, height * 0.38, depth);
      [-1, 1].forEach((sx) => {
        const arm = new THREE.Mesh(armGeo, matDark);
        arm.position.set(sx * (width / 2 - depth * 0.09), -height * 0.06, 0);
        group.add(arm);
      });
    } else if (variant === 'bed') {
      // Dark wood frame base
      const frame = new THREE.Mesh(
        new THREE.BoxGeometry(width + 0.06, height * 0.18, depth + 0.06),
        matDark,
      );
      frame.position.y = -height * 0.38;
      group.add(frame);
      // Mattress: light (preset color)
      const mattressMat = this.createPBRMaterial('fabric', color);
      const mattress = new THREE.Mesh(
        new THREE.BoxGeometry(width - 0.02, height * 0.32, depth),
        mattressMat,
      );
      mattress.position.y = -height * 0.12;
      mattress.castShadow = true;
      group.add(mattress);
      // Dark headboard
      const headboard = new THREE.Mesh(
        new THREE.BoxGeometry(width + 0.04, height * 0.65, 0.1),
        matDark,
      );
      headboard.position.set(0, height * 0.08, -depth / 2 + 0.05);
      headboard.castShadow = true;
      group.add(headboard);
      // White pillows
      const pillowMat = this.createPBRMaterial('fabric', '#F8FAFC');
      const pillowGeo = new THREE.BoxGeometry(width * 0.36, height * 0.1, 0.28);
      [-0.22, 0.22].forEach((px) => {
        const p = new THREE.Mesh(pillowGeo, pillowMat);
        p.position.set(px * width, height * 0.1, -depth / 2 + 0.28);
        group.add(p);
      });
    } else if (variant === 'bookshelf') {
      // Dark outer frame
      const frame = new THREE.Mesh(new THREE.BoxGeometry(width, height, depth), matDark);
      frame.castShadow = true;
      group.add(frame);
      // Light shelves
      const shelfMat = this.createPBRMaterial('wood', '#D4A97A');
      const shelfCount = 4;
      for (let i = 0; i < shelfCount; i++) {
        const shelf = new THREE.Mesh(
          new THREE.BoxGeometry(width - 0.06, 0.04, depth + 0.02),
          shelfMat,
        );
        shelf.position.y = -height / 2 + (i + 1) * (height / (shelfCount + 1));
        group.add(shelf);
      }
    } else {
      // Default: dark legs + light top
      const top = new THREE.Mesh(new THREE.BoxGeometry(width, height * 0.1, depth), matWood);
      top.position.y = height / 2 - height * 0.05;
      top.castShadow = true;
      group.add(top);
      const body = new THREE.Mesh(
        new THREE.BoxGeometry(width * 0.9, height * 0.88, depth * 0.9),
        matDark,
      );
      body.position.y = -height * 0.06;
      group.add(body);
    }
    return group;
  }

  createLandscapeMesh(width, height, depth, color, variant) {
    const group = new THREE.Group();
    if (variant === 'tree') {
      const trunkMat = this.createPBRMaterial('wood', '#6B4226');
      const trunk = new THREE.Mesh(
        new THREE.CylinderGeometry(0.15, 0.2, height * 0.4, 8),
        trunkMat,
      );
      trunk.position.y = -height * 0.3;
      trunk.castShadow = true;
      group.add(trunk);
      const foliageMat = this.createPBRMaterial('fabric', color || '#2D6A4F');
      const foliage = new THREE.Mesh(
        new THREE.ConeGeometry(width * 0.5, height * 0.65, 8),
        foliageMat,
      );
      foliage.position.y = height * 0.05;
      foliage.castShadow = true;
      group.add(foliage);
    } else if (variant === 'bush') {
      const bushMat = this.createPBRMaterial('fabric', color || '#228B22');
      const bush = new THREE.Mesh(
        new THREE.SphereGeometry(Math.max(width, depth) / 2, 8, 6),
        bushMat,
      );
      bush.scale.set(1, height / width, 1);
      bush.castShadow = true;
      group.add(bush);
    } else if (variant === 'pool') {
      // Pool: hollow concrete rim + dark interior + blue water with texture
      const rimMat = this.createPBRMaterial('concrete', '#C5CDD1');
      const rimThick = 0.2;
      // 4 borders to make a hollow rim so water is visible inside
      const zBorderGeo = new THREE.BoxGeometry(width + rimThick * 2, height + 0.2, rimThick);
      const xBorderGeo = new THREE.BoxGeometry(rimThick, height + 0.2, depth);

      const rimTop = new THREE.Mesh(zBorderGeo, rimMat);
      rimTop.position.set(0, 0, -depth / 2 - rimThick / 2);
      rimTop.receiveShadow = true;
      group.add(rimTop);

      const rimBot = new THREE.Mesh(zBorderGeo, rimMat);
      rimBot.position.set(0, 0, depth / 2 + rimThick / 2);
      rimBot.receiveShadow = true;
      group.add(rimBot);

      const rimLeft = new THREE.Mesh(xBorderGeo, rimMat);
      rimLeft.position.set(-width / 2 - rimThick / 2, 0, 0);
      rimLeft.receiveShadow = true;
      group.add(rimLeft);

      const rimRight = new THREE.Mesh(xBorderGeo, rimMat);
      rimRight.position.set(width / 2 + rimThick / 2, 0, 0);
      rimRight.receiveShadow = true;
      group.add(rimRight);

      // Dark navy interior (floor of the pool)
      const shellMat = new THREE.MeshStandardMaterial({
        color: 0x0a1f2e,
        roughness: 0.8,
        metalness: 0.0,
      });
      const shell = new THREE.Mesh(new THREE.BoxGeometry(width, 0.1, depth), shellMat);
      shell.position.y = -height * 0.4;
      group.add(shell);

      // Create procedural water texture
      const canvas = document.createElement('canvas');
      canvas.width = 256;
      canvas.height = 256;
      const ctx = canvas.getContext('2d');
      ctx.fillStyle = '#007EA8';
      ctx.fillRect(0, 0, 256, 256);
      ctx.strokeStyle = '#48CAE4';
      ctx.lineWidth = 2;
      for (let i = 0; i < 15; i++) {
        ctx.beginPath();
        ctx.moveTo(0, Math.random() * 256);
        ctx.bezierCurveTo(
          85,
          Math.random() * 256,
          170,
          Math.random() * 256,
          256,
          Math.random() * 256,
        );
        ctx.stroke();
      }
      const waterTex = new THREE.CanvasTexture(canvas);
      waterTex.wrapS = THREE.RepeatWrapping;
      waterTex.wrapT = THREE.RepeatWrapping;
      waterTex.repeat.set(width / 2, depth / 2);

      // Water surface
      const waterMat = new THREE.MeshStandardMaterial({
        color: 0x0096c7,
        map: waterTex,
        emissive: 0x0055aa,
        emissiveIntensity: 0.3,
        transparent: true,
        opacity: 0.85,
        roughness: 0.1,
        metalness: 0.1,
        side: THREE.FrontSide,
        depthWrite: false,
      });
      const water = new THREE.Mesh(new THREE.BoxGeometry(width, height * 0.8, depth), waterMat);
      water.position.y = height * 0.1;
      water.renderOrder = 2;
      group.add(water);
    } else if (variant === 'fence') {
      const fenceMat = this.createPBRMaterial('wood', color || '#A0522D');
      // Horizontal rails
      const topRail = new THREE.Mesh(new THREE.BoxGeometry(width, 0.08, depth), fenceMat);
      topRail.position.y = height / 2 - 0.04;
      topRail.castShadow = true;
      group.add(topRail);
      const botRail = new THREE.Mesh(new THREE.BoxGeometry(width, 0.08, depth), fenceMat);
      botRail.position.y = -height / 2 + 0.04;
      group.add(botRail);
      // Vertical slats
      const slatCount = Math.max(3, Math.floor(width / 0.25));
      const slatGeo = new THREE.BoxGeometry(0.06, height - 0.16, depth);
      for (let i = 0; i < slatCount; i++) {
        const slat = new THREE.Mesh(slatGeo, fenceMat);
        slat.position.x = -width / 2 + (i + 0.5) * (width / slatCount);
        slat.castShadow = true;
        group.add(slat);
      }
    } else {
      const geo = new THREE.BoxGeometry(width, height, depth);
      const mat = this.createPBRMaterial('fabric', color || '#4ADE80');
      const mesh = new THREE.Mesh(geo, mat);
      mesh.castShadow = true;
      group.add(mesh);
    }
    return group;
  }

  createStructuralMesh(width, height, depth, color, variant) {
    const group = new THREE.Group();
    const mat = this.createPBRMaterial('concrete', color);
    if (variant === 'column_round') {
      const geo = new THREE.CylinderGeometry(width / 2, width / 2, height, 16);
      const mesh = new THREE.Mesh(geo, mat);
      mesh.castShadow = true;
      group.add(mesh);
    } else if (variant === 'beam') {
      const geo = new THREE.BoxGeometry(width, height, depth);
      const mesh = new THREE.Mesh(geo, mat);
      mesh.castShadow = true;
      group.add(mesh);
    } else if (variant === 'arch') {
      // Arch: two columns + curved top
      const colMat = this.createPBRMaterial('concrete', color);
      const colGeo = new THREE.BoxGeometry(depth, height * 0.75, depth);
      const lCol = new THREE.Mesh(colGeo, colMat);
      lCol.position.set(-width / 2 + depth / 2, -height * 0.125, 0);
      group.add(lCol);
      const rCol = new THREE.Mesh(colGeo.clone(), colMat);
      rCol.position.set(width / 2 - depth / 2, -height * 0.125, 0);
      group.add(rCol);
      const archGeo = new THREE.TorusGeometry(width / 2 - depth / 2, depth / 2, 8, 16, Math.PI);
      const arch = new THREE.Mesh(archGeo, colMat);
      arch.position.y = height * 0.25;
      arch.rotation.z = Math.PI;
      arch.castShadow = true;
      group.add(arch);
    } else {
      // column_square / default
      const geo = new THREE.BoxGeometry(width, height, depth);
      const mesh = new THREE.Mesh(geo, mat);
      mesh.castShadow = true;
      group.add(mesh);
    }
    return group;
  }

  createFixtureMesh(width, height, depth, color, variant) {
    const group = new THREE.Group();
    const matWhite = this.createPBRMaterial('ceramic', color);
    const matDark = this.createPBRMaterial('wood', '#1E293B');
    const matSlate = this.createPBRMaterial('concrete', '#334155');

    if (variant === 'toilet') {
      // White ceramic body
      const base = new THREE.Mesh(
        new THREE.CylinderGeometry(width / 2, (width / 2) * 0.9, height * 0.4, 12),
        matWhite,
      );
      base.position.y = -height * 0.3;
      group.add(base);
      const tank = new THREE.Mesh(
        new THREE.BoxGeometry(width * 0.8, height * 0.5, depth * 0.4),
        matWhite,
      );
      tank.position.set(0, 0, -depth / 2 + depth * 0.2);
      group.add(tank);
    } else if (variant === 'sink') {
      // White basin + dark pedestal
      const basin = new THREE.Mesh(new THREE.BoxGeometry(width, height * 0.15, depth), matWhite);
      basin.position.y = height * 0.35;
      group.add(basin);
      const pedestal = new THREE.Mesh(
        new THREE.CylinderGeometry(0.1, 0.15, height * 0.7, 8),
        matSlate,
      );
      pedestal.position.y = -0.05;
      group.add(pedestal);
    } else if (variant === 'bathtub') {
      // White tub exterior
      const tub = new THREE.Mesh(new THREE.BoxGeometry(width, height, depth), matWhite);
      tub.castShadow = true;
      group.add(tub);
      // Blue-tinted water inside
      const innerMat = this.createPBRMaterial('ceramic', '#C8E6F5');
      const inner = new THREE.Mesh(
        new THREE.BoxGeometry(width - 0.1, height * 0.55, depth - 0.1),
        innerMat,
      );
      inner.position.y = height * 0.18;
      group.add(inner);
      // Dark rim strip
      const rim = new THREE.Mesh(
        new THREE.BoxGeometry(width + 0.02, height * 0.06, depth + 0.02),
        matSlate,
      );
      rim.position.y = height * 0.47;
      group.add(rim);
    } else if (variant === 'kitchen_counter') {
      // Dark base cabinet
      const base = new THREE.Mesh(new THREE.BoxGeometry(width, height * 0.88, depth), matDark);
      base.position.y = -height * 0.06;
      base.castShadow = true;
      group.add(base);
      // White/light countertop
      const top = new THREE.Mesh(
        new THREE.BoxGeometry(width + 0.04, height * 0.08, depth + 0.04),
        matWhite,
      );
      top.position.y = height * 0.46;
      group.add(top);
    } else {
      const geo = new THREE.BoxGeometry(width, height, depth);
      group.add(new THREE.Mesh(geo, matWhite));
    }
    return group;
  }

  createPartMesh(partData) {
    const { type, variant, width, height, depth } = partData;
    const color = partData.color || '#6B7280';
    const colorFront = partData.color_front || color;

    switch (type) {
      case 'wall':
        return this.createWallMesh(width, height, depth, colorFront, variant);
      case 'door':
        return this.createDoorMesh(width, height, depth, color, variant);
      case 'window':
        return this.createWindowMesh(width, height, depth, color, variant);
      case 'roof':
        if (partData.shape_points) {
          return this.createCustomPolyMesh(partData);
        }
        return this.createRoofMesh(width, height, depth, color, variant);
      case 'stairs':
        return this.createStairsMesh(width, height, depth, color);
      case 'structural':
        return this.createStructuralMesh(width, height, depth, color, variant);
      case 'furniture':
        return this.createFurnitureMesh(width, height, depth, color, variant);
      case 'fixture':
        return this.createFixtureMesh(width, height, depth, color, variant);
      case 'landscape':
        return this.createLandscapeMesh(width, height, depth, color, variant);
      case 'floor':
      default:
        if (partData.shape_points) {
          return this.createCustomPolyMesh(partData);
        }
        return this.createFloorMesh(width, height, depth, color, variant);
    }
  }

  createCustomPolyMesh(partData) {
    if (!partData.shape_points || partData.shape_points.length < 3) {
      return this.createFloorMesh(
        partData.width,
        partData.height,
        partData.depth,
        partData.color,
        partData.variant,
      );
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

    const mat = new THREE.MeshStandardMaterial({
      color: partData.color,
      roughness: 0.8,
      side: THREE.DoubleSide,
    });
    return new THREE.Mesh(geometry, mat);
  }

  // ============ ADD PART TO SCENE ============

  addPartToScene(partData, save = true) {
    console.log(`[Editor] addPartToScene called - save=${save}, partData=`, partData);

    const tempId = partData.id || `temp_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    partData.id = tempId;

    const mesh = this.createPartMesh(partData);

    mesh.position.set(partData.position_x, partData.position_y, partData.position_z);
    mesh.rotation.y = ((partData.rotation_y || 0) * Math.PI) / 180;

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
    this.parts.set(tempId, { mesh, data: partData, openings: [] });

    // ── WALL OPENING SYSTEM ──
    // When a door or window is placed, find the parent wall and rebuild it with a hole
    if (partData.type === 'door' || partData.type === 'window') {
      const parentWall = this.findWallForOpening(partData);
      if (parentWall) {
        mesh.userData.parentWallId = parentWall.data.id;
        if (!parentWall.openings) {
          parentWall.openings = [];
        }
        if (!parentWall.openings.includes(tempId)) {
          parentWall.openings.push(tempId);
        }
        this.rebuildWallWithOpenings(parentWall.data.id);
      }
    }

    // BROADCAST for Realtime
    if (save) {
      window.dispatchEvent(
        new CustomEvent('part-placed', {
          detail: {
            count: this.parts.size,
            isLocal: true,
            partData: partData,
          },
        }),
      );

      this.hasUnsavedChanges = true;
      this.showToastEvent('Draft Updated', 'info');
      console.log('[Editor] Local part placed and broadcast:', tempId);

      // Trigger auto-save
      this.triggerAutoSave();
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
      rotation_y: Math.round((mesh.rotation.y * 180) / Math.PI),
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
          Accept: 'application/json',
          'X-CSRF-TOKEN': this.csrfToken,
        },
        body: JSON.stringify(data),
      });

      if (!response.ok) {
        return null;
      }

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

    // Trigger auto-save
    this.triggerAutoSave();
  }

  async deletePartAPI(partId) {
    try {
      const response = await fetch(`${this.api.parts}/${partId}`, {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
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
    if (!partData) {
      return false;
    }

    if (partData.mesh) {
      this.scene.remove(partData.mesh);
      this.disposeGroup(partData.mesh);
    }

    this.parts.delete(partId);

    // Dispatch for Realtime broadcast
    window.dispatchEvent(
      new CustomEvent('part-deleted', {
        detail: {
          id: partId,
          isLocal: true,
        },
      }),
    );

    // Trigger auto-save
    this.triggerAutoSave();

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
          popup: 'swal-premium swal-toast compact-toast',
        },
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
    this.updateDebugInfo(`Placing: ${preset.name} - Click to place, R rotate, Q cancel`);

    // Show mobile placement controls, hide action bar
    if (this.isTouchDevice) {
      const placePanel = document.getElementById('mobile-placement-controls');
      if (placePanel) {
        placePanel.classList.add('show');
      }
      const actionBar = document.getElementById('mobile-action-bar');
      if (actionBar) {
        actionBar.style.display = 'none';
      }
    }

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
          rotation =
            this.previewRotation === 90 || this.previewRotation === 270 ? this.previewRotation : 90;
        } else {
          rotation =
            this.previewRotation === 0 || this.previewRotation === 180 ? this.previewRotation : 0;
        }
      }

      if (this.isCellOccupied(x, z, this.currentFloor)) {
        isValid = false;
      }
    } else if (preset.type === 'floor') {
      const snapped = this.snapToCenter(x, z);
      x = snapped.x;
      z = snapped.z;
      y = floorHeight + preset.default_height / 2 + 0.002; // Lift slightly to avoid z-fighting with ground
      if (this.isCellOccupied(x, z, this.currentFloor)) {
        isValid = false;
      }
    } else if (preset.type === 'roof') {
      const snapped = this.snapToCenter(x, z);
      x = snapped.x;
      z = snapped.z;
      y = floorHeight + 2.8 + preset.default_height / 2;
      if (this.isCellOccupied(x, z, this.currentFloor)) {
        isValid = false;
      }
    } else if (preset.type === 'stairs') {
      const isRotated = this.previewRotation % 180 !== 0;

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

      x = Math.max(this.gridSize / 2, Math.min(this.gridUnits - this.gridSize / 2, x));
      z = Math.max(this.gridSize / 2, Math.min(this.gridUnits - this.gridSize / 2, z));

      y = floorHeight + preset.default_height / 2;
      if (this.isCellOccupied(x, z, this.currentFloor)) {
        isValid = false;
      }
    } else if (preset.type === 'door' || preset.type === 'window') {
      // ──────────────────────────────────────────────────────────
      // WALL-ATTACHED: snap to grid EDGE first (same as walls),
      // then find wall at that exact snapped position.
      // This keeps preview on the grid line, not floating in center.
      // ──────────────────────────────────────────────────────────
      const snapped = this.snapToEdge(x, z);
      x = snapped.x;
      z = snapped.z;

      // Auto-rotation from edge orientation
      if (snapped.isVertical) {
        rotation = 90;
      } else {
        rotation = 0;
      }

      // Find wall at this snapped edge (tolerance 0.6 = generous half-cell)
      wallFound = this.findWallAtGridEdge(x, z, this.currentFloor);

      // Also try nearest-wall search as fallback (catches walls after rebuild)
      if (!wallFound) {
        wallFound = this.findNearestWall(x, z, this.currentFloor, 0.8);
      }

      if (!wallFound) {
        isValid = false;
      } else {
        // Use the WALL's exact position and rotation
        x = wallFound.data.position_x;
        z = wallFound.data.position_z;
        rotation = wallFound.data.rotation_y || 0;

        const existingOpening = this.hasOpeningAtPosition(x, z, this.currentFloor);
        if (existingOpening) {
          isValid = false;
        }
      }

      // Y position
      if (preset.type === 'door') {
        y = floorHeight + preset.default_height / 2;
      } else {
        const sill = preset.default_height >= 2.0 ? 0.2 : preset.default_height >= 1.2 ? 0.5 : 0.9;
        y = floorHeight + sill + preset.default_height / 2;
      }
    } else if (preset.type === 'structural') {
      // ── STRUCTURAL: columns snap to grid intersections, beams snap to edges ──
      const variant = preset.variant || '';
      const gs = this.gridSize;
      if (variant.includes('column')) {
        // Columns sit at grid line crossings (corner of 4 cells)
        x = Math.round(x / gs) * gs;
        z = Math.round(z / gs) * gs;
      } else {
        // Beams/arches snap to grid edge like walls (auto-rotate)
        const snapped = this.snapToEdge(x, z);
        x = snapped.x;
        z = snapped.z;
        if (snapped.isVertical) {
          rotation =
            this.previewRotation === 90 || this.previewRotation === 270 ? this.previewRotation : 90;
        } else {
          rotation =
            this.previewRotation === 0 || this.previewRotation === 180 ? this.previewRotation : 0;
        }
      }
      y = floorHeight + preset.default_height / 2;
    } else if (
      preset.type === 'furniture' ||
      preset.type === 'landscape' ||
      preset.type === 'fixture'
    ) {
      // ── SMART SNAP: even-unit objects → grid line; odd-unit objects → cell center ──
      const gs = this.gridSize;
      const isRotated = this.previewRotation % 180 !== 0;
      const effW = isRotated ? preset.default_depth || 1 : preset.default_width || 1;
      const effD = isRotated ? preset.default_width || 1 : preset.default_depth || 1;
      const wCells = Math.round(effW);
      const dCells = Math.round(effD);

      if (wCells % 2 === 0) {
        x = Math.round(x / gs) * gs;
      } else {
        x = Math.floor(x / gs) * gs + gs / 2;
      }
      if (dCells % 2 === 0) {
        z = Math.round(z / gs) * gs;
      } else {
        z = Math.floor(z / gs) * gs + gs / 2;
      }
      x = Math.max(gs / 2, Math.min(this.gridUnits - gs / 2, x));
      z = Math.max(gs / 2, Math.min(this.gridUnits - gs / 2, z));

      // Swimming pool: sink INTO the floor so the top rim is flush with ground
      if (preset.variant === 'pool' && !preset.name.toLowerCase().includes('deck')) {
        // Top of pool = floorHeight, so center = floorHeight - height/2
        // Add a tiny lip (0.05) so the rim is slightly above ground
        y = floorHeight - preset.default_height / 2 + 0.05;
      } else if (preset.name.toLowerCase().includes('deck')) {
        // Decks should sit on top of the ground/floor layer (which is up to ~0.05 high)
        y = floorHeight + 0.052 + preset.default_height / 2;
      } else {
        y = floorHeight + preset.default_height / 2;
      }
    }

    if (!this.isWithinBounds(x, z)) {
      isValid = false;
    }

    return { x, y, z, rotation, isValid, wallFound };
  }

  // ============ PLACE PART ============

  placePart(point) {
    if (!this.currentPreset) {
      return null;
    }
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

    // Only scale wall/floor/roof/stairs by gridSize — furniture, landscape etc use raw preset dimensions
    if (['wall', 'floor', 'roof', 'stairs'].includes(preset.type)) {
      width *= this.gridSize;
      if (preset.type !== 'wall') {
        depth *= this.gridSize;
      }
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
    if (!intersectPoint) {
      return;
    }
    this.hidePreview();

    const pos = this.calculatePlacementPosition(preset, intersectPoint);

    const isValid = pos.isValid;
    const validColor = 0x22c55e; // green
    const invalidColor = 0xef4444; // red
    const color = isValid ? validColor : invalidColor;

    // Dimensions for preview ghost
    let width = preset.default_width || 1;
    const height = preset.default_height || 3;
    let depth = preset.default_depth || 0.2;

    if (['wall', 'floor', 'roof', 'stairs'].includes(preset.type)) {
      width *= this.gridSize;
      if (preset.type !== 'wall') {
        depth *= this.gridSize;
      }
    } else if (preset.type === 'door' || preset.type === 'window') {
      // Use ACTUAL preset dimensions — no caps, no overrides.
      // The actual mesh is built from these exact values so preview must match.
      width = preset.default_width || 1;
      depth = preset.default_depth || 0.15;
    }

    // ── GHOST MESH (semi-transparent, correct color) ──
    const geo = new THREE.BoxGeometry(width, height, depth);
    // Doors/windows need higher opacity to be visible against glass walls
    const ghostOpacity = preset.type === 'door' || preset.type === 'window' ? 0.55 : 0.3;
    const mat = new THREE.MeshBasicMaterial({
      color,
      transparent: true,
      opacity: ghostOpacity,
      side: THREE.DoubleSide,
      depthWrite: false,
      depthTest: false, // Always render on top — critical for wall-embedded previews
    });
    this.previewMesh = new THREE.Mesh(geo, mat);
    this.previewMesh.renderOrder = 100; // Render after all opaque geometry

    // Position the ghost
    const py = pos.y;
    let px = pos.x,
      pz = pos.z;

    // For doors/windows: push ghost slightly OUT from the wall face so it's
    // visible from the front. The wall normal depends on rotation:
    //   rotation=0  → wall faces ±Z → offset on Z axis
    //   rotation=90 → wall faces ±X → offset on X axis
    if (preset.type === 'door' || preset.type === 'window') {
      const wallDepth = depth; // ghost depth = preset depth
      const faceOffset = wallDepth * 0.5 + 0.02; // half-depth pushes face flush
      const rotRad = pos.rotation * (Math.PI / 180);
      // Wall normal direction (perpendicular to wall face)
      px += Math.sin(rotRad) * faceOffset;
      pz += Math.cos(rotRad) * faceOffset;
    }

    this.previewMesh.position.set(px, py, pz);
    // Apply rotation — critical for walls, doors, windows
    this.previewMesh.rotation.y = pos.rotation * (Math.PI / 180);
    this.previewMesh.name = 'preview';
    this.previewMesh.userData.isValid = isValid;
    this.scene.add(this.previewMesh);

    // ── WIREFRAME OUTLINE — always visible, sharp edges show exact grid fit ──
    const edgesGeo = new THREE.EdgesGeometry(geo);
    const edgesMat = new THREE.LineBasicMaterial({
      color,
      linewidth: 2,
      depthTest: false, // Always visible
    });
    const wireframe = new THREE.LineSegments(edgesGeo, edgesMat);
    wireframe.renderOrder = 101;
    this.previewMesh.add(wireframe);

    // ── FLOOR FOOTPRINT / EDGE INDICATOR ──
    const gridY = (this.currentFloor - 1) * 3 + 0.018;

    if (preset.type === 'wall') {
      // Wall is snapped to a grid LINE.
      // rotation=0/180 → wall runs along X (horizontal)
      // rotation=90/270 → wall runs along Z (vertical)
      // The footprint shows EXACTLY which grid edge the wall occupies.
      const rot = pos.rotation % 180;
      // When rotation=90 the wall runs along Z, so world footprint is: depth×width
      const footW = rot === 90 ? depth : width;
      const footD = rot === 90 ? width : depth;
      const footGeo = new THREE.PlaneGeometry(footW, footD);
      const footMat = new THREE.MeshBasicMaterial({
        color,
        transparent: true,
        opacity: 0.65,
        side: THREE.DoubleSide,
        depthWrite: false,
        depthTest: false,
      });
      this.previewMarker = new THREE.Mesh(footGeo, footMat);
      this.previewMarker.renderOrder = 99;
      this.previewMarker.rotation.x = -Math.PI / 2;
      this.previewMarker.position.set(pos.x, gridY, pos.z);
      this.scene.add(this.previewMarker);
    } else if (preset.type === 'door' || preset.type === 'window') {
      // Door/window footprint matches the actual preset width×depth on the grid edge.
      const rot = pos.rotation % 180;
      const footW = rot === 90 ? depth : width;
      const footD = rot === 90 ? width : depth;
      const footGeo = new THREE.PlaneGeometry(footW, footD);
      const footMat = new THREE.MeshBasicMaterial({
        color,
        transparent: true,
        opacity: 0.65,
        side: THREE.DoubleSide,
        depthWrite: false,
        depthTest: false,
      });
      this.previewMarker = new THREE.Mesh(footGeo, footMat);
      this.previewMarker.renderOrder = 99;
      this.previewMarker.rotation.x = -Math.PI / 2;
      this.previewMarker.position.set(pos.x, gridY, pos.z);
      this.scene.add(this.previewMarker);
    } else {
      // Flat footprint on ground for floors, furniture, etc.
      const footGeo = new THREE.PlaneGeometry(width, depth);
      const footMat = new THREE.MeshBasicMaterial({
        color,
        transparent: true,
        opacity: 0.3,
        side: THREE.DoubleSide,
        depthWrite: false,
      });
      this.previewMarker = new THREE.Mesh(footGeo, footMat);
      this.previewMarker.rotation.x = -Math.PI / 2;
      this.previewMarker.rotation.z = pos.rotation * (Math.PI / 180);
      this.previewMarker.position.set(pos.x, gridY, pos.z);
      this.scene.add(this.previewMarker);
    }
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
    if (!partData) {
      return;
    }

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
      const lineMat = new THREE.LineBasicMaterial({ color: 0x3b82f6, linewidth: 2 });
      this.selectionOutline = new THREE.LineSegments(edges, lineMat);
      this.selectionOutline.position.copy(mesh.position);
      this.selectionOutline.rotation.copy(mesh.rotation);
      this.selectionOutline.name = 'selection-outline';
      this.scene.add(this.selectionOutline);
    }

    // Show properties panel event
    window.dispatchEvent(
      new CustomEvent('part-selected', {
        detail: {
          id: partId,
          type: partData.data.type,
          variant: partData.data.variant,
          color: partData.data.color,
        },
      }),
    );

    this.updateDebugInfo(`Selected: ${partData.data.type} - G delete, T move, C clone`);
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
    this.updateDebugInfo(`Tool: ${labels[tool] || tool} - Click on a part`);
  }

  // DELETE TOOL
  deletePart(partId) {
    // ROLE CHECK
    if (this.userRole === 'viewer') {
      this.showToastEvent('Only Editors can delete!', 'error');
      return;
    }

    const partData = this.parts.get(partId);
    if (!partData) {
      return;
    }

    // Save for undo
    this.saveUndoState('delete', { ...partData.data });

    // ── WALL OPENING SYSTEM ── When deleting a door/window, heal the parent wall
    const parentWallId = partData?.mesh?.userData?.parentWallId;
    if (parentWallId) {
      const wallEntry = this.parts.get(parentWallId);
      if (wallEntry) {
        wallEntry.openings = (wallEntry.openings || []).filter((id) => id !== partId);
        this.rebuildWallWithOpenings(parentWallId);
      }
    }

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

    // Trigger auto-save
    this.triggerAutoSave();

    this.deselectPart();
    this.hasUnsavedChanges = true;
    this.emitPartCount();
    this.showToastEvent('Deleted', 'info');
  }

  // ── WALL OPENING HELPERS ──

  // Find the wall part that spatially matches a door/window's X,Z position
  findWallForOpening(openingData) {
    const threshold = 0.35;
    for (const [id, entry] of this.parts) {
      if (entry.data.type !== 'wall') {
        continue;
      }
      if (entry.data.floor_number !== (openingData.floor_number || 1)) {
        continue;
      }
      const dx = Math.abs(entry.data.position_x - openingData.position_x);
      const dz = Math.abs(entry.data.position_z - openingData.position_z);
      if (dx < threshold && dz < threshold) {
        return entry;
      }
    }
    return null;
  }

  // Rebuild a wall mesh to include holes for all its linked doors/windows
  rebuildWallWithOpenings(wallId) {
    const wallEntry = this.parts.get(wallId);
    if (!wallEntry || wallEntry.data.type !== 'wall') {
      return;
    }

    const wallData = wallEntry.data;
    const openingIds = wallEntry.openings || [];

    // Collect valid openings that still exist in the scene
    const openings = [];
    for (const oid of openingIds) {
      const op = this.parts.get(oid);
      if (!op) {
        continue;
      }
      openings.push({
        w: op.data.width,
        h: op.data.height,
        // y offset from wall center (wall center = wallData.position_y)
        cy: op.data.position_y - wallData.position_y,
      });
    }

    // Build the new mesh
    const newMesh =
      openings.length > 0
        ? this.createWallSegmentedMesh(wallData, openings)
        : this.createWallMesh(
            wallData.width,
            wallData.height,
            wallData.depth,
            wallData.color_front || wallData.color,
            wallData.variant,
          );

    // Copy transform
    newMesh.position.copy(wallEntry.mesh.position);
    newMesh.rotation.copy(wallEntry.mesh.rotation);
    newMesh.userData = { ...wallEntry.mesh.userData };
    newMesh.name = wallEntry.mesh.name;

    // Swap in scene
    this.scene.remove(wallEntry.mesh);
    this.disposeGroup(wallEntry.mesh);
    this.scene.add(newMesh);
    wallEntry.mesh = newMesh;
  }

  // Create a wall with rectangular hole(s) punched through it using wall segments
  createWallSegmentedMesh(wallData, openings) {
    const group = new THREE.Group();
    const W = wallData.width || 1;
    const H = wallData.height || 3;
    const D = wallData.depth || 0.2;
    const color = wallData.color_front || wallData.color || '#6B7280';
    const mat = () => {
      const m = this.createPBRMaterial('concrete', color);
      m.side = THREE.DoubleSide;
      return m;
    };

    const addSeg = (w, h, x, y) => {
      if (w <= 0.005 || h <= 0.005) {
        return;
      }
      const seg = new THREE.Mesh(new THREE.BoxGeometry(w, h, D), mat());
      seg.position.set(x, y, 0);
      seg.castShadow = true;
      seg.receiveShadow = true;
      group.add(seg);
    };

    // For simplicity handle ONE centered opening (first one)
    const op = openings[0];
    const ow = Math.min(op.w, W);
    const oh = Math.min(op.h, H);
    const oy = op.cy; // center y offset from wall center

    const leftGap = (W - ow) / 2;
    const rightGap = (W - ow) / 2;
    const bottomGap = H / 2 + oy - oh / 2; // from wall bottom to opening bottom
    const topGap = H / 2 - oy - oh / 2; // from opening top to wall top

    // Bottom strip: full width, below the opening
    addSeg(W, bottomGap, 0, -H / 2 + bottomGap / 2);
    // Top strip: full width, above the opening
    addSeg(W, topGap, 0, H / 2 - topGap / 2);
    // Left strip: beside the opening
    addSeg(leftGap, oh, -W / 2 + leftGap / 2, oy);
    // Right strip: beside the opening
    addSeg(rightGap, oh, W / 2 - rightGap / 2, oy);

    return group;
  }

  // MOVE TOOL
  startMove(partId) {
    // ROLE CHECK
    if (this.userRole === 'viewer') {
      this.showToastEvent('Only Editors can move parts!', 'error');
      return;
    }

    const partData = this.parts.get(partId);
    if (!partData) {
      return;
    }

    this.isMoving = true;
    this.movingPartId = partId;
    this.container.style.cursor = 'grabbing';

    // Make part semi-transparent while moving
    const setOpacity = (obj) => {
      if (obj.material) {
        obj.material.transparent = true;
        obj.material.opacity = 0.5;
      }
      if (obj.children) {
        obj.children.forEach(setOpacity);
      }
    };
    setOpacity(partData.mesh);

    this.updateDebugInfo('Moving - Click to place, Q to cancel');
  }

  finishMove(point) {
    if (!this.isMoving || !this.movingPartId) {
      return;
    }

    const partData = this.parts.get(this.movingPartId);
    if (!partData) {
      return;
    }

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
      this.showToastEvent("Can't place here", 'error');
      return;
    }

    // Save old position for undo
    const oldData = { ...partData.data };

    // Update position
    partData.mesh.position.set(pos.x, pos.y, pos.z);
    partData.mesh.rotation.y = (pos.rotation * Math.PI) / 180;
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
      if (obj.children) {
        obj.children.forEach(restoreOpacity);
      }
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
    window.dispatchEvent(
      new CustomEvent('part-updated', {
        detail: {
          id: this.movingPartId,
          isLocal: true,
          data: {
            position_x: partData.data.position_x,
            position_y: partData.data.position_y,
            position_z: partData.data.position_z,
            rotation_y: partData.data.rotation_y,
          },
        },
      }),
    );

    this.isMoving = false;
    this.movingPartId = null;
    this.container.style.cursor = 'grab';
    this.hasUnsavedChanges = true;
    this.showToastEvent('Part moved', 'success');
    this.updateDebugInfo('Part moved');
  }

  cancelMove() {
    if (!this.isMoving || !this.movingPartId) {
      return;
    }

    const partData = this.parts.get(this.movingPartId);
    if (partData) {
      const restoreOpacity = (obj) => {
        if (obj.material) {
          obj.material.transparent = false;
          obj.material.opacity = 1;
        }
        if (obj.children) {
          obj.children.forEach(restoreOpacity);
        }
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
    if (!partData) {
      return;
    }

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
    this.updateDebugInfo(`Cloning ${partData.data.type} - Click to place`);
  }

  // ============ CUSTOM POLY DRAW MODE (Bloxburg Style) ============

  toggleDrawMode(type = 'floor') {
    if (this.isDrawingPoly && this.drawType === type) {
      this.exitDrawMode();
    } else {
      if (this.isDrawingPoly) {
        this.clearDrawGraphics();
      }
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
    this.updateDebugInfo(
      `DRAWING ${label}: Click points to layout. Click 1st point or 'Enter' to finish.`,
    );
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
    const isShared =
      (p1.x === p3.x && p1.z === p3.z) ||
      (p1.x === p4.x && p1.z === p4.z) ||
      (p2.x === p3.x && p2.z === p3.z) ||
      (p2.x === p4.x && p2.z === p4.z);
    if (isShared) {
      return false;
    }

    return ccw(p1, p3, p4) !== ccw(p2, p3, p4) && ccw(p1, p2, p3) !== ccw(p1, p2, p4);
  }

  isDrawValid(newPoint = null) {
    if (this.polyPoints.length < 2) {
      return true;
    }

    const pts = [...this.polyPoints];
    if (newPoint) {
      pts.push(newPoint);
    }

    // Construct edges
    const edges = [];
    for (let i = 0; i < pts.length - 1; i++) {
      edges.push({ a: pts[i], b: pts[i + 1] });
    }

    // Check if the NEWEST edge intersects any PREVIOUS edges
    if (edges.length > 1) {
      const lastEdge = edges[edges.length - 1];
      for (let i = 0; i < edges.length - 2; i++) {
        // Skip the edge right before it
        if (this.doLinesIntersect(lastEdge.a, lastEdge.b, edges[i].a, edges[i].b)) {
          return false;
        }
      }
    }

    return true;
  }

  getGridIntersection() {
    const rayTargets = [this.ground];
    if (this.platform) {
      rayTargets.push(this.platform);
    }
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
    if (!intersect) {
      return;
    }

    const pt = intersect.point.clone();

    // If clicking near the first point and we have >=3 points, close it!
    if (this.polyPoints.length >= 3) {
      const firstPt = this.polyPoints[0];
      const dist = pt.distanceTo(firstPt);
      if (dist < 1.5) {
        // Snapping tolerance
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
    if (!this.vertexMarkers) {
      this.vertexMarkers = [];
    }
    this.vertexMarkers.push(marker);

    // Solidify the last temp line if there's >1 point
    if (this.polyPoints.length > 1) {
      const p1 = this.polyPoints[this.polyPoints.length - 2];
      const p2 = this.polyPoints[this.polyPoints.length - 1];
      this.createSolidLine(p1, p2);
    }
  }

  handleDrawMove() {
    if (this.polyPoints.length === 0) {
      return;
    }

    this.raycaster.setFromCamera(this.mouse, this.camera);
    const intersect = this.getGridIntersection();
    if (!intersect) {
      return;
    }

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
    if (pts.length < 3) {
      return;
    }

    const shape = new THREE.Shape();
    shape.moveTo(pts[0].x, pts[0].z); // Map world X,Z to shape X,Y
    for (let i = 1; i < pts.length; i++) {
      shape.lineTo(pts[i].x, pts[i].z);
    }

    const depthHeight = this.drawType === 'floor' ? 0.05 : 0.2;
    const geo = new THREE.ExtrudeGeometry(shape, { depth: depthHeight, bevelEnabled: false });

    // Rotate 90deg to lay flat on XZ plane. y_local becomes z_world.
    geo.rotateX(Math.PI / 2);

    const color = isValid ? 0x22c55e : 0xef4444;
    const mat = new THREE.MeshStandardMaterial({
      color,
      transparent: true,
      opacity: 0.5, // More opaque
      side: THREE.DoubleSide,
    });

    this.drawPreviewMesh = new THREE.Mesh(geo, mat);
    const floorY = this.getFloorY();
    const finalY = (this.drawType === 'roof' ? floorY + 2.8 : floorY) + depthHeight + 0.002;
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
      this.polyLines.forEach((line) => this.scene.remove(line));
    }
    this.polyLines = [];
    if (this.tempLine) {
      this.scene.remove(this.tempLine);
      this.tempLine = null;
    }
    if (this.vertexMarkers) {
      this.vertexMarkers.forEach((m) => this.scene.remove(m));
    }
    this.vertexMarkers = [];
    if (this.drawPreviewMesh) {
      this.scene.remove(this.drawPreviewMesh);
      if (this.drawPreviewMesh.geometry) {
        this.drawPreviewMesh.geometry.dispose();
      }
      this.drawPreviewMesh = null;
    }
  }

  finishDrawPoly() {
    if (this.polyPoints.length < 3) {
      return;
    }

    // Create THREE.Shape from points (Map XZ world to XY shape)
    const shape = new THREE.Shape();
    const start = this.polyPoints[0];
    shape.moveTo(start.x, start.z);

    for (let i = 1; i < this.polyPoints.length; i++) {
      const pt = this.polyPoints[i];
      shape.lineTo(pt.x, pt.z);
    }

    // Generate thickness using ExtrudeGeometry
    const depthHeight = this.drawType === 'floor' ? 0.05 : 0.2;
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
    const mat = new THREE.MeshStandardMaterial({
      color: colorString,
      roughness: 0.8,
      side: THREE.DoubleSide,
    });
    const mesh = new THREE.Mesh(geometry, mat);

    // Position it!
    const floorY = this.getFloorY();
    // Since geometry extrudes from Z=0 to Z=depthHeight, rotating it 90deg X makes it go from Y=0 to Y=-depthHeight.
    // We set position to floorY + depthHeight + 0.002 so it rests on the floor correctly without z-fighting.
    const finalBaseY = this.drawType === 'roof' ? floorY + 3.0 : floorY;
    mesh.position.set(center.x, finalBaseY + depthHeight + 0.002, center.z);

    mesh.castShadow = true;
    mesh.receiveShadow = true;

    // Serialize original points so it can be perfectly recreated on load!
    const ptsData = this.polyPoints.map((p) => ({ x: p.x, y: p.y, z: p.z }));

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
      shape_points: ptsData,
    };

    this.scene.add(mesh);
    this.parts.set(tempId, { mesh, data: mesh.userData });

    this.saveUndoState('create', {
      parts: [
        {
          id: tempId,
          data: mesh.userData,
          position: mesh.position.clone(),
          rotation: mesh.rotation.clone(),
        },
      ],
    });
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
    this.updateDebugInfo('PAINT MODE - Click to paint, Exit to stop');

    window.dispatchEvent(new CustomEvent('paint-mode-changed', { detail: { active: true } }));
  }

  exitPaintMode() {
    if (!this.paintMode) {
      return;
    }
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
    this.updateDebugInfo('MATERIAL MODE - Click to apply, Exit to stop');
    window.dispatchEvent(new CustomEvent('material-mode-changed', { detail: { active: true } }));
  }

  exitMaterialMode() {
    if (!this.materialMode) {
      return;
    }
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
      newColor: color,
    });

    mesh.userData.color = color;
    mesh.userData.color_front = color;
    mesh.userData.color_back = color;

    // Update mesh visuals
    const applyColor = (mat) => {
      if (mat && mat.color && typeof mat.color.set === 'function') {
        if (mat.transparent || mat.opacity < 1) {
          return;
        } // Ignore glass/translucent materials
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
      newMaterial: material,
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
      case 'wood':
        roughness = 0.85;
        metalness = 0.0;
        textureMapUrl = '/img/textures/wood.png';
        break;
      case 'brick':
        roughness = 0.95;
        metalness = 0.0;
        textureMapUrl = '/img/textures/brick.png';
        break;
      case 'concrete':
        roughness = 0.85;
        metalness = 0.1;
        break;
      case 'stone':
        roughness = 0.8;
        metalness = 0.1;
        break;
      case 'marble':
        roughness = 0.2;
        metalness = 0.1;
        textureMapUrl = '/img/textures/marble.png';
        break;
      case 'metal':
        roughness = 0.3;
        metalness = 0.9;
        break;
      case 'glass':
        roughness = 0.1;
        metalness = 0.8;
        transparent = true;
        opacity = 0.4;
        break;
      case 'default':
        roughness = 0.7;
        metalness = 0.1;
        break;
    }

    let loadedTexture = null;
    if (textureMapUrl) {
      if (!this.textureLoader) {
        this.textureLoader = new THREE.TextureLoader();
      }
      if (!this.textureCache) {
        this.textureCache = {};
      }

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
        // Glass handling: transparent when picked, solid when changed
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

    if (this.paintMode) {
      this.exitPaintMode();
      return;
    }
    if (this.materialMode) {
      this.exitMaterialMode();
      return;
    }
    if (this.selectedPart) {
      this.deselectPart();
      return;
    }
    if (this.currentTool !== 'select') {
      this.setTool('select');
      return;
    }
  }

  // ============ EVENT LISTENERS ============

  setupEventListeners() {
    const canvas = this.renderer.domElement;

    canvas.addEventListener('pointermove', (e) => this.onMouseMove(e));
    canvas.addEventListener('pointerdown', (e) => this.onMouseDown(e));
    canvas.addEventListener('pointerup', (e) => this.onMouseUp(e));
    canvas.addEventListener('contextmenu', (e) => this.onContextMenu(e));

    // Mobile touch events
    if (this.isTouchDevice) {
      canvas.addEventListener('touchstart', (e) => this.onTouchStart(e), { passive: false });
      canvas.addEventListener('touchend', (e) => this.onTouchEnd(e), { passive: false });
      canvas.addEventListener('touchmove', (e) => this.onTouchMove(e), { passive: false });
    }

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
        cancelButton: 'swal-cancel-btn',
      },
      buttonsStyling: false,
      allowOutsideClick: false,
    });

    if (result.isConfirmed) {
      return 'save';
    }
    if (result.isDenied) {
      return 'discard';
    }
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
      const preset = this.currentPreset;
      const isWallAttached = preset.type === 'door' || preset.type === 'window';

      let point = null;

      if (isWallAttached) {
        // ── WALL-STICKY PREVIEW ──────────────────────────────────────
        // 1. Build mesh→entry map (so we can O(1) look up which wall was hit)
        // 2. Raycast wall faces → if hit, snap to THAT wall's stored position
        // 3. Fallback to ground (shows red ghost when not over a wall)
        // ─────────────────────────────────────────────────────────────
        const wallMeshes = [];
        const meshToEntry = new Map();

        for (const [, entry] of this.parts) {
          if (entry.data.type !== 'wall') {
            continue;
          }
          const obj = entry.mesh;
          if (obj.isMesh) {
            wallMeshes.push(obj);
            meshToEntry.set(obj, entry);
          } else {
            obj.traverse((c) => {
              if (c.isMesh) {
                wallMeshes.push(c);
                meshToEntry.set(c, entry);
              }
            });
          }
        }

        let foundEntry = null;
        if (wallMeshes.length > 0) {
          const wallHits = this.raycaster.intersectObjects(wallMeshes, false);
          if (wallHits.length > 0) {
            foundEntry = meshToEntry.get(wallHits[0].object) || null;
          }
        }

        if (foundEntry) {
          // STICKY: cursor is over the wall face — lock preview to wall position
          // Y=0 is fine; calculatePlacementPosition recomputes Y from floorHeight
          point = new THREE.Vector3(foundEntry.data.position_x, 0, foundEntry.data.position_z);
        } else {
          // Cursor not over any wall — show red ghost at nearest grid edge
          const gHits = this.raycaster.intersectObjects(
            [this.ground, this.platform].filter(Boolean),
          );
          if (gHits.length > 0) {
            point = gHits[0].point.clone();
          } else {
            // Edge case: ground missed (steep camera angle)
            const dir = this.raycaster.ray.direction;
            const orig = this.raycaster.ray.origin;
            if (dir.y !== 0) {
              const t = -orig.y / dir.y;
              if (t > 0) {
                point = this.raycaster.ray.at(t, new THREE.Vector3());
              }
            }
          }
        }
      } else {
        const gHits = this.raycaster.intersectObject(this.ground);
        if (gHits.length > 0) {
          point = gHits[0].point;
        }
      }

      if (point) {
        this.showPreview(preset, point);
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
    if (this.selectedPart === mesh.userData.id) {
      return;
    }

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
      let colorHex = 0x3b82f6; // Default Light Blue
      if (this.currentTool === 'delete') {
        colorHex = 0xef4444;
      } // Red for delete
      if (this.currentTool === 'select') {
        colorHex = 0xffffff;
      } // White for select
      if (this.paintMode) {
        colorHex = 0xfab005;
      } // Yellow for paint

      // If DELETE tool, also tint the actual object
      if (this.currentTool === 'delete') {
        this.hoveredPartsEmissive = [];
        const highlight = (obj) => {
          if (obj.material) {
            const mats = Array.isArray(obj.material) ? obj.material : [obj.material];
            mats.forEach((m) => {
              if (m.emissive) {
                this.hoveredPartsEmissive.push({ material: m, color: m.emissive.clone() });
                m.emissive.setHex(0xff0000);
                m.emissiveIntensity = 0.5;
              }
            });
          }
          if (obj.children) {
            obj.children.forEach(highlight);
          }
        };
        highlight(mesh);
      }

      const edges = new THREE.EdgesGeometry(targetGeo);
      const lineMat = new THREE.LineBasicMaterial({
        color: colorHex,
        linewidth: 2,
        transparent: true,
        opacity: 0.8,
      });
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
      this.hoveredPartsEmissive.forEach((item) => {
        item.material.emissive.copy(item.color);
        item.material.emissiveIntensity = 0;
      });
      this.hoveredPartsEmissive = null;
    }

    const cursors = { select: 'default', delete: 'crosshair', move: 'grab', clone: 'copy' };
    this.container.style.cursor = cursors[this.currentTool] || 'default';
  }

  onMouseDown(event) {
    if (event.button !== 0) {
      return;
    } // Left click only

    // Ensure the canvas gets focus if we click it
    this.renderer.domElement.focus();

    this.updateMouseCoords(event);

    console.log(
      '[PointerDown] Click detected. Placing:',
      this.isPlacing,
      'Preset:',
      !!this.currentPreset,
    );

    // Disable OrbitControls during placement on mobile to prevent camera rotation
    if (this.isTouchDevice && this.isPlacing && this.controls) {
      this.controls.enabled = false;
      // Re-enable after a short delay
      setTimeout(() => {
        if (this.controls) {
          this.controls.enabled = true;
        }
      }, 100);
    }

    // CUSTOM DRAW TOOL
    if (this.isDrawingPoly) {
      this.handleDrawClick();
      return;
    }

    // PLACEMENT MODE
    if (this.isPlacing && this.currentPreset && !this.isMoving) {
      this.raycaster.setFromCamera(this.mouse, this.camera);
      const preset = this.currentPreset;
      const isWallAttached = preset.type === 'door' || preset.type === 'window';

      let hitPoint = null;

      if (isWallAttached) {
        // Use same mesh→entry map approach as hover for consistency
        const wallMeshes = [];
        const meshToEntry = new Map();

        for (const [, entry] of this.parts) {
          if (entry.data.type !== 'wall') {
            continue;
          }
          const obj = entry.mesh;
          if (obj.isMesh) {
            wallMeshes.push(obj);
            meshToEntry.set(obj, entry);
          } else {
            obj.traverse((c) => {
              if (c.isMesh) {
                wallMeshes.push(c);
                meshToEntry.set(c, entry);
              }
            });
          }
        }

        let foundEntry = null;
        if (wallMeshes.length > 0) {
          const wallHits = this.raycaster.intersectObjects(wallMeshes, false);
          if (wallHits.length > 0) {
            foundEntry = meshToEntry.get(wallHits[0].object) || null;
          }
        }

        if (foundEntry) {
          // Use wall's EXACT stored position — guaranteed to match findWallAtGridEdge
          hitPoint = new THREE.Vector3(foundEntry.data.position_x, 0, foundEntry.data.position_z);
        } else {
          // Not clicking on a wall — try ground fallback
          const gHits = this.raycaster.intersectObjects(
            [this.ground, this.platform].filter(Boolean),
          );
          if (gHits.length > 0) {
            hitPoint = gHits[0].point.clone();
          }
        }
      } else {
        const gHits = this.raycaster.intersectObjects([this.ground, this.platform].filter(Boolean));
        if (gHits.length > 0) {
          hitPoint = gHits[0].point;
        }
      }

      if (hitPoint) {
        this.placePart(hitPoint);
      } else {
        console.error('[MouseDown] Raycast failed. Make sure ground/platform exist!');
      }
      return;
    }

    // MOVING MODE - finish move
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
      window.dispatchEvent(
        new CustomEvent('open-issue-modal', {
          detail: {
            partId: hitPart.userData.id,
            partType: hitPart.userData.type,
          },
        }),
      );
    }
  }

  // ============ MOBILE TOUCH HANDLERS ============

  onTouchStart(event) {
    if (event.touches.length === 1) {
      const touch = event.touches[0];
      this.longPressTriggered = false;

      // Start long-press timer
      this.longPressTimer = setTimeout(() => {
        this.longPressTriggered = true;
        this.handleLongPress(touch);
      }, this.longPressDuration);
    }
  }

  onTouchMove(event) {
    // Cancel long-press if finger moves (user is panning/rotating)
    if (this.longPressTimer) {
      clearTimeout(this.longPressTimer);
      this.longPressTimer = null;
    }
  }

  onTouchEnd(event) {
    // Cancel long-press timer
    if (this.longPressTimer) {
      clearTimeout(this.longPressTimer);
      this.longPressTimer = null;
    }

    // If long-press was triggered, don't process as tap
    if (this.longPressTriggered) {
      this.longPressTriggered = false;
      event.preventDefault();
      return;
    }

    // Handle tap
    if (event.changedTouches.length === 1) {
      const touch = event.changedTouches[0];
      this.handleTap(touch);
    }
  }

  onMouseUp(event) {
    // Mouse up handler (replaces no-op)
  }

  handleTap(touch) {
    // Double-tap detection
    const now = Date.now();
    const timeSinceLastTap = now - this.lastTapTime;
    this.lastTapTime = now;

    const isDoubleTap = timeSinceLastTap < this.doubleTapThreshold;

    // Show tap feedback
    this.showTapFeedback(touch.clientX, touch.clientY);

    // Convert touch coords to mouse coords for raycasting
    const fakeEvent = {
      clientX: touch.clientX,
      clientY: touch.clientY,
    };
    this.updateMouseCoords(fakeEvent);

    if (isDoubleTap && this.currentTool === 'select') {
      // Double-tap = select part
      const hitPart = this.raycastParts();
      if (hitPart) {
        this.selectPart(hitPart.userData.id);
      }
    } else if (!this.isPlacing && this.currentTool === 'select') {
      // Single tap in select mode = select part
      const hitPart = this.raycastParts();
      if (hitPart) {
        this.selectPart(hitPart.userData.id);
      }
    }
    // If in placement mode, let onMouseDown handle it (it checks pointer events which include touch)
  }

  handleLongPress(touch) {
    // Long-press = create issue (same as right-click on desktop)
    const fakeEvent = {
      clientX: touch.clientX,
      clientY: touch.clientY,
      preventDefault: () => {},
    };
    this.updateMouseCoords(fakeEvent);
    const hitPart = this.raycastParts();
    if (hitPart) {
      this.selectPart(hitPart.userData.id);

      window.dispatchEvent(
        new CustomEvent('open-issue-modal', {
          detail: {
            partId: hitPart.userData.id,
            partType: hitPart.userData.type,
          },
        }),
      );
    }
  }

  showTapFeedback(x, y) {
    // Remove existing feedback
    this.hideTapFeedback();

    // Create tap indicator
    const el = document.createElement('div');
    el.style.position = 'fixed';
    el.style.left = x - 20 + 'px';
    el.style.top = y - 20 + 'px';
    el.style.width = '40px';
    el.style.height = '40px';
    el.style.borderRadius = '50%';
    el.style.border = '2px solid rgba(59, 130, 246, 0.6)';
    el.style.pointerEvents = 'none';
    el.style.zIndex = '9998';
    el.style.transition = 'transform 0.2s ease, opacity 0.2s ease';
    el.style.transform = 'scale(0.5)';
    el.style.opacity = '1';
    document.body.appendChild(el);
    this.tapFeedbackEl = el;

    // Animate
    requestAnimationFrame(() => {
      el.style.transform = 'scale(1)';
      el.style.opacity = '0';
    });

    // Remove after animation
    setTimeout(() => {
      this.hideTapFeedback();
    }, 250);
  }

  hideTapFeedback() {
    if (this.tapFeedbackEl) {
      this.tapFeedbackEl.remove();
      this.tapFeedbackEl = null;
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
      if (mesh.visible) {
        partMeshes.push(mesh);
      }
    });

    this.raycaster.setFromCamera(this.mouse, this.camera);
    const intersects = this.raycaster.intersectObjects(partMeshes, true);

    if (intersects.length > 0) {
      let target = intersects[0].object;
      while (target.parent && !target.userData.id) {
        target = target.parent;
      }
      if (target.userData.id) {
        return target;
      }
    }
    return null;
  }

  onKeyDown(event) {
    if (
      document.activeElement.tagName === 'INPUT' ||
      document.activeElement.tagName === 'TEXTAREA'
    ) {
      return;
    }

    const key = event.key.toLowerCase();
    this.keysPressed[key] = true;

    // ===== BLOXBURG 2026 HOTKEYS =====

    // Q / Escape - cancel / exit
    if (key === 'q' || key === 'escape') {
      event.preventDefault();
      if (this.isDrawingPoly) {
        this.exitDrawMode();
      } else {
        this.cancelSelection();
      }
    }

    // Enter - Finish drawing
    if (key === 'enter' && this.isDrawingPoly) {
      event.preventDefault();
      this.finishDrawPoly();
    }

    // R - rotate preview
    if (key === 'r' && this.isPlacing) {
      event.preventDefault();
      this.previewRotation = (this.previewRotation + 45) % 360;
      this.updateDebugInfo(`Rotation: ${this.previewRotation}┬░`);
    }

    // G or Delete - delete tool / delete selected
    if (key === 'g' || key === 'delete') {
      event.preventDefault();
      if (this.selectedPart) {
        this.deletePart(this.selectedPart);
      } else {
        this.setTool(this.currentTool === 'delete' ? 'select' : 'delete');
      }
    }

    // T - move/transform tool
    if (key === 't') {
      event.preventDefault();
      if (this.selectedPart) {
        this.startMove(this.selectedPart);
      } else {
        this.setTool(this.currentTool === 'move' ? 'select' : 'move');
      }
    }

    // C - clone tool
    if (key === 'c' && !event.ctrlKey && !event.metaKey) {
      event.preventDefault();
      if (this.selectedPart) {
        this.clonePart(this.selectedPart);
      } else {
        this.setTool(this.currentTool === 'clone' ? 'select' : 'clone');
      }
    }

    // F - paint mode
    if (key === 'f') {
      event.preventDefault();
      if (this.paintMode) {
        this.exitPaintMode();
      } else {
        this.enterPaintMode();
      }
    }

    // B - day/night toggle
    if (key === 'b') {
      event.preventDefault();
      this.toggleDayNight();
    }

    // J - grid size toggle
    if (key === 'j') {
      event.preventDefault();
      this.cycleGridSize();
    }

    // H - toggle grid visibility
    if (key === 'h') {
      event.preventDefault();
      this.toggleGrid();
    }

    // Space - bird's eye view (hold)
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
        case 's':
          event.preventDefault();
          this.saveBuild();
          break;
      }
    }
  }

  onKeyUp(event) {
    const key = event.key.toLowerCase();
    this.keysPressed[key] = false;

    // Space release - exit bird's eye
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

    this.updateDebugInfo("Bird's Eye View - Release Space to return");
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

    this.updateDebugInfo("Returned from Bird's Eye View");
  }

  // ============ DAY/NIGHT TOGGLE (B key) ============

  toggleDayNight() {
    this.isNightMode = !this.isNightMode;
    const cx = this.gridUnits / 2;

    if (this.isNightMode) {
      // ── NIGHT SKY ──
      this.scene.background = new THREE.Color(0x060c18);
      this.scene.fog = new THREE.Fog(0x060c18, 50, 200);
      this.ambientLight.intensity = 0.08;
      this.directionalLight.intensity = 0.05;
      this.directionalLight.color.setHex(0x334477);

      // Moon light — cool blue overhead
      if (!this.moonLight) {
        this.moonLight = new THREE.PointLight(0x99bbdd, 1.2, 200);
        this.moonLight.position.set(cx + 20, 60, cx - 20);
        this.scene.add(this.moonLight);
      }

      // Street lamp warm glow
      if (!this.lampLight) {
        this.lampLight = new THREE.PointLight(0xffaa44, 1.5, 30);
        this.lampLight.position.set(cx + this.gridUnits / 2 + 14, 5, cx);
        this.scene.add(this.lampLight);
      }

      // Stars
      if (!this.starField) {
        const starGeo = new THREE.BufferGeometry();
        const positions = [];
        for (let i = 0; i < 600; i++) {
          const theta = Math.random() * Math.PI * 2;
          const phi = Math.random() * Math.PI * 0.5; // upper hemisphere only
          const r = 220 + Math.random() * 40;
          positions.push(
            r * Math.sin(phi) * Math.cos(theta) + cx,
            Math.abs(r * Math.cos(phi)) + 10,
            r * Math.sin(phi) * Math.sin(theta) + cx,
          );
        }
        starGeo.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
        const starMat = new THREE.PointsMaterial({
          color: 0xffffff,
          size: 0.8,
          sizeAttenuation: true,
        });
        this.starField = new THREE.Points(starGeo, starMat);
        this.scene.add(this.starField);
      }
      this.starField.visible = true;

      // Scenery goes dark (save originals first time)
      if (this.sceneryObjects) {
        if (!this._sceneryColorStore) {
          this._sceneryColorStore = [];
        }
        this.sceneryObjects.forEach((obj, idx) => {
          if (obj.material && obj.material.color) {
            if (!this._sceneryColorStore[idx]) {
              this._sceneryColorStore[idx] = obj.material.color.getHex();
            }
            obj.material.color.setHex(0x222233);
          }
        });
      }

      // ── OBJECT GLOW ── apply emissive by type
      this._nightEmissiveStore = [];
      this.parts.forEach(({ mesh, data }) => {
        const applyGlow = (obj, hexColor, intensity) => {
          if (obj.material && obj.material.emissive !== undefined) {
            this._nightEmissiveStore.push({
              mat: obj.material,
              prevEmissive: obj.material.emissive.getHex(),
              prevIntensity: obj.material.emissiveIntensity,
            });
            obj.material.emissive.setHex(hexColor);
            obj.material.emissiveIntensity = intensity;
          }
          obj.children?.forEach((c) => applyGlow(c, hexColor, intensity));
        };

        const t = data.type;
        const v = data.variant || '';
        if (t === 'window') {
          applyGlow(mesh, 0xffe580, 0.7); // warm golden window glow
        } else if (t === 'door' && v.includes('glass')) {
          applyGlow(mesh, 0xffd580, 0.3);
        } else if (t === 'fixture') {
          applyGlow(mesh, 0x88aaff, 0.2); // cool kitchen/bath glow
        } else if (t === 'landscape' && v === 'pool') {
          applyGlow(mesh, 0x00ccff, 0.5); // glowing pool
        } else if (t === 'landscape' && v === 'tree') {
          applyGlow(mesh, 0x112200, 0.1); // barely visible dark tree
        }
      });
    } else {
      // ── DAY MODE ──
      this.scene.background = new THREE.Color(0x87ceeb);
      this.scene.fog = new THREE.Fog(0x87ceeb, 60, 280);
      this.ambientLight.intensity = 0.5;
      this.directionalLight.intensity = 1.4;
      this.directionalLight.color.setHex(0xfff0d0);

      if (this.moonLight) {
        this.scene.remove(this.moonLight);
        this.moonLight = null;
      }
      if (this.lampLight) {
        this.scene.remove(this.lampLight);
        this.lampLight = null;
      }
      if (this.starField) {
        this.starField.visible = false;
      }

      // Restore scenery colors from saved originals
      if (this.sceneryObjects && this._sceneryColorStore) {
        this.sceneryObjects.forEach((obj, idx) => {
          if (obj.material && obj.material.color && this._sceneryColorStore[idx]) {
            obj.material.color.setHex(this._sceneryColorStore[idx]);
          }
        });
      }

      // Restore all emissives
      if (this._nightEmissiveStore) {
        this._nightEmissiveStore.forEach(({ mat, prevEmissive, prevIntensity }) => {
          mat.emissive.setHex(prevEmissive);
          mat.emissiveIntensity = prevIntensity;
        });
        this._nightEmissiveStore = [];
      }
    }

    window.dispatchEvent(
      new CustomEvent('daynight-changed', {
        detail: { night: this.isNightMode },
      }),
    );
    this.showToastEvent(this.isNightMode ? '🌙 Night Mode' : '☀️ Day Mode', 'info');
  }

  // ============ GRID SIZE TOGGLE (J key) ============

  cycleGridSize() {
    this.gridSizeIndex = (this.gridSizeIndex + 1) % this.gridSizes.length;
    this.gridSize = this.gridSizes[this.gridSizeIndex];

    this.rebuildGrid();

    window.dispatchEvent(
      new CustomEvent('gridsize-changed', {
        detail: { size: this.gridSize },
      }),
    );

    this.showToastEvent(`Grid: ${this.gridSize}x`, 'info');
    this.updateDebugInfo(`Grid size: ${this.gridSize}`);
  }

  // ============ CAMERA MOVEMENT ============

  updateCameraMovement() {
    if (!this.controls || this.birdsEyeActive) {
      return;
    }

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

    if (this.keysPressed['arrowleft']) {
      spherical.theta += orbitSpeed;
    }
    if (this.keysPressed['arrowright']) {
      spherical.theta -= orbitSpeed;
    }
    if (this.keysPressed['arrowup']) {
      spherical.phi = Math.max(0.1, spherical.phi - orbitSpeed);
    }
    if (this.keysPressed['arrowdown']) {
      spherical.phi = Math.min(Math.PI - 0.1, spherical.phi + orbitSpeed);
    }

    if (
      this.keysPressed['arrowleft'] ||
      this.keysPressed['arrowright'] ||
      this.keysPressed['arrowup'] ||
      this.keysPressed['arrowdown']
    ) {
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
      this.gridHelper.position.y = (floor - 1) * 3 + 0.002;
    }

    // Recursive helper — handles Groups (furniture/structural/fixture) and single meshes
    const applyFloorVisual = (obj, isCurrent) => {
      if (obj.material) {
        const mats = Array.isArray(obj.material) ? obj.material : [obj.material];
        mats.forEach((m) => {
          m.opacity = isCurrent ? 1.0 : 0.28;
          m.transparent = !isCurrent;
        });
      }
      if (obj.children) {
        obj.children.forEach((c) => applyFloorVisual(c, isCurrent));
      }
    };

    // Show ALL parts from ALL floors — ghost mode for non-current floors
    this.parts.forEach(({ mesh }) => {
      const partFloor = mesh.userData.floor_number || 1;
      mesh.visible = true;
      const isCurrent = partFloor === floor;
      mesh.userData.isGhost = !isCurrent;
      applyFloorVisual(mesh, isCurrent);
    });

    // Move camera up to see the new floor level
    const targetY = (floor - 1) * 3;
    this.camera.position.y = Math.max(this.camera.position.y, 20 + targetY);
    if (this.controls) {
      this.controls.target.y = targetY;
      this.controls.update();
    }

    this.updateDebugInfo(`Floor ${floor}`);
    window.dispatchEvent(new CustomEvent('floor-changed', { detail: { floor } }));
  }

  addFloor() {
    if (this.currentFloor >= this.maxFloors) {
      this.showToastEvent(`Maximum ${this.maxFloors} floors reached.`, 'warning');
      return;
    }
    this.currentFloor++;
    this.setFloor(this.currentFloor);
    window.dispatchEvent(new CustomEvent('floor-added', { detail: { floor: this.currentFloor } }));
    this.showToastEvent(`Floor ${this.currentFloor} added — build up!`, 'success');
    this.updateDebugInfo(`Added Floor ${this.currentFloor}!`);
  }

  toggleRoof() {
    this.roofVisible = !this.roofVisible;
    this.parts.forEach(({ mesh }) => {
      if (mesh.userData.type === 'roof') {
        mesh.visible = this.roofVisible;
      }
    });
    window.dispatchEvent(
      new CustomEvent('roof-toggled', { detail: { visible: this.roofVisible } }),
    );
  }

  toggleGrid() {
    this.gridHelper.visible = !this.gridHelper.visible;
  }

  // ============ UNDO/REDO ============

  saveUndoState(action, data) {
    this.undoStack.push({ action, data, timestamp: Date.now() });
    if (this.undoStack.length > this.maxUndoSteps) {
      this.undoStack.shift();
    }
    this.redoStack = [];
  }

  undo() {
    if (this.undoStack.length === 0) {
      return;
    }

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
          if (typeof state.data.id === 'number') {
            this.deletePartAPI(state.data.id);
          }
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
          part.mesh.rotation.y = ((old.rotation_y || 0) * Math.PI) / 180;
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
              part.mesh.material.forEach((m) => m.color.set(color));
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
    if (this.redoStack.length === 0) {
      return;
    }

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

          // Trigger auto-save
          this.triggerAutoSave();
        }
        break;
      }
      case 'move': {
        const part = this.parts.get(state.data.id);
        if (part) {
          const newD = state.data.newData;
          part.mesh.position.set(newD.position_x, newD.position_y, newD.position_z);
          part.mesh.rotation.y = ((newD.rotation_y || 0) * Math.PI) / 180;
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
              part.mesh.material.forEach((m) => m.color.set(color));
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
      stack.forEach((entry) => {
        if (entry.data && entry.data.id === oldId) {
          entry.data.id = newId;
        }
      });
    };
    updateStack(this.undoStack);
    updateStack(this.redoStack);
    console.log(`[History] Migrated ID ${oldId} -> ${newId} in stacks`);
  }

  // ============ AUTO-SAVE ============

  triggerAutoSave() {
    // Don't auto-save if manual save is in progress
    if (this.isSaving) {
      return;
    }

    // Clear existing timer
    if (this.autoSaveTimer) {
      clearTimeout(this.autoSaveTimer);
    }

    // Set new debounce timer
    this.autoSaveTimer = setTimeout(async () => {
      if (this.hasUnsavedChanges || this.deletedIds.size > 0 || this.dirtyPartIds.size > 0) {
        const timeSinceLastSave = Date.now() - this.lastAutoSave;
        // Only auto-save if at least 5 seconds since last auto-save (prevent spam)
        if (timeSinceLastSave >= 5000 || this.lastAutoSave === 0) {
          if (DEBUG_MODE) {
            console.log('[Auto-Save] Triggering auto-save...');
          }
          await this.saveBuild(true); // true = isAutoSave
          this.lastAutoSave = Date.now();
        }
      }
    }, this.autoSaveDelay);
  }

  // ============ SAVE (Draft to Server Commit) ============

  async saveBuild(isAutoSave = false) {
    if (!this.hasUnsavedChanges && this.deletedIds.size === 0 && this.dirtyPartIds.size === 0) {
      if (!isAutoSave) {
        this.showToastEvent('Nothing to save', 'info');
      }
      return;
    }

    if (this.isSaving) {
      return;
    }
    this.isSaving = true;

    // Clear auto-save timer to prevent double-saves
    if (this.autoSaveTimer) {
      clearTimeout(this.autoSaveTimer);
      this.autoSaveTimer = null;
    }

    // Use subtle indicator for auto-save, full overlay for manual save
    if (isAutoSave) {
      // Subtle auto-save indicator
      const indicator = document.createElement('div');
      indicator.id = 'build-editor-autosave-indicator';
      indicator.style.position = 'fixed';
      indicator.style.bottom = '16px';
      indicator.style.left = '50%';
      indicator.style.transform = 'translateX(-50%)';
      indicator.style.padding = '8px 16px';
      indicator.style.backgroundColor = 'rgba(59, 130, 246, 0.9)';
      indicator.style.backdropFilter = 'blur(8px)';
      indicator.style.color = '#fff';
      indicator.style.fontSize = '13px';
      indicator.style.fontWeight = '600';
      indicator.style.borderRadius = '8px';
      indicator.style.zIndex = '9999';
      indicator.style.display = 'flex';
      indicator.style.alignItems = 'center';
      indicator.style.gap = '8px';
      indicator.innerHTML = `
                <svg style="width: 14px; height: 14px; animation: spin 1s linear infinite;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Auto-saving...
                <style>@keyframes spin { 100% { transform: rotate(360deg); } }</style>
            `;
      document.body.appendChild(indicator);
    } else {
      // Full overlay for manual save
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
    }

    try {
      let successCount = 0;
      let failCount = 0;

      // 1. PROCESS DELETIONS
      for (const id of this.deletedIds) {
        const ok = await this.deletePartAPI(id);
        if (ok) {
          successCount++;
        } else {
          failCount++;
        }
      }
      this.deletedIds.clear();

      // 2. PROCESS CREATIONS (New parts with temp IDs)
      const newParts = Array.from(this.parts.entries()).filter(([id]) =>
        String(id).startsWith('temp_'),
      );

      for (const [tempId, part] of newParts) {
        const result = await this.createPartOnServer(part.mesh, tempId);
        if (result) {
          successCount++;
        } else {
          failCount++;
        }
      }

      // 3. PROCESS UPDATES (Existing parts modified post-load)
      for (const id of this.dirtyPartIds) {
        const part = this.parts.get(id);
        if (part) {
          const data = {
            position_x: part.mesh.position.x,
            position_y: part.mesh.position.y,
            position_z: part.mesh.position.z,
            rotation_y: Math.round((part.mesh.rotation.y * 180) / Math.PI),
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

      if (isAutoSave) {
        // Subtle success for auto-save
        if (DEBUG_MODE) {
          console.log(`[Auto-Save] Complete: ${successCount} saved, ${failCount} failed`);
        }
      } else {
        // Full dialog for manual save
        Swal.fire({
          icon: failCount === 0 ? 'success' : 'warning',
          title: failCount === 0 ? 'Build Saved!' : 'Saved with Errors',
          html:
            failCount === 0
              ? 'All your changes have been successfully committed.'
              : `Sync complete. ${successCount} succeeded, ${failCount} failed.`,
          customClass: {
            popup: 'swal-premium',
            confirmButton: 'swal-confirm-btn',
          },
          buttonsStyling: false,
        });
      }
    } catch (error) {
      console.error('[Editor] Fatal error during save:', error);
      if (isAutoSave) {
        if (DEBUG_MODE) {
          console.error('[Auto-Save] Failed:', error);
        }
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Save Failed',
          html: 'A critical error occurred while syncing.<br>Please check your connection.',
          customClass: {
            popup: 'swal-premium',
            confirmButton: 'swal-confirm-btn',
          },
          buttonsStyling: false,
        });
      }
    } finally {
      this.isSaving = false;
      // Remove whichever indicator was shown
      const overlay = document.getElementById('build-editor-saving-overlay');
      if (overlay) {
        overlay.remove();
      }
      const indicator = document.getElementById('build-editor-autosave-indicator');
      if (indicator) {
        indicator.remove();
      }
    }
  }

  async updatePartAPI_Real(partId, data) {
    try {
      // Include optimistic locking via updated_at
      const part = this.parts.get(partId);
      if (part && part.data && part.data.updated_at) {
        data.updated_at = part.data.updated_at;
      }

      const response = await fetch(`${this.api.parts}/${partId}`, {
        method: 'PUT',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': this.csrfToken,
        },
        body: JSON.stringify(data),
      });

      if (response.status === 409) {
        const err = await response.json();
        this.showToastEvent('Another user modified this part. Refresh to see latest.', 'error');
        return false;
      }

      // Update stored updated_at after successful save
      if (response.ok) {
        const updated = await response.json();
        if (updated && updated.updated_at && part) {
          part.data.updated_at = updated.updated_at;
        }
      }

      return response.ok;
    } catch (e) {
      return false;
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
    if (!minimapContainer) {
      return;
    }

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
    if (!this.minimap) {
      return;
    }

    const toRemove = [];
    this.minimap.scene.children.forEach((child) => {
      if (child.userData.isMinimapPart) {
        toRemove.push(child);
      }
    });
    toRemove.forEach((child) => this.minimap.scene.remove(child));

    this.parts.forEach(({ mesh }) => {
      if (!mesh.visible) {
        return;
      }
      const color =
        mesh.userData.type === 'roof'
          ? 0x94a3b8
          : mesh.userData.type === 'window'
            ? 0x93c5fd
            : mesh.userData.type === 'door'
              ? 0x78350f
              : 0x64748b;

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
    if (debugInfo) {
      debugInfo.textContent = message;
    }
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
  }

  // ============ ISSUE PINS SYSTEM ============

  addIssuePin(issueData) {
    if (!issueData.position_x && !issueData.part_id) {
      console.log('[Editor] Issue has no position, skipping pin');
      return null;
    }

    // Get position from issue data or part
    let position;
    if (
      issueData.position_x !== null &&
      issueData.position_y !== null &&
      issueData.position_z !== null
    ) {
      position = new THREE.Vector3(
        issueData.position_x,
        issueData.position_y,
        issueData.position_z,
      );
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
    if (!this.issuePins) {
      this.issuePins = new Map();
    }
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
    pin.children.forEach((child) => {
      if (child.geometry) {
        child.geometry.dispose();
      }
      if (child.material) {
        child.material.dispose();
      }
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
    if (!this.issuePins) {
      return;
    }

    this.issuePins.forEach((pin, issueId) => {
      this.removeIssuePin(issueId);
    });

    this.issuePins.clear();
  }

  showIssuePinsForFloor(floorNumber) {
    if (!this.issuePins) {
      return;
    }

    this.issuePins.forEach((pin, issueId) => {
      // Get issue data to check floor
      // For now, show all pins - in a full implementation,
      // you'd filter by floor based on the attached part's floor
      pin.visible = true;
    });
  }

  getIssueColor(status) {
    const colors = {
      open: 0xef4444, // Red
      in_progress: 0xeab308, // Yellow
      resolved: 0x22c55e, // Green
      closed: 0x6b7280, // Gray
    };
    return colors[status] || colors.closed;
  }

  animateIssuePin(pin) {
    const self = this;
    const floatSpeed = 2;
    const floatHeight = 0.2;

    function animate() {
      if (!pin.parent) {
        return;
      } // Stop if removed from scene

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
      const ease =
        progress < 0.5 ? 2 * progress * progress : 1 - Math.pow(-2 * progress + 2, 2) / 2;

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
    issues.forEach((issue) => {
      this.addIssuePin(issue);
    });

    console.log('[Editor] Loaded', issues.length, 'issue pins');
  }

  // ============ MOBILE CONTROL PANEL ============

  toggleMobileControlPanel() {
    const panel = document.getElementById('mobile-control-panel');
    if (panel) {
      const isOpen = panel.classList.contains('open');
      panel.classList.toggle('open');

      // Update chevron icon
      const chevron = panel.querySelector('.mobile-panel-chevron');
      if (chevron) {
        chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
      }
    }
  }

  mobileRotate() {
    if (this.isPlacing) {
      this.previewRotation = (this.previewRotation + 90) % 360;
      this.showToastEvent('Rotated 90°', 'info');
    } else if (this.selectedPart) {
      const part = this.parts.get(this.selectedPart);
      if (part) {
        part.mesh.rotation.y += Math.PI / 2;
        this.updatePartAPI(this.selectedPart, {
          rotation_y: Math.round((part.mesh.rotation.y * 180) / Math.PI),
        });
        this.showToastEvent('Part rotated', 'info');
      }
    }
  }

  mobileDelete() {
    if (this.selectedPart) {
      this.deletePart(this.selectedPart);
      this.showToastEvent('Part deleted', 'success');
    } else if (this.isPlacing) {
      this.cancelPlacement();
    }
  }

  mobileToggleTransform() {
    if (this.isPlacing) {
      this.cancelPlacement();
    } else {
      this.setTool(this.currentTool === 'move' ? 'select' : 'move');
      this.showToastEvent(this.currentTool === 'move' ? 'Move mode' : 'Select mode', 'info');
    }
  }

  mobileToggleDayNight() {
    this.toggleDayNight();
  }

  mobileCycleGrid() {
    this.cycleGridSize();
  }

  cancelPlacement() {
    this.isPlacing = false;
    this.currentPreset = null;
    this.container.style.cursor = 'default';
    if (this.previewMesh) {
      this.scene.remove(this.previewMesh);
      this.previewMesh = null;
    }
    if (this.previewMarker) {
      this.scene.remove(this.previewMarker);
      this.previewMarker = null;
    }
    this.updateDebugInfo('Placement cancelled');
    this.showToastEvent('Placement cancelled', 'info');

    // Hide mobile placement controls, show action bar
    if (this.isTouchDevice) {
      const placePanel = document.getElementById('mobile-placement-controls');
      if (placePanel) {
        placePanel.classList.remove('show');
      }
      const actionBar = document.getElementById('mobile-action-bar');
      if (actionBar) {
        actionBar.style.display = 'flex';
      }
    }
  }
}

// Initialize editor
let editor;

document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('editor-canvas');
  const buildId = container?.dataset.buildId;
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

  console.log(
    '[CSRF Debug] Token found:',
    csrfToken ? 'YES (length: ' + csrfToken.length + ')' : 'NO',
  );
  console.log('[CSRF Debug] Container:', container ? 'YES' : 'NO');
  console.log('[CSRF Debug] Build ID:', buildId ? 'YES' : 'NO');

  if (container && buildId && csrfToken) {
    editor = new BuildEditor(container, buildId, csrfToken);
    window.editor = editor;
    console.log('[Editor] Assigned to window.editor');
  } else {
    console.error(
      '[Editor] Missing required elements! Container:',
      !!container,
      'BuildID:',
      !!buildId,
      'CSRF:',
      !!csrfToken,
    );
  }
});
