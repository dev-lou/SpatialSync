@extends('layouts.editor')
@section('title', $build->name)

@section('content')
<div class="editor-layout" :class="{ 'sidebar-open': sidebarOpen }" x-data="editorApp()">
    <!-- Top Bar -->
    <header class="editor-topbar">
        <div class="editor-topbar__left">
            <!-- Explicit Back Button -->
            <a href="{{ route('builds.index') }}" class="btn btn--ghost btn--sm" style="display: flex; align-items: center; gap: 6px; color: var(--text-secondary); text-decoration: none;" title="Back to Builds">
                <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                <span style="font-size: 13px; font-weight: 600;">Back</span>
            </a>
            <div class="editor-topbar__divider"></div>
            
            <button class="btn btn--ghost btn--sm" @click="sidebarOpen = !sidebarOpen" title="Toggle Sidebar">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
            <div class="editor-topbar__divider"></div>
            
            <span class="editor-topbar__title">{{ $build->name }}</span>
            
            <!-- Real-time Connection Indicator -->
            <div class="rt-indicator" :title="'Connection: ' + rtStatus + (isReconnecting ? ' (Reconnecting...)' : '')">
                <div class="rt-indicator__dot" :class="rtStatus" :class="{ 'reconnecting': isReconnecting }"></div>
                <span class="rt-indicator__label" x-text="isReconnecting ? 'Reconnecting...' : (rtStatus === 'connected' ? 'Live' : (rtStatus === 'connecting' ? 'Syncing...' : 'Offline'))"></span>
            </div>

            <!-- View Only Badge for Viewers -->
            <div class="view-only-badge" x-show="userRole === 'viewer'" title="You have view-only access">
                <i data-lucide="eye" class="w-3 h-3"></i>
                <span>View Only</span>
            </div>

            <button class="btn btn--ghost btn--sm" @click="confirmReload()" title="Refresh Editor">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="editor-topbar__center">
            <!-- Floor Selector -->
            <div class="floor-selector">
                <button class="floor-btn floor-btn--arrow" 
                        @click="goUpFloor()" 
                        :disabled="currentFloor >= floors.length"
                        title="Go Up Floor">
                    <i data-lucide="chevron-up" class="w-4 h-4"></i>
                </button>
                
                <div class="floor-display" x-text="'Floor ' + currentFloor"></div>
                
                <button class="floor-btn floor-btn--arrow" 
                        @click="goDownFloor()" 
                        :disabled="currentFloor <= 1"
                        title="Go Down Floor">
                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                </button>
                
                <div class="floor-divider"></div>
                
                <button class="floor-btn floor-btn--add" 
                        @click="addFloor()" 
                        x-show="floors.length < 10"
                        title="Add New Floor">
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
            <div class="sidebar__tab" :class="{ active: sidebarTab === 'issues' }" @click="sidebarTab = 'issues'">
                Issues
                <span class="tab-badge" x-show="issues.filter(i => i.status === 'open' || i.status === 'in_progress').length > 0" 
                      x-text="issues.filter(i => i.status === 'open' || i.status === 'in_progress').length"></span>
            </div>
        </div>

        <div class="sidebar__content">
            <!-- Collaboration Tab -->
            <div x-show="sidebarTab === 'collab'">
                <!-- Invite Members - Admin/Editor Only -->
                <div class="sidebar-section" x-show="userRole !== 'viewer'">
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

                <!-- Share Link - Admin/Editor Only -->
                <div class="sidebar-section" x-show="userRole !== 'viewer'">
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

                <!-- View Only Message for Viewers -->
                <div class="sidebar-section" x-show="!userPermissions.can_manage_members && userRole === 'viewer'">
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-center gap-2 text-yellow-700 mb-2">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                            <span class="font-semibold text-sm">View Only Access</span>
                        </div>
                        <p class="text-xs text-yellow-600">
                            You can view this build and participate in chat, but cannot edit or invite members. Contact the owner for edit access.
                        </p>
                    </div>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section__title">
                        <i data-lucide="users"></i> Active Members
                    </div>
                    <div class="member-list space-y-2">
                        <template x-for="member in members" :key="member.id">
                            <div class="member-item group" x-data="{ isOpen: false }" @click.away="isOpen = false">
                                <div class="member-info">
                                    <div class="avatar avatar--sm" style="background: linear-gradient(135deg, var(--accent) 0%, #818CF8 100%); width: 32px; height: 32px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: 700; text-transform: uppercase;" x-text="member.name.substring(0, 1)"></div>
                                    <div class="flex-1 min-w-0">
                                        <div class="member-name truncate font-semibold text-sm" x-text="member.name"></div>
                                        <div class="member-role text-[10px] uppercase tracking-wider font-bold text-slate-400 group-hover:text-accent transition-colors" x-text="member.role"></div>
                                    </div>
                                </div>
                                
                                <!-- Role Management Buttons - Admin Only -->
                                <div class="flex items-center gap-1.5" x-show="userPermissions.can_manage_members && String(member.id) !== '{{ $auth_user_id }}'">
                                    <!-- Toggle Role Button (Camouflaged) -->
                                    <button @click="toggleRole(member)" 
                                            class="w-8 h-8 flex items-center justify-center rounded-xl transition-all duration-200 border-none outline-none group/role hover:bg-accent/10 text-accent"
                                            style="background: transparent !important;"
                                            :title="member.role === 'editor' ? 'Set as Viewer' : 'Set as Editor'">
                                        <!-- Eye Icon (Viewer mode) -->
                                        <template x-if="member.role === 'editor'">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition-transform group-hover/role:scale-110"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </template>
                                        <!-- Edit/Pencil Icon (Editor mode) -->
                                        <template x-if="member.role !== 'editor'">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition-transform group-hover/role:scale-110"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                        </template>
                                    </button>

                                    <!-- Remove Member Button (Camouflaged) -->
                                    <button @click="removeMember(member.id)" 
                                            class="w-8 h-8 flex items-center justify-center rounded-xl transition-all duration-200 border-none outline-none group/rem hover:bg-red-500/10 text-red-500"
                                            style="background: transparent !important;"
                                            title="Remove Member">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition-transform group-hover/rem:scale-110"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Chat Tab -->
            <div x-show="sidebarTab === 'chat'" class="h-full flex flex-col">
                <div class="sidebar-section__title mb-4">
                    <i data-lucide="message-square"></i> Project Chat
                    <span x-show="rtStatus !== 'connected'" class="ml-2 text-xs text-yellow-500" title="Realtime offline - messages will still save but won't be live">
                        <i data-lucide="wifi-off" class="w-3 h-3 inline"></i> Offline
                    </span>
                </div>
                
                <!-- Offline Notice -->
                <div x-show="rtStatus !== 'connected' && rtStatus !== 'connecting'" 
                     class="chat-offline-notice">
                    <i data-lucide="wifi-off"></i>
                    <span>Working offline - messages will save locally</span>
                </div>
                
                <div class="chat-messages" id="chat-messages">
                    <!-- Empty State -->
                    <div x-show="chatMessages.length === 0" class="chat-empty-state">
                        <div class="chat-empty-state__icon">
                            <i data-lucide="message-circle"></i>
                        </div>
                        <p class="chat-empty-state__title">No messages yet</p>
                        <p class="chat-empty-state__subtitle" x-show="rtStatus === 'connected'">Be the first to start the conversation!</p>
                        <p class="chat-empty-state__subtitle" x-show="rtStatus !== 'connected'">Messages will appear here when you send them.</p>
                    </div>
                    
                    <template x-for="msg in chatMessages" :key="msg.id || msg.temp_id">
                        <div class="message-row">
                            <div class="message"
                                 :class="msg.user_id === '{{ $auth_user_id }}' ? 'message--mine' : 'message--other'">
                                <div class="message__header" x-show="msg.user_id !== '{{ $auth_user_id }}'">
                                    <span class="message__user" x-text="msg.user?.name || msg.user_name || 'Collaborator'"></span>
                                </div>
                                <div class="message__content" x-text="msg.message || msg.content || msg.text || ''"></div>
                                <div class="message__time" x-show="msg.created_at" x-text="formatTime(msg.created_at)"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Issues Tab -->
            <div x-show="sidebarTab === 'issues'" class="h-full flex flex-col">
                <div class="sidebar-section__title mb-2">
                    <i data-lucide="alert-circle"></i> Build Issues
                    <span class="text-xs text-slate-400 ml-2" x-text="issues.length + ' total'"></span>
                </div>

                <!-- Filter Buttons -->
                <div class="flex gap-2 mb-3">
                    <button class="filter-btn text-xs" :class="{ active: issueFilter === 'all' }" @click="issueFilter = 'all'">
                        All
                    </button>
                    <button class="filter-btn text-xs" :class="{ active: issueFilter === 'open' }" @click="issueFilter = 'open'">
                        Open
                    </button>
                    <button class="filter-btn text-xs" :class="{ active: issueFilter === 'resolved' }" @click="issueFilter = 'resolved'">
                        Resolved
                    </button>
                </div>

                <!-- Issues List -->
                <div class="flex-1 overflow-y-auto space-y-2 pr-1">
                    <template x-for="issue in filteredIssues" :key="issue.id">
                        <div class="issue-card" :class="{ 'issue-card--selected': selectedIssue?.id === issue.id }" @click="selectIssue(issue)">
                            <div class="issue-card-header">
                                <div class="issue-status-dot" :style="{ background: issue.status_color }"></div>
                                <div class="issue-title" x-text="issue.title"></div>
                            </div>
                            <div class="issue-card-body">
                                <div class="issue-meta">
                                    <span class="issue-priority-badge" :style="{ background: issue.priority_color + '15', color: issue.priority_color, borderColor: issue.priority_color + '40' }">
                                        <span x-text="issue.priority_label"></span>
                                    </span>
                                    <span class="issue-creator" x-text="issue.creator_name"></span>
                                </div>
                                <div x-show="issue.part_id" class="issue-attachment">
                                    <i data-lucide="box"></i> Attached to part
                                </div>
                            </div>
                            <div class="issue-card-actions" x-show="selectedIssue?.id === issue.id">
                                <button class="btn btn--secondary btn--xs" @click.stop="updateIssueStatus(issue.id, 'in_progress')" x-show="issue.status === 'open'">
                                    Start
                                </button>
                                <button class="btn btn--success btn--xs" @click.stop="updateIssueStatus(issue.id, 'resolved')" x-show="issue.status !== 'resolved' && issue.status !== 'closed'">
                                    Resolve
                                </button>
                                <button class="btn btn--ghost btn--xs text-red-500" @click.stop="deleteIssue(issue.id)">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Empty State -->
                    <div x-show="filteredIssues.length === 0" class="empty-state">
                        <i data-lucide="check-circle" class="w-12 h-12 text-slate-400 mb-2"></i>
                        <p class="text-sm text-slate-400">No issues found</p>
                        <p class="text-xs text-slate-500">Right-click any part to create an issue</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="sidebar__footer" x-show="sidebarTab === 'chat'">
            <div class="chat-input-wrapper">
                <input type="text" class="chat-input" 
                       placeholder="Type a message..." 
                       x-model="newMessage" @keyup.enter="sendMessage()">
                <button class="chat-send-btn" @click="sendMessage()" :disabled="!newMessage.trim()">
                    <i data-lucide="send"></i>
                </button>
            </div>
        </div>

        <!-- Issues Footer -->
        <div class="sidebar__footer" x-show="sidebarTab === 'issues'">
            <button class="btn btn--primary w-full" @click="openIssueModal()" :disabled="!selectedPartId">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span x-text="selectedPartId ? 'Create Issue for Selected Part' : 'Select a Part First'"></span>
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
                <button class="floating-tool-btn" :class="{ active: currentTool === 'move', 'opacity-50 cursor-not-allowed': !userPermissions.can_edit_geometry }" 
                        @click="userPermissions.can_edit_geometry && setTool('move')" 
                        :disabled="!userPermissions.can_edit_geometry"
                        title="Move Tool (T)">
                    <i data-lucide="move" class="w-5 h-5"></i>
                    <span class="tooltip" x-text="userPermissions.can_edit_geometry ? 'Move' : 'Move (No Permission)'"></span>
                </button>
                <button class="floating-tool-btn" :class="{ active: currentTool === 'clone', 'opacity-50 cursor-not-allowed': !userPermissions.can_edit_geometry }" 
                        @click="userPermissions.can_edit_geometry && setTool('clone')" 
                        :disabled="!userPermissions.can_edit_geometry"
                        title="Clone Tool (C)">
                    <i data-lucide="copy" class="w-5 h-5"></i>
                    <span class="tooltip" x-text="userPermissions.can_edit_geometry ? 'Clone' : 'Clone (No Permission)'"></span>
                </button>
                <button class="floating-tool-btn" :class="{ active: currentTool === 'delete', 'opacity-50 cursor-not-allowed': !userPermissions.can_delete_parts }" 
                        @click="userPermissions.can_delete_parts && setTool('delete')" 
                        :disabled="!userPermissions.can_delete_parts"
                        title="Delete Tool (G)">
                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                    <span class="tooltip" x-text="userPermissions.can_delete_parts ? 'Delete' : 'Delete (No Permission)'"></span>
                </button>
            </div>

            <div class="floating-toolbar__divider"></div>

            <div class="floating-toolbar__group">
                <button class="floating-tool-btn" :class="{ 'btn--primary': paintModeActive, 'opacity-50 cursor-not-allowed': !userPermissions.can_edit_geometry }" 
                        @click="userPermissions.can_edit_geometry && togglePaintMode()" 
                        :disabled="!userPermissions.can_edit_geometry"
                        title="Paint Mode">
                    <i data-lucide="paintbrush" class="w-5 h-5"></i>
                    <span class="tooltip" x-text="userPermissions.can_edit_geometry ? 'Paint' : 'Paint (No Permission)'"></span>
                </button>
                <button class="floating-tool-btn" :class="{ 'btn--primary': materialModeActive, 'opacity-50 cursor-not-allowed': !userPermissions.can_edit_geometry }" 
                        @click="userPermissions.can_edit_geometry && toggleMaterialMode()" 
                        :disabled="!userPermissions.can_edit_geometry"
                        title="Material Mode">
                    <i data-lucide="layers" class="w-5 h-5"></i>
                    <span class="tooltip" x-text="userPermissions.can_edit_geometry ? 'Material' : 'Material (No Permission)'"></span>
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
                            :class="{ active: selectedPresetId === '{{ $preset['id'] ?? 0 }}', 'opacity-50 cursor-not-allowed': !userPermissions.can_edit_geometry }"
                            @click="userPermissions.can_edit_geometry && selectPreset({{ json_encode([
                                'id' => isset($preset['id']) ? $preset['id'] : '',
                                'name' => isset($preset['name']) ? $preset['name'] : '',
                                'type' => isset($preset['type']) ? $preset['type'] : '',
                                'variant' => isset($preset['variant']) ? $preset['variant'] : '',
                                'default_width' => isset($preset['default_width']) ? $preset['default_width'] : 1,
                                'default_height' => isset($preset['default_height']) ? $preset['default_height'] : 3,
                                'default_depth' => isset($preset['default_depth']) ? $preset['default_depth'] : 0.2,
                                'default_color' => isset($preset['default_color']) ? $preset['default_color'] : '',
                                'icon' => isset($preset['icon']) ? $preset['icon'] : '',
                            ]) }})"
                            :disabled="!userPermissions.can_edit_geometry">
                        <div class="part-card__icon">
                            <i data-lucide="{{ $preset['icon'] ?? 'box' }}"></i>
                        </div>
                        <span class="part-card__name">{{ $preset['name'] ?? 'Unknown' }}</span>
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

    <!-- Issue Creation Modal -->
    <div class="modal-overlay" x-show="showIssueModal" x-transition @click.self="showIssueModal = false">
        <div class="modal-content issue-modal">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    Create New Issue
                </h3>
                <button class="btn btn--ghost btn--sm" @click="showIssueModal = false">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Title <span class="text-red-500">*</span></label>
                    <input type="text" class="form-input" x-model="newIssue.title" placeholder="e.g., Wall needs to be moved 2ft left">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea class="form-textarea" x-model="newIssue.description" rows="3" placeholder="Describe the issue in detail..."></textarea>
                </div>
                <div class="form-group">
                    <label>Priority</label>
                    <div class="priority-selector">
                        <button class="priority-btn" :class="{ active: newIssue.priority === 'low' }" @click="newIssue.priority = 'low'" style="--color: #22c55e;">
                            <span class="priority-dot" style="background: #22c55e;"></span>
                            Low
                        </button>
                        <button class="priority-btn" :class="{ active: newIssue.priority === 'medium' }" @click="newIssue.priority = 'medium'" style="--color: #eab308;">
                            <span class="priority-dot" style="background: #eab308;"></span>
                            Medium
                        </button>
                        <button class="priority-btn" :class="{ active: newIssue.priority === 'high' }" @click="newIssue.priority = 'high'" style="--color: #f97316;">
                            <span class="priority-dot" style="background: #f97316;"></span>
                            High
                        </button>
                        <button class="priority-btn" :class="{ active: newIssue.priority === 'critical' }" @click="newIssue.priority = 'critical'" style="--color: #dc2626;">
                            <span class="priority-dot" style="background: #dc2626;"></span>
                            Critical
                        </button>
                    </div>
                </div>
                <div class="form-group" x-show="selectedPartId">
                    <label>Attached to Part</label>
                    <div class="selected-part-info">
                        <i data-lucide="box" class="w-4 h-4"></i>
                        <span x-text="selectedPartId ? 'Part ID: ' + selectedPartId.substring(0, 8) + '...' : 'No part selected'"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn--secondary" @click="showIssueModal = false">Cancel</button>
                <button class="btn btn--primary" @click="createIssue()" :disabled="!newIssue.title.trim()">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Create Issue
                </button>
            </div>
        </div>
    </div>
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
        userPermissions: @json($userPermissions),
        shareUrl: '',
        chatMessages: @json($messages),
        issues: @json($issues),
        selectedIssue: null,
        issueFilter: 'all', // all, open, resolved
        showIssueModal: false,
        newIssue: {
            title: '',
            description: '',
            priority: 'medium',
            part_id: null
        },
        initialMessagesCount: {{ count($messages) }},
        newMessage: '',
        supabase: null,
        rtChannel: null,
        rtStatus: 'connecting', // connecting, connected, error, closed
        tabId: Math.random().toString(36).substr(2, 9),
        pendingSyncEvents: [], // Queue for sync events before editor ready
        
        // Connection resilience
        reconnectAttempts: 0,
        maxReconnectAttempts: 10,
        reconnectDelay: 1000,
        reconnectTimer: null,
        isReconnecting: false,
        activeChannelId: null, // Track which channel is currently active
        
        toolIcons: { select: 'mouse-pointer', delete: 'trash-2', move: 'move', clone: 'copy' },
        toolLabels: { select: 'Select Tool', delete: 'Delete Tool — Click to remove', move: 'Move Tool — Click to pick up', clone: 'Clone Tool — Click to duplicate' },

        async init() {
            // Debug: Log issues data
            console.log('[Issues] Loaded from server:', this.issues.length, 'issues');
            console.log('[Issues] Data:', JSON.parse(JSON.stringify(this.issues)));
            
            // Initialize Supabase for Realtime
            this.supabase = supabase.createClient(
                '{{ config('supabase.url') }}',
                '{{ config('supabase.anon_key') }}',
                {
                    realtime: {
                        timeout: 20000,
                        params: {
                            eventsPerSecond: 10
                        }
                    }
                }
            );

            console.log('Supabase client initialized:', this.supabase ? 'OK' : 'FAILED');
            
            // Test REST API connectivity first
            try {
                const { data, error } = await this.supabase.from('builds').select('id').limit(1);
                if (error) {
                    console.error('Supabase REST API test failed:', error);
                    console.error('%c[DIAGNOSTIC] Cannot connect to Supabase REST API. Check:', 'color: #ff6b6b; font-weight: bold;');
                    console.error('  - SUPABASE_URL in .env is correct');
                    console.error('  - SUPABASE_ANON_KEY in .env is correct');
                    console.error('  - Your project is not paused');
                    console.error('  - Network connectivity to Supabase');
                } else {
                    console.log('Supabase REST API test: OK (connected to database)');
                }
            } catch (testErr) {
                console.error('Supabase REST API test error:', testErr);
            }

            // Room-based channel for absolute real-time
            const channelId = 'main_' + Date.now();
            this.activeChannelId = channelId;
            
            this.rtChannel = this.supabase.channel('build:{{ $build->id }}', {
                config: {
                    broadcast: { self: false }, // Don't receive own broadcasts
                    presence: { key: '{{ $auth_user_id }}' + '_' + this.tabId }
                }
            });
            
            console.log('RT Created new channel with ID:', channelId);

            this.rtChannel
                .on('presence', { event: 'sync' }, () => {
                    const state = this.rtChannel.presenceState();
                    if (window.editor) window.editor.updateRemoteCursors(state);
                })
                .on('broadcast', { event: 'chat' }, (payload) => {
                    console.log('RT Received Chat Envelope:', JSON.stringify(payload, null, 2));

                    // Supabase sends: payload.payload = { data: { message, user, user_id, ... } }
                    // Try multiple possible payload structures
                    let msg = payload.payload?.data || payload.payload || payload.data || payload;
                    
                    console.log('RT Extracted chat message:', msg);
                    
                    if (msg && (msg.message || msg.content || msg.text)) {
                        // Ensure message has a unique key for Alpine
                        const messageWithId = {
                            ...msg,
                            temp_id: msg.id || msg.temp_id || `rt_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
                            created_at: msg.created_at || new Date().toISOString()
                        };
                        
                        // Avoid duplicates
                        const exists = this.chatMessages.some(m => 
                            (m.id && m.id === messageWithId.id) || 
                            (m.temp_id && m.temp_id === messageWithId.temp_id)
                        );
                        
                        if (!exists) {
                            this.chatMessages.push(messageWithId);
                            this.scrollToBottom();
                            console.log('RT Chat message added:', messageWithId);
                        } else {
                            console.log('RT Chat: Duplicate message ignored');
                        }
                    } else {
                        console.warn('RT Chat: Invalid message structure received', { payload, extractedMsg: msg });
                    }
                })
                .on('broadcast', { event: 'sync-part' }, (payload) => {
                    console.log('RT Received Sync-Part Envelope:', JSON.stringify(payload, null, 2));

                    // Supabase sends: payload.payload = { action: 'add', data: {...} }
                    const data = payload.payload;
                    
                    if (!data || !data.action) {
                        console.warn('RT Sync: Invalid payload - missing action', { payload, extractedData: data });
                        return;
                    }

                    // Queue event if editor not ready yet
                    if (!window.editor) {
                        console.log('RT Sync: Editor not ready, queuing event:', data.action, 'Queue size:', this.pendingSyncEvents.length + 1);
                        this.pendingSyncEvents.push(data);
                        
                        // Also try to process queue immediately in case editor just became ready
                        this.$nextTick(() => {
                            if (window.editor && this.pendingSyncEvents.length > 0) {
                                console.log('RT Editor now ready, processing', this.pendingSyncEvents.length, 'queued events');
                                const events = [...this.pendingSyncEvents];
                                this.pendingSyncEvents = [];
                                events.forEach(evt => this.processSyncEvent(evt));
                            }
                        });
                        return;
                    }

                    this.processSyncEvent(data);
                })
                .subscribe(async (status, err) => {
                    // Check if this channel is still the active one
                    if (channelId !== this.activeChannelId) {
                        console.log(`RT Ignoring event from old channel ${channelId}, current is ${this.activeChannelId}`);
                        return;
                    }
                    
                    console.log('RT Subscription Status:', status, err ? 'Error:' + err.message : '');
                    
                    if (err) {
                        console.error('RT Subscription Error:', err);
                        
                        // Specific diagnostic for common errors
                        if (err.message && err.message.includes('UnableToConnectToProject')) {
                            console.error('%c[DIAGNOSTIC] Supabase Realtime cannot connect to your project database.', 'color: #ff6b6b; font-weight: bold;');
                            console.error('%c[DIAGNOSTIC] Solutions to try:', 'color: #ff6b6b;');
                            console.error('  1. Check if your Supabase project is active (not paused)');
                            console.error('  2. Go to Database → Replication and ensure Realtime is enabled');
                            console.error('  3. Try re-enabling Realtime: Database → Replication → Toggle Realtime OFF then ON');
                            console.error('  4. Check your .env SUPABASE_URL and SUPABASE_ANON_KEY are correct');
                            console.error('  5. Your project might need to be restarted - contact Supabase support if issue persists');
                        }
                    }
                    
                    this.rtStatus = status === 'SUBSCRIBED' ? 'connected' : (status === 'CLOSED' ? 'closed' : 'error');
                    
                    if (status === 'SUBSCRIBED') {
                        // Reset reconnection attempts on successful connection
                        this.reconnectAttempts = 0;
                        this.isReconnecting = false;
                        
                        // Immediately track presence
                        try {
                            await this.rtChannel.track({
                                online_at: new Date().toISOString(),
                                name: '{{ $auth_user_name }}',
                                role: this.userRole,
                                tabId: this.tabId
                            });
                            console.log('RT Connected and presence tracked');
                        } catch (trackErr) {
                            console.error('RT Presence tracking failed:', trackErr);
                        }
                        
                        // Sync any parts that may have been added while page was loading
                        // Small delay to let editor finish initializing
                        setTimeout(() => this.syncMissedParts(), 500);
                    } else if (status === 'CLOSED' || status === 'CHANNEL_ERROR' || status === 'TIMED_OUT') {
                        console.warn('RT Connection lost (status: ' + status + ')');
                        // Only trigger reconnection if not already reconnecting
                        if (!this.isReconnecting && this.reconnectAttempts < this.maxReconnectAttempts) {
                            console.log('RT Scheduling reconnection...');
                            // Add delay before reconnection on localhost to prevent rapid reconnection loops
                            const delay = this.reconnectAttempts === 0 ? 2000 : 1000;
                            setTimeout(() => this.handleReconnection(), delay);
                        } else if (this.isReconnecting) {
                            console.log('RT Reconnection already in progress, skipping...');
                        }
                    }
                });

            // Link channel and role to editor
            this.$nextTick(() => {
                const self = this;
                console.log('RT Starting editor check interval, editor exists:', !!window.editor);
                const checkEditor = setInterval(() => {
                    if (window.editor) {
                        console.log('RT Editor found! Linking channel and processing', self.pendingSyncEvents.length, 'queued events');
                        window.editor.rtChannel = self.rtChannel;
                        window.editor.userRole = self.userRole;
                        window.editor.myPresenceKey = '{{ $auth_user_id }}' + '_' + self.tabId;
                        clearInterval(checkEditor);
                        
                        // Initialize issue pins
                        if (self.issues && self.issues.length > 0) {
                            console.log('[Editor] Loading', self.issues.length, 'issue pins');
                            window.editor.loadIssuePins(self.issues);
                        }
                        
                        // Process any queued sync events
                        const queueLength = self.pendingSyncEvents.length;
                        if (queueLength > 0) {
                            console.log('RT Processing', queueLength, 'queued sync events');
                            const eventsToProcess = [...self.pendingSyncEvents];
                            self.pendingSyncEvents = [];
                            eventsToProcess.forEach(data => {
                                try {
                                    self.processSyncEvent(data);
                                } catch (err) {
                                    console.error('RT Error processing queued event:', err, data);
                                }
                            });
                        }
                    }
                }, 100);
            });

            // Notify editor about role change
            this.$watch('userRole', (val) => {
                if (window.editor) window.editor.userRole = val;
            });

            window.addEventListener('part-placed', async (e) => {
                // Broadcast to others if we placed it locally
                if (e.detail.isLocal) {
                    console.log('RT Sending Sync-Part (add)');
                    const result = await this.sendBroadcast('sync-part', {
                        action: 'add',
                        data: e.detail.partData
                    });
                    if (result.success) {
                        console.log('RT Sync-Part (add) broadcast sent via', result.method);
                    } else {
                        console.error('RT Failed to send sync-part broadcast:', result.error);
                    }
                }
                document.getElementById('parts-count').textContent = e.detail.count;
            });

            window.addEventListener('part-deleted', async (e) => {
                if (e.detail.isLocal) {
                    console.log('RT Sending Sync-Part (delete)');
                    const result = await this.sendBroadcast('sync-part', {
                        action: 'delete',
                        id: e.detail.id
                    });
                    if (result.success) {
                        console.log('RT Sync-Part (delete) broadcast sent via', result.method);
                    } else {
                        console.error('RT Failed to send sync-part broadcast:', result.error);
                    }
                }
            });

            window.addEventListener('part-updated', async (e) => {
                if (e.detail.isLocal) {
                    console.log('RT Sending Sync-Part (update)');
                    const result = await this.sendBroadcast('sync-part', {
                        action: 'update',
                        id: e.detail.id,
                        data: e.detail.data
                    });
                    if (result.success) {
                        console.log('RT Sync-Part (update) broadcast sent via', result.method);
                    } else {
                        console.error('RT Failed to send sync-part broadcast:', result.error);
                    }
                }
            });

            window.addEventListener('floor-changed', (e) => {
                this.currentFloor = e.detail.floor;
                document.getElementById('current-floor').textContent = e.detail.floor;
            });

            // Fetch latest messages from API to get any messages sent while away
            console.log(`Init: ${this.initialMessagesCount} messages loaded from server, fetching from API...`);
            this.fetchMessages();
            
            // Poll for new messages every 30 seconds (fallback when realtime misses messages)
            this.messagePollInterval = setInterval(() => {
                this.fetchMessages();
            }, 30000);
            
            // Also fetch when user returns to the tab
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') {
                    this.fetchMessages();
                }
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

            // Right-click on part to create issue
            window.addEventListener('open-issue-modal', (e) => {
                if (e.detail && e.detail.partId) {
                    this.newIssue.part_id = e.detail.partId;
                    this.sidebarOpen = true;
                    this.sidebarTab = 'issues';
                    this.showIssueModal = true;
                }
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
            
            // Cleanup on page unload
            window.addEventListener('beforeunload', () => {
                if (this.reconnectTimer) {
                    clearTimeout(this.reconnectTimer);
                }
                if (this.messagePollInterval) {
                    clearInterval(this.messagePollInterval);
                }
                if (this.rtChannel) {
                    this.rtChannel.unsubscribe();
                }
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

        async toggleRole(member) {
            const newRole = member.role === 'editor' ? 'viewer' : 'editor';
            try {
                const res = await fetch(`/builds/{{ $build->id }}/members/${member.id}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ role: newRole })
                });
                if (res.ok) {
                    member.role = newRole;
                    this.showToast(`Role updated to ${newRole}`);
                }
            } catch (err) {
                this.showToast('Failed to update role', 'error');
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
                const res = await fetch(`/editor/builds/{{ $build->id }}/messages`);
                if (!res.ok) {
                    console.error('Failed to fetch messages, status:', res.status);
                    return;
                }
                const messages = await res.json();
                
                // Ensure messages have required fields
                const processedMessages = messages.map(msg => ({
                    ...msg,
                    message: msg.message || msg.content || '',
                    user: msg.user || { name: msg.user_name || 'Unknown' }
                }));
                
                // Merge with existing messages, avoiding duplicates by server id first
                const existingServerIds = new Set(this.chatMessages.filter(m => m.id).map(m => m.id));
                const newMessages = processedMessages.filter(m => !existingServerIds.has(m.id));
                
                console.log(`Fetched ${messages.length} messages from server, ${newMessages.length} new, ${existingServerIds.size} existing`);
                
                if (newMessages.length > 0) {
                    this.chatMessages = [...this.chatMessages, ...newMessages].sort((a, b) => 
                        new Date(a.created_at || 0) - new Date(b.created_at || 0)
                    );
                    this.scrollToBottom();
                }
            } catch (err) {
                console.error('Failed to fetch messages', err);
            }
        },

        async sendMessage() {
            if (!this.newMessage.trim()) return;
            const messageText = this.newMessage;
            this.newMessage = '';

            try {
                const res = await fetch(`/editor/builds/{{ $build->id }}/messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ message: messageText })
                });
                
                if (!res.ok) {
                    const errorData = await res.json();
                    console.error('Server error saving message:', res.status, errorData);
                    this.newMessage = messageText; // Restore for retry
                    this.showToast('Failed to save message: ' + (errorData.error || 'Server error'), 'error');
                    return;
                }
                
                const data = await res.json();
                console.log('Message saved successfully:', data);
                
                if (!data.id) {
                    console.error('CRITICAL: Message saved but no ID returned! Data:', data);
                    this.showToast('Message may not have saved properly', 'warning');
                }

                // Ensure message text and user_id are included (server might not return them)
                const messageData = {
                    ...data,
                    message: data.message || data.content || messageText,
                    user_id: data.user_id || '{{ $auth_user_id }}',
                    user: data.user || { name: '{{ $auth_user_name }}' },
                    temp_id: data.id || `temp_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
                    created_at: data.created_at || new Date().toISOString()
                };

                // Broadcast instantly using payload key
                console.log('RT Sending Chat broadcast:', messageData);
                const result = await this.sendBroadcast('chat', { data: messageData });
                console.log('RT Chat broadcast result:', result);
                if (result && result.success) {
                    console.log('RT Chat broadcast sent via', result.method);
                } else {
                    console.error('RT Failed to send chat broadcast:', result?.error || 'No result returned');
                }

                // Only add if not already in list (prevent duplicates from realtime)
                const exists = this.chatMessages.some(m => m.id === messageData.id);
                if (!exists) {
                    this.chatMessages.push(messageData);
                    this.scrollToBottom();
                } else {
                    console.log('Message already exists, skipping duplicate');
                }
            } catch (err) {
                console.error('Failed to send message:', err);
                // Restore message so user can retry
                this.newMessage = messageText;
                this.showToast('Failed to send message. Check connection and try again.', 'error');
            }
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = document.getElementById('chat-messages');
                // With column-reverse, newest messages are at visual bottom (scrollTop: 0)
                if (el) el.scrollTop = 0;
            });
        },

        formatTime(timestamp) {
            if (!timestamp) return '';
            const date = new Date(timestamp);
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },

        processSyncEvent(data) {
            try {
                console.log('RT Processing action:', data.action, 'Data:', data);

                if (data.action === 'add' && data.data) {
                    console.log('RT Rendering Remote Part:', data.data);
                    window.editor.addPartToScene(data.data, false);
                } else if (data.action === 'delete' && data.id) {
                    console.log('RT Deleting Remote Part ID:', data.id);
                    window.editor.deletePartFromRealtime(data.id);
                } else if (data.action === 'update' && data.id && data.data) {
                    console.log('RT Updating Remote Part ID:', data.id);
                    window.editor.updatePartInRealtime(data.id, data.data);
                } else {
                    console.warn('RT Sync: Unknown action or missing data fields', data);
                }
            } catch (err) {
                console.error('RT Sync Error:', err);
            }
        },

        async syncMissedParts() {
            if (!window.editor) {
                console.warn('RT Cannot sync parts - editor not ready');
                return;
            }
            
            console.log('RT Syncing missed parts from database...');
            
            try {
                const response = await fetch(`/editor/builds/{{ $build->id }}/parts`, {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const parts = await response.json();
                let addedCount = 0;
                let skippedCount = 0;
                
                // Get existing part IDs from editor's parts Map
                const existingIds = Array.from(window.editor.parts?.keys() || []);
                
                parts.forEach(partData => {
                    // Check if part already exists (by UUID from database)
                    const exists = existingIds.some(id => id.includes(partData.id) || id === partData.id);
                    
                    if (!exists) {
                        console.log('RT Adding missed part:', partData.id);
                        window.editor.addPartToScene(partData, false);
                        addedCount++;
                    } else {
                        skippedCount++;
                    }
                });
                
                if (addedCount > 0) {
                    console.log(`RT Synced ${addedCount} missed parts from database (${skippedCount} already existed)`);
                    this.showToast(`Synced ${addedCount} parts that were added while you were offline`, 'success');
                } else {
                    console.log(`RT No missed parts to sync - all ${skippedCount} parts already up to date`);
                }
            } catch (error) {
                console.error('RT Error syncing missed parts:', error);
                this.showToast('Could not sync missed parts', 'warning');
            }
        },

        handleReconnection() {
            if (this.isReconnecting || this.reconnectAttempts >= this.maxReconnectAttempts) {
                if (this.reconnectAttempts >= this.maxReconnectAttempts) {
                    console.error('RT Max reconnection attempts reached. Please refresh the page.');
                    this.showToast('Connection lost. Please refresh the page.', 'error');
                }
                return;
            }

            this.isReconnecting = true;
            this.reconnectAttempts++;
            
            // Exponential backoff: 3s, 6s, 12s, 24s, ... up to 60s max (slower for localhost stability)
            const delay = Math.min(this.reconnectDelay * 3 * Math.pow(2, this.reconnectAttempts - 1), 60000);
            
            console.log(`RT Reconnection attempt ${this.reconnectAttempts}/${this.maxReconnectAttempts} in ${delay}ms`);
            this.showToast(`Reconnecting... (attempt ${this.reconnectAttempts})`, 'warning');

            this.reconnectTimer = setTimeout(() => {
                console.log('RT Attempting to recreate channel...');
                
                // Generate new channel ID for this reconnection attempt
                const reconnectionChannelId = 'reconn_' + Date.now() + '_' + this.reconnectAttempts;
                const previousChannelId = this.activeChannelId;
                this.activeChannelId = reconnectionChannelId;
                console.log(`RT Switching from channel ${previousChannelId} to ${reconnectionChannelId}`);
                
                // Unsubscribe from old channel if exists
                if (this.rtChannel) {
                    this.rtChannel.unsubscribe();
                }
                
                // Create new channel
                this.rtChannel = this.supabase.channel('build:{{ $build->id }}', {
                    config: {
                        broadcast: { self: false },
                        presence: { key: '{{ $auth_user_id }}' + '_' + this.tabId }
                    }
                });

                // Re-attach all listeners
                this.rtChannel
                    .on('presence', { event: 'sync' }, () => {
                        const state = this.rtChannel.presenceState();
                        if (window.editor) window.editor.updateRemoteCursors(state);
                    })
                    .on('broadcast', { event: 'chat' }, (payload) => {
                        console.log('RT Received Chat Envelope:', JSON.stringify(payload, null, 2));
                        // Try multiple possible payload structures
                        let msg = payload.payload?.data || payload.payload || payload.data || payload;
                        console.log('RT Extracted chat message:', msg);
                        
                        if (msg && (msg.message || msg.content || msg.text)) {
                            const messageWithId = {
                                ...msg,
                                temp_id: msg.id || msg.temp_id || `rt_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
                                created_at: msg.created_at || new Date().toISOString()
                            };
                            
                            const exists = this.chatMessages.some(m => 
                                (m.id && m.id === messageWithId.id) || 
                                (m.temp_id && m.temp_id === messageWithId.temp_id)
                            );
                            
                            if (!exists) {
                                this.chatMessages.push(messageWithId);
                                this.scrollToBottom();
                                console.log('RT Chat message added:', messageWithId);
                            }
                        }
                    })
                    .on('broadcast', { event: 'sync-part' }, (payload) => {
                        console.log('RT Received Sync-Part Envelope:', JSON.stringify(payload, null, 2));
                        const data = payload.payload;
                        
                        if (!data || !data.action) {
                            console.warn('RT Sync: Invalid payload - missing action', { payload, extractedData: data });
                            return;
                        }

                        if (!window.editor) {
                            console.log('RT Sync: Editor not ready, queuing event:', data.action, 'Queue size:', this.pendingSyncEvents.length + 1);
                            this.pendingSyncEvents.push(data);
                            return;
                        }

                        this.processSyncEvent(data);
                    })
                    .subscribe(async (status) => {
                        // Check if this reconnection channel is still the active one
                        if (reconnectionChannelId !== this.activeChannelId) {
                            console.log(`RT Ignoring reconnection event from old channel ${reconnectionChannelId}, current is ${this.activeChannelId}`);
                            return;
                        }
                        
                        console.log('RT Reconnection Status:', status);
                        this.rtStatus = status === 'SUBSCRIBED' ? 'connected' : (status === 'CLOSED' ? 'closed' : 'error');
                        
                        if (status === 'SUBSCRIBED') {
                            this.reconnectAttempts = 0;
                            this.isReconnecting = false;
                            this.showToast('Reconnected successfully!', 'success');
                            
                            await this.rtChannel.track({
                                online_at: new Date().toISOString(),
                                name: '{{ $auth_user_name }}',
                                role: this.userRole,
                                tabId: this.tabId
                            });
                            
                            // Update editor reference
                            if (window.editor) {
                                window.editor.rtChannel = this.rtChannel;
                            }
                            
                            console.log('RT Reconnected and presence tracked');
                            
                            // Sync any parts that were added while we were offline
                            await this.syncMissedParts();
                        } else if (status === 'CLOSED' || status === 'CHANNEL_ERROR') {
                            this.isReconnecting = false;
                            // Only trigger reconnection if this is still the active channel
                            if (reconnectionChannelId === this.activeChannelId && !this.isReconnecting) {
                                this.handleReconnection();
                            }
                        }
                    });
            }, delay);
        },

        async sendBroadcast(event, payload) {
            // Try WebSocket first if connected
            if (this.rtStatus === 'connected') {
                try {
                    await this.rtChannel.send({
                        type: 'broadcast',
                        event: event,
                        payload: payload
                    });
                    return { success: true, method: 'websocket' };
                } catch (err) {
                    console.warn('RT WebSocket broadcast failed, falling back to REST:', err);
                }
            }
            
            // Fallback to REST API (works even when WebSocket is down)
            try {
                await this.rtChannel.send({
                    type: 'broadcast',
                    event: event,
                    payload: payload
                }, { httpSend: true });
                console.log('RT Broadcast sent via REST API');
                return { success: true, method: 'rest' };
            } catch (err) {
                console.error('RT Both WebSocket and REST broadcast failed:', err);
                
                // If connection is down, trigger reconnection
                if (this.rtStatus === 'closed' || this.rtStatus === 'error') {
                    this.handleReconnection();
                }
                
                return { success: false, error: err };
            }
        },

        setFloor(floor) {
            this.currentFloor = floor;
            if (typeof editor !== 'undefined') editor.setFloor(floor);
        },
        
        goUpFloor() {
            if (this.currentFloor < this.floors.length) {
                this.setFloor(this.currentFloor + 1);
            }
        },
        
        goDownFloor() {
            if (this.currentFloor > 1) {
                this.setFloor(this.currentFloor - 1);
            }
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

        // ============ ISSUE METHODS ============
        
        get filteredIssues() {
            if (this.issueFilter === 'all') return this.issues;
            if (this.issueFilter === 'open') return this.issues.filter(i => i.status === 'open' || i.status === 'in_progress');
            if (this.issueFilter === 'resolved') return this.issues.filter(i => i.status === 'resolved' || i.status === 'closed');
            return this.issues;
        },

        get selectedPartId() {
            if (typeof editor !== 'undefined' && editor.selectedPart) {
                return editor.selectedPart;
            }
            return null;
        },

        selectIssue(issue) {
            this.selectedIssue = issue;
            // If issue has 3D position, focus camera on it
            if (issue.position_x !== null && typeof editor !== 'undefined') {
                editor.focusOnPosition(issue.position_x, issue.position_y, issue.position_z);
            }
            // If issue is attached to a part, select that part
            if (issue.part_id && typeof editor !== 'undefined') {
                editor.selectPart(issue.part_id);
            }
        },

        openIssueModal() {
            if (!this.selectedPartId) {
                this.showToast('Please select a part first', 'warning');
                return;
            }
            this.newIssue.part_id = this.selectedPartId;
            this.showIssueModal = true;
        },

        async createIssue() {
            if (!this.newIssue.title.trim()) {
                this.showToast('Title is required', 'error');
                return;
            }

            try {
                const buildId = '{{ $build->id }}';
                
                // Get part position if attached to part
                let positionData = {};
                if (this.newIssue.part_id && typeof editor !== 'undefined') {
                    const partData = editor.parts.get(this.newIssue.part_id);
                    if (partData) {
                        positionData = {
                            position_x: partData.mesh.position.x,
                            position_y: partData.mesh.position.y,
                            position_z: partData.mesh.position.z,
                        };
                    }
                }

                const res = await fetch(`/editor/builds/${buildId}/issues`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        ...this.newIssue,
                        ...positionData,
                    }),
                });

                if (!res.ok) {
                    throw new Error('Failed to create issue');
                }

                const issue = await res.json();
                this.issues.push(issue);
                
                // Add 3D pin to editor
                if (typeof editor !== 'undefined') {
                    editor.addIssuePin(issue);
                }

                this.showIssueModal = false;
                this.newIssue = { title: '', description: '', priority: 'medium', part_id: null };
                this.showToast('Issue created successfully!', 'success');
            } catch (err) {
                console.error('Error creating issue:', err);
                this.showToast('Failed to create issue', 'error');
            }
        },

        async updateIssueStatus(issueId, status) {
            try {
                const buildId = '{{ $build->id }}';
                const res = await fetch(`/editor/builds/${buildId}/issues/${issueId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ status }),
                });

                if (!res.ok) {
                    throw new Error('Failed to update issue status');
                }

                const data = await res.json();
                const issue = this.issues.find(i => i.id === issueId);
                if (issue) {
                    issue.status = data.status;
                    issue.status_color = this.getStatusColor(data.status);
                }

                // Update 3D pin color
                if (typeof editor !== 'undefined') {
                    editor.updateIssuePin(issueId, data.status);
                }

                this.showToast(`Issue marked as ${this.getStatusLabel(data.status)}`, 'success');
            } catch (err) {
                console.error('Error updating issue:', err);
                this.showToast('Failed to update issue status', 'error');
            }
        },

        async deleteIssue(issueId) {
            if (!confirm('Are you sure you want to delete this issue?')) {
                return;
            }

            try {
                const buildId = '{{ $build->id }}';
                const res = await fetch(`/editor/builds/${buildId}/issues/${issueId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                if (!res.ok) {
                    throw new Error('Failed to delete issue');
                }

                this.issues = this.issues.filter(i => i.id !== issueId);
                
                // Remove 3D pin
                if (typeof editor !== 'undefined') {
                    editor.removeIssuePin(issueId);
                }

                this.showToast('Issue deleted', 'success');
            } catch (err) {
                console.error('Error deleting issue:', err);
                this.showToast('Failed to delete issue', 'error');
            }
        },

        getStatusColor(status) {
            const colors = {
                open: '#ef4444',
                in_progress: '#eab308',
                resolved: '#22c55e',
                closed: '#6b7280',
            };
            return colors[status] || '#6b7280';
        },

        getStatusLabel(status) {
            const labels = {
                open: 'Open',
                in_progress: 'In Progress',
                resolved: 'Resolved',
                closed: 'Closed',
            };
            return labels[status] || status;
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

    .material-btn.active {
        background: var(--accent);
        color: white;
        border-color: var(--accent);
    }

    /* Real-time Indicator Styling */
    .rt-indicator {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 4px 8px;
        background: var(--bg-secondary);
        border-radius: 6px;
        margin-left: 8px;
    }

    .rt-indicator__dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #94a3b8; /* Connecting/Initial */
        position: relative;
    }

    .rt-indicator__dot.connecting {
        background: #eab308;
        box-shadow: 0 0 8px #eab308;
        animation: rt-pulse 1.5s infinite;
    }

    .rt-indicator__dot.connected {
        background: #22c55e;
        box-shadow: 0 0 8px #22c55e;
    }

    .rt-indicator__dot.error, .rt-indicator__dot.closed {
        background: #ef4444;
        box-shadow: 0 0 8px #ef4444;
    }

    .rt-indicator__dot.reconnecting {
        background: #f97316;
        box-shadow: 0 0 8px #f97316;
        animation: rt-pulse 1s infinite;
    }

    .rt-indicator__label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
    }

    @keyframes rt-pulse {
        0% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.2); }
        100% { opacity: 1; transform: scale(1); }
    }

    /* View Only Badge */
    .view-only-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        background: rgba(234, 179, 8, 0.1);
        border: 1px solid rgba(234, 179, 8, 0.3);
        border-radius: 6px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #eab308;
        margin-left: 8px;
    }

    .view-only-badge i {
        stroke: #eab308;
    }

    /* Collaboration Sidebar */

    /* Chat Tab Styles */
    .chat-messages {
        display: flex;
        flex-direction: column-reverse;
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        scroll-behavior: smooth;
        min-height: 0;
    }
    
    /* Message Styles */
    .message-row {
        display: flex;
        margin-bottom: 12px;
        width: 100%;
    }
    
    .message {
        max-width: 85%;
        padding: 10px 14px;
        border-radius: 12px;
        font-size: 14px;
        line-height: 1.4;
    }
    
    .message--mine {
        margin-left: auto;
        background: var(--accent);
        color: white;
        border-bottom-right-radius: 4px;
    }
    
    .message--other {
        margin-right: auto;
        background: var(--bg-secondary);
        color: var(--text-primary);
        border-bottom-left-radius: 4px;
    }
    
    .message__header {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 4px;
    }
    
    .message__content {
        word-break: break-word;
    }
    
    .message__time {
        font-size: 11px;
        opacity: 0.7;
        margin-top: 4px;
        text-align: right;
    }

    /* Empty State - Centered */
    .chat-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        text-align: center;
        padding: 32px 16px;
        gap: 12px;
    }

    .chat-empty-state__icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--bg-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 4px;
    }

    .chat-empty-state__icon i {
        width: 28px;
        height: 28px;
        color: var(--text-muted);
    }

    .chat-empty-state__title {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }

    .chat-empty-state__subtitle {
        font-size: 13px;
        color: var(--text-secondary);
        margin: 0;
    }

    /* Chat Input Area */
    .sidebar__footer {
        padding: 12px 16px;
        border-top: 1px solid var(--border);
        background: var(--bg-card);
    }

    .chat-input-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
    }

    .chat-input {
        flex: 1;
        padding: 10px 14px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--bg-primary);
        color: var(--text-primary);
        font-size: 14px;
        line-height: 1.4;
        min-height: 40px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .chat-input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .chat-input::placeholder {
        color: var(--text-muted);
    }

    .chat-send-btn {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: var(--accent);
        color: white;
        border: none;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .chat-send-btn:hover:not(:disabled) {
        background: var(--accent-hover);
        transform: translateY(-1px);
    }

    .chat-send-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .chat-send-btn i {
        width: 18px;
        height: 18px;
    }

    /* Sidebar Footer */
    .sidebar__footer {
        padding: 12px 16px;
        border-top: 1px solid var(--border);
        background: var(--bg-card);
        flex-shrink: 0;
    }

    /* Offline Notice */
    .chat-offline-notice {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 16px;
        margin: 0 16px 12px;
        background: rgba(234, 179, 8, 0.1);
        border: 1px solid rgba(234, 179, 8, 0.3);
        border-radius: 8px;
        font-size: 12px;
        color: #eab308;
        text-align: center;
    }

    .chat-offline-notice i {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }

    .chat-offline-text {
        font-size: 11px;
        color: var(--text-muted);
        text-align: center;
        margin-bottom: 8px;
        display: block;
        width: 100%;
    }

    /* ============ FLOOR SELECTOR ============ */
    .floor-selector {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 4px 8px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 8px;
    }

    .floor-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border: none;
        border-radius: 6px;
        background: transparent;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .floor-btn:hover:not(:disabled) {
        background: var(--bg-secondary);
        color: var(--text-primary);
    }

    .floor-btn:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }

    .floor-btn--arrow {
        background: var(--bg-secondary);
    }

    .floor-btn--arrow:hover:not(:disabled) {
        background: var(--accent);
        color: white;
    }

    .floor-btn--add {
        background: var(--accent);
        color: white;
    }

    .floor-btn--add:hover {
        background: var(--accent-hover);
    }

    .floor-display {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
        min-width: 60px;
        text-align: center;
    }

    .floor-divider {
        width: 1px;
        height: 20px;
        background: var(--border);
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

    /* ============ MEMBER LIST PREMIUM STYLES ============ */
    .member-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 14px;
        background: var(--surface);
        border: 1.5px solid var(--border);
        border-radius: 18px;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        margin-bottom: 8px;
    }

    .member-item:hover {
        background: var(--surface-up);
        border-color: var(--accent);
        transform: translateY(-2px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.06);
    }

    .member-item.group:hover .member-role {
        color: var(--accent);
    }

    .member-info {
        display: flex;
        align-items: center;
        gap: 14px;
        flex: 1;
        min-width: 0;
    }

    .avatar--sm {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border: 2px solid white;
    }

    /* BULLETPROOF DROPDOWN FIXES */
    .member-item button {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
    }

    /* Force horizontal layout for dropdown actions */
    .dropdown-action-btn {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: flex-start !important;
        height: auto !important;
        padding: 8px 12px !important;
        width: 100% !important;
    }

    .action-icon-box {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
    }

    .member-dropdown-menu {
        transform-origin: top right;
        display: flex !important;
        flex-direction: column !important;
        gap: 2px !important;
    }

    /* ============ ISSUE SYSTEM STYLES ============ */
    
    /* Tab Badge */
    .tab-badge {
        background: #ef4444;
        color: white;
        font-size: 10px;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 10px;
        margin-left: 4px;
        min-width: 16px;
        text-align: center;
    }

    /* Filter Buttons */
    .filter-btn {
        padding: 4px 12px;
        border-radius: 6px;
        border: 1px solid var(--border);
        background: var(--bg-card);
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.15s ease;
    }
    
    .filter-btn:hover {
        border-color: var(--accent);
        color: var(--accent);
    }
    
    .filter-btn.active {
        background: var(--accent);
        color: white;
        border-color: var(--accent);
    }

    /* Issue Cards */
    .issue-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }
    
    .issue-card:hover {
        border-color: var(--accent);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.06);
    }
    
    .issue-card--selected {
        border-color: var(--accent);
        background: rgba(59, 130, 246, 0.03);
        box-shadow: 0 0 0 1px var(--accent);
    }

    .issue-card-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }

    .issue-status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .issue-title {
        font-weight: 600;
        font-size: 14.5px;
        color: var(--text-primary);
        line-height: 1.3;
        margin: 0;
    }

    .issue-card-body {
        padding-left: 20px; /* Aligns with the title, skipping the 10px dot + 10px gap */
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .issue-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .issue-priority-badge {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid;
    }

    .issue-creator {
        font-size: 12px;
        font-weight: 500;
        color: var(--text-secondary);
    }

    .issue-attachment {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: var(--text-tertiary);
        margin-top: 2px;
    }

    .issue-attachment i {
        width: 14px;
        height: 14px;
    }

    .issue-card-actions {
        display: flex;
        gap: 8px;
        margin-top: 14px;
        padding-left: 20px;
        width: 100%;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 32px 16px;
    }

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .modal-content {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        width: 100%;
        max-width: 480px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
    }

    .modal-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 18px;
        font-weight: 700;
        margin: 0;
        color: var(--text-primary);
    }

    .modal-body {
        padding: 24px;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 20px 24px;
        border-top: 1px solid var(--border);
        background: var(--bg-secondary);
        border-radius: 0 0 16px 16px;
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--text-secondary);
    }

    .form-input,
    .form-textarea {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: var(--bg-secondary);
        color: var(--text-primary);
        font-size: 14px;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .form-input::placeholder,
    .form-textarea::placeholder {
        color: var(--text-tertiary);
    }

    .form-input:focus,
    .form-textarea:focus {
        outline: none;
        border-color: var(--accent);
        background: var(--surface);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-textarea {
        resize: vertical;
        min-height: 100px;
    }

    /* Priority Selector */
    .priority-selector {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .priority-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: var(--surface);
        color: var(--text-secondary);
        font-weight: 500;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .priority-btn:hover {
        background: var(--bg-secondary);
        color: var(--text-primary);
    }

    .priority-btn.active {
        background: var(--bg-secondary);
        color: var(--text-primary);
        border-color: var(--color);
        box-shadow: 0 0 0 1px var(--color);
    }

    .priority-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    /* Selected Part Info */
    .selected-part-info {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        background: rgba(59, 130, 246, 0.05);
        border: 1px dashed rgba(59, 130, 246, 0.3);
        border-radius: 10px;
        font-size: 13px;
        font-weight: 500;
        color: var(--accent);
    }

    /* Button Sizes */
    .btn--xs {
        padding: 4px 8px;
        font-size: 11px;
    }

    .btn--success {
        background: #22c55e;
        color: white;
    }

    .btn--success:hover {
        background: #16a34a;
    }

    /* ============ END ISSUE SYSTEM STYLES ============ */
</style>
@endpush
