/**
 * Contract Evaluation - Dispatch Drop Zone
 * Allows bulk PDF upload with automatic filename-based worker matching
 */

let dispatchQueue = [];
let workersList = [];

/**
 * Initialize the dispatch drop zone functionality
 * @param {Array} workers - Array of worker objects with id, firstname, lastname, fullname
 */
function initializeDispatchZone(workers) {
    workersList = workers.map(w => ({
        ...w,
        firstname: w.firstname.toLowerCase().trim(),
        lastname: w.lastname.toLowerCase().trim()
    }));

    const dispatchZone = document.getElementById('dispatch-zone');
    if (!dispatchZone) return;

    // Prevent default drag behavior
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dispatchZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    // Highlight drop zone when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dispatchZone.addEventListener(eventName, () => {
            dispatchZone.classList.add('drag-over');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dispatchZone.addEventListener(eventName, () => {
            dispatchZone.classList.remove('drag-over');
        }, false);
    });

    // Handle dropped files
    dispatchZone.addEventListener('drop', handleDispatchDrop, false);

    // Also allow clicking to select files
    dispatchZone.addEventListener('click', function(e) {
        // Don't trigger file browser when clicking on buttons, selects, or any interactive elements
        if (e.target.closest('#dispatch-preview') ||
            e.target.closest('button') ||
            e.target.closest('select') ||
            e.target.tagName === 'BUTTON' ||
            e.target.tagName === 'SELECT') {
            return;
        }

        const input = document.createElement('input');
        input.type = 'file';
        input.multiple = true;
        input.accept = '.pdf';
        input.onchange = (e) => {
            handleDispatchDrop({ dataTransfer: { files: e.target.files } });
        };
        input.click();
    });
}

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

function handleDispatchDrop(e) {
    const files = Array.from(e.dataTransfer.files);
    const pdfFiles = files.filter(file =>
        file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')
    );

    if (pdfFiles.length === 0) {
        alert(window.translations?.selectPdfOnly || 'Please select PDF files only');
        return;
    }

    // Add to dispatch queue with matching
    pdfFiles.forEach(file => {
        const matchResult = matchFileToWorker(file);
        dispatchQueue.push(matchResult);
    });

    renderDispatchPreview();
}

/**
 * Match a file to a worker based on filename
 * @param {File} file - The file to match
 * @returns {Object} Match result object
 */
function matchFileToWorker(file) {
    const filename = file.name.toLowerCase()
        .replace('.pdf', '')
        .replace(/[_\-\s]+/g, ' ') // Normalize separators to spaces
        .trim();

    let matchStatus = 'unmatched';
    let workerContractId = null;
    let possibleMatches = [];

    // Try to find matches
    workersList.forEach(worker => {
        let shouldAdd = false;

        const firstnameMatch = filename.includes(worker.firstname);
        const lastnameMatch = filename.includes(worker.lastname);
        const fullnameMatch = filename.includes(worker.firstname + ' ' + worker.lastname);

        if (fullnameMatch || (firstnameMatch && lastnameMatch)) {
            shouldAdd = true;
        }
        else if (firstnameMatch || lastnameMatch) {
            shouldAdd = true;
        }
        else {
            const nameParts = [...worker.firstname.split(' '), ...worker.lastname.split(' ')];
            if (nameParts.some(part => part && filename.includes(part))) {
                shouldAdd = true;
            }
        }

        if (shouldAdd) {
            possibleMatches.push(worker);
        }
    });

    // Determine match status
    if (possibleMatches.length === 1) {
        matchStatus = 'matched';
        workerContractId = possibleMatches[0].id;
    } else if (possibleMatches.length > 1) {
        matchStatus = 'ambiguous';
    } else {
        matchStatus = 'unmatched';
    }

    return {
        file: file,
        filename: file.name,
        matchStatus: matchStatus,
        workerContractId: workerContractId,
        possibleMatches: possibleMatches,
        uploading: false,
        uploaded: false
    };
}

