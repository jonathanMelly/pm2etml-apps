{{--
 After some trials, to avoid manually handling json data (with old(...) feature), checkboxes have been 'duplicated'
 with hidden fields so the data, even if false, is still kept...
 The best would be to have a 2 options (false/true) radio button with toggle UI...
 --}}
<x-app-layout>
    @push('custom-scripts')
        <style>
            .upload-container {
                border: 2px dashed transparent;
                transition: all 0.2s ease;
                padding: 8px;
                border-radius: 4px;
            }

            .upload-container:hover {
                border-color: hsl(var(--primary));
                background-color: hsl(var(--base-200));
            }

            .upload-container.border-primary {
                border-color: hsl(var(--primary)) !important;
                background-color: hsl(var(--base-100)) !important;
            }

            /* Custom grade selector styling */
            .grade-selector {
                display: flex;
                gap: 0.5rem;
                justify-content: center;
                align-items: center;
                position: relative;
            }

            .grade-option {
                position: relative;
                cursor: pointer;
                z-index: 1;
            }

            .grade-option input[type="radio"] {
                position: absolute;
                opacity: 0;
                width: 0;
                height: 0;
            }

            .grade-circle {
                width: 3rem;
                height: 3rem;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 0.875rem;
                border: 3px solid hsl(var(--bc) / 0.2);
                background-color: hsl(var(--b1));
                color: hsl(var(--bc) / 0.5);
                transition: all 0.2s ease;
            }

            .grade-option:hover .grade-circle {
                border-color: hsl(var(--bc) / 0.4);
                transform: scale(1.05);
            }

            .grade-option input[type="radio"]:checked ~ .grade-circle {
                color: white;
                font-weight: 800;
                transform: scale(1.1);
            }

            .grade-option input[type="radio"]:checked ~ .grade-circle.grade-na {
                background-color: hsl(var(--er));
                border-color: hsl(var(--er));
            }

            .grade-option input[type="radio"]:checked ~ .grade-circle.grade-pa {
                background-color: hsl(var(--wa));
                border-color: hsl(var(--wa));
            }

            .grade-option input[type="radio"]:checked ~ .grade-circle.grade-a {
                background-color: hsl(var(--su));
                border-color: hsl(var(--su));
            }

            .grade-option input[type="radio"]:checked ~ .grade-circle.grade-la {
                background-color: hsl(var(--su));
                border-color: hsl(var(--su));
            }

            /* Connector line between circles */
            .grade-selector::before {
                content: '';
                position: absolute;
                height: 3px;
                background-color: hsl(var(--bc) / 0.1);
                width: calc(100% - 6rem);
                top: 50%;
                left: 3rem;
                transform: translateY(-50%);
                z-index: 0;
            }
        </style>
        <script>
            let contractsEvaluations = {};
            let attachmentsToDelete = [];

            function updateFormFields() {
                document.querySelector('[name=attachmentsToDelete]').value = JSON.stringify(attachmentsToDelete);
            }

            document.addEventListener("DOMContentLoaded", function () {
                initializePDFUploads();
            });

            function initializePDFUploads() {
                // Load existing attachments for each worker contract
                @foreach($contracts as $contract)
                    @foreach($contract->workersContracts as $workerContract)
                        @if($workerContract->evaluationAttachments->isNotEmpty())
                            @foreach($workerContract->evaluationAttachments as $attachment)
                            showExistingAttachment('{{$workerContract->id}}', {
                                id: '{{$attachment->id}}',
                                name: '{{$attachment->name}}',
                                size: {{$attachment->size}}
                            });
                            @endforeach
                        @endif
                    @endforeach
                @endforeach

                document.querySelectorAll('.pdf-file-input').forEach(input => {
                    const container = input.closest('.upload-container');
                    const progressDiv = container.querySelector('.upload-progress');
                    const progress = progressDiv.querySelector('progress');
                    const browseBtn = container.querySelector('.browse-pdf-btn');

                    // File input change handler
                    input.addEventListener('change', function(e) {
                        if (e.target.files.length > 0) {
                            uploadPDF(e.target.files[0], container);
                        }
                    });

                    // Drag and drop functionality
                    container.addEventListener('dragover', function(e) {
                        e.preventDefault();
                        container.classList.add('border-primary', 'bg-base-50');
                    });

                    container.addEventListener('dragleave', function(e) {
                        e.preventDefault();
                        container.classList.remove('border-primary', 'bg-base-50');
                    });

                    container.addEventListener('drop', function(e) {
                        e.preventDefault();
                        container.classList.remove('border-primary', 'bg-base-50');

                        const files = e.dataTransfer.files;
                        if (files.length > 0 && files[0].type === 'application/pdf') {
                            uploadPDF(files[0], container);
                        } else {
                            alert('{{__("Please select a PDF file")}}');
                        }
                    });
                });
            }

            function uploadPDF(file, container) {
                const formData = new FormData();
                const workerContractId = container.dataset.workerContractId;
                const progressDiv = container.querySelector('.upload-progress');
                const progress = progressDiv.querySelector('progress');
                const browseBtn = container.querySelector('.browse-pdf-btn');

                formData.append('file', file);
                formData.append('worker_contract_id', workerContractId);
                formData.append('_token', '{{csrf_token()}}');

                // Show progress
                progressDiv.classList.remove('hidden');
                browseBtn.disabled = true;

                fetch('{{route("contract-evaluation-attachment.store")}}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }

                    // Add new attachment to the list
                    addAttachmentToContainer(container, {
                        id: data.id,
                        name: data.name,
                        size: data.size
                    });

                    // Hide progress and reset file input
                    progressDiv.classList.add('hidden');
                    browseBtn.disabled = false;
                    container.querySelector('.pdf-file-input').value = '';
                })
                .catch(error => {
                    console.error('Upload error:', error);
                    alert('{{__("Upload failed:")}} ' + error.message);
                    progressDiv.classList.add('hidden');
                    browseBtn.disabled = false;
                });
            }

            function showExistingAttachment(workerContractId, attachment) {
                const container = document.querySelector(`[data-worker-contract-id="${workerContractId}"]`);
                if (container) {
                    addAttachmentToContainer(container, attachment);
                }
            }

            function addAttachmentToContainer(container, attachment) {
                const attachmentsList = container.querySelector('.attachments-list');
                const browseBtn = container.querySelector('.browse-pdf-btn');

                // Create attachment item
                const attachmentItem = document.createElement('div');
                attachmentItem.className = 'flex items-center justify-between text-xs bg-base-200 p-1 rounded mb-1';
                attachmentItem.dataset.attachmentId = attachment.id;

                attachmentItem.innerHTML = `
                    <span class="pdf-name">${attachment.name}</span>
                    <button type="button" class="btn btn-ghost btn-xs remove-pdf-btn">
                        <i class="fas fa-times"></i>
                    </button>
                `;

                // Add remove functionality to the new item
                attachmentItem.querySelector('.remove-pdf-btn').addEventListener('click', function() {
                    removeSpecificAttachment(attachmentItem);
                });

                attachmentsList.appendChild(attachmentItem);
                attachmentsList.classList.remove('hidden');

                // Update button text
                const currentCount = attachmentsList.children.length;
                browseBtn.textContent = currentCount > 0 ? '{{__("Add PDF")}}' : '{{__("Upload PDF")}}';
            }

            function removeSpecificAttachment(attachmentItem) {
                const attachmentName = attachmentItem.querySelector('.pdf-name').textContent;

                // Show confirmation dialog
                if (!confirm('{{__("Are you sure you want to delete this attachment?")}}' + '\n"' + attachmentName + '"')) {
                    return; // User cancelled, don't proceed with deletion
                }

                const attachmentId = attachmentItem.dataset.attachmentId;
                const container = attachmentItem.closest('.upload-container');
                const attachmentsList = container.querySelector('.attachments-list');
                const browseBtn = container.querySelector('.browse-pdf-btn');

                if (attachmentId) {
                    // Mark attachment for deletion (deferred until form save)
                    attachmentItem.style.opacity = '0.5';
                    attachmentItem.dataset.markedForDeletion = 'true';

                    // Add strikethrough to the filename
                    const pdfName = attachmentItem.querySelector('.pdf-name');
                    pdfName.style.textDecoration = 'line-through';

                    // Disable the remove button and change its appearance
                    const removeBtn = attachmentItem.querySelector('.remove-pdf-btn');
                    removeBtn.disabled = true;
                    removeBtn.style.opacity = '0.3';
                    removeBtn.style.cursor = 'not-allowed';

                    // Store attachment ID for deletion on form submit
                    if (!attachmentsToDelete.includes(attachmentId)) {
                        attachmentsToDelete.push(attachmentId);
                    }
                    updateFormFields();
                }

                // Update button text
                const visibleAttachments = Array.from(attachmentsList.children).filter(item =>
                    item.dataset.markedForDeletion !== 'true'
                );
                browseBtn.textContent = visibleAttachments.length > 0 ? '{{__("Add PDF")}}' : '{{__("Upload PDF")}}';
            }

            // Legacy function - kept for compatibility with old event listeners
            function removePDF(container) {
                // This function is no longer used but kept for compatibility
                console.warn('removePDF function is deprecated, use removeSpecificAttachment instead');
            }

            function submitEvaluationForm() {
                // Ensure form fields are up to date
                updateFormFields();
                console.log('Attachments to delete:', attachmentsToDelete);
                document.querySelector('#eval').submit();
            }

        </script>

        {{-- Dispatch Drop Zone Module --}}
        @vite(['resources/js/contract-dispatch.js'])
        <script type="module">
            // Build workers data from server
            const workers = [
                @foreach($contracts as $contract)
                    @foreach($contract->workersContracts as $workerContract)
                        {
                            id: '{{$workerContract->id}}',
                            firstname: '{{$workerContract->groupMember->user->firstname}}',
                            lastname: '{{$workerContract->groupMember->user->lastname}}',
                            fullname: '{{$workerContract->groupMember->user->getFirstnameL()}}'
                        },
                    @endforeach
                @endforeach
            ];

            // Set up global configuration
            window.csrfToken = '{{csrf_token()}}';
            window.uploadUrl = '{{route("contract-evaluation-attachment.store")}}';
            window.translations = {
                selectPdfOnly: '{{__("Please select PDF files only")}}',
                uploading: '{{__("Uploading...")}}',
                selectWorker: '{{__("Select worker...")}}',
                assignWorkerFirst: '{{__("Please assign a worker first")}}',
                workerContainerNotFound: '{{__("Worker container not found")}}',
                uploadFailed: '{{__("Upload failed:")}}',
                noMatchedFiles: '{{__("No matched files to upload")}}',
                clearWhileUploading: '{{__("Some files are still uploading. Are you sure you want to clear?")}}'
            };

            // Make addAttachmentToContainer globally available for dispatch module
            window.addAttachmentToContainer = addAttachmentToContainer;

            // Initialize dispatch zone
            initializeDispatchZone(workers);
        </script>
    @endpush
    <form id="eval" x-on:submit.prevent action="{{route('contracts.evaluate')}}" method="post">
        @csrf
        <input type="hidden" id="contractsEvaluations" name="contractsEvaluations" value="">
        <input type="hidden" id="attachmentsToDelete" name="attachmentsToDelete" value="">
        <div class="sm:mx-6 bg-base-200 bg-opacity-50 rounded-box sm:p-3 p-1 flex flex-col items-center">

            <div class="stats shadow mb-4">

                <div class="stat">
                    <div class="stat-figure text-secondary">
                        <div class="avatar">
                            <div class="w-24 rounded">
                                <img src="{{route('dmz-asset',['file'=>$job->image?->storage_path])}}" />
                            </div>
                        </div>
                    </div>
                    <div class="stat-title">
                        <i class="fa-solid fa-calendar-day"></i> {{\Illuminate\Support\Carbon::parse($contracts->min('start'))->format(\App\SwissFrenchDateFormat::DATE)}}
                        <i class="fa-solid fa-arrow-right"></i>
                        <i class="fa-solid fa-calendar-days"></i> {{\Illuminate\Support\Carbon::parse($contracts->max('end'))->format(\App\SwissFrenchDateFormat::DATE)}}
                    </div>
                    <div class="stat-value">{{$job->title}}</div>
                    <div class="stat-desc">{{trans_choice(":number evaluation|:number evaluations",sizeof($contracts),['number'=>sizeof($contracts)])}}</div>
                </div>

            </div>

            {{-- Dispatch Drop Zone --}}
            <div class="w-full max-w-4xl mb-6">
                <div id="dispatch-zone" class="dispatch-zone text-center cursor-pointer">
                    <div id="dispatch-empty-state">
                        <i class="fas fa-cloud-upload-alt text-4xl mb-2 opacity-50"></i>
                        <p class="text-lg font-semibold">{{__('Drop multiple PDFs here to auto-dispatch')}}</p>
                        <p class="text-sm opacity-70">{{__('Files will be matched by firstname or lastname')}}</p>
                        <p class="text-xs opacity-50 mt-2">{{__('No upload will happen until you confirm')}}</p>
                    </div>
                    <div id="dispatch-preview" class="hidden">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">
                                <i class="fas fa-list-check mr-2"></i>{{__('Files Ready for Dispatch')}}
                                <span id="dispatch-count" class="badge badge-primary ml-2">0</span>
                            </h3>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="clearDispatchZone()">
                                <i class="fas fa-times mr-1"></i>{{__('Clear All')}}
                            </button>
                        </div>
                        <div id="dispatch-files-list" class="space-y-2 max-h-96 overflow-y-auto">
                            <!-- Files will be dynamically added here -->
                        </div>
                        <div class="flex gap-2 mt-4 justify-end">
                            <button type="button" class="btn btn-primary btn-sm" onclick="uploadAllMatched()">
                                <i class="fas fa-upload mr-1"></i>{{__('Upload All Matched')}}
                                <span id="matched-count" class="badge badge-success ml-1">0</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <table class="table table-compact table-zebra w-auto">
                <thead>
                {{-- CONTRACTS MULTI ACTION HEADERS --}}
                <tr>
                    <th>
                        {{__('Worker(s)')}}
                    </th>
                    <th class="w-96 text-center">{{__('Gave satisfaction')}}</th>
                    <th>{{__('Last evaluated')}}</th>
                    <th class="w-48">{{__('Evaluation Document')}}</th>
                </tr>
                </thead>
                <tbody>
                {{-- For historical reasons, contract ids are used ... thus needs 2 imbricated loops --}}
                @foreach($contracts as $contract)
                    @foreach($contract->workersContracts as $workerContract)
                        @php
                            /* @var $contract \App\Models\Contract */
                            /* @var $workerContract \App\Models\WorkerContract */

                            $commentName = 'comment-'.$workerContract->id;
                            $evaluationName = 'evaluation_result-'.$workerContract->id;

                            //By default, contracts are validated with 'a' (less work for teacher)
                            $currentEval = old($evaluationName, $workerContract->alreadyEvaluated() ? $workerContract->evaluation_result : 'a');

                        @endphp
                        <tr class="h-16">
                            <td class="">{{$workerContract->groupMember->user->getFirstnameL()}}</td>
                            <td class="text-center flex" x-data="{evaluation:'{{$currentEval}}'}" class="w-auto">
                                <input type="hidden" name="workersContracts[]" value="{{$workerContract->id}}">

                                {{-- DaisyUI Steps for grades --}}
                                <ul class="steps steps-horizontal steps-evaluation w-full mb-2">
                                    <li class="step cursor-pointer"
                                        data-content="NA"
                                        :class="evaluation === 'na' ? 'step-error' : ''"
                                        @click="evaluation = 'na'">
                                    </li>
                                    <li class="step cursor-pointer"
                                        data-content="PA"
                                        :class="evaluation === 'pa' ? 'step-warning' : ''"
                                        @click="evaluation = 'pa'">
                                    </li>
                                    <li class="step cursor-pointer"
                                        data-content="A"
                                        :class="evaluation === 'a' ? 'step-success' : ''"
                                        @click="evaluation = 'a'">
                                    </li>
                                    <li class="step cursor-pointer"
                                        data-content="LA"
                                        :class="evaluation === 'la' ? 'step-success' : ''"
                                        @click="evaluation = 'la'">
                                    </li>
                                </ul>

                                <input type="hidden" name="{{$evaluationName}}" :value="evaluation">

                                {{-- Show comment field for failed/partial evaluations --}}
                                <textarea placeholder="{{__('What must be improved')}}..."
                                          class="textarea h-10 pl-1 border-error border text-xs @error($commentName) border-2 border-dashed @enderror"
                                          name="{{$commentName}}"
                                          x-show="evaluation === 'na' || evaluation === 'pa'">{{old($commentName,$workerContract->success_comment)}}</textarea>
                                @error($commentName)
                                <br/><i class="text-xs text-error">{{$errors->first($commentName)}}</i>
                                @enderror
                            </td>
                            <td class="text-center">
                                {{$workerContract->alreadyEvaluated()?
                                    $workerContract->success_date->format(\App\SwissFrenchDateFormat::DATE_TIME)
                                    :__('-')}}</td>
                            <td class="text-center w-auto">
                                <div class="upload-container" data-worker-contract-id="{{$workerContract->id}}">
                                    <input type="file"
                                           id="pdf-{{$workerContract->id}}"
                                           accept=".pdf"
                                           class="pdf-file-input hidden"
                                           data-worker-contract-id="{{$workerContract->id}}" />
                                    <button type="button"
                                            class="btn btn-outline btn-xs browse-pdf-btn"
                                            onclick="document.getElementById('pdf-{{$workerContract->id}}').click()">
                                        <i class="fas fa-file-pdf mr-1"></i>{{__('Upload PDF')}}
                                    </button>
                                    <div class="attachments-list mt-1 hidden">
                                        <!-- Multiple attachments will be dynamically added here -->
                                    </div>
                                    <div class="upload-progress mt-1 hidden">
                                        <progress class="progress progress-primary progress-xs w-full" value="0" max="100"></progress>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="4"/>
                </tr>
                </tfoot>
            </table>

            <div class="flex gap-2 my-2">
                <button type="button" class="btn btn-primary"
                        onclick="submitEvaluationForm()">
                    {{__('Save evaluation results')}}</button>
                <button type="button" class="btn btn-outline btn-error"
                        onclick="if(confirm('{{__("Are you sure you want to cancel? Any unsaved changes will be lost.")}}')) { history.back(); }">
                    {{__('Cancel')}}</button>
            </div>
        </div>


    </form>

</x-app-layout>
