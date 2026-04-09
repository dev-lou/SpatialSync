@extends('layouts.editor')
@section('title', $build->name)

@section('content')
<div class="editor-layout" x-data="editorApp()">
    <!-- Top Bar -->
    <header class="editor-topbar">
        <div class="editor-topbar__left">
            <a href="{{ route('dashboard') }}" class="editor-topbar__logo">
                <i data-lucide="home" class="w-5 h-5"></i>
            </a>
            <div class="editor-topbar__divider"></div>
            <span class="editor-topbar__title">{{ $build->name }}</span>
        </div>

        <div class="editor-topbar__center">
            <!-- Floor Selector -->
            <div class="floor-selector">
                <template x-for="floor in floors" :key="floor">
                    <button class="floor-btn"
                            :class="{ active: currentFloor === floor }"
                            @click="setFloor(floor)"
                            x-text="floor">
                    </button>
                </template>
                <button class="floor-btn floor-btn--add" 
                        @click="addFloor()" 
                        x-show="floors.length < 10"
                        title="Add Floor">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <div class="editor-topbar__right">
            <!-- Day/Night Toggle -->
            <button class="btn btn--sm" 
                    :class="isNightMode ? 'btn--primary' : 'btn--ghost'"
                    @click="toggleDayNight()" title="Day/Night (B)">
                <i :data-lucide="isNightMode ? 'moon' : 'sun'" class="w-4 h-4"></i>
            </button>

            <div class="editor-topbar__divider"></div>
            
            <!-- Paint Mode Button -->
            <button class="btn btn--sm" 
                    :class="paintModeActive ? 'btn--primary' : 'btn--ghost'"
                    @click="togglePaintMode()">
                <i data-lucide="paintbrush" class="w-4 h-4"></i>
                <span>Paint</span>
            </button>
            
            <!-- Material Mode Button -->
            <button class="btn btn--sm"
                    :class="materialModeActive ? 'btn--primary' : 'btn--ghost'"
                    @click="toggleMaterialMode()">
                <i data-lucide="layers" class="w-4 h-4"></i>
                <span>Material</span>
            </button>
            
            <div class="editor-topbar__divider"></div>
            
            <button class="btn btn--ghost btn--sm" :class="{ 'btn--primary': !roofVisible }" @click="toggleRoof()">
                <i data-lucide="home" class="w-4 h-4"></i>
                <span x-text="roofVisible ? 'Hide Roof' : 'Show Roof'"></span>
            </button>
            <div class="editor-topbar__divider"></div>
            <button class="btn btn--ghost btn--sm" @click="undo()" title="Undo (Ctrl+Z)">
                <i data-lucide="undo-2" class="w-4 h-4"></i>
            </button>
            <button class="btn btn--ghost btn--sm" @click="redo()" title="Redo (Ctrl+Y)">
                <i data-lucide="redo-2" class="w-4 h-4"></i>
            </button>
            <div class="editor-topbar__divider"></div>
            <button class="btn btn--secondary btn--sm" @click="saveBuild()">
                <i data-lucide="save" class="w-4 h-4"></i>
                Save
            </button>
        </div>
    </header>

    <!-- Canvas -->
    <div class="editor-canvas">
        <div id="editor-canvas" data-build-id="{{ $build->id }}"></div>
        
        <!-- Placement Mode Indicator -->
        <div class="placement-indicator" x-show="selectedPresetId" x-transition>
            <i data-lucide="mouse-pointer-click" class="w-4 h-4"></i>
            <span>Click to place — <kbd>R</kbd> rotate · <kbd>Q</kbd> cancel</span>
        </div>
        
        <!-- Tool Indicator -->
        <div class="tool-indicator" x-show="currentTool !== 'select' && !selectedPresetId" x-transition>
            <i :data-lucide="toolIcons[currentTool]" class="w-4 h-4"></i>
            <span x-text="toolLabels[currentTool]"></span>
        </div>
        
        <!-- Minimap -->
        <div id="minimap" class="minimap-container"></div>
        
        <!-- Grid Size Badge -->
        <div class="grid-badge" x-show="gridSize !== 1" x-transition>
            <span x-text="'Grid: ' + gridSize + 'x'"></span>
        </div>
    </div>

    <!-- Paint Mode Overlay -->
    <div class="edit-mode-panel paint-panel" 
         x-show="paintModeActive" 
         x-transition>
        <div class="edit-mode-header">
            <i data-lucide="paintbrush" class="w-5 h-5"></i>
            <span>Paint Mode</span>
            <button class="btn btn--sm btn--secondary" @click="togglePaintMode()">Exit</button>
        </div>
        <div class="edit-mode-content">
            <label>Select Color</label>
            <div class="color-grid">
                <button class="color-btn" style="background: #EF4444;" @click="setPaintColor('#EF4444')" title="Red"></button>
                <button class="color-btn" style="background: #F97316;" @click="setPaintColor('#F97316')" title="Orange"></button>
                <button class="color-btn" style="background: #EAB308;" @click="setPaintColor('#EAB308')" title="Yellow"></button>
                <button class="color-btn" style="background: #22C55E;" @click="setPaintColor('#22C55E')" title="Green"></button>
                <button class="color-btn" style="background: #3B82F6;" @click="setPaintColor('#3B82F6')" title="Blue"></button>
                <button class="color-btn" style="background: #8B5CF6;" @click="setPaintColor('#8B5CF6')" title="Purple"></button>
                <button class="color-btn" style="background: #EC4899;" @click="setPaintColor('#EC4899')" title="Pink"></button>
                <button class="color-btn" style="background: #FFFFFF;" @click="setPaintColor('#FFFFFF')" title="White"></button>
                <button class="color-btn" style="background: #6B7280;" @click="setPaintColor('#6B7280')" title="Gray"></button>
                <button class="color-btn" style="background: #1F2937;" @click="setPaintColor('#1F2937')" title="Dark"></button>
                <button class="color-btn" style="background: #92400E;" @click="setPaintColor('#92400E')" title="Brown"></button>
                <button class="color-btn" style="background: #78350F;" @click="setPaintColor('#78350F')" title="Wood"></button>
            </div>
            <div class="color-picker-row">
                <input type="color" id="custom-color" value="#6B7280" @change="setPaintColor($event.target.value)">
                <span>Custom Color</span>
            </div>
        </div>
        <div class="edit-mode-hint">
            Click on any object to paint it
        </div>
    </div>

    <!-- Material Mode Overlay -->
    <div class="edit-mode-panel material-panel" 
         x-show="materialModeActive" 
         x-transition>
        <div class="edit-mode-header">
            <i data-lucide="layers" class="w-5 h-5"></i>
            <span>Material Mode</span>
            <button class="btn btn--sm btn--secondary" @click="toggleMaterialMode()">Exit</button>
        </div>
        <div class="edit-mode-content">
            <label>Select Material</label>
            <div class="material-grid">
                <button class="material-btn" @click="setMaterial('default')">Default</button>
                <button class="material-btn" @click="setMaterial('wood')">Wood</button>
                <button class="material-btn" @click="setMaterial('brick')">Brick</button>
                <button class="material-btn" @click="setMaterial('concrete')">Concrete</button>
                <button class="material-btn" @click="setMaterial('glass')">Glass</button>
                <button class="material-btn" @click="setMaterial('metal')">Metal</button>
                <button class="material-btn" @click="setMaterial('stone')">Stone</button>
                <button class="material-btn" @click="setMaterial('marble')">Marble</button>
            </div>
        </div>
        <div class="edit-mode-hint">
            Click on any object to apply material
        </div>
    </div>

    <!-- Bottom Toolbar -->
    <div class="editor-bottom">
        <!-- Tools Row -->
        <div class="editor-tools">
            <div class="tool-group">
                <button class="tool-btn" :class="{ active: currentTool === 'select' }" 
                        @click="setTool('select')" title="Select (Q)">
                    <i data-lucide="mouse-pointer" class="w-4 h-4"></i>
                    <span>Select</span>
                </button>
                <button class="tool-btn tool-btn--move" :class="{ active: currentTool === 'move' }" 
                        @click="setTool('move')" title="Move (T)">
                    <i data-lucide="move" class="w-4 h-4"></i>
                    <span>Move</span>
                </button>
                <button class="tool-btn tool-btn--clone" :class="{ active: currentTool === 'clone' }" 
                        @click="setTool('clone')" title="Clone (C)">
                    <i data-lucide="copy" class="w-4 h-4"></i>
                    <span>Clone</span>
                </button>
                <button class="tool-btn tool-btn--delete" :class="{ active: currentTool === 'delete' }" 
                        @click="setTool('delete')" title="Delete (G)">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    <span>Delete</span>
                </button>
            </div>
            <div class="tool-divider"></div>
            <div class="tool-group">
                <button class="tool-btn" @click="toggleGrid()" title="Toggle Grid (H)">
                    <i data-lucide="grid-3x3" class="w-4 h-4"></i>
                    <span>Grid</span>
                </button>
                <button class="tool-btn" @click="cycleGridSize()" title="Grid Size (J)">
                    <i data-lucide="maximize-2" class="w-4 h-4"></i>
                    <span x-text="'Snap ' + gridSize + 'x'">Snap 1x</span>
                </button>
            </div>
        </div>

        <!-- Category Tabs -->
        <div class="editor-tabs">
            @php
                $categories = [
                    'wall' => ['icon' => 'square', 'label' => 'Walls'],
                    'floor' => ['icon' => 'layers', 'label' => 'Floors'],
                    'roof' => ['icon' => 'triangle', 'label' => 'Roofs'],
                    'door' => ['icon' => 'door-open', 'label' => 'Doors'],
                    'window' => ['icon' => 'app-window', 'label' => 'Windows'],
                    'stairs' => ['icon' => 'trending-up', 'label' => 'Stairs'],
                ];
            @endphp

            @foreach($categories as $type => $cat)
                <button class="editor-tab" 
                        :class="{ active: activeTab === '{{ $type }}' }"
                        @click="activeTab = '{{ $type }}'">
                    <i data-lucide="{{ $cat['icon'] }}"></i>
                    {{ $cat['label'] }}
                </button>
            @endforeach
        </div>

        <!-- Parts Grid -->
        <div class="editor-parts">
            @foreach($presets as $type => $items)
                @foreach($items as $preset)
                    <button class="part-card"
                            x-show="activeTab === '{{ $type }}'"
                            :class="{ active: selectedPresetId === {{ $preset->id }} }"
                            @click="selectPreset({{ json_encode([
                                'id' => $preset->id,
                                'name' => $preset->name,
                                'type' => $preset->type,
                                'variant' => $preset->variant,
                                'default_width' => $preset->default_width,
                                'default_height' => $preset->default_height,
                                'default_depth' => $preset->default_depth,
                                'default_color' => $preset->default_color,
                                'icon' => $preset->icon,
                            ]) }})">
                        <div class="part-card__icon">
                            <i data-lucide="{{ $preset->icon }}"></i>
                        </div>
                        <span class="part-card__name">{{ $preset->name }}</span>
                    </button>
                @endforeach
            @endforeach
        </div>
    </div>

    <!-- Debug Bar -->
    <div class="debug-bar">
        <span>
            <strong>ConstructHub</strong>
            <span class="status-ok" id="debug-three">Three.js: OK</span>
        </span>
        <span id="debug-info">Loading...</span>
        <span>
            Parts: <strong id="parts-count">0</strong> | 
            Floor: <strong id="current-floor">1</strong> |
            Grid: <strong id="grid-size-display">1x</strong>
        </span>
    </div>

    <!-- Keyboard Hints -->
    <div class="keyboard-hint">
        <kbd>WASD</kbd> Move<br>
        <kbd>R</kbd> Rotate<br>
        <kbd>G</kbd> Delete<br>
        <kbd>T</kbd> Move Part<br>
        <kbd>C</kbd> Clone<br>
        <kbd>B</kbd> Day/Night<br>
        <kbd>J</kbd> Grid Size<br>
        <kbd>Space</kbd> Bird's Eye<br>
        <kbd>Q</kbd> Cancel
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toast-container"></div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/build/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
<script>
    if (typeof THREE !== 'undefined') {
        document.getElementById('debug-three').textContent = 'Three.js: OK';
        document.getElementById('debug-three').className = 'status-ok';
    } else {
        document.getElementById('debug-three').textContent = 'Three.js: FAILED';
        document.getElementById('debug-three').className = 'status-error';
    }
