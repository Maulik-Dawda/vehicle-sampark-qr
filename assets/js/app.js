/**
 * Vehicle Sampark - Professional Frontend JavaScript Application
 * Handles Wizard Modal, Dynamic Form Builder, Tag Previews & Database Search
 */

document.addEventListener('DOMContentLoaded', () => {
    initModalHandlers();
    initDatabaseSearch();
    initGeneratorWizard();
    initFileDropzonePreviews();
});

function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
    toast.innerHTML = `
        <i class="fa-solid ${icon}"></i>
        <span>${escapeHtml(message)}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/* ==========================================================================
   Modals Engine
   ========================================================================== */

function initModalHandlers() {
    const modals = document.querySelectorAll('.modal-backdrop');
    
    modals.forEach(modal => {
        const closeBtns = modal.querySelectorAll('.modal-close, [data-modal-close]');
        closeBtns.forEach(btn => {
            btn.addEventListener('click', () => closeModal(modal));
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal(modal);
        });
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modal) {
    if (typeof modal === 'string') {
        modal = document.getElementById(modal);
    }
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

/* ==========================================================================
   Database-Wide Search Handling
   ========================================================================== */

function initDatabaseSearch() {
    const searchInputs = document.querySelectorAll('.search-input');
    
    searchInputs.forEach(input => {
        const form = input.closest('form');
        if (!form) return;

        let searchDebounceTimer;

        input.addEventListener('input', () => {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => {
                form.submit();
            }, 500);
        });
    });
}

/* ==========================================================================
   QR Code Generator Wizard & Form Builder
   ========================================================================== */

let formFieldsState = [];

function initGeneratorWizard() {
    const btnOpen = document.getElementById('btnOpenGenerator');
    const wizardModal = document.getElementById('generatorModal');
    if (!btnOpen || !wizardModal) return;

    btnOpen.addEventListener('click', () => {
        resetWizard();
        openModal('generatorModal');
    });

    const step1 = document.getElementById('wizardStep1');
    const step2 = document.getElementById('wizardStep2');
    const btnNext = document.getElementById('btnWizardNext');
    const btnBack = document.getElementById('btnWizardBack');
    const btnSubmit = document.getElementById('btnWizardSubmit');

    if (btnNext) {
        btnNext.addEventListener('click', () => {
            const countInput = document.getElementById('qrQuantity');
            const titleInput = document.getElementById('formTitle');

            if (!countInput || !countInput.value || parseInt(countInput.value) < 1) {
                showToast('Please enter a valid number of QR codes to generate (at least 1)', 'error');
                countInput.focus();
                return;
            }

            if (!titleInput || !titleInput.value.trim()) {
                showToast('Please enter a Form Title', 'error');
                titleInput.focus();
                return;
            }

            step1.style.display = 'none';
            step2.style.display = 'block';
            btnNext.style.display = 'none';
            btnBack.style.display = 'inline-flex';
            btnSubmit.style.display = 'inline-flex';

            // Pre-seed exact fields requested by user:
            // Full Name, Mobile Number, WhatsApp Number, Car Name, Car Number, Car Model
            if (formFieldsState.length === 0) {
                addFormField('text', 'Full Name');
                addFormField('mobile', 'Mobile Number');
                addFormField('mobile', 'WhatsApp Number');
                addFormField('text', 'Car Name / Make (e.g. Toyota)');
                addFormField('text', 'Car Number / Plate #');
                addFormField('text', 'Car Model (e.g. Fortuner)');
            }
        });
    }

    if (btnBack) {
        btnBack.addEventListener('click', () => {
            step1.style.display = 'block';
            step2.style.display = 'none';
            btnNext.style.display = 'inline-flex';
            btnBack.style.display = 'none';
            btnSubmit.style.display = 'none';
        });
    }

    const paletteBtns = document.querySelectorAll('.btn-field-add');
    paletteBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.getAttribute('data-type');
            addFormField(type);
        });
    });

    if (btnSubmit) {
        btnSubmit.addEventListener('click', async () => {
            const quantity = parseInt(document.getElementById('qrQuantity').value);
            const formTitle = document.getElementById('formTitle').value.trim();
            const formDescription = document.getElementById('formDescription').value.trim();

            if (formFieldsState.length === 0) {
                showToast('Please add at least one field to your dynamic form', 'error');
                return;
            }

            syncFieldsStateFromDOM();

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';

            try {
                const response = await fetch('api.php?action=create_batch', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        quantity: quantity,
                        form_title: formTitle,
                        form_description: formDescription,
                        fields: formFieldsState
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showToast(`Successfully created batch with ${quantity} Vehicle QR Tags!`, 'success');
                    closeModal('generatorModal');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);
                } else {
                    showToast(result.message || 'Failed to create QR code batch', 'error');
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Generate QR Codes';
                }
            } catch (err) {
                console.error(err);
                showToast('Network error while creating batch', 'error');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Generate QR Codes';
            }
        });
    }
}

function resetWizard() {
    formFieldsState = [];
    document.getElementById('qrQuantity').value = '5';
    document.getElementById('formTitle').value = 'Vehicle QR Tag Registration';
    document.getElementById('formDescription').value = 'Scan QR code to register vehicle owner details for direct call and WhatsApp contact.';
    
    document.getElementById('wizardStep1').style.display = 'block';
    document.getElementById('wizardStep2').style.display = 'none';
    document.getElementById('btnWizardNext').style.display = 'inline-flex';
    document.getElementById('btnWizardBack').style.display = 'none';
    document.getElementById('btnWizardSubmit').style.display = 'none';
    
    renderFieldsList();
}

function addFormField(type, defaultLabel = '') {
    const fieldId = 'field_' + Math.random().toString(36).substr(2, 9);
    
    let label = defaultLabel;
    if (!label) {
        const typeLabels = {
            'text': 'Short Text',
            'textarea': 'Long Description',
            'email': 'Email Address',
            'mobile': 'Mobile / Phone Number',
            'radio': 'Select Choice (Radio)',
            'checkbox': 'Checkbox Option',
            'dropdown': 'Dropdown Menu',
            'image': 'Upload Image (Photo)',
            'file': 'Upload File (Document)',
            'date': 'Date Picker'
        };
        label = typeLabels[type] || 'Field Label';
    }

    const newField = {
        id: fieldId,
        type: type,
        label: label,
        required: true,
        options: (type === 'radio' || type === 'dropdown') ? ['Option 1', 'Option 2'] : []
    };

    formFieldsState.push(newField);
    renderFieldsList();
}

function renderFieldsList() {
    const container = document.getElementById('builderFieldsContainer');
    if (!container) return;

    if (formFieldsState.length === 0) {
        container.innerHTML = `
            <div class="empty-state" style="padding: 2rem 1rem;">
                <p style="color: var(--text-muted);">No form fields added yet. Click a field type above to start building.</p>
            </div>
        `;
        return;
    }

    container.innerHTML = '';

    formFieldsState.forEach((field, index) => {
        const card = document.createElement('div');
        card.className = 'builder-field-card';
        card.setAttribute('data-field-id', field.id);

        let optionsHTML = '';
        if (field.type === 'radio' || field.type === 'dropdown') {
            optionsHTML = `
                <div class="options-builder">
                    <label class="form-label" style="font-size: 0.8rem; margin-top: 0.3rem;">Options (One per line or add button):</label>
                    <div id="optionsList_${field.id}" style="display: flex; flex-direction: column; gap: 0.4rem;">
                        ${field.options.map((opt, optIndex) => `
                            <div class="option-row" style="display: flex; gap: 0.5rem;">
                                <input type="text" class="form-control opt-input" value="${escapeHtml(opt)}" placeholder="Option text">
                                <button type="button" class="btn btn-outline btn-sm btn-icon" onclick="removeFieldOption('${field.id}', ${optIndex})">
                                    <i class="fa-solid fa-times"></i>
                                </button>
                            </div>
                        `).join('')}
                    </div>
                    <button type="button" class="btn btn-outline btn-sm" style="margin-top: 0.4rem; align-self: flex-start;" onclick="addFieldOption('${field.id}')">
                        <i class="fa-solid fa-plus"></i> Add Option
                    </button>
                </div>
            `;
        }

        card.innerHTML = `
            <div class="builder-field-header">
                <span class="field-type-badge"><i class="fa-solid ${getFieldIcon(field.type)}"></i> ${field.type}</span>
                <button type="button" class="field-remove-btn" title="Delete Field" onclick="deleteFormField('${field.id}')">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
            <div class="form-group" style="margin-bottom: 0.5rem;">
                <input type="text" class="form-control field-label-input" value="${escapeHtml(field.label)}" placeholder="Enter Question / Field Label">
            </div>
            ${optionsHTML}
        `;

        container.appendChild(card);
    });
}

function getFieldIcon(type) {
    const icons = {
        'text': 'fa-font',
        'textarea': 'fa-align-left',
        'email': 'fa-envelope',
        'mobile': 'fa-phone',
        'radio': 'fa-circle-dot',
        'checkbox': 'fa-square-check',
        'dropdown': 'fa-caret-down',
        'image': 'fa-image',
        'file': 'fa-file-arrow-up',
        'date': 'fa-calendar-days'
    };
    return icons[type] || 'fa-sliders';
}

function deleteFormField(fieldId) {
    formFieldsState = formFieldsState.filter(f => f.id !== fieldId);
    renderFieldsList();
}

function addFieldOption(fieldId) {
    syncFieldsStateFromDOM();
    const field = formFieldsState.find(f => f.id === fieldId);
    if (field) {
        field.options.push(`Option ${field.options.length + 1}`);
        renderFieldsList();
    }
}

function removeFieldOption(fieldId, optIndex) {
    syncFieldsStateFromDOM();
    const field = formFieldsState.find(f => f.id === fieldId);
    if (field && field.options.length > 1) {
        field.options.splice(optIndex, 1);
        renderFieldsList();
    } else {
        showToast('At least one option is required', 'error');
    }
}

function syncFieldsStateFromDOM() {
    formFieldsState.forEach(field => {
        const card = document.querySelector(`.builder-field-card[data-field-id="${field.id}"]`);
        if (card) {
            const labelInput = card.querySelector('.field-label-input');
            if (labelInput) {
                field.label = labelInput.value.trim() || field.label;
            }

            if (field.type === 'radio' || field.type === 'dropdown') {
                const optInputs = card.querySelectorAll('.opt-input');
                field.options = Array.from(optInputs).map(inp => inp.value.trim()).filter(val => val !== '');
                if (field.options.length === 0) field.options = ['Option 1'];
            }
        }
    });
}

/* ==========================================================================
   Physical Tag Card Preview & Modal Display
   ========================================================================== */

async function viewQRCodeModal(codeNumber) {
    const modal = document.getElementById('qrViewModal');
    if (!modal) return;

    document.getElementById('qrCodeTitle').innerText = `Vehicle Tag (${codeNumber})`;
    document.getElementById('qrImageContainer').innerHTML = '<i class="fa-solid fa-spinner fa-spin fa-2x" style="color: var(--primary);"></i>';

    openModal('qrViewModal');

    try {
        const response = await fetch(`api.php?action=get_qrcode&code=${encodeURIComponent(codeNumber)}`);
        const result = await response.json();

        if (result.success) {
            const data = result.data;

            if (data.tag_html) {
                document.getElementById('qrImageContainer').innerHTML = `
                    <div style="overflow-x: auto; padding: 10px 0;">
                        ${data.tag_html}
                    </div>
                `;
            } else {
                document.getElementById('qrImageContainer').innerHTML = `
                    <img src="${data.qr_image}" alt="QR Code" style="width: 220px; height: 220px; border-radius: var(--radius-md); background: #ffffff; padding: 10px; border: 1px solid var(--border-color); display: inline-block;">
                `;
            }
            
            document.getElementById('qrScanUrlInput').value = data.scan_url;
            document.getElementById('qrDownloadBtn').href = data.download_url || `qr.php?code=${encodeURIComponent(codeNumber)}&download=1`;
            document.getElementById('qrDownloadBtn').setAttribute('download', `${codeNumber}.png`);
        } else {
            showToast('Failed to load QR code image', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Network error loading QR code', 'error');
    }
}

function copyScanUrl() {
    const urlInput = document.getElementById('qrScanUrlInput');
    if (urlInput) {
        urlInput.select();
        navigator.clipboard.writeText(urlInput.value);
        showToast('Scan URL copied to clipboard!', 'success');
    }
}

async function viewSubmissionDetails(codeNumber) {
    const modal = document.getElementById('detailsViewModal');
    if (!modal) return;

    document.getElementById('detailsModalTitle').innerText = `Registered Owner Details (${codeNumber})`;
    document.getElementById('detailsModalBody').innerHTML = `
        <div style="text-align: center; padding: 2rem;">
            <i class="fa-solid fa-spinner fa-spin fa-2x" style="color: var(--primary);"></i>
            <p style="margin-top: 0.5rem; color: var(--text-muted);">Loading registered owner details...</p>
        </div>
    `;

    openModal('detailsViewModal');

    try {
        const response = await fetch(`api.php?action=get_submission&code=${encodeURIComponent(codeNumber)}`);
        const result = await response.json();

        if (result.success) {
            const data = result.data;

            if (data.status === 'pending') {
                document.getElementById('detailsModalBody').innerHTML = `
                    <div class="empty-state" style="padding: 2rem;">
                        <i class="fa-solid fa-clock-rotate-left empty-icon" style="color: var(--accent-orange);"></i>
                        <h3>Unregistered Vehicle QR Code</h3>
                        <p style="margin-bottom: 1.5rem;">This QR code is pending first-time scan and registration by the vehicle owner.</p>
                        <a href="${data.scan_url}" target="_blank" class="btn btn-outline">
                            <i class="fa-solid fa-external-link"></i> Open Registration Page
                        </a>
                    </div>
                `;
            } else {
                let fieldsHTML = '';
                const responses = data.responses || {};

                for (const [label, val] of Object.entries(responses)) {
                    let displayVal = escapeHtml(val);
                    
                    if (typeof val === 'string' && (val.startsWith('uploads/') || val.match(/\.(jpg|jpeg|png|gif|webp)$/i))) {
                        displayVal = `
                            <div style="margin-top: 0.4rem;">
                                <a href="${val}" target="_blank">
                                    <img src="${val}" style="max-width: 180px; max-height: 180px; border-radius: var(--radius-md); border: 1px solid var(--border-color);" alt="Upload">
                                </a>
                                <br>
                                <a href="${val}" target="_blank" class="btn btn-outline btn-sm" style="margin-top: 0.4rem;">
                                    <i class="fa-solid fa-download"></i> View / Download
                                </a>
                            </div>
                        `;
                    } else if (typeof val === 'string' && val.match(/\.(pdf|doc|docx|txt|zip)$/i)) {
                        displayVal = `
                            <a href="${val}" target="_blank" class="btn btn-outline btn-sm" style="margin-top: 0.4rem;">
                                <i class="fa-solid fa-file-lines"></i> View Document (${val.split('/').pop()})
                            </a>
                        `;
                    }

                    fieldsHTML += `
                        <div class="form-group" style="background: #f8fafc; padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                            <label class="form-label" style="color: var(--primary);">${escapeHtml(label)}</label>
                            <div style="font-size: 0.95rem; color: #0f172a; font-weight: 600;">${displayVal}</div>
                        </div>
                    `;
                }

                document.getElementById('detailsModalBody').innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                        <div>
                            <span class="badge badge-submitted"><i class="fa-solid fa-shield-halved"></i> Vehicle Registered</span>
                            <span style="font-size: 0.85rem; color: var(--text-muted); margin-left: 0.75rem;">
                                <i class="fa-regular fa-calendar"></i> ${data.submitted_at}
                            </span>
                        </div>
                        <a href="download_pdf.php?code=${encodeURIComponent(codeNumber)}" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-file-pdf"></i> Download PDF Receipt
                        </a>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        ${fieldsHTML}
                    </div>
                `;
            }
        }
    } catch (err) {
        console.error(err);
        showToast('Error fetching registration details', 'error');
    }
}

function initFileDropzonePreviews() {
    const fileInputs = document.querySelectorAll('.file-dropzone input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', (e) => {
            const file = e.target.files[0];
            const dropzone = input.closest('.file-dropzone');
            const previewBox = dropzone.querySelector('.file-preview');

            if (file && previewBox) {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        previewBox.innerHTML = `
                            <img src="${event.target.result}" alt="Preview">
                            <span style="font-size: 0.85rem; color: #0f172a;">${escapeHtml(file.name)} (${(file.size / 1024).toFixed(1)} KB)</span>
                        `;
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewBox.innerHTML = `
                        <i class="fa-solid fa-file-check" style="font-size: 1.5rem; color: var(--primary);"></i>
                        <span style="font-size: 0.85rem; color: #0f172a;">${escapeHtml(file.name)} (${(file.size / 1024).toFixed(1)} KB)</span>
                    `;
                }
            }
        });
    });
}
