@extends('layouts.editor')
@section('title', $build->name)

@section('content')
<div class="editor-layout" :class="{ 'sidebar-open': sidebarOpen }" x-data="editorApp()">
    <!-- Mobile Overlay Backdrop -->
    <div class="mobile-sidebar-overlay" 
         x-show="sidebarOpen" 
         @click="sidebarOpen = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
    </div>
    
    <!-- Top Bar -->
    <header class="editor-topbar">
        <div class="editor-topbar__left">
            <!-- Explicit Back Button -->
            <a href="{{ route('builds.index') }}" class="btn btn--ghost btn--sm" style="display: flex; align-items: center; gap: 6px; color: var(--text-secondary); text-decoration: none;" title="Back to Builds">
                <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                <span class="hidden-md" style="font-size: 13px; font-weight: 600;">Back</span>
            </a>
            <div class="editor-topbar__divider"></div>
            
            <button class="btn btn--ghost btn--sm" @click="sidebarOpen = !sidebarOpen" title="Toggle Sidebar">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
            <div class="editor-topbar__divider"></div>
            
            <span class="editor-topbar__title">{{ $build->name }}</span>
            
            <!-- Real-time Connection Indicator -->
            <div class="rt-indicator" :title="'Connection: ' + displayStatus">
                <div class="rt-indicator__dot" :class="displayStatus"></div>
                <span class="rt-indicator__label" x-text="displayStatus === 'connected' ? 'Live' : (displayStatus === 'connecting' ? 'Syncing...' : (displayStatus === 'reconnecting' ? 'Reconnecting...' : 'Offline'))"></span>
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
                <div class="nb-shortcut"><kbd>WASD</kbd> <span>Move</span></div>
                <div class="nb-shortcut"><kbd>R</kbd> <span>Rotate</span></div>
                <div class="nb-shortcut"><kbd>G</kbd> <span>Delete</span></div>
                <div class="nb-shortcut"><kbd>T</kbd> <span>Transform</span></div>
                <div class="nb-shortcut"><kbd>SPACE</kbd> <span>View</span></div>
                <div class="nb-shortcut"><kbd>Q</kbd> <span>Cancel</span></div>
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
            
            <!-- Export Dropdown -->
            <div class="export-dropdown" @click.away="exportDropdownOpen = false">
                <button class="btn btn--ghost btn--sm" @click="exportDropdownOpen = !exportDropdownOpen" :class="{ 'active': exportDropdownOpen }" title="Export Build">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Export</span>
                    <i data-lucide="chevron-down" class="w-3 h-3 transition-transform" :class="{ 'rotate-180': exportDropdownOpen }"></i>
                </button>
                
                <div class="dropdown-menu dropdown-menu--right" 
                     x-show="exportDropdownOpen" 
                     x-cloak 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95">
                    <div class="dropdown-header">Export Build</div>
                    <button class="dropdown-item" @click="saveBuild(); setTimeout(() => { if(typeof editor !== 'undefined') editor.exportPNG(); exportDropdownOpen = false; }, 100)">
                        <div class="dropdown-item__icon"><i data-lucide="image"></i></div>
                        <div class="dropdown-item__content">
                            <div class="dropdown-item__title">Export as PNG</div>
                            <div class="dropdown-item__desc">High-quality 3D capture</div>
                        </div>
                    </button>
                    <a href="{{ route('builds.export', ['build' => $build->id, 'format' => 'json']) }}" class="dropdown-item" @click="exportDropdownOpen = false">
                        <div class="dropdown-item__icon"><i data-lucide="file-json"></i></div>
                        <div class="dropdown-item__content">
                            <div class="dropdown-item__title">Export as JSON</div>
                            <div class="dropdown-item__desc">Full geometry data backup</div>
                        </div>
                    </a>
                    <div class="dropdown-divider"></div>
                    <button class="dropdown-item" @click="blueprintModalOpen = true; exportDropdownOpen = false">
                        <div class="dropdown-item__icon" style="color: #3B82F6;"><i data-lucide="file-text"></i></div>
                        <div class="dropdown-item__content">
                            <div class="dropdown-item__title" style="color: var(--text-primary);">Export Blueprint PDF</div>
                            <div class="dropdown-item__desc">Professional floor plan drawing</div>
                        </div>
                    </button>
            </div>

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
                        <i data-lucide="link"></i> Share Invite Link
                    </div>
                    
                    <div x-show="!shareUrl">
                        <button class="btn btn--primary btn--sm w-full" @click="getShareUrl()" style="justify-content: center; gap: 8px;">
                            <i data-lucide="zap" class="w-4 h-4"></i>
                            Generate Invite Link
                        </button>
                        <p class="text-[10px] text-slate-400 mt-2 text-center">Anyone with this link can view and participate.</p>
                    </div>

                    <div x-show="shareUrl" x-cloak class="space-y-2">
                        <div class="flex gap-2">
                            <input type="text" readonly :value="shareUrl" class="chat-input text-xs" @click="$el.select()">
                            <button class="btn btn--secondary btn--sm" @click="copyShareUrl()" title="Copy Link">
                                <i data-lucide="copy" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <div class="flex justify-between items-center px-1">
                            <div class="flex items-center gap-1.5 py-1 px-2.5 rounded-full bg-green-500/10 border border-green-500/20 shadow-sm">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></div>
                                <span class="text-[10px] text-green-600 font-bold uppercase tracking-wider">Active</span>
                            </div>
                            <span @click="shareUrl = ''" style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; cursor: pointer; transition: color 0.2s;" @mouseenter="$el.style.color='#ef4444'" @mouseleave="$el.style.color='#94a3b8'">Hide</span>
                        </div>
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
                                    <div class="avatar avatar--sm" style="width: 32px; height: 32px; border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, var(--accent) 0%, #818CF8 100%); display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
                                        <template x-if="member.avatar_url">
                                            <img :src="member.avatar_url" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!member.avatar_url">
                                            <span class="text-white font-bold text-xs uppercase" x-text="member.name.substring(0, 1)"></span>
                                        </template>
                                    </div>
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
                        <template x-if="members.length === 0">
                            <div class="member-empty-state">
                                <i data-lucide="users-x" class="w-6 h-6"></i>
                                <p>No team members invited yet</p>
                                <small>Add members above to start collaborating</small>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Chat Tab -->
            <div x-show="sidebarTab === 'chat'" class="h-full flex flex-col">
                <div class="sidebar-section__title mb-4">
                    <i data-lucide="message-square"></i> Project Chat
                    <span x-show="rtStatus !== 'connected'" class="ml-2 text-xs text-blue-500" title="Messages save to server and sync when connection is restored">
                        <i data-lucide="wifi-off" class="w-3 h-3 inline"></i> <span x-text="rtStatus === 'error' ? 'Syncing...' : 'Offline'"></span>
                    </span>
                </div>
                
                <!-- Offline Notice - Updated messaging -->
                <div x-show="rtStatus !== 'connected' && rtStatus !== 'connecting'" 
                     class="chat-offline-notice">
                    <i data-lucide="wifi-off"></i>
                    <span x-text="rtStatus === 'error' ? 'Saving messages - will sync when reconnected' : 'Working offline - messages save to server'"></span>
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
                                 :class="String(msg.user_id) === String('{{ $auth_user_id }}') ? 'message--mine' : 'message--other'">
                                <div class="message__header" x-show="String(msg.user_id) !== String('{{ $auth_user_id }}')">
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

            <!-- Grid Controls -->
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

            <div class="floating-toolbar__divider"></div>

            <!-- Night Mode & Scenery -->
            <div class="floating-toolbar__group">
                <button class="floating-tool-btn" 
                        :class="{ 'night-active': isNightMode }"
                        @click="toggleDayNight()" 
                        :title="isNightMode ? 'Switch to Day (B)' : 'Switch to Night (B)'">
                    <!-- Sun icon (day) -->
                    <svg x-show="!isNightMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="4"/>
                        <line x1="12" y1="2" x2="12" y2="6"/>
                        <line x1="12" y1="18" x2="12" y2="22"/>
                        <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/>
                        <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/>
                        <line x1="2" y1="12" x2="6" y2="12"/>
                        <line x1="18" y1="12" x2="22" y2="12"/>
                        <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"/>
                        <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"/>
                    </svg>
                    <!-- Moon icon (night) -->
                    <svg x-show="isNightMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    </svg>
                    <span class="tooltip" x-text="isNightMode ? 'Day Mode' : 'Night Mode'"></span>
                </button>
                <button class="floating-tool-btn scenery-trigger"
                        @click="sceneryPanelOpen = !sceneryPanelOpen"
                        title="Change Scenery">
                    <i data-lucide="image" class="w-5 h-5"></i>
                    <span class="tooltip">Scenery</span>
                </button>
            </div>

            <!-- Scenery Pop-out Panel -->
            <div class="scenery-panel" x-show="sceneryPanelOpen" x-transition @click.outside="sceneryPanelOpen = false">
                <div class="scenery-panel__title">🌍 Choose Scenery</div>
                <button class="scenery-option" :class="{active: currentScenery === 'neighborhood'}" @click="setScenery('neighborhood')">
                    <span class="scenery-icon">🏘️</span>
                    <div>
                        <div class="scenery-name">Modern Neighborhood</div>
                        <div class="scenery-desc">Sidewalks, roads &amp; city trees</div>
                    </div>
                </button>
                <button class="scenery-option" :class="{active: currentScenery === 'nature'}" @click="setScenery('nature')">
                    <span class="scenery-icon">🌿</span>
                    <div>
                        <div class="scenery-name">Nature &amp; Mountains</div>
                        <div class="scenery-desc">Forest backdrop, rolling hills</div>
                    </div>
                </button>
                <button class="scenery-option" :class="{active: currentScenery === 'urban'}" @click="setScenery('urban')">
                    <span class="scenery-icon">🏙️</span>
                    <div>
                        <div class="scenery-name">Urban City</div>
                        <div class="scenery-desc">Dense blocks &amp; city grid</div>
                    </div>
                </button>
                <button class="scenery-option" :class="{active: currentScenery === 'desert'}" @click="setScenery('desert')">
                    <span class="scenery-icon">🏜️</span>
                    <div>
                        <div class="scenery-name">Desert Oasis</div>
                        <div class="scenery-desc">Sand dunes &amp; dry landscape</div>
                    </div>
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
            <div style="margin-bottom: 16px; padding: 12px; background: var(--bg-secondary); border-radius: 10px; display: flex; align-items: center; justify-content: space-between; border: 1px solid var(--border-default);">
                <span style="font-size: 13px; opacity: 0.8; font-weight: 500;">Currently Selected</span>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div :style="`width: 24px; height: 24px; border-radius: 6px; background: ${currentPaintColor}; box-shadow: 0 2px 4px rgba(0,0,0,0.2); border: 2px solid var(--border-default);`"></div>
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
            <div style="margin-bottom: 16px; padding: 12px; background: var(--bg-secondary); border-radius: 10px; display: flex; align-items: center; justify-content: space-between; border: 1px solid var(--border-default);">
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
                    'structural' => ['icon' => 'building-2', 'label' => 'Structure'],
                    'furniture' => ['icon' => 'armchair', 'label' => 'Furniture'],
                    'fixture' => ['icon' => 'bath', 'label' => 'Fixtures'],
                    'landscape' => ['icon' => 'tree-pine', 'label' => 'Landscape'],
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
                    style="border-color: var(--accent); background: var(--accent-light);"
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
            <strong>SpatialSync</strong>
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

    <!-- ============ BLUEPRINT EXPORT MODAL ============ -->
    <div class="modal-overlay" x-show="blueprintModalOpen" x-transition @click.self="blueprintModalOpen = false">
        <div class="modal-content" style="width: 480px; max-width: 95vw;">
            <div class="modal-header">
                <h3 class="modal-title" style="display:flex;align-items:center;gap:8px;">
                    <i data-lucide="file-text" style="width:20px;height:20px;color:#3B82F6;"></i>
                    Export Blueprint PDF
                </h3>
                <button class="btn btn--ghost btn--sm" @click="blueprintModalOpen = false">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="modal-body" style="display:flex;flex-direction:column;gap:20px;">

                <!-- Paper Size + Scale -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group" style="margin:0;">
                        <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-tertiary);margin-bottom:6px;display:block;">Paper Size</label>
                        <div style="display:flex;gap:8px;">
                            <button class="blueprint-opt-btn" :class="{active: bpPaper==='a4'}" @click="bpPaper='a4'">A4</button>
                            <button class="blueprint-opt-btn" :class="{active: bpPaper==='a3'}" @click="bpPaper='a3'">A3</button>
                        </div>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-tertiary);margin-bottom:6px;display:block;">Scale</label>
                        <div style="display:flex;gap:8px;">
                            <button class="blueprint-opt-btn" :class="{active: bpScale===50}"  @click="bpScale=50">1:50</button>
                            <button class="blueprint-opt-btn" :class="{active: bpScale===100}" @click="bpScale=100">1:100</button>
                            <button class="blueprint-opt-btn" :class="{active: bpScale===200}" @click="bpScale=200">1:200</button>
                        </div>
                    </div>
                </div>

                <!-- Floors -->
                <div class="form-group" style="margin:0;">
                    <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-tertiary);margin-bottom:6px;display:block;">Floors to Export</label>
                    <div style="display:flex;gap:8px;">
                        <button class="blueprint-opt-btn" :class="{active: bpFloors==='all'}"     @click="bpFloors='all'">All Floors</button>
                        <button class="blueprint-opt-btn" :class="{active: bpFloors==='current'}" @click="bpFloors='current'">Current Floor Only</button>
                    </div>
                </div>

                <!-- Toggles -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <label class="blueprint-toggle">
                        <span>Include Furniture</span>
                        <div class="toggle-track" :class="{active: bpFurniture}" @click="bpFurniture=!bpFurniture">
                            <div class="toggle-thumb"></div>
                        </div>
                    </label>
                    <label class="blueprint-toggle">
                        <span>Dimension Lines</span>
                        <div class="toggle-track" :class="{active: bpDimensions}" @click="bpDimensions=!bpDimensions">
                            <div class="toggle-thumb"></div>
                        </div>
                    </label>
                </div>

                <!-- Preview hint -->
                <div style="padding:12px;background:var(--bg-secondary);border-radius:12px;border:1px solid var(--border-default);display:flex;gap:10px;align-items:flex-start;">
                    <i data-lucide="info" style="width:16px;height:16px;color:#3B82F6;flex-shrink:0;margin-top:1px;"></i>
                    <p style="font-size:12px;color:var(--text-secondary);line-height:1.5;">
                        Each floor will be exported as a separate page with walls, doors, windows, and architectural notation. Dimensions are in <strong>metres</strong>.
                    </p>
                </div>
            </div>

            <div class="modal-footer" style="display:flex;gap:10px;justify-content:flex-end;padding:16px 20px;border-top:1px solid var(--border-default);">
                <button class="btn btn--secondary" @click="blueprintModalOpen = false">Cancel</button>
                <button class="btn btn--primary" @click="generateBlueprint()" :disabled="bpExporting">
                    <i data-lucide="file-down" class="w-4 h-4"></i>
                    <span x-text="bpExporting ? 'Generating...' : 'Generate PDF'"></span>
                </button>
            </div>
        </div>
    </div>

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
                <button class="btn btn--secondary" @click="showIssueModal = false" :disabled="creatingIssue">Cancel</button>
                <button class="btn btn--primary" @click="createIssue()" :disabled="!newIssue.title.trim() || creatingIssue">
                    <template x-if="creatingIssue">
                        <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Creating...</span>
                    </template>
                    <template x-if="!creatingIssue">
                        <span>
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Create Issue
                        </span>
                    </template>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Mobile sidebar toggle -->