</script>
</script>
<script src="{{ asset('js/build-editor.js') }}?v={{ filemtime(public_path('js/build-editor.js')) }}"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('editorApp', () => ({
        currentFloor: 1,
        floors: [1],
        maxFloors: 10,
        roofVisible: true,
        activeTab: 'wall',
        selectedPresetId: null,
        paintModeActive: false,
        materialModeActive: false,
        currentPaintColor: '#6B7280',
        currentMaterial: 'default',
        currentTool: 'select',
        gridSize: 1,
        isNightMode: false,
        
        toolIcons: { select: 'mouse-pointer', delete: 'trash-2', move: 'move', clone: 'copy' },
        toolLabels: { select: 'Select Tool', delete: 'Delete Tool — Click to remove', move: 'Move Tool — Click to pick up', clone: 'Clone Tool — Click to duplicate' },
        
        init() {
            window.addEventListener('part-placed', (e) => {
                document.getElementById('parts-count').textContent = e.detail.count;
            });
            
            window.addEventListener('floor-changed', (e) => {
                this.currentFloor = e.detail.floor;
                document.getElementById('current-floor').textContent = e.detail.floor;
            });
            
            window.addEventListener('floor-added', (e) => {
                if (!this.floors.includes(e.detail.floor)) {
                    this.floors.push(e.detail.floor);
                }
            });
            
            window.addEventListener('roof-toggled', (e) => {
                this.roofVisible = e.detail.visible;
            });
            
            window.addEventListener('toast', (e) => {
                this.showToast(e.detail.message, e.detail.type);
            });
            
            window.addEventListener('preset-deselected', () => {
                this.selectedPresetId = null;
            });
            
            window.addEventListener('paint-mode-changed', (e) => {
                this.paintModeActive = e.detail.active;
                if (e.detail.active) this.selectedPresetId = null;
            });
            
            window.addEventListener('material-mode-changed', (e) => {
                this.materialModeActive = e.detail.active;
                if (e.detail.active) this.selectedPresetId = null;
            });
            
            window.addEventListener('tool-changed', (e) => {
                this.currentTool = e.detail.tool;
                this.$nextTick(() => lucide.createIcons());
            });
            
            window.addEventListener('gridsize-changed', (e) => {
                this.gridSize = e.detail.size;
                const el = document.getElementById('grid-size-display');
                if (el) el.textContent = e.detail.size + 'x';
            });
            
            window.addEventListener('daynight-changed', (e) => {
                this.isNightMode = e.detail.night;
                this.$nextTick(() => lucide.createIcons());
            });
            
            window.addEventListener('preset-selected', () => {
                this.currentTool = 'select';
            });

            this.$nextTick(() => {
                lucide.createIcons();
            });
        },
        
        setFloor(floor) {
            this.currentFloor = floor;
            if (typeof editor !== 'undefined') editor.setFloor(floor);
        },
        
        addFloor() {
            if (typeof editor !== 'undefined') editor.addFloor();
        },
        
        toggleRoof() {
            if (typeof editor !== 'undefined') editor.toggleRoof();
        },
        
        toggleGrid() {
            if (typeof editor !== 'undefined') editor.toggleGrid();
        },
        
        cycleGridSize() {
            if (typeof editor !== 'undefined') editor.cycleGridSize();
        },
        
        toggleDayNight() {
            if (typeof editor !== 'undefined') editor.toggleDayNight();
        },
        
        setTool(tool) {
            this.currentTool = tool;
            if (typeof editor !== 'undefined') editor.setTool(tool);
        },
        
        selectPreset(preset) {
            this.selectedPresetId = preset.id;
            if (typeof editor !== 'undefined') editor.selectPreset(preset);
        },
        
        togglePaintMode() {
            if (typeof editor !== 'undefined') {
                if (this.paintModeActive) {
                    editor.exitPaintMode();
                } else {
                    editor.enterPaintMode();
                }
            }
        },
        
        setPaintColor(color) {
            this.currentPaintColor = color;
            if (typeof editor !== 'undefined') editor.setPaintColor(color);
        },
        
        toggleMaterialMode() {
            if (typeof editor !== 'undefined') {
                if (this.materialModeActive) {
                    editor.exitMaterialMode();
                } else {
                    editor.enterMaterialMode();
                }
            }
        },
        
        setMaterial(material) {
            this.currentMaterial = material;
            if (typeof editor !== 'undefined') editor.setMaterial(material);
        },
        
        undo() {
            if (typeof editor !== 'undefined') editor.undo();
        },
        
        redo() {
            if (typeof editor !== 'undefined') editor.redo();
        },
        
        saveBuild() {
            if (typeof editor !== 'undefined') editor.saveBuild();
        },
        
        showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast toast--${type}`;
            toast.textContent = message;
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        },
    }));
});
</script>
@endpush

@push('styles')
<style>
    /* ============ EDIT MODE PANELS ============ */
    .edit-mode-panel {
        position: fixed;
        top: 60px;
        left: 50%;
        transform: translateX(-50%);
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        padding: 16px;
        z-index: 1000;
        min-width: 320px;
    }
    
    .edit-mode-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border);
    }
    
    .edit-mode-header i { color: var(--accent); }
    
    .edit-mode-header span {
        flex: 1;
        font-weight: 600;
        font-size: 16px;
    }
    
    .edit-mode-content label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-tertiary);
        margin-bottom: 8px;
    }
    
    .edit-mode-hint {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid var(--border);
        font-size: 12px;
        color: var(--text-secondary);
        text-align: center;
    }
    
    .color-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 8px;
    }
    
    .color-btn {
        width: 40px;
        height: 40px;
        border: 2px solid var(--border);
        border-radius: 8px;
        cursor: pointer;
        transition: transform 0.15s ease, border-color 0.15s ease;
    }
    
    .color-btn:hover {
        transform: scale(1.1);
        border-color: var(--accent);
    }
    
    .color-picker-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 12px;
    }
    
    .color-picker-row input[type="color"] {
        width: 40px;
        height: 40px;
        padding: 0;
        border: 2px solid var(--border);
        border-radius: 8px;
        cursor: pointer;
    }
    
    .color-picker-row span {
        font-size: 13px;
        color: var(--text-secondary);
    }
    
    .material-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
    }
    
    .material-btn {
        padding: 12px 8px;
        background: var(--bg-secondary);
        border: 2px solid var(--border);
        border-radius: 8px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 500;
        color: var(--text-primary);
        transition: background 0.15s ease, border-color 0.15s ease;
    }
    
    .material-btn:hover {
        background: var(--bg-tertiary);
        border-color: var(--accent);
    }

    /* ============ TOOLS ROW ============ */
    .editor-tools {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 16px;
        border-bottom: 1px solid var(--border);
        background: var(--bg-secondary);
    }
    
    .tool-group {
        display: flex;
        gap: 4px;
    }
    
    .tool-divider {
        width: 1px;
        height: 24px;
        background: var(--border);
        margin: 0 4px;
    }
    
    .tool-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        padding: 6px 12px;
        font-size: 10px;
        font-weight: 600;
        color: var(--text-secondary);
        background: transparent;
        border: 1.5px solid transparent;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    
    .tool-btn svg { width: 16px; height: 16px; }
    
    .tool-btn:hover {
        background: var(--surface);
        color: var(--text-primary);
    }
    
    .tool-btn.active {
        background: var(--accent);
        color: white;
        border-color: var(--accent);
    }
    
    .tool-btn--delete.active {
        background: #EF4444;
        border-color: #EF4444;
    }
    
    .tool-btn--move.active {
        background: #F59E0B;
        border-color: #F59E0B;
    }
    
    .tool-btn--clone.active {
        background: #8B5CF6;
        border-color: #8B5CF6;
    }
    
    /* ============ TOOL INDICATOR ============ */
    .tool-indicator {
        position: absolute;
        top: 16px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: rgba(239, 68, 68, 0.95);
        color: white;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        pointer-events: none;
        z-index: 10;
    }
    
    /* ============ MINIMAP ============ */
    .minimap-container {
        position: absolute;
        bottom: 16px;
        right: 16px;
        width: 150px;
        height: 150px;
        border: 2px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        overflow: hidden;
        opacity: 0.8;
        pointer-events: none;
    }
    
    /* ============ GRID SIZE BADGE ============ */
    .grid-badge {
        position: absolute;
        top: 16px;
        right: 16px;
        padding: 4px 12px;
        background: rgba(59, 130, 246, 0.9);
        color: white;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        font-family: 'JetBrains Mono', monospace;
        pointer-events: none;
        z-index: 10;
    }
    
    /* ============ KBD IN INDICATOR ============ */
    .placement-indicator kbd {
        display: inline-block;
        padding: 1px 5px;
        background: rgba(255,255,255,0.2);
        border-radius: 3px;
        font-size: 11px;
        font-family: 'JetBrains Mono', monospace;
    }
</style>
@endpush
