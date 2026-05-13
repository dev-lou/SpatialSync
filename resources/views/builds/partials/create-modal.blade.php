<style>
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--space-4);
    opacity: 0;
    visibility: hidden;
    transition: opacity var(--dur-base), visibility var(--dur-base);
}

.modal-backdrop.active {
    opacity: 1;
    visibility: visible;
}

.modal {
    background: var(--surface);
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-2xl);
    width: 100%;
    max-width: 900px;
    max-height: 85vh;
    overflow-x: hidden;
    overflow-y: auto;
    transform: scale(0.95) translateY(20px);
    transition: transform var(--dur-base) var(--ease-spring);
}

@media (max-width: 768px) {
    .modal {
        max-width: calc(100vw - 32px);
    }
}

.modal-backdrop.active .modal {
    transform: scale(1) translateY(0);
}

.modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--space-6);
    border-bottom: 1px solid var(--border-default);
}

.modal__title {
    font-size: var(--text-xl);
    font-weight: 600;
    color: var(--text-primary);
}

.modal__close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border: none;
    background: var(--bg-secondary);
    border-radius: var(--radius-lg);
    color: var(--text-secondary);
    cursor: pointer;
    transition: background-color var(--dur-base), color var(--dur-base);
}

.modal__close:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
}

.modal__body {
    padding: var(--space-6);
    overflow-x: hidden;
}

.modal__footer {
    display: flex;
    gap: var(--space-3);
    justify-content: flex-end;
    padding: var(--space-6);
    border-top: 1px solid var(--border-default);
    background: var(--bg-secondary);
    border-radius: 0 0 var(--radius-2xl) var(--radius-2xl);
}

/* ── TEMPLATE PICKER ─────────────────────────── */
.template-picker {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--space-3);
    margin-bottom: var(--space-6);
}

@media (max-width: 768px) {
    .template-picker {
        grid-template-columns: repeat(2, 1fr);
    }
}

.template-option {
    position: relative;
    padding: var(--space-4);
    background: var(--bg-secondary);
    border: 2px solid var(--border-default);
    border-radius: var(--radius-lg);
    cursor: pointer;
    transition: border-color var(--dur-base), box-shadow var(--dur-base), transform var(--dur-base);
    text-align: center;
}

.template-option:hover {
    border-color: var(--border-strong);
    transform: translateY(-2px);
}

.template-option.selected {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-light);
    transform: translateY(-2px);
}

.template-option__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    margin: 0 auto var(--space-2);
    background: var(--surface);
    color: var(--accent);
    border-radius: var(--radius-lg);
}

.template-option__label {
    font-size: var(--text-sm);
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: var(--space-1);
}

.template-option__description {
    font-size: var(--text-xs);
    color: var(--text-tertiary);
}

.template-option__check {
    position: absolute;
    top: var(--space-2);
    right: var(--space-2);
    width: 1.25rem;
    height: 1.25rem;
    background: var(--accent);
    color: white;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: scale(0.5);
    transition: opacity var(--dur-base), transform var(--dur-base);
}

.template-option.selected .template-option__check {
    opacity: 1;
    transform: scale(1);
}

/* ── FAB BUTTON ──────────────────────────────── */
.fab {
    position: fixed;
    bottom: var(--space-8);
    right: var(--space-8);
    width: 4rem;
    height: 4rem;
    background: var(--accent);
    color: white;
    border: none;
    border-radius: var(--radius-full);
    box-shadow: var(--shadow-xl), 0 0 20px rgba(0, 102, 255, 0.3);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform var(--dur-base) var(--ease-spring), box-shadow var(--dur-base);
    z-index: 100;
}

.fab:hover {
    transform: scale(1.1);
    box-shadow: var(--shadow-2xl), 0 0 30px rgba(0, 102, 255, 0.4);
}

.fab i {
    width: 1.5rem;
    height: 1.5rem;
}

@media (max-width: 767px) {
    .fab {
        bottom: var(--space-4);
        right: var(--space-4);
    }
}
</style>
</style>

    <!-- Create Build Modal -->
    <div class="modal-backdrop" :class="{ 'active': showModal }" @click.self="closeModal()">
        <div class="modal" @click.stop>
            <div class="modal__header">
                <h3 class="modal__title">Create New Build</h3>
                <button type="button" class="modal__close" @click="closeModal()">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <form action="{{ route('builds.store') }}" method="POST" @submit="handleSubmit($event)">
                @csrf
                <div class="modal__body">
                    <input type="hidden" name="template" value="blank">
                    
                    <!-- Build Name -->
                    <div class="form-group">
                        <label for="build-name" class="form-label">Build name</label>
                        <input 
                            type="text" 
                            id="build-name" 
                            name="name" 
                            class="form-input" 
                            placeholder="e.g., Modern Office Layout"
                            required
                            x-model="name"
                            autocomplete="off"
                            x-ref="nameInput"
                        >
                    </div>
                    
                    <!-- Description (optional) -->
                    <div class="form-group">
                        <label for="build-description" class="form-label">
                            Description <span class="text-tertiary">(optional)</span>
                        </label>
                        <textarea 
                            id="build-description" 
                            name="description" 
                            class="form-textarea" 
                            rows="2"
                            placeholder="Brief description of this build..."
                            x-model="description"
                        ></textarea>
                    </div>
                </div>
                
                <div class="modal__footer">
                    <button type="button" class="btn btn--secondary" @click="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn--primary btn-glow" :disabled="!name.trim() || isSubmitting" style="display: flex; align-items: center; justify-content: center; gap: 8px; min-width: 140px;">
                        <template x-if="!isSubmitting">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                <span>Create Build</span>
                            </div>
                        </template>
                        <template x-if="isSubmitting">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                                <span>Creating...</span>
                            </div>
                        </template>
                    </button>
                </div>
            </form>
        </div>
    </div>


<script>
function createBuildModalApp() {
    return {
        showModal: false,
        template: 'blank',
        name: '',
        description: '',
        isSubmitting: false,
        
        openModal() {
            this.showModal = true;
            this.template = 'blank';
            this.name = '';
            this.description = '';
            document.body.style.overflow = 'hidden';
            
            // Focus the name input after modal opens
            this.$nextTick(() => {
                this.$refs.nameInput?.focus();
                lucide.createIcons();
            });
        },
        
        closeModal() {
            this.showModal = false;
            document.body.style.overflow = '';
        },
        
        handleSubmit(e) {
            if (!this.name.trim()) {
                e.preventDefault();
                return;
            }
            this.isSubmitting = true;
        }
    }
}

// Close modal on escape key
</script>