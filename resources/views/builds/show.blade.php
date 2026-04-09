@extends('layouts.editor')
@section('title', $build->name)

@section('content')
<div class="editor-layout" :class="{ 'sidebar-open': sidebarOpen }" x-data="editorApp()">
    <!-- Top Bar -->
    <header class="editor-topbar">
        <div class="editor-topbar__left">
            <button class="btn btn--ghost btn--sm" @click="sidebarOpen = !sidebarOpen" title="Toggle Sidebar">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
            <div class="editor-topbar__divider"></div>
            <a href="{{ route('dashboard') }}" class="editor-topbar__logo">
                <i data-lucide="box" class="w-5 h-5"></i>
            </a>
            <div class="editor-topbar__divider"></div>
            <span class="editor-topbar__title">{{ $build->name }}</span>
            <button class="btn btn--ghost btn--sm" @click="confirmReload()" title="Refresh Editor">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            </button>
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

            <div class="editor-topbar__divider mx-4 nav-shortcut-divider"></div>

            <!-- Horizontal Keybind Guide -->
            <div class="navbar-shortcuts">
                <div class="nb-shortcut"><kbd>WASD / ↑↓←→</kbd> Move</div>
                <div class="nb-shortcut"><kbd>R</kbd> Rotate</div>
                <div class="nb-shortcut"><kbd>G</kbd> Delete</div>
                <div class="nb-shortcut"><kbd>T</kbd> Transform</div>
                <div class="nb-shortcut"><kbd>SPACE</kbd> View</div>
                <div class="nb-shortcut"><kbd>Q</kbd> Cancel</div>
            </div>
        </div>

        <div class="editor-topbar__right">

            <button class="btn btn--ghost btn--sm" @click="undo()" title="Undo (Ctrl+Z)">
                <i data-lucide="undo-2" class="w-4 h-4"></i>
            </button>
            <button class="btn btn--ghost btn--sm" @click="redo()" title="Redo (Ctrl+Y / Ctrl+Shift+Z)">
                <i data-lucide="redo-2" class="w-4 h-4"></i>
            </button>
            <div class="editor-topbar__divider"></div>
            <button class="btn btn--secondary btn--sm" @click="saveBuild()">
                <i data-lucide="save" class="w-4 h-4"></i>
                Save
            </button>
        </div>
    </header>

    <!-- Collaboration Sidebar -->
    <aside class="sidebar" :class="{ 'sidebar--open': sidebarOpen }">
        <div class="sidebar__header">
            <span style="font-weight: 700; font-size: 15px;">Collaboration</span>
            <button class="btn btn--ghost btn--sm" @click="sidebarOpen = false">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="sidebar__tabs">
            <div class="sidebar__tab" :class="{ active: sidebarTab === 'collab' }" @click="sidebarTab = 'collab'">
                Invite
            </div>
            <div class="sidebar__tab" :class="{ active: sidebarTab === 'chat' }" @click="sidebarTab = 'chat'">
                Chat
            </div>
        </div>

        <div class="sidebar__content">
            <!-- Collaboration Tab -->
            <div x-show="sidebarTab === 'collab'">
                <div class="sidebar-section">
                    <div class="sidebar-section__title">
                        <i data-lucide="user-plus"></i> Invite Members
                    </div>
                    <div class="search-box">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        <input type="text" placeholder="Search by name or email..." 
                               x-model="userSearchQuery" 
                               @input.debounce.300ms="searchUsers()">
                    </div>
                    
                    <div class="search-results" x-show="searchResults.length > 0">
                        <template x-for="user in searchResults" :key="user.id">
                            <div class="search-result" @click="addMember(user.email)">
                                <div>
                                    <div class="member-name" x-text="user.name"></div>
                                    <div class="member-role" x-text="user.email"></div>
                                </div>
                                <i data-lucide="plus" class="w-4 h-4 text-accent"></i>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section__title">
                        <i data-lucide="share-2"></i> Share Link
                    </div>
                    <div class="flex gap-2">
                        <input type="text" readonly x-model="shareUrl" class="chat-input" placeholder="Generate a link...">
                        <button class="btn btn--secondary btn--sm" @click="getShareUrl()">
                            <i data-lucide="copy" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section__title">
                        <i data-lucide="users"></i> Active Members
                    </div>
                    <div class="member-list">
                        <template x-for="member in members" :key="member.id">
                            <div class="member-item">
                                <div class="member-info">
                                    <div class="avatar avatar--sm" style="background: linear-gradient(135deg, var(--accent) 0%, #818CF8 100%); width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; font-weight: 700; text-transform: uppercase;" x-text="member.name.substring(0, 1)"></div>
                                    <div>
                                        <div class="member-name" x-text="member.name"></div>
                                        <div class="member-role" x-text="member.role"></div>
                                    </div>
                                </div>
                                <button class="btn btn--ghost btn--sm" 
                                        x-show="userRole === 'admin' && member.id !== {{ Auth::id() }}"
                                        @click="removeMember(member.id)">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5 text-danger"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Chat Tab -->
            <div x-show="sidebarTab === 'chat'" class="h-full flex flex-col">
                <div class="sidebar-section__title mb-4">
                    <i data-lucide="message-square"></i> Project Chat
                </div>
                <div class="chat-messages" id="chat-messages">
                    <template x-for="msg in chatMessages" :key="msg.id">
                        <div class="message" :class="msg.user_id === {{ Auth::id() }} ? 'message--mine' : 'message--other'">
                            <div class="message__user" x-show="msg.user_id !== {{ Auth::id() }}" x-text="msg.user.name"></div>
                            <div x-text="msg.message"></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="sidebar__footer" x-show="sidebarTab === 'chat'">
            <input type="text" class="chat-input" placeholder="Type a message..." 
                   x-model="newMessage" @keyup.enter="sendMessage()">
            <button class="btn btn--primary" @click="sendMessage()">
                <i data-lucide="send" class="w-4 h-4"></i>
            </button>
        </div>
    </aside>

    <!-- Canvas -->
    <div class="editor-canvas">
        <div id="editor-canvas" data-build-id="{{ $build->id }}"></div>
        
        <!-- Floating Toolbar (Right Side) -->
        <aside class="floating-toolbar">
            <div class="floating-toolbar__group">
                <button class="floating-tool-btn" :class="{ active: currentTool === 'select' }" 
                        @click="setTool('select')" title="Select Tool (Q)">
                    <i data-lucide="mouse-pointer" class="w-5 h-5"></i>
                    <span class="tooltip">Select</span>
                </button>
                <button class="floating-tool-btn" :class="{ active: currentTool === 'move' }" 
                        @click="setTool('move')" title="Move Tool (T)">
                    <i data-lucide="move" class="w-5 h-5"></i>
                    <span class="tooltip">Move</span>
                </button>
                <button class="floating-tool-btn" :class="{ active: currentTool === 'clone' }" 
                        @click="setTool('clone')" title="Clone Tool (C)">
                    <i data-lucide="copy" class="w-5 h-5"></i>
                    <span class="tooltip">Clone</span>
                </button>
                <button class="floating-tool-btn" :class="{ active: currentTool === 'delete' }" 
                        @click="setTool('delete')" title="Delete Tool (G)">
                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                    <span class="tooltip">Delete</span>
                </button>
            </div>

            <div class="floating-toolbar__divider"></div>

            <div class="floating-toolbar__group">
                <button class="floating-tool-btn" :class="{ 'btn--primary': paintModeActive }" 
                        @click="togglePaintMode()" title="Paint Mode">
                    <i data-lucide="paintbrush" class="w-5 h-5"></i>
                    <span class="tooltip">Paint</span>
                </button>
                <button class="floating-tool-btn" :class="{ 'btn--primary': materialModeActive }" 
                        @click="toggleMaterialMode()" title="Material Mode">
                    <i data-lucide="layers" class="w-5 h-5"></i>
                    <span class="tooltip">Material</span>
                </button>
                <button class="floating-tool-btn" :class="{ 'btn--primary': !roofVisible }" 
                        @click="toggleRoof()" title="Toggle Roof">
                    <i data-lucide="home" class="w-5 h-5"></i>
                    <span class="tooltip">Roof</span>
                </button>
            </div>

            <div class="floating-toolbar__divider"></div>

            <div class="floating-toolbar__group">
                <button class="floating-tool-btn" @click="toggleGrid()" title="Toggle Grid (H)">
                    <i data-lucide="grid-3x3" class="w-5 h-5"></i>
                    <span class="tooltip">Toggle Grid</span>
                </button>
                <button class="floating-tool-btn" @click="cycleGridSize()" title="Grid Size (J)">
                    <i data-lucide="maximize-2" class="w-5 h-5"></i>
                    <span class="tooltip" x-text="'Snap ' + gridSize + 'X'">Snap 1X</span>
                </button>
            </div>
        </aside>

        <!-- Placement Mode Indicator -->
        <div class="placement-indicator" x-show="selectedPresetId" x-transition>
            <i data-lucide="mouse-pointer-click" class="w-4 h-4"></i>
            <span>Click to place — <kbd>R</kbd> rotate · <kbd>↑↓←→</kbd> move · <kbd>Q</kbd> cancel</span>
        </div>
        
        <!-- Tool Indicator -->
        <div class="tool-indicator" x-show="currentTool !== 'select' && !selectedPresetId" x-transition>
            <i :data-lucide="toolIcons[currentTool]" class="w-4 h-4"></i>
            <span x-text="toolLabels[currentTool]"></span>
        </div>
        
        <!-- Minimap Removed -->
        
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
            <!-- Active Selection Display -->
            <div style="margin-bottom: 16px; padding: 12px; background: rgba(0,0,0,0.15); border-radius: 10px; display: flex; align-items: center; justify-content: space-between; border: 1px solid rgba(255,255,255,0.05);">
                <span style="font-size: 13px; opacity: 0.8; font-weight: 500;">Currently Selected</span>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div :style="`width: 24px; height: 24px; border-radius: 6px; background: ${currentPaintColor}; box-shadow: 0 2px 4px rgba(0,0,0,0.2); border: 2px solid rgba(255,255,255,0.8);`"></div>
                    <span x-text="currentPaintColor" style="font-family: monospace; font-size: 13px; font-weight: 700; color: var(--accent);"></span>
                </div>
            </div>

            <label>Select Palette</label>
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
            <!-- Active Selection Display -->
            <div style="margin-bottom: 16px; padding: 12px; background: rgba(0,0,0,0.15); border-radius: 10px; display: flex; align-items: center; justify-content: space-between; border: 1px solid rgba(255,255,255,0.05);">
                <span style="font-size: 13px; opacity: 0.8; font-weight: 500;">Currently Selected</span>
                <span x-text="currentMaterial" style="text-transform: uppercase; letter-spacing: 0.05em; font-size: 13px; font-weight: 700; color: var(--accent); background: rgba(var(--accent-rgb), 0.1); padding: 4px 10px; border-radius: 6px;"></span>
            </div>

            <label>Available Patterns</label>
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
            <!-- Bloxburg Custom Poly Draw Tool -->
            <button class="part-card"
                    x-show="activeTab === 'floor' || activeTab === 'roof'"
                    :class="{ active: isDrawingPoly }"
                    style="border-color: var(--accent); background: rgba(var(--accent-rgb), 0.05);"
                    @click="toggleDrawMode(activeTab)">
                <div class="part-icon" style="color: var(--accent);">
                    <i data-lucide="pen-tool"></i>
                </div>
                <span>Draw Manual</span>
            </button>

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

    <!-- Toast Container -->
    <!-- Modern Toasts handled by SweetAlert2 in layout -->
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
        isDrawingPoly: false,
        currentPaintColor: '#6B7280',
        currentMaterial: 'default',
        currentTool: 'select',
        gridSize: 1,
        isNightMode: false,

        // Sidebar State
        sidebarOpen: false,
        sidebarTab: 'collab',
        userSearchQuery: '',
        searchResults: [],
        members: @json($membersData),
        userRole: '{{ $userRole }}',
        shareUrl: '',
        chatMessages: @json($messages),
        newMessage: '',
        
        toolIcons: { select: 'mouse-pointer', delete: 'trash-2', move: 'move', clone: 'copy' },
        toolLabels: { select: 'Select Tool', delete: 'Delete Tool — Click to remove', move: 'Move Tool — Click to pick up', clone: 'Clone Tool — Click to duplicate' },
        
        init() {
            // Chat & Sync Polling
            setInterval(() => {
                if (this.sidebarTab === 'chat' && this.sidebarOpen) {
                    this.fetchMessages();
                }
            }, 3000);

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
            
            window.addEventListener('draw-mode-changed', (e) => {
                this.isDrawingPoly = e.detail.active;
                if (e.detail.active) this.selectedPresetId = null;
            });
            
            window.addEventListener('preset-selected', () => {
                this.currentTool = 'select';
            });

            this.$nextTick(() => {
                lucide.createIcons();
                this.scrollToBottom();
            });
        },

        // Sidebar Actions
        async searchUsers() {
            if (this.userSearchQuery.length < 2) {
                this.searchResults = [];
                return;
            }
            try {
                const res = await fetch(`/users/search?q=${this.userSearchQuery}`);
                this.searchResults = await res.json();
                this.$nextTick(() => lucide.createIcons());
            } catch (err) {
                console.error('Search failed', err);
            }
        },

        async addMember(email) {
            try {
                const res = await fetch(`/builds/{{ $build->id }}/members`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ email, role: 'viewer' })
                });
                const data = await res.json();
                if (res.ok) {
                    this.members.push(data.user);
                    this.userSearchQuery = '';
                    this.searchResults = [];
                    this.showToast(data.message);
                } else {
                    this.showToast(data.message || 'Failed to add member', 'error');
                }
            } catch (err) {
                this.showToast('Something went wrong', 'error');
            }
        },

        async removeMember(userId) {
            if (!confirm('Are you sure you want to remove this member?')) return;
            try {
                const res = await fetch(`/builds/{{ $build->id }}/members/${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                if (res.ok) {
                    this.members = this.members.filter(m => m.id !== userId);
                    this.showToast('Member removed');
                }
            } catch (err) {
                this.showToast('Failed to remove member', 'error');
            }
        },

        async getShareUrl() {
            try {
                const res = await fetch(`/builds/{{ $build->id }}/share`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await res.json();
                this.shareUrl = data.url;
                navigator.clipboard.writeText(data.url);
                this.showToast('Share link copied to clipboard!');
            } catch (err) {
                this.showToast('Failed to generate share link', 'error');
            }
        },

        async fetchMessages() {
            try {
                const res = await fetch(`/api/builds/{{ $build->id }}/messages`);
                const messages = await res.json();
                if (messages.length > this.chatMessages.length) {
                    this.chatMessages = messages;
                    this.scrollToBottom();
                }
            } catch (err) {
                console.error('Failed to fetch messages', err);
            }
        },

        async sendMessage() {
            if (!this.newMessage.trim()) return;
            const message = this.newMessage;
            this.newMessage = '';
            
            try {
                const res = await fetch(`/api/builds/{{ $build->id }}/messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ message })
                });
                const data = await res.json();
                this.chatMessages.push(data);
                this.scrollToBottom();
            } catch (err) {
                this.showToast('Failed to send message', 'error');
            }
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = document.getElementById('chat-messages');
                if (el) el.scrollTop = el.scrollHeight;
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
        
        toggleDrawMode(type) {
            if (typeof editor !== 'undefined') {
                editor.toggleDrawMode(type);
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
            showSweetToast(message, type);
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

    .editor-topbar__center {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 24px;
        min-width: 0;
    }

    /* ============ NAVBAR SHORTCUTS ============ */
    .navbar-shortcuts {
        display: none;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }
    
    .nav-shortcut-divider {
        display: none;
    }
    
    @media (min-width: 1100px) {
        .navbar-shortcuts {
            display: flex;
        }
        .nav-shortcut-divider {
            display: block;
        }
    }
    
    .nb-shortcut {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-tertiary);
        letter-spacing: 0.05em;
        white-space: nowrap;
    }
    
    .nb-shortcut kbd {
        background: var(--bg-secondary);
        color: var(--text-primary);
        padding: 2px 6px;
        border-radius: 4px;
        font-family: var(--font-mono);
        font-size: 10px;
        border: 1px solid var(--border-default);
        box-shadow: 0 1px 0 var(--border-strong);
        min-width: 24px;
        text-align: center;
    }
    
    /* Hide the old keyboard hint styles from layout if they conflict */
    .keyboard-hint { display: none !important; }

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
    
    /* ============ FLOATING TOOLBAR ============ */
    .floating-toolbar {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding: 12px;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px) saturate(180%);
        -webkit-backdrop-filter: blur(12px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 20px;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
        z-index: 1000;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .floating-toolbar:hover {
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 16px 48px rgba(0, 0, 0, 0.12);
    }
    
    .floating-toolbar__group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .floating-toolbar__divider {
        height: 1px;
        background: var(--border);
        margin: 4px 0;
        opacity: 0.5;
    }
    
    .floating-tool-btn {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        border: none;
        background: transparent;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .floating-tool-btn:hover {
        background: var(--bg-secondary);
        color: var(--accent);
        transform: scale(1.05);
    }
    
    .floating-tool-btn.active {
        background: var(--accent);
        color: white;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    
    .floating-tool-btn.btn--primary {
        background: var(--accent);
        color: white;
    }
    
    /* Tooltip */
    .floating-tool-btn .tooltip {
        position: absolute;
        right: calc(100% + 12px);
        top: 50%;
        transform: translateY(-50%) translateX(10px);
        padding: 6px 12px;
        background: var(--surface);
        color: var(--text-primary);
        font-size: 11px;
        font-weight: 600;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        opacity: 0;
        pointer-events: none;
        transition: all 0.2s ease;
        white-space: nowrap;
        border: 1px solid var(--border);
    }
    
    .floating-tool-btn:hover .tooltip {
        opacity: 1;
        transform: translateY(-50%) translateX(0);
    }

    /* Hide the old keyboard hint styles from layout if they conflict */
    .keyboard-hint { display: none !important; }

    /* ============ MODERN SWEETALERT2 PREMIUM THEME ============ */
    .swal-premium .swal2-popup {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 24px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        padding: 2.5rem;
        box-shadow: 0 40px 100px rgba(0, 0, 0, 0.12), 0 10px 40px rgba(0, 0, 0, 0.08);
    }
    
    .swal-premium .swal2-title {
        font-size: 1.6rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #1a1a1a;
        margin-bottom: 0.5rem;
    }
    
    .swal-premium .swal2-html-container {
        font-size: 1.05rem;
        color: #4b5563;
        line-height: 1.6;
        font-weight: 500;
    }
    
    .swal-premium .swal2-icon {
        border-width: 2px !important;
        margin-bottom: 2rem !important;
        transform: scale(1.1);
        border-color: var(--accent) !important;
        color: var(--accent) !important;
    }
    
    /* Premium Buttons Styling */
    .swal-premium .swal2-actions {
        margin-top: 2.5rem !important;
        gap: 12px;
        width: 100%;
        justify-content: center;
    }

    .swal-confirm-btn, .swal-deny-btn, .swal-cancel-btn {
        border: none !important;
        outline: none !important;
        border-radius: 14px !important;
        font-weight: 700 !important;
        font-size: 14px !important;
        padding: 14px 24px !important;
        min-width: 130px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        text-transform: none;
    }

    .swal-confirm-btn {
        background: linear-gradient(135deg, #0066FF, #0052CC) !important;
        color: white !important;
    }

    .swal-confirm-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 102, 255, 0.3);
        filter: brightness(1.1);
    }

    .swal-deny-btn {
        background: linear-gradient(135deg, #EF4444, #DC2626) !important;
        color: white !important;
    }

    .swal-deny-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
        filter: brightness(1.1);
    }

    .swal-cancel-btn {
        background: #f3f4f6 !important;
        color: #1f2937 !important;
    }

    .swal-cancel-btn:hover {
        background: #e5e7eb !important;
        transform: translateY(-2px);
    }

    /* Toasts logic */
    .swal-toast {
        padding: 12px 20px !important;
        border-radius: 16px !important;
    }
</style>
@endpush