<button class="mobile-sidebar-toggle" @click="sidebarOpen = !sidebarOpen" aria-label="Toggle sidebar">
    <i data-lucide="users" class="w-5 h-5"></i>
</button>

<!-- Mobile Action Bar (replaces floating Tools panel) -->
<div class="mobile-action-bar" id="mobile-action-bar" style="display: none;">
    <button class="mobile-action-btn" onclick="if(window.editor) window.editor.mobileRotate()" title="Rotate">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
    </button>
    <button class="mobile-action-btn" onclick="if(window.editor) window.editor.mobileDelete()" title="Delete">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
    </button>
    <button class="mobile-action-btn" onclick="if(window.editor) window.editor.mobileToggleTransform()" title="Move">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 9l-3 3 3 3"/><path d="M9 5l3-3 3 3"/><path d="M15 19l-3 3-3-3"/><path d="M19 9l3 3-3 3"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="12" y1="2" x2="12" y2="22"/></svg>
    </button>
    <button class="mobile-action-btn" onclick="if(window.editor) window.editor.mobileToggleDayNight()" title="Day/Night">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
    </button>
    <button class="mobile-action-btn" onclick="if(window.editor) window.editor.mobileCycleGrid()" title="Grid">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/><path d="M9 3v18"/><path d="M15 3v18"/></svg>
    </button>
</div>