function renderDispatchPreview() {
    const dispatchZone = document.getElementById('dispatch-zone');
    const emptyState = document.getElementById('dispatch-empty-state');
    const preview = document.getElementById('dispatch-preview');
    const filesList = document.getElementById('dispatch-files-list');
    const countBadge = document.getElementById('dispatch-count');
    const matchedCountBadge = document.getElementById('matched-count');

    if (dispatchQueue.length === 0) {
        emptyState.classList.remove('hidden');
        preview.classList.add('hidden');
        dispatchZone.classList.remove('has-files');
        return;
    }

    emptyState.classList.add('hidden');
    preview.classList.remove('hidden');
    dispatchZone.classList.add('has-files');

    countBadge.textContent = dispatchQueue.length;
    const matchedCount = dispatchQueue.filter(f => f.matchStatus === 'matched' && !f.uploaded).length;
    matchedCountBadge.textContent = matchedCount;

    filesList.innerHTML = '';

    dispatchQueue.forEach((fileMatch, index) => {
        const item = createDispatchFileItem(fileMatch, index);
        filesList.appendChild(item);
    });
}

function createDispatchFileItem(fileMatch, index) {
    const div = document.createElement('div');
    div.className = 'dispatch-file-item bg-base-200 p-3 rounded-lg flex items-center justify-between gap-3';

    let statusIcon = '';
    let statusClass = '';
    let workerAssignment = '';

    const t = window.translations || {};

    if (fileMatch.uploaded) {
        statusIcon = '<i class="fas fa-check-circle text-success"></i>';
        statusClass = 'opacity-50';
        const worker = workersList.find(w => w.id === fileMatch.workerContractId);
        workerAssignment = `<span class="text-sm text-success">${worker.fullname} ✓</span>`;
    } else if (fileMatch.uploading) {
        statusIcon = '<i class="fas fa-spinner fa-spin text-primary"></i>';
        workerAssignment = `<span class="text-sm text-primary">${t.uploading || 'Uploading...'}</span>`;
    } else if (fileMatch.matchStatus === 'matched') {
        statusIcon = '<i class="fas fa-check-circle match-status-matched"></i>';
        statusClass = 'match-status-matched';
        const worker = workersList.find(w => w.id === fileMatch.workerContractId);
        workerAssignment = `<span class="text-sm">${worker.fullname}</span>`;
    } else if (fileMatch.matchStatus === 'ambiguous') {
        statusIcon = '<i class="fas fa-exclamation-triangle match-status-ambiguous"></i>';
        statusClass = 'match-status-ambiguous';
        workerAssignment = createWorkerDropdown(fileMatch, index);
    } else {
        statusIcon = '<i class="fas fa-times-circle match-status-unmatched"></i>';
        statusClass = 'match-status-unmatched';
        workerAssignment = createWorkerDropdown(fileMatch, index);
    }

    div.innerHTML = `
        <div class="flex items-center gap-3 flex-1 min-w-0">
            ${statusIcon}
            <div class="flex-1 min-w-0">
                <div class="font-medium truncate ${statusClass}">${fileMatch.filename}</div>
                <div class="text-xs">${workerAssignment}</div>
            </div>
        </div>
        <div class="flex gap-2 items-center">
            ${!fileMatch.uploaded && !fileMatch.uploading ? `
                <button type="button"
                        class="btn btn-success btn-xs dispatch-upload-btn"
                        data-index="${index}"
                        ${fileMatch.matchStatus === 'matched' ? '' : 'disabled'}>
                    <i class="fas fa-upload"></i>
                </button>
                <button type="button"
                        class="btn btn-error btn-xs dispatch-remove-btn"
                        data-index="${index}">
                    <i class="fas fa-times"></i>
                </button>
            ` : ''}
        </div>
    `;

    // Add event listeners after creating the element
    setTimeout(() => {
        const uploadBtn = div.querySelector('.dispatch-upload-btn');
        const removeBtn = div.querySelector('.dispatch-remove-btn');

        if (uploadBtn) {
            uploadBtn.addEventListener('click', () => uploadSingleDispatchedFile(index));
        }
        if (removeBtn) {
            removeBtn.addEventListener('click', () => removeFromDispatchQueue(index));
        }
    }, 0);

    return div;
}