<!-- Mobile Placement Controls (shown when placing parts) -->
<div class="mobile-placement-controls" id="mobile-placement-controls" style="display: none;">
    <button class="mobile-place-btn" onclick="if(window.editor) window.editor.mobileRotate()">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
        Rotate
    </button>
    <button class="mobile-place-btn mobile-place-cancel" onclick="if(window.editor) window.editor.cancelPlacement()">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        Cancel
    </button>
</div>

<style>
    .mobile-sidebar-toggle { display: none; }
    .mobile-action-bar { display: none; }
    .mobile-placement-controls { display: none; }
    
    @media (max-width: 768px) {
        .mobile-sidebar-toggle {
            display: flex !important;
            position: fixed; bottom: 16px; right: 16px;
            z-index: 1002; width: 48px; height: 48px;
            border-radius: 50%; background: var(--accent); color: #fff;
            border: none; box-shadow: 0 4px 16px rgba(0,102,255,0.3);
            cursor: pointer; align-items: center; justify-content: center;
        }
        
        /* Mobile Action Bar - compact row above floating toolbar */
        .mobile-action-bar {
            display: flex !important;
            position: fixed;
            bottom: calc(76px + env(safe-area-inset-bottom));
            left: 50%;
            transform: translateX(-50%);
            z-index: 998;
            gap: 4px;
            padding: 6px 8px;
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(12px);
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.08);
        }
        .mobile-action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(51, 65, 85, 0.6);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .mobile-action-btn:active {
            background: rgba(59, 130, 246, 0.4);
        }
        
        /* Mobile Placement Controls */
        .mobile-placement-controls {
            display: none !important;
            position: fixed;
            bottom: calc(76px + env(safe-area-inset-bottom));
            left: 50%;
            transform: translateX(-50%);
            z-index: 999;
            gap: 8px;
        }
        .mobile-placement-controls.show {
            display: flex !important;
        }
        .mobile-place-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            background: rgba(30, 41, 59, 0.9);
            backdrop-filter: blur(12px);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
        .mobile-place-btn:active {
            background: rgba(59, 130, 246, 0.3);
        }
        .mobile-place-cancel {
            background: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.4);
        }
        .mobile-place-cancel:active {
            background: rgba(239, 68, 68, 0.4);
        }
    }
</style>

@endsection

@push('scripts')
<script type="importmap">
{
    "imports": {
        "three": "https://cdn.jsdelivr.net/npm/three@0.170.0/build/three.module.js",
        "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.170.0/examples/jsm/"
    }
}
</script>
<script type="module">
    import * as THREE from 'three';
    import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
    window.THREE = THREE;
    window.OrbitControls = OrbitControls;
    const el = document.getElementById('debug-three');
    if (el) {
        el.textContent = 'Three.js: OK';
        el.className = 'status-ok';
    }
</script>
<script>
// Production-safe debug logging — gated behind APP_DEBUG
window.DEBUG_MODE = {{ app()->environment('local') ? 'true' : 'false' }};
function debugLog(...args) {
    if (window.DEBUG_MODE) console.log(...args);
}
function debugWarn(...args) {
    if (window.DEBUG_MODE) console.warn(...args);
}
function debugError(...args) {
    if (window.DEBUG_MODE) console.error(...args);
}
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
        sceneryPanelOpen: false,
        currentScenery: 'neighborhood',

        // Blueprint Export State
        blueprintModalOpen: false,
        bpPaper:      'a4',
        bpScale:      100,
        bpFloors:     'all',
        bpFurniture:  true,
        bpDimensions: true,
        bpExporting:  false,

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
        creatingIssue: false,
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
        displayStatus: 'connecting', // debounced status for UI display
        tabId: Math.random().toString(36).substr(2, 9),
        pendingSyncEvents: [],
        offlinePartSyncQueue: [],
        offlineChatQueue: [], // Queue for sync events before editor ready
        
        // Connection resilience — tuned for stability
        reconnectAttempts: 0,
        maxReconnectAttempts: 5,
        reconnectDelay: 2000,
        reconnectTimer: null,
        isReconnecting: false,
        activeChannelId: null, // Track which channel is currently active
        _statusDebounceTimer: null, // Debounce UI flicker
        _lastConnectedAt: 0, // Timestamp of last successful connection
        _graceUntil: 0, // Grace period after initial connect (no false alarms)
        _hasShownReconnectToast: false,
        _realtimeEnabled: true, // Only show reconnect toast once per cycle
        
        toolIcons: { select: 'mouse-pointer', delete: 'trash-2', move: 'move', clone: 'copy' },
        toolLabels: { select: 'Select Tool', delete: 'Delete Tool — Click to remove', move: 'Move Tool — Click to pick up', clone: 'Clone Tool — Click to duplicate' },
        exportDropdownOpen: false,

        async init() {
            // Debug: Log issues data
            debugLog('[Issues] Loaded from server:', this.issues.length, 'issues');
            debugLog('[Issues] Data:', JSON.parse(JSON.stringify(this.issues)));
            
            // Initialize Supabase for Realtime
            this.supabase = supabase.createClient(
                '{{ config('supabase.url') }}',
                '{{ config('supabase.anon_key') }}',
                {
                    realtime: {
                        timeout: 60000,
                        logger: (kind, msg, data) => {
                            if (kind === 'error' || msg?.includes('close') || msg?.includes('error')) {
                                debugWarn('[RT Logger]', kind, msg, data);
                            }
                        },
                        params: {
                            eventsPerSecond: 10
                        }
                    }
                }
            );

            debugLog('Supabase client initialized:', this.supabase ? 'OK' : 'FAILED');
            
            // Realtime keepalive — sends tiny heartbeat every 5s to prevent Render idle timeout
            this._rtKeepalive = setInterval(() => {
                if (this.rtStatus === 'connected' && this.rtChannel) {
                    try {
                        this.rtChannel.send({
                            type: 'broadcast',
                            event: 'heartbeat',
                            payload: { ts: Date.now() }
                        }).catch(() => {});
                    } catch (e) {}
                }
            }, 10000);
            
            // Test REST API connectivity first
            try {
                const { data, error } = await this.supabase.from('builds').select('id').limit(1);
                if (error) {
                    debugError('Supabase REST API test failed:', error);
                    debugError('%c[DIAGNOSTIC] Cannot connect to Supabase REST API. Check:', 'color: #ff6b6b; font-weight: bold;');
                    debugError('  - SUPABASE_URL in .env is correct');
                    debugError('  - SUPABASE_ANON_KEY in .env is correct');
                    debugError('  - Your project is not paused');
                    debugError('  - Network connectivity to Supabase');
                } else {
                    debugLog('Supabase REST API test: OK (connected to database)');
                }
            } catch (testErr) {
                debugError('Supabase REST API test error:', testErr);
            }

            // Room-based channel for absolute real-time
            const channelId = 'main_' + Date.now();
            this.activeChannelId = channelId;
            
            this.rtChannel = this.supabase.channel('build:{{ $build->id }}', {
                config: {
                    broadcast: { self: false, ack: false },
                    presence: { key: '{{ $auth_user_id }}' + '_' + this.tabId }
                }
            });
            
            debugLog('RT Created new channel with ID:', channelId);

            this.rtChannel
                .on('presence', { event: 'sync' }, () => {
                    const state = this.rtChannel.presenceState();
                    if (window.editor) window.editor.updateRemoteCursors(state);
                })
                .on('broadcast', { event: 'chat' }, (payload) => {
                    debugLog('RT Received Chat Envelope:', JSON.stringify(payload, null, 2));

                    let msg = payload.payload?.data || payload.payload || payload.data || payload;
                    
                    if (msg && msg.ts && !msg.message && !msg.content && !msg.text) return;
                    
                    debugLog('RT Extracted chat message:', msg);
                    
                    if (msg && (msg.message || msg.content || msg.text)) {
                        const messageWithId = {
                            ...msg,
                            temp_id: msg.id || msg.temp_id || `rt_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
                            created_at: msg.created_at || new Date().toISOString()
                        };
                        
                        const exists = this.chatMessages.some(m => 
                            (m.id && m.id === messageWithId.id) || 
                            (m.temp_id && m.temp_id === messageWithId.temp_id) ||
                            (m.user_id === messageWithId.user_id &&
                             m.message === messageWithId.message &&
                             Math.abs(new Date(m.created_at || 0).getTime() - new Date(messageWithId.created_at || 0).getTime()) < 5000)
                        );
                        
                        if (!exists) {
                            this.chatMessages.push(messageWithId);
                            if (this.chatMessages.length > 500) this.chatMessages.shift();
                            this.scrollToBottom();
                            debugLog('RT Chat message added:', messageWithId);
                        } else {
                            debugLog('RT Chat: Duplicate message ignored');
                        }
                    } else {
                        debugWarn('RT Chat: Invalid message structure received', { payload, extractedMsg: msg });
                    }
                })
                .on('broadcast', { event: 'sync-part' }, (payload) => {
                    debugLog('RT Received Sync-Part Envelope:', JSON.stringify(payload, null, 2));

                    // Supabase sends: payload.payload = { action: 'add', data: {...} }
                    const data = payload.payload;
                    
                    if (!data || !data.action) {
                        debugWarn('RT Sync: Invalid payload - missing action', { payload, extractedData: data });
                        return;
                    }

                    // Queue event if editor not ready yet
                    if (!window.editor) {
                        // Cap pending queue at 100 to prevent unbounded growth
                        if (this.pendingSyncEvents.length >= 100) {
                            const dropped = this.pendingSyncEvents.shift();
                            debugWarn('RT Sync queue capped, dropped oldest event:', dropped.action);
                        }
                        debugLog('RT Sync: Editor not ready, queuing event:', data.action, 'Queue size:', this.pendingSyncEvents.length + 1);
                        this.pendingSyncEvents.push(data);
                        
                        // Also try to process queue immediately in case editor just became ready
                        this.$nextTick(() => {
                            if (window.editor && this.pendingSyncEvents.length > 0) {
                                debugLog('RT Editor now ready, processing', this.pendingSyncEvents.length, 'queued events');
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
                    if (channelId !== this.activeChannelId) {
                        debugLog(`RT Ignoring event from old channel ${channelId}, current is ${this.activeChannelId}`);
                        return;
                    }
                    
                    debugLog('RT Subscription Status:', status, err ? 'Error:' + err.message : '');
                    
                    if (err) {
                        debugError('RT Subscription Error:', err);
                        
                        if (err.message && err.message.includes('UnableToConnectToProject')) {
                            debugError('%c[DIAGNOSTIC] Supabase Realtime cannot connect to your project database.', 'color: #ff6b6b; font-weight: bold;');
                            debugError('%c[DIAGNOSTIC] Solutions to try:', 'color: #ff6b6b;');
                            debugError('  1. Check if your Supabase project is active (not paused)');
                            debugError('  2. Go to Database → Replication and ensure Realtime is enabled');
                            debugError('  3. Try re-enabling Realtime: Database → Replication → Toggle Realtime OFF then ON');
                            debugError('  4. Check your .env SUPABASE_URL and SUPABASE_ANON_KEY are correct');
                            debugError('  5. Your project might need to be restarted - contact Supabase support if issue persists');
                        }
                    }
                    
                    this.rtStatus = status === 'SUBSCRIBED' ? 'connected' : (status === 'CLOSED' ? 'closed' : 'error');
                    
                    if (status === 'SUBSCRIBED') {
                        this.reconnectAttempts = 0;
                        this.isReconnecting = false;
                        this._lastConnectedAt = Date.now();
                        this._hasShownReconnectToast = false;
                        this._graceUntil = Date.now() + 8000;
                        this._realtimeEnabled = true;
                        this._setDisplayStatus('connected');
                        
                        try {
                            await this.rtChannel.track({
                                online_at: new Date().toISOString(),
                                name: '{{ $auth_user_name }}',
                                role: this.userRole,
                                tabId: this.tabId
                            });
                            debugLog('RT Connected and presence tracked');
                        } catch (trackErr) {
                            debugError('RT Presence tracking failed:', trackErr);
                        }
                        
                        setTimeout(() => this.syncMissedParts(), 500);
                    } else if (status === 'CLOSED' || status === 'CHANNEL_ERROR' || status === 'TIMED_OUT') {
                        debugWarn('RT Connection event (status: ' + status + ')');
                        
                        if (Date.now() < this._graceUntil) {
                            debugLog('RT Within grace period, ignoring transient disconnect');
                            return;
                        }
                        
                        if (status === 'CLOSED' && !this._realtimeEnabled && this.reconnectAttempts === 0) {
                            debugError('%c[REALTIME DISABLED] Supabase Realtime may not be enabled for this project.', 'color: #ff6b6b; font-weight: bold;');
                            debugError('%c[REQUIRED SETUP]', 'color: #ffaa00; font-weight: bold;');
                            debugError('  1. Go to https://mpxdhzazdzkygercrniz.supabase.co/project/default/editor');
                            debugError('  2. Navigate to Database → Replication');
                            debugError('  3. Enable Realtime for these tables: build_messages, build_parts');
                            debugError('  4. Toggle: turn OFF then ON if already enabled');
                            debugError('  Chat and collaboration will still work via database polling (30s interval).');
                        }
                        
                        this.rtStatus = 'reconnecting';
                        this._setDisplayStatus('reconnecting');
                        
                        if (!this.isReconnecting && this.reconnectAttempts < this.maxReconnectAttempts) {
                            debugLog('RT Scheduling reconnection...');
                            const delay = this.reconnectAttempts === 0 ? 2000 : 3000;
                            setTimeout(() => this.handleReconnection(), delay);
                        } else if (this.isReconnecting) {
                            debugLog('RT Reconnection already in progress, skipping...');
                        } else {
                            debugError('RT Max reconnection attempts reached. Using polling fallback only.');
                            this._setDisplayStatus('error');
                            this._realtimeEnabled = false;
                        }
                    }
                });

            // Link channel and role to editor
            this.$nextTick(() => {
                const self = this;
                let checkAttempts = 0;
                const maxCheckAttempts = 50; // 5 seconds max
                debugLog('RT Starting editor check interval, editor exists:', !!window.editor);
                const checkEditor = setInterval(() => {
                    checkAttempts++;
                    if (checkAttempts > maxCheckAttempts) {
                        clearInterval(checkEditor);
                        debugWarn('RT Editor check timed out after', maxCheckAttempts, 'attempts');
                        return;
                    }
                    if (window.editor) {
                        debugLog('RT Editor found! Linking channel and processing', self.pendingSyncEvents.length, 'queued events');
                        window.editor.rtChannel = self.rtChannel;
                        window.editor.userRole = self.userRole;
                        window.editor.myPresenceKey = '{{ $auth_user_id }}' + '_' + self.tabId;
                        clearInterval(checkEditor);
                        
                        // Initialize issue pins
                        if (self.issues && self.issues.length > 0) {
                            debugLog('[Editor] Loading', self.issues.length, 'issue pins');
                            window.editor.loadIssuePins(self.issues);
                        }
                        
                        // Process any queued sync events
                        const queueLength = self.pendingSyncEvents.length;
                        if (queueLength > 0) {
                            debugLog('RT Processing', queueLength, 'queued sync events');
                            const eventsToProcess = [...self.pendingSyncEvents];
                            self.pendingSyncEvents = [];
                            eventsToProcess.forEach(data => {
                                try {
                                    self.processSyncEvent(data);
                                } catch (err) {
                                    debugError('RT Error processing queued event:', err, data);
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
                if (e.detail.isLocal) {
                    debugLog('RT Sending Sync-Part (add)');
                    const result = await this.sendBroadcast('sync-part', {
                        action: 'add',
                        data: e.detail.partData
                    });
                    if (result.success) {
                        debugLog('RT Sync-Part (add) broadcast sent via', result.method);
                    } else {
                        debugError('RT Failed to send sync-part broadcast, queuing for retry:', result.error);
                        this.offlinePartSyncQueue.push({
                            action: 'add',
                            data: e.detail.partData,
                            timestamp: Date.now()
                        });
                    }
                    document.getElementById('parts-count').textContent = e.detail.count;
                }
            });

            window.addEventListener('part-deleted', async (e) => {
                if (e.detail.isLocal) {
                    debugLog('RT Sending Sync-Part (delete)');
                    const result = await this.sendBroadcast('sync-part', {
                        action: 'delete',
                        id: e.detail.id
                    });
                    if (result.success) {
                        debugLog('RT Sync-Part (delete) broadcast sent via', result.method);
                    } else {
                        debugError('RT Failed to send sync-part delete broadcast, queuing for retry:', result.error);
                        this.offlinePartSyncQueue.push({
                            action: 'delete',
                            id: e.detail.id,
                            timestamp: Date.now()
                        });
                    }
                }
            });

            // Throttled part-updated broadcast — collects changes for 200ms then sends the latest
            this._partUpdateQueue = {};
            this._partUpdateTimer = null;
            window.addEventListener('part-updated', async (e) => {
                if (!e.detail.isLocal) return;

                const partId = e.detail.id;
                this._partUpdateQueue[partId] = e.detail;

                if (this._partUpdateTimer) return;

                this._partUpdateTimer = setTimeout(async () => {
                    this._partUpdateTimer = null;
                    const queue = this._partUpdateQueue;
                    this._partUpdateQueue = {};

                    for (const [id, detail] of Object.entries(queue)) {
                        debugLog('RT Sending Sync-Part (update) for', id);
                        const result = await this.sendBroadcast('sync-part', {
                            action: 'update',
                            id: id,
                            data: detail.data
                        });
                        if (!result.success) {
                            debugError('RT Failed to send sync-part update broadcast, queuing for retry:', result.error);
                            this.offlinePartSyncQueue.push({
                                action: 'update',
                                id: id,
                                data: detail.data,
                                timestamp: Date.now()
                            });
                        }
                    }
                }, 200);
            });

            window.addEventListener('floor-changed', (e) => {
                this.currentFloor = e.detail.floor;
                document.getElementById('current-floor').textContent = e.detail.floor;
            });

            // Fetch latest messages from API to get any messages sent while away
            debugLog(`Init: ${this.initialMessagesCount} messages loaded from server, fetching from API...`);
            this.fetchMessages();
            
            // Poll for new messages every 30 seconds (fallback when realtime misses messages)
            this.messagePollInterval = setInterval(() => {
                this.fetchMessages();
                if (!this._realtimeEnabled) {
                    this.syncMissedParts();
                }
            }, this._realtimeEnabled ? 30000 : 10000);
            
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
                if (this._rtKeepalive) {
                    clearInterval(this._rtKeepalive);
                }
                if (this.messagePollInterval) {
                    clearInterval(this.messagePollInterval);
                }
                if (this._partUpdateTimer) {
                    clearTimeout(this._partUpdateTimer);
                }
                if (this.rtChannel) {
                    try {
                        this.rtChannel.unsubscribe();
                    } catch (e) {}
                }
                if (this.supabase) {
                    try {
                        this.supabase.removeAllChannels();
                    } catch (e) {}
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
                debugError('Search failed', err);
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
            const confirmed = await Swal.fire({
                title: 'Remove Member?',
                text: 'Are you sure you want to remove this member?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove',
                cancelButtonText: 'Cancel'
            });
            if (!confirmed.isConfirmed) return;
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
                this.copyShareUrl();
            } catch (err) {
                this.showToast('Failed to generate share link', 'error');
            }
        },

        copyShareUrl() {
            if (!this.shareUrl) return;
            navigator.clipboard.writeText(this.shareUrl);
            this.showToast('Invite link copied to clipboard!', 'success');
            this.$nextTick(() => { if(window.lucide) lucide.createIcons(); });
        },

        async fetchMessages() {
            try {
                const res = await fetch(`/editor/builds/{{ $build->id }}/messages`);
                if (!res.ok) {
                    debugError('Failed to fetch messages, status:', res.status);
                    return;
                }
                const messages = await res.json();
                
                const processedMessages = messages.map(msg => ({
                    ...msg,
                    message: msg.message || msg.content || '',
                    user: msg.user || { name: msg.user_name || 'Unknown' }
                }));
                
                const existingIds = new Set();
                const existingTempIds = new Set();
                this.chatMessages.forEach(m => {
                    if (m.id) existingIds.add(m.id);
                    if (m.temp_id) existingTempIds.add(m.temp_id);
                });
                
                const serverIds = new Set(processedMessages.map(m => m.id).filter(Boolean));
                
                const newMessages = processedMessages.filter(m => {
                    if (m.id && existingIds.has(m.id)) return false;
                    if (m.temp_id && existingTempIds.has(m.temp_id)) return false;
                    const matchByContent = this.chatMessages.some(existing =>
                        existing.user_id === m.user_id &&
                        existing.message === m.message &&
                        Math.abs(new Date(existing.created_at || 0).getTime() - new Date(m.created_at || 0).getTime()) < 5000
                    );
                    return !matchByContent;
                });
                
                debugLog(`Fetched ${messages.length} messages from server, ${newMessages.length} new, ${existingIds.size} existing`);
                
                if (newMessages.length > 0) {
                    const merged = [...this.chatMessages, ...newMessages].sort((a, b) => 
                        new Date(a.created_at || 0) - new Date(b.created_at || 0)
                    );
                    if (merged.length > 500) merged = merged.slice(merged.length - 500);
                    this.chatMessages = merged;
                    
                    this.chatMessages.forEach(m => {
                        if (m.id && serverIds.has(m.id) && m.temp_id && m.temp_id.startsWith('temp_')) {
                            delete m.temp_id;
                        }
                    });
                    
                    this.scrollToBottom();
                }
            } catch (err) {
                debugError('Failed to fetch messages', err);
            }
        },

        async sendMessage() {
            if (!this.newMessage.trim()) return;
            const messageText = this.newMessage;
            this.newMessage = '';

            const tempId = `temp_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;

            const messageData = {
                temp_id: tempId,
                message: messageText,
                user_id: '{{ $auth_user_id }}',
                user: { name: '{{ $auth_user_name }}' },
                created_at: new Date().toISOString()
            };

            this.chatMessages.push(messageData);
            if (this.chatMessages.length > 500) this.chatMessages.shift();
            this.scrollToBottom();

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
                    const errorData = await res.json().catch(() => ({}));
                    const errorMsg = errorData.error || errorData.message || `Server error (${res.status})`;
                    debugError('Server error saving message:', res.status, errorData);
                    this.chatMessages = this.chatMessages.filter(m => m.temp_id !== tempId);
                    this.newMessage = messageText;
                    this.showToast('Failed to save message: ' + errorMsg, 'error');
                    return;
                }
                
                const data = await res.json();
                debugLog('Message saved successfully:', data);

                const idx = this.chatMessages.findIndex(m => m.temp_id === tempId);
                if (idx !== -1) {
                    this.chatMessages[idx] = {
                        ...this.chatMessages[idx],
                        id: data.id,
                        created_at: data.created_at || this.chatMessages[idx].created_at
                    };
                }

                debugLog('RT Sending Chat broadcast:', messageData);
                const result = await this.sendBroadcast('chat', { data: { ...messageData, id: data.id } });
                debugLog('RT Chat broadcast result:', result);
                if (!result.success) {
                    debugWarn('RT Chat broadcast failed, queuing for retry');
                    this.offlineChatQueue.push({
                        event: 'chat',
                        payload: { data: { ...messageData, id: data.id } },
                        timestamp: Date.now()
                    });
                }
            } catch (err) {
                debugError('Failed to send message:', err);
                this.chatMessages = this.chatMessages.filter(m => m.temp_id !== tempId);
                this.newMessage = messageText;
                this.showToast('Failed to send message. Check connection and try again.', 'error');
            }
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = document.getElementById('chat-messages');
                if (el) el.scrollTop = el.scrollHeight;
            });
        },

        formatTime(timestamp) {
            if (!timestamp) return '';
            const date = new Date(timestamp);
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },

        processSyncEvent(data) {
            try {
                debugLog('RT Processing action:', data.action, 'Data:', data);

                if (data.action === 'add' && data.data) {
                    debugLog('RT Rendering Remote Part:', data.data);
                    window.editor.addPartToScene(data.data, false);
                } else if (data.action === 'delete' && data.id) {
                    debugLog('RT Deleting Remote Part ID:', data.id);
                    window.editor.deletePartFromRealtime(data.id);
                } else if (data.action === 'update' && data.id && data.data) {
                    debugLog('RT Updating Remote Part ID:', data.id);
                    window.editor.updatePartInRealtime(data.id, data.data);
                } else {
                    debugWarn('RT Sync: Unknown action or missing data fields', data);
                }
            } catch (err) {
                debugError('RT Sync Error:', err);
            }
        },

        async syncMissedParts() {
            if (!window.editor) {
                debugWarn('RT Cannot sync parts - editor not ready');
                return;
            }
            
            debugLog('RT Syncing missed parts from database...');
            
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
                    const exists = existingIds.some(id => id === partData.id);
                    
                    if (!exists) {
                        debugLog('RT Adding missed part:', partData.id);
                        window.editor.addPartToScene(partData, false);
                        addedCount++;
                    } else {
                        skippedCount++;
                    }
                });
                
                if (addedCount > 0) {
                    debugLog(`RT Synced ${addedCount} missed parts from database (${skippedCount} already existed)`);
                    this.showToast(`Synced ${addedCount} parts that were added while you were offline`, 'success');
                } else {
                    debugLog(`RT No missed parts to sync - all ${skippedCount} parts already up to date`);
                }
            } catch (error) {
                debugError('RT Error syncing missed parts:', error);
                this.showToast('Could not sync missed parts', 'warning');
            }
        },

        async flushOfflinePartSyncQueue() {
            if (this.offlinePartSyncQueue.length === 0 && this.offlineChatQueue.length === 0) {
                return;
            }

            const partQueue = [...this.offlinePartSyncQueue];
            this.offlinePartSyncQueue = [];

            const chatQueue = [...this.offlineChatQueue];
            this.offlineChatQueue = [];

            const maxAge = 5 * 60 * 1000;
            const now = Date.now();
            let flushedCount = 0;

            for (const item of chatQueue) {
                if (now - item.timestamp > maxAge) {
                    debugLog('RT Discarding stale offline chat message, age:', Math.round((now - item.timestamp) / 1000) + 's');
                    continue;
                }

                debugLog('RT Flushing offline chat message');
                const result = await this.sendBroadcast(item.event, item.payload);
                if (result.success) {
                    flushedCount++;
                } else {
                    debugWarn('RT Failed to flush offline chat message, re-queuing');
                    this.offlineChatQueue.push(item);
                }
            }

            for (const item of partQueue) {
                if (now - item.timestamp > maxAge) {
                    debugLog('RT Discarding stale offline sync event:', item.action, 'age:', Math.round((now - item.timestamp) / 1000) + 's');
                    continue;
                }

                debugLog('RT Flushing offline sync event:', item.action);
                const result = await this.sendBroadcast('sync-part', item);
                if (result.success) {
                    flushedCount++;
                } else {
                    debugWarn('RT Failed to flush offline sync event, re-queuing:', item.action);
                    this.offlinePartSyncQueue.push(item);
                }
            }

            if (flushedCount > 0) {
                debugLog(`RT Flushed ${flushedCount} offline events`);
            }
        },

        // Debounced display status update — prevents UI flicker
        _setDisplayStatus(status) {
            // Connected always updates instantly (good news travels fast)
            if (status === 'connected') {
                clearTimeout(this._statusDebounceTimer);
                this.displayStatus = 'connected';
                return;
            }
            // Non-connected states are debounced by 1 second
            clearTimeout(this._statusDebounceTimer);
            this._statusDebounceTimer = setTimeout(() => {
                // Only downgrade if still not connected
                if (this.rtStatus !== 'connected') {
                    this.displayStatus = status;
                }
            }, 1000);
        },

        handleReconnection() {
            if (this.isReconnecting || this.reconnectAttempts >= this.maxReconnectAttempts) {
                if (this.reconnectAttempts >= this.maxReconnectAttempts) {
                    debugError('RT Max reconnection attempts reached. Please refresh the page.');
                    this._setDisplayStatus('error');
                    this.showToast('Connection lost. Please refresh the page.', 'error');
                }
                return;
            }

            this.isReconnecting = true;
            this.reconnectAttempts++;
            
            // Exponential backoff: 6s, 12s, 24s, ... up to 60s max (tuned for localhost)
            const delay = Math.min(this.reconnectDelay * 3 * Math.pow(2, this.reconnectAttempts - 1), 60000);
            
            debugLog(`RT Reconnection attempt ${this.reconnectAttempts}/${this.maxReconnectAttempts} in ${delay}ms`);
            // Only show toast on first attempt to reduce spam
            if (!this._hasShownReconnectToast) {
                this._hasShownReconnectToast = true;
                this.showToast('Connection unstable, reconnecting...', 'warning');
            }

            this.reconnectTimer = setTimeout(() => {
                debugLog('RT Attempting to recreate channel...');
                
                // Generate new channel ID for this reconnection attempt
                const reconnectionChannelId = 'reconn_' + Date.now() + '_' + this.reconnectAttempts;
                const previousChannelId = this.activeChannelId;
                this.activeChannelId = reconnectionChannelId;
                debugLog(`RT Switching from channel ${previousChannelId} to ${reconnectionChannelId}`);
                
                // Unsubscribe from old channel if exists
                if (this.rtChannel) {
                    try {
                        this.rtChannel.unsubscribe();
                    } catch (e) {
                        debugWarn('RT Error unsubscribing from old channel:', e);
                    }
                }
                
                // Create new channel
                this.rtChannel = this.supabase.channel('build:{{ $build->id }}', {
                    config: {
                        broadcast: { self: false, ack: false },
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
                        debugLog('RT Received Chat Envelope:', JSON.stringify(payload, null, 2));
                        let msg = payload.payload?.data || payload.payload || payload.data || payload;
                        debugLog('RT Extracted chat message:', msg);
                        
                        if (msg && (msg.message || msg.content || msg.text)) {
                            const messageWithId = {
                                ...msg,
                                temp_id: msg.id || msg.temp_id || `rt_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
                                created_at: msg.created_at || new Date().toISOString()
                            };
                            
                            const exists = this.chatMessages.some(m => 
                                (m.id && m.id === messageWithId.id) || 
                                (m.temp_id && m.temp_id === messageWithId.temp_id) ||
                                (m.user_id === messageWithId.user_id &&
                                 m.message === messageWithId.message &&
                                 Math.abs(new Date(m.created_at || 0).getTime() - new Date(messageWithId.created_at || 0).getTime()) < 5000)
                            );
                            
                            if (!exists) {
                                this.chatMessages.push(messageWithId);
                                this.scrollToBottom();
                                debugLog('RT Chat message added:', messageWithId);
                            }
                        }
                    })
                    .on('broadcast', { event: 'sync-part' }, (payload) => {
                        debugLog('RT Received Sync-Part Envelope:', JSON.stringify(payload, null, 2));
                        const data = payload.payload;
                        
                        if (!data || !data.action) {
                            debugWarn('RT Sync: Invalid payload - missing action', { payload, extractedData: data });
                            return;
                        }

                        if (!window.editor) {
                            debugLog('RT Sync: Editor not ready, queuing event:', data.action, 'Queue size:', this.pendingSyncEvents.length + 1);
                            this.pendingSyncEvents.push(data);
                            return;
                        }

                        this.processSyncEvent(data);
                    })
                    .subscribe(async (status) => {
                        // Check if this reconnection channel is still the active one
                        if (reconnectionChannelId !== this.activeChannelId) {
                            debugLog(`RT Ignoring reconnection event from old channel ${reconnectionChannelId}, current is ${this.activeChannelId}`);
                            return;
                        }
                        
                        debugLog('RT Reconnection Status:', status);
                        this.rtStatus = status === 'SUBSCRIBED' ? 'connected' : 'reconnecting';
                        
                    if (status === 'SUBSCRIBED') {
                        this.reconnectAttempts = 0;
                        this.isReconnecting = false;
                        this._lastConnectedAt = Date.now();
                        this._hasShownReconnectToast = false;
                        this._graceUntil = Date.now() + 8000;
                        this._setDisplayStatus('connected');
                        this.showToast('Reconnected successfully!', 'success');
                        
                        await this.rtChannel.track({
                            online_at: new Date().toISOString(),
                            name: '{{ $auth_user_name }}',
                            role: this.userRole,
                            tabId: this.tabId
                        });
                        
                        if (window.editor) {
                            window.editor.rtChannel = this.rtChannel;
                        }
                        
                        debugLog('RT Reconnected and presence tracked');
                        
                        await this.flushOfflinePartSyncQueue();
                        
                        await this.syncMissedParts();
                    } else if (status === 'CLOSED' || status === 'CHANNEL_ERROR') {
                        this.isReconnecting = false;
                        if (Date.now() < this._graceUntil) return;
                        if (reconnectionChannelId === this.activeChannelId && !this.isReconnecting) {
                            this._setDisplayStatus('reconnecting');
                            this.handleReconnection();
                        }
                    } else if (status === 'TIMED_OUT') {
                        debugWarn('RT Reconnection timed out');
                        this.isReconnecting = false;
                        if (reconnectionChannelId === this.activeChannelId) {
                            this.handleReconnection();
                        }
                    }
                    });
            }, delay);
        },

        async sendBroadcast(event, payload) {
            if (this.rtStatus === 'connected') {
                try {
                    await this.rtChannel.send({
                        type: 'broadcast',
                        event: event,
                        payload: payload
                    });
                    return { success: true, method: 'websocket' };
                } catch (err) {
                    debugWarn('RT WebSocket broadcast failed:', err);
                    if (this.rtStatus === 'closed' || this.rtStatus === 'error') {
                        this.handleReconnection();
                    }
                    return { success: false, error: err };
                }
            }
            
            debugWarn('RT Not connected, broadcast queued for reconnection');
            return { success: false, error: 'not_connected' };
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

        toggleDayNight() {
            if (typeof editor !== 'undefined') editor.toggleDayNight();
        },

        setScenery(theme) {
            this.currentScenery = theme;
            this.sceneryPanelOpen = false;
            if (typeof editor !== 'undefined') editor.setScenery(theme);
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

        // ============ BLUEPRINT EXPORT ============
        async generateBlueprint() {
            if (typeof editor === 'undefined' || !window.BlueprintExporter) {
                this.showToast('Blueprint exporter not loaded', 'error');
                return;
            }
            this.bpExporting = true;
            this.blueprintModalOpen = false;
            this.showToast('Generating blueprint...', 'info');

            try {
                const exporter = new BlueprintExporter(editor);
                await exporter.export({
                    paperSize:  this.bpPaper,
                    scale:      this.bpScale,
                    floors:     this.bpFloors === 'current' ? this.currentFloor : 'all',
                    furniture:  this.bpFurniture,
                    dimensions: this.bpDimensions,
                    buildName:  '{{ $build->name }}'
                });
                this.showToast('Blueprint downloaded!', 'success');
            } catch (e) {
                debugError('Blueprint export error:', e);
                this.showToast('Export failed — check console', 'error');
            } finally {
                this.bpExporting = false;
            }
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

            this.creatingIssue = true;

            try {
                const buildId = '{{ $build->id }}';
                
                // Get part position if attached to part
                let positionData = {};
                let validPartId = null;
                
                if (this.newIssue.part_id && typeof editor !== 'undefined') {
                    const partData = editor.parts.get(this.newIssue.part_id);
                    if (partData) {
                        positionData = {
                            position_x: partData.mesh.position.x,
                            position_y: partData.mesh.position.y,
                            position_z: partData.mesh.position.z,
                        };
                        // Only send part_id if it's a valid UUID (not a temp ID)
                        const uuidRegex = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
                        if (uuidRegex.test(this.newIssue.part_id)) {
                            validPartId = this.newIssue.part_id;
                        }
                    }
                }

                const res = await fetch(`/editor/builds/${buildId}/issues`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        title: this.newIssue.title,
                        description: this.newIssue.description,
                        priority: this.newIssue.priority,
                        part_id: validPartId,
                        ...positionData,
                    }),
                });

                if (!res.ok) {
                    const errorData = await res.json().catch(() => ({}));
                    const errorMsg = errorData.error || errorData.message || `Server error (${res.status})`;
                    debugError('Issue creation failed:', res.status, errorData);
                    throw new Error(errorMsg);
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
                debugError('Error creating issue:', err);
                this.showToast('Failed to create issue: ' + err.message, 'error');
            } finally {
                this.creatingIssue = false;
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
                debugError('Error updating issue:', err);
                this.showToast('Failed to update issue status', 'error');
            }
        },

        async deleteIssue(issueId) {
            const result = await Swal.fire({
                title: 'Delete Issue?',
                text: 'Are you sure you want to delete this issue?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel'
            });
            if (!result.isConfirmed) return;

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
                debugError('Error deleting issue:', err);
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
        flex-direction: column;
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
    
    /* Navbar Shortcuts Adaptive */
    .navbar-shortcuts {
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.3s ease;
    }

    .nb-shortcut {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-tertiary);
        white-space: nowrap;
    }

    .nb-shortcut kbd {
        display: inline-block;
        padding: 2px 6px;
        background: var(--bg-tertiary);
        border: 1px solid var(--border-default);
        border-radius: 4px;
        color: var(--text-primary);
        font-family: 'JetBrains Mono', monospace;
        font-size: 10px;
        box-shadow: 0 2px 0 var(--border-default);
    }

    /* Collapse labels when sidebar is open or screen is small */
    .sidebar-open .nb-shortcut span {
        display: none;
    }
    @media (max-width: 1400px) {
        .nb-shortcut span {
            display: none;
        }
        .nav-shortcut-divider {
            display: none;
        }
        .navbar-shortcuts {
            gap: 8px;
        }
    }

    @media (max-width: 1100px) {
        .navbar-shortcuts {
            display: none;
        }
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
        background: color-mix(in srgb, var(--surface) 85%, transparent);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: 1px solid var(--border-default);
        border-radius: 20px;
        box-shadow: var(--shadow-lg);
        z-index: 1000;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .floating-toolbar:hover {
        background: var(--surface);
        box-shadow: var(--shadow-xl);
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

    /* Night mode active state on button */
    .floating-tool-btn.night-active {
        background: var(--accent-muted);
        color: var(--accent-active);
        box-shadow: var(--shadow-sm);
    }
    .floating-tool-btn.night-active:hover {
        background: var(--accent-light);
    }

    /* Scenery pop-out panel — light mode */
    .scenery-panel {
        position: absolute;
        right: calc(100% + 12px);
        bottom: 0;
        width: 230px;
        background: var(--surface);
        border: 1px solid var(--border-default);
        border-radius: 16px;
        padding: 10px;
        box-shadow: var(--shadow-lg);
        z-index: 200;
    }
    .scenery-panel__title {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-tertiary);
        padding: 4px 8px 8px;
    }
    .scenery-option {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 9px 10px;
        border-radius: 10px;
        border: 1.5px solid transparent;
        background: transparent;
        color: var(--text-primary);
        cursor: pointer;
        text-align: left;
        transition: background 0.15s, border-color 0.15s;
        margin-bottom: 3px;
    }
    .scenery-option:hover { background: var(--bg-secondary); }
    .scenery-option.active {
        background: var(--accent-light);
        border-color: var(--accent);
    }
    .scenery-icon { font-size: 20px; line-height: 1; flex-shrink: 0; }
    .scenery-name { font-size: 12.5px; font-weight: 600; color: var(--text-primary); line-height: 1.3; }
    .scenery-desc { font-size: 11px; color: var(--text-tertiary); margin-top: 1px; }

    /* ============ MODERN SWEETALERT2 PREMIUM THEME ============ */
    .swal-premium .swal2-popup {
        background: color-mix(in srgb, var(--surface) 85%, transparent);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: 1px solid var(--border-default);
        border-radius: 24px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        padding: 2.5rem;
        box-shadow: 0 40px 100px rgba(0, 0, 0, 0.12), 0 10px 40px rgba(0, 0, 0, 0.08);
    }
    
    .swal-premium .swal2-title {
        font-size: 1.6rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }
    
    .swal-premium .swal2-html-container {
        font-size: 1.05rem;
        color: var(--text-secondary);
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

    .member-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 20px 16px;
        text-align: center;
        color: var(--text-secondary);
        border: 1px dashed var(--border-default);
        border-radius: 8px;
        background: var(--bg-secondary);
    }

    .member-empty-state i {
        color: var(--text-tertiary);
        opacity: 0.6;
    }

    .member-empty-state p {
        margin: 0;
        font-size: 13px;
        font-weight: 500;
    }

    .member-empty-state small {
        margin: 0;
        font-size: 12px;
        color: var(--text-tertiary);
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

    /* ============ EXPORT DROPDOWN ============ */
    .export-dropdown {
        position: relative;
    }

    .dropdown-menu {
        position: absolute;
        top: calc(100% + 8px);
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: 1px solid var(--border-default);
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        padding: 8px;
        z-index: 1001;
        transform-origin: top left;
    }

    .dropdown-menu--right {
        right: 0;
        transform-origin: top right;
    }

    .dropdown-header {
        padding: 8px 12px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--text-secondary);
        opacity: 0.5;
    }

    .dropdown-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 10px 14px;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: left;
        width: 100%;
        color: var(--text-primary);
        text-decoration: none;
    }

    .dropdown-item:hover {
        background: rgba(255, 255, 255, 0.5);
        transform: translateX(4px);
    }

    .dropdown-item__icon {
        width: 32px;
        height: 32px;
        background: var(--bg-secondary);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--accent);
        flex-shrink: 0;
    }

    .dropdown-item__icon svg { width: 16px; height: 16px; }

    .dropdown-item__title {
        font-size: 13px;
        font-weight: 600;
        line-height: 1.2;
    }

    .dropdown-item__desc {
        font-size: 11px;
        color: var(--text-secondary);
        margin-top: 2px;
    }

    [data-theme="dark"] .dropdown-menu {
        background: rgba(30, 41, 59, 0.8);
        border-color: rgba(255, 255, 255, 0.1);
    }
    
    [data-theme="dark"] .dropdown-item:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    [data-theme="dark"] .dropdown-item__icon {
        background: rgba(255, 255, 255, 0.05);
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

    /* Loading spinner animation */
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .animate-spin {
        animation: spin 1s linear infinite;
    }

    .btn--success {
        background: #22c55e;
        color: white;
    }

    .btn--success:hover {
        background: #16a34a;
    }

    /* ============ END ISSUE SYSTEM STYLES ============ */

    /* ============ TABLET RESPONSIVE (769px - 1024px) ============ */
    @media (max-width: 1024px) and (min-width: 769px) {
        .sidebar {
            width: 280px;
        }
        .editor-layout.sidebar-open {
            margin-left: 280px;
        }
        .floating-toolbar {
            right: 12px;
            padding: 10px;
        }
        .floating-tool-btn {
            width: 40px;
            height: 40px;
        }
        .floating-tool-btn i {
            width: 20px;
            height: 20px;
        }
        .nb-shortcut span {
            display: none;
        }
        .nav-shortcut-divider {
            display: none !important;
        }
    }

    /* Mobile sidebar overlay */
    .mobile-sidebar-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        z-index: 1040;
        display: none;
    }

    @media (max-width: 768px) {
        .mobile-sidebar-overlay {
            display: block;
        }
    @media (max-width: 768px) {
        /* Sidebar becomes full-screen overlay from bottom */
        .sidebar {
            width: 100% !important;
            max-width: 100% !important;
            left: 0 !important;
            top: auto !important;
            bottom: 0 !important;
            height: 70vh !important;
            max-height: 70vh !important;
            border-radius: var(--radius-xl) var(--radius-xl) 0 0 !important;
            transform: translateY(100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1050 !important;
        }
        .sidebar-open .sidebar {
            transform: translateY(0);
        }
        .editor-layout.sidebar-open {
            margin-left: 0 !important;
        }

        /* Topbar mobile layout */
        .editor-topbar {
            grid-template-columns: 1fr auto auto !important;
            gap: 8px !important;
            padding: 8px 12px !important;
            flex-wrap: wrap;
        }
        .editor-topbar__left {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            flex-wrap: wrap;
        }
        .editor-topbar__title {
            display: none !important;
        }
        .editor-topbar__center {
            order: 3;
            width: 100%;
            justify-content: center;
            padding-top: 4px;
        }
        .editor-topbar__right {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Hide non-essential elements on mobile */
        .editor-topbar__center .navbar-shortcuts,
        .editor-topbar__center .nav-shortcut-divider,
        .editor-topbar__right .export-dropdown,
        .editor-topbar__right .editor-topbar__divider {
            display: none !important;
        }

        /* Floor selector mobile */
        .floor-selector {
            padding: 4px 6px;
            gap: 4px;
        }
        .floor-btn {
            width: 28px;
            height: 28px;
        }
        .floor-display {
            font-size: 12px;
            min-width: 50px;
        }

        /* RT indicator mobile */
        .rt-indicator {
            padding: 3px 6px;
        }
        .rt-indicator__label {
            font-size: 9px;
        }
        .rt-indicator__dot {
            width: 6px;
            height: 6px;
        }
        .view-only-badge {
            padding: 3px 6px;
            font-size: 9px;
        }

        /* Sidebar mobile optimizations */
        .sidebar-section__title {
            font-size: 14px;
            padding: 12px 16px;
        }
        .sidebar__footer {
            padding: 12px;
        }

        /* Chat mobile */
        .chat-messages {
            padding: 12px;
        }
        .message {
            max-width: 90%;
            padding: 8px 12px;
            font-size: 13px;
        }
        .chat-input-wrapper {
            gap: 6px;
        }
        .chat-input {
            padding: 8px 12px;
            font-size: 13px;
            min-height: 36px;
        }
        .chat-send-btn {
            width: 36px;
            height: 36px;
        }
        .chat-offline-notice {
            margin: 0 12px 8px;
            padding: 8px 12px;
            font-size: 11px;
        }

        /* Issues mobile */
        .filter-btn {
            padding: 6px 10px;
            font-size: 11px;
        }
        .issue-card {
            padding: 12px;
        }
        .issue-card-header {
            gap: 6px;
        }
        .issue-title {
            font-size: 13px;
        }
        .issue-priority-badge {
            padding: 3px 8px;
            font-size: 11px;
        }
        .issue-card-actions {
            gap: 4px;
        }
        .btn--xs {
            padding: 4px 8px;
            font-size: 10px;
        }

        /* Floating toolbar mobile - move to bottom for thumb reach */
        .floating-toolbar {
            position: fixed !important;
            right: 50% !important;
            top: auto !important;
            bottom: calc(16px + env(safe-area-inset-bottom)) !important;
            transform: translateX(50%) !important;
            flex-direction: row !important;
            padding: 10px 16px;
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        }
        .floating-toolbar__group {
            flex-direction: row !important;
            gap: 6px;
        }
        .floating-toolbar__divider {
            width: 1px;
            height: 24px;
        }
        .floating-tool-btn {
            width: 44px;
            height: 44px;
            min-width: 44px;
        }
        .floating-tool-btn i {
            width: 22px;
            height: 22px;
        }
        .floating-tool-btn .tooltip {
            display: none !important;
        }

        /* Mobile sidebar toggle - larger and more visible */
        .mobile-sidebar-toggle {
            display: flex !important;
            position: fixed;
            bottom: calc(16px + env(safe-area-inset-bottom));
            right: 16px;
            z-index: 1060;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--accent);
            color: #fff;
            border: none;
            box-shadow: 0 6px 20px rgba(0,102,255,0.4);
            cursor: pointer;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease;
        }
        .mobile-sidebar-toggle:active {
            transform: scale(0.95);
        }
        .mobile-sidebar-toggle i {
            width: 24px;
            height: 24px;
        }

        /* Issue modal mobile */
        .modal-content {
            width: 95% !important;
            max-width: 95% !important;
            max-height: 85vh !important;
            margin: 16px;
        }
        .modal-header {
            padding: 16px;
        }
        .modal-body {
            padding: 16px;
        }
        .modal-footer {
            padding: 12px 16px;
            flex-direction: column;
            gap: 8px;
        }
        .modal-footer .btn {
            width: 100%;
        }
        .priority-selector {
            gap: 6px;
        }
        .priority-btn {
            padding: 8px 12px;
            font-size: 12px;
            flex: 1;
            justify-content: center;
        }
        .form-group label {
            font-size: 12px;
        }
        .form-input,
        .form-textarea {
            padding: 10px 12px;
            font-size: 13px;
        }

        /* Story dots mobile */
        .story-progress {
            right: 8px !important;
        }
        .story-dot {
            width: 6px;
            height: 6px;
        }

        /* Properties panel mobile */
        .properties-panel {
            width: 100% !important;
            right: 0 !important;
            left: 0 !important;
            top: auto !important;
            bottom: 0 !important;
            max-height: 50vh !important;
            border-radius: var(--radius-xl) var(--radius-xl) 0 0 !important;
            z-index: 1100 !important;
        }
        .keyboard-hint {
            display: none !important;
        }
        .editor-tab span {
            display: none !important;
        }
        .editor-tab {
            padding: 8px !important;
        }
        .editor-bottom {
            min-height: auto !important;
        }
        .editor-parts {
            padding: 8px !important;
            min-height: 64px !important;
        }

        /* Safe area insets for notched phones */
        .editor-topbar {
            padding-top: calc(8px + env(safe-area-inset-top));
        }
        .editor-bottom {
            padding-bottom: calc(8px + env(safe-area-inset-bottom));
        }
    }
    /* End @media (max-width: 768px) */

    /* ============ SMALL MOBILE (≤390px) ============ */
    @media (max-width: 390px) {
        .editor-topbar {
            padding: 6px 8px !important;
            gap: 6px !important;
        }
        .btn--ghost.btn--sm {
            padding: 6px 8px;
        }
        .btn--ghost.btn--sm i {
            width: 18px;
            height: 18px;
        }
        .floor-selector {
            padding: 3px 4px;
            gap: 3px;
        }
        .floor-btn {
            width: 26px;
            height: 26px;
        }
        .floor-display {
            font-size: 11px;
            min-width: 45px;
        }
        .floating-toolbar {
            padding: 8px 12px;
        }
        .floating-tool-btn {
            width: 40px;
            height: 40px;
            min-width: 40px;
        }
        .floating-tool-btn i {
            width: 20px;
            height: 20px;
        }
        .mobile-sidebar-toggle {
            width: 48px;
            height: 48px;
            bottom: calc(12px + env(safe-area-inset-bottom));
            right: 12px;
        }
        .mobile-sidebar-toggle i {
            width: 20px;
            height: 20px;
        }
        .sidebar {
            height: 60vh !important;
            max-height: 60vh !important;
        }
        .message {
            max-width: 95%;
            font-size: 12px;
        }
        .chat-input {
            font-size: 12px;
        }
        
        /* Small screen adjustments */
        .floating-toolbar {
            bottom: calc(12px + env(safe-area-inset-bottom)) !important;
            padding: 8px 12px;
        }
        .mobile-action-bar {
            bottom: calc(68px + env(safe-area-inset-bottom));
            padding: 4px 6px;
            gap: 2px;
        }
        .mobile-action-btn {
            width: 36px;
            height: 36px;
        }
        .mobile-placement-controls {
            bottom: calc(68px + env(safe-area-inset-bottom));
        }
        .mobile-place-btn {
            padding: 8px 12px;
            font-size: 12px;
        }
    }
</style>
@endpush