function createWorkerDropdown(fileMatch, index) {
    const selectId = `worker-select-${index}`;
    const t = window.translations || {};
    let options = `<option value="">${t.selectWorker || 'Select worker...'}</option>`;

    const workersToShow = fileMatch.possibleMatches.length > 0 ? fileMatch.possibleMatches : workersList;

    workersToShow.forEach(worker => {
        options += `<option value="${worker.id}">${worker.fullname}</option>`;
    });

    setTimeout(() => {
        const select = document.getElementById(selectId);
        if (select) {
            select.addEventListener('change', function() {
                assignWorkerToFile(index, this.value);
            });
        }
    }, 0);

    return `<select id="${selectId}" class="select select-xs select-bordered w-full max-w-xs">${options}</select>`;
}

function assignWorkerToFile(index, workerId) {
    if (!workerId) return;

    dispatchQueue[index].workerContractId = workerId;
    dispatchQueue[index].matchStatus = 'matched';
    renderDispatchPreview();
}

/**
 * Upload a single file from the dispatch queue
 * @param {number} index - Index in the dispatch queue
 */
function uploadSingleDispatchedFile(index) {
    const fileMatch = dispatchQueue[index];
    const t = window.translations || {};

    if (!fileMatch.workerContractId) {
        alert(t.assignWorkerFirst || 'Please assign a worker first');
        return;
    }

    if (fileMatch.uploading || fileMatch.uploaded) {
        return;
    }

    fileMatch.uploading = true;
    renderDispatchPreview();

    const container = document.querySelector(`[data-worker-contract-id="${fileMatch.workerContractId}"]`);

    if (!container) {
        alert(t.workerContainerNotFound || 'Worker container not found');
        fileMatch.uploading = false;
        renderDispatchPreview();
        return;
    }

    // Build form data
    const formData = new FormData();
    formData.append('file', fileMatch.file);
    formData.append('worker_contract_id', fileMatch.workerContractId);
    formData.append('_token', window.csrfToken || document.querySelector('[name="_token"]')?.value || '');

    fetch(window.uploadUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }

        // Add attachment to the worker's container using existing function
        if (typeof window.addAttachmentToContainer === 'function') {
            window.addAttachmentToContainer(container, {
                id: data.id,
                name: data.name,
                size: data.size
            });
        }

        fileMatch.uploading = false;
        fileMatch.uploaded = true;
        renderDispatchPreview();
    })
    .catch(error => {
        console.error('Upload error:', error);
        alert((t.uploadFailed || 'Upload failed:') + ' ' + error.message);
        fileMatch.uploading = false;
        renderDispatchPreview();
    });
}

/**
 * Upload all matched files in the dispatch queue
 */
function uploadAllMatched() {
    const t = window.translations || {};
    const matchedFiles = dispatchQueue.filter(f =>
        f.matchStatus === 'matched' &&
        !f.uploaded &&
        !f.uploading &&
        f.workerContractId
    );

    if (matchedFiles.length === 0) {
        alert(t.noMatchedFiles || 'No matched files to upload');
        return;
    }

    matchedFiles.forEach((fileMatch, idx) => {
        const index = dispatchQueue.indexOf(fileMatch);
        // Stagger uploads slightly to avoid overwhelming the server
        setTimeout(() => {
            uploadSingleDispatchedFile(index);
        }, idx * 200);
    });
}

/**
 * Remove a file from the dispatch queue
 * @param {number} index - Index in the dispatch queue
 */
function removeFromDispatchQueue(index) {
    dispatchQueue.splice(index, 1);
    renderDispatchPreview();
}

/**
 * Clear all files from the dispatch zone
 */
function clearDispatchZone() {
    const t = window.translations || {};
    if (dispatchQueue.some(f => f.uploading)) {
        if (!confirm(t.clearWhileUploading || 'Some files are still uploading. Are you sure you want to clear?')) {
            return;
        }
    }
    dispatchQueue = [];
    renderDispatchPreview();
}

// Export for global access (for onclick handlers in blade)
// Export for global access
if (typeof window !== 'undefined') {
    window.initializeDispatchZone = initializeDispatchZone;
    window.uploadAllMatched = uploadAllMatched;
    window.clearDispatchZone = clearDispatchZone;
    window.uploadSingleDispatchedFile = uploadSingleDispatchedFile;
    window.removeFromDispatchQueue = removeFromDispatchQueue;
}
