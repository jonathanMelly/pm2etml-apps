<x-app-layout>
    <div class="min-h-screen bg-gray-50 pb-20" x-data="{ activeTab: 0 }">
        
        {{-- STICKY HEADER --}}
        <div class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-600 text-white w-8 h-8 rounded flex items-center justify-center">
                        <i class="fa-solid fa-heart-pulse"></i>
                    </div>
                    <h1 class="text-lg font-bold text-gray-900 leading-tight">
                        {{ __('Pulse Evaluation') }}
                    </h1>
                </div>
                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900 flex items-center gap-2 transition-colors">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-8">
            
            {{-- TABS (Only if multiple) --}}
            @if($evaluations->count() > 1)
                <div class="flex border-b border-gray-200 mb-6 overflow-x-auto">
                    @foreach($evaluations as $index => $evaluation)
                        <button 
                            class="px-6 py-3 border-b-2 font-medium text-sm whitespace-nowrap transition-colors"
                            :class="activeTab === {{ $index }} ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            @click="activeTab = {{ $index }}">
                            {{ $evaluation->student->firstname }} {{ $evaluation->student->lastname }}
                        </button>
                    @endforeach
                </div>
            @endif

            @foreach($evaluations as $index => $evaluation)
                @php
                    $currentUserId = Auth::id();
                    $currentUserType = ($currentUserId === $evaluation->teacher_id) ? 'teacher' : 'student';
                    $myVersions = $evaluation->versions->where('evaluator_type', $currentUserType)->values();
                    $otherUserType = $currentUserType === 'teacher' ? 'student' : 'teacher';
                    $otherVersions = $evaluation->versions->where('evaluator_type', $otherUserType)->values();
                @endphp

                <div x-show="activeTab === {{ $index }}" 
                     x-data="{
                        myVersion: {{ $myVersions->count() + 1 }},
                        myMaxVersion: {{ $myVersions->count() + 1 }},
                        myVersions: {{ $myVersions->map(function($v) {
                            return [
                                'number' => $v->version_number,
                                'name' => $v->version_name,
                                'date' => $v->created_at->format('d.m.Y H:i'),
                                'creator' => $v->creator->firstname . ' ' . $v->creator->lastname,
                                'general_remark_history' => $v->generalRemark ? $v->comments->sortByDesc('created_at')->map(function($c) {
                                    return [
                                        'date' => $c->created_at->format('d.m.Y H:i'),
                                        'body' => $c->body
                                    ];
                                })->values() : [],
                                'appreciations' => $v->appreciations->mapWithKeys(function($a) {
                                    return [$a->criterion_id => [
                                        'value' => $a->value,
                                        'is_ignored' => $a->is_ignored ?? false,
                                        'remark_history' => $a->remark ? $a->comments->sortByDesc('created_at')->map(function($c) {
                                            return [
                                                'date' => $c->created_at->format('d.m.Y H:i'),
                                                'body' => $c->body
                                            ];
                                        })->values() : []
                                    ]];
                                })
                            ];
                        })->toJson() }},
                        
                        otherMaxVersion: {{ $otherVersions->count() }},
                        otherVersions: {{ $otherVersions->map(function($v) {
                            return [
                                'number' => $v->version_number,
                                'name' => $v->version_name,
                                'date' => $v->created_at->format('d.m.Y H:i'),
                                'creator' => $v->creator->firstname . ' ' . $v->creator->lastname,
                                'general_remark' => $v->generalRemark ? $v->generalRemark->body : '',
                                'general_remark_history' => $v->generalRemark ? $v->comments->sortByDesc('created_at')->map(function($c) {
                                    return [
                                        'date' => $c->created_at->format('d.m.Y H:i'),
                                        'body' => $c->body
                                    ];
                                })->values() : [],
                                'appreciations' => $v->appreciations->mapWithKeys(function($a) {
                                    return [$a->criterion_id => [
                                        'value' => $a->value,
                                        'is_ignored' => $a->is_ignored ?? false,
                                        'remark' => $a->remark ? $a->remark->body : ''
                                    ]];
                                })
                            ];
                        })->toJson() }},
                        
                        currentUserType: '{{ $currentUserType }}',
                        otherUserType: '{{ $otherUserType }}',
                        status: '{{ $evaluation->status }}',
                        currentAppreciations: {},
                        
                        // View Options
                        @php
                            $user = Auth::user();
                            $isAdmin = $user->isAdmin();
                            $hasCounterPart = $otherVersions->count() > 0;
                            
                            // Default: 2 columns (4 for Admin)
                            $defaultMode = $isAdmin ? 4 : 2;
                            
                            // Exception: Default to 1 column if Teacher/Student AND Counter-part exists
                            // (This allows side-by-side comparison by default, but user can switch)
                            $forceOneCol = !$isAdmin && ($currentUserType === 'teacher' || $currentUserType === 'student') && $hasCounterPart;
                            
                            $initialViewMode = $forceOneCol ? 1 : $defaultMode;
                            // We no longer lock the view, just change the default
                            $isLocked = false;
                        @endphp
                        viewMode: {{ $initialViewMode }},
                        isViewLocked: {{ $isLocked ? 'true' : 'false' }},
                        
                        get allGeneralComments() {
                            let comments = [];
                            
                            // Collect from my versions
                            this.myVersions.forEach(v => {
                                if (v.general_remark_history) {
                                    v.general_remark_history.forEach(c => {
                                        comments.push({
                                            date: c.date,
                                            body: c.body,
                                            // Parse date for sorting (d.m.Y H:i)
                                            timestamp: this.parseDate(c.date),
                                            author: this.currentUserType === 'teacher' ? '{{ __('Teacher') }}' : '{{ __('Student') }}',
                                            isMe: true
                                        });
                                    });
                                }
                            });
                            
                            // Collect from other versions
                            this.otherVersions.forEach(v => {
                                if (v.general_remark_history) {
                                    v.general_remark_history.forEach(c => {
                                        comments.push({
                                            date: c.date,
                                            body: c.body,
                                            timestamp: this.parseDate(c.date),
                                            author: this.otherUserType === 'teacher' ? '{{ __('Teacher') }}' : '{{ __('Student') }}',
                                            isMe: false
                                        });
                                    });
                                }
                            });
                            
                            return comments.sort((a, b) => a.timestamp - b.timestamp);
                        },
                        
                        parseDate(dateStr) {
                            // d.m.Y H:i
                            let parts = dateStr.split(' ');
                            let dateParts = parts[0].split('.');
                            let timeParts = parts[1].split(':');
                            return new Date(dateParts[2], dateParts[1]-1, dateParts[0], timeParts[0], timeParts[1]).getTime();
                        },
                        
                        init() {
                            if (this.myVersion < this.myMaxVersion) {
                                this.currentAppreciations = this.myVersions[this.myVersion-1].appreciations;
                                for (let id in this.currentAppreciations) {
                                    if (this.currentAppreciations[id].is_ignored === undefined) {
                                        this.currentAppreciations[id].is_ignored = false;
                                    }
                                }
                            }
                        },
                        get score() {
                            let activeAppreciations = Object.values(this.currentAppreciations).filter(a => !a.is_ignored);
                            let values = activeAppreciations.map(a => a.value);
                            
                            if (values.length === 0) return '-';
                            if (values.includes('NA')) return 'NA';
                            if (values.includes('PA')) return 'PA';
                            
                            let laCount = values.filter(v => v === 'LA').length;
                            if (laCount >= 4) return 'LA';
                            
                            return 'A';
                        },
                        get isReadOnly() {
                            return this.myVersion < this.myMaxVersion || (this.currentUserType === 'student' && this.status === 'clos');
                        },
                        updateAppreciation(criterionId, field, value) {
                            if (!this.currentAppreciations[criterionId]) {
                                this.currentAppreciations[criterionId] = { value: 'A', is_ignored: false, remark_history: [] };
                            }
                            this.currentAppreciations[criterionId][field] = value;
                        },
                        initCriterion(criterionId, position) {
                            if (this.myVersion == this.myMaxVersion && !this.currentAppreciations[criterionId]) {
                                let history = [];
                                if (this.myMaxVersion > 1) {
                                    // Get history from the latest saved version (myMaxVersion - 1)
                                    // Array index is version number - 1, so latest saved is at index myMaxVersion - 2
                                    let latestSaved = this.myVersions[this.myMaxVersion - 2];
                                    if (latestSaved && latestSaved.appreciations[criterionId]) {
                                        history = latestSaved.appreciations[criterionId].remark_history || [];
                                        // Also add the latest remark itself if it exists and isn't already in history
                                        // (The backend structure puts all comments in history, so this might be redundant if backend is correct,
                                        // but let's stick to what we have: remark_history comes from comments)
                                    }
                                }
                                
                                this.currentAppreciations[criterionId] = {
                                    value: 'A',
                                    is_ignored: position === 7,
                                    remark_history: history
                                };
                            }
                        },
                        getPreviousScore(criterionId) {
                            if (this.otherMaxVersion === 0 || this.myVersion !== this.myMaxVersion) return null;
                            const latest = this.otherVersions[this.otherMaxVersion-1].appreciations[criterionId];
                            return latest && !latest.is_ignored ? latest.value : null;
                        },
                        getPreviousRemark(criterionId) {
                            if (this.otherMaxVersion === 0 || this.myVersion !== this.myMaxVersion) return '';
                            const latest = this.otherVersions[this.otherMaxVersion-1].appreciations[criterionId];
                            return latest && !latest.is_ignored ? latest.remark : '';
                        },
                        getComparisonArrow(criterionId) {
                            if (this.otherMaxVersion < 2 || this.myVersion !== this.myMaxVersion) return '';
                            const latest = this.otherVersions[this.otherMaxVersion-1].appreciations[criterionId];
                            const previous = this.otherVersions[this.otherMaxVersion-2].appreciations[criterionId];
                            if (!latest || !previous || latest.is_ignored || previous.is_ignored) return '';
                            
                            const scores = { 'NA': 0, 'PA': 1, 'A': 2, 'LA': 3 };
                            const currentScore = scores[latest.value];
                            const previousScore = scores[previous.value];
                            
                            if (currentScore > previousScore) return '↗';
                            if (currentScore < previousScore) return '↘';
                            return '→';
                        },
                        getPreviousVersionName() {
                            if (this.otherMaxVersion === 0) return '';
                            return this.otherVersions[this.otherMaxVersion-1].name;
                        }
                    }"
                    x-effect="
                        if (myVersion < myMaxVersion) {
                            currentAppreciations = myVersions[myVersion-1].appreciations;
                        }
                    ">
                    
                    {{-- INFO BAR & CONTROLS --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6 flex flex-col md:flex-row gap-6 items-start md:items-center justify-between">
                        
                        {{-- Context Info --}}
                        <div class="flex flex-wrap gap-6 text-sm">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                                    <i class="fa-solid {{ $currentUserType === 'student' ? 'fa-chalkboard-user' : 'fa-user-graduate' }}"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold">{{ $currentUserType === 'student' ? __('Teacher') : __('Student') }}</div>
                                    <div class="font-bold text-gray-900">
                                        {{ $currentUserType === 'student' ? $evaluation->teacher->firstname . ' ' . $evaluation->teacher->lastname : $evaluation->student->firstname . ' ' . $evaluation->student->lastname }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="hidden md:block w-px h-10 bg-gray-200"></div>

                            <div class="flex items-center gap-3">
                                <div>
                                    <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold">{{ __('Project') }}</div>
                                    <div class="font-medium text-gray-900">{{ $evaluation->jobDefinition->title }}</div>
                                </div>
                            </div>

                            <div class="hidden md:block w-px h-10 bg-gray-200"></div>

                            <div class="flex items-center gap-3">
                                <div>
                                    <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold">{{ __('Dates') }}</div>
                                    <div class="font-medium text-gray-900">
                                        {{ $evaluation->start_date ? $evaluation->start_date->format('d.m.Y') : '-' }} 
                                        <span class="text-gray-400 mx-1">→</span> 
                                        {{ $evaluation->end_date ? $evaluation->end_date->format('d.m.Y') : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            {{-- View Selector --}}
                            <div class="flex items-center bg-gray-100 rounded-lg p-1" x-show="!isViewLocked">
                                <button @click="viewMode = 1" 
                                        class="p-2 rounded text-xs font-bold transition-colors"
                                        :class="viewMode === 1 ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                                    <i class="fa-solid fa-list"></i>
                                </button>
                                <button @click="viewMode = 2" 
                                        class="p-2 rounded text-xs font-bold transition-colors"
                                        :class="viewMode === 2 ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                                    <i class="fa-solid fa-table-columns"></i>
                                </button>
                                <button @click="viewMode = 4" 
                                        class="p-2 rounded text-xs font-bold transition-colors hidden xl:block"
                                        :class="viewMode === 4 ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                                    <i class="fa-solid fa-border-all"></i>
                                </button>
                            </div>

                            {{-- History Slider --}}
                            <div class="w-full md:w-auto flex items-center gap-4 bg-gray-50 rounded-lg p-2 border border-gray-200" x-show="myMaxVersion > 1">
                                <div class="text-xs font-semibold text-gray-500 uppercase px-2">{{ __('History') }}</div>
                                <div class="flex-1 md:w-48 relative flex items-center">
                                    <input type="range" min="1" :max="myMaxVersion" x-model.number="myVersion" class="range range-xs range-primary w-full z-10" step="1" />
                                    <div class="w-full flex justify-between text-xs px-1 absolute top-1/2 -translate-y-1/2 pointer-events-none">
                                        <template x-for="i in myMaxVersion">
                                            <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                        </template>
                                    </div>
                                </div>
                                <div class="text-xs font-bold text-indigo-600 min-w-[60px] text-right">
                                    <span x-show="myVersion == myMaxVersion">{{ __('New') }}</span>
                                    <span x-show="myVersion < myMaxVersion" x-text="'v' + myVersion"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- STATUS & SCORE SUMMARY --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        {{-- Status --}}
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center justify-between cursor-pointer hover:bg-gray-50 transition-colors"
                             @click="if(myVersion == myMaxVersion && currentUserType === 'teacher') { status = status === 'clos' ? 'encours' : 'clos' }">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center"
                                     :class="status === 'clos' ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-blue-600'">
                                    <i class="fa-solid text-xl" :class="status === 'clos' ? 'fa-check' : 'fa-pen'"></i>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500 uppercase tracking-wider font-semibold">{{ __('Status') }}</div>
                                    <div class="text-xl font-bold text-gray-900" x-text="status === 'clos' ? '{{ __('Sommative') }}' : '{{ __('Formative') }}'"></div>
                                </div>
                            </div>
                            <div x-show="currentUserType === 'teacher' && myVersion == myMaxVersion">
                                <i class="fa-solid fa-rotate text-gray-400"></i>
                            </div>
                        </div>

                        {{-- Global Score --}}
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                                    <i class="fa-solid fa-chart-pie text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500 uppercase tracking-wider font-semibold">{{ __('Global Score') }}</div>
                                    <div class="text-2xl font-black text-gray-900" x-text="score"></div>
                                </div>
                            </div>
                            <div class="w-32 h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-600 transition-all duration-500" 
                                     :style="`width: ${score === 'LA' ? '100' : score === 'A' ? '75' : score === 'PA' ? '50' : score === 'NA' ? '25' : '0'}%`"></div>
                            </div>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success mb-6 shadow-sm border border-green-200 bg-green-50 text-green-800 rounded-lg">
                            <i class="fa-solid fa-circle-check"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- FORM --}}
                    <form method="POST" action="{{ route('eval_pulse.update', $evaluation->id) }}">
                        @csrf
                        <input type="hidden" name="status" :value="status">

                        {{-- Criteria Icons Mapping --}}
                        @php
                            $criteriaIcons = [
                                1 => 'fa-gauge-high',
                                2 => 'fa-award',
                                3 => 'fa-brain',
                                4 => 'fa-diagram-project',
                                5 => 'fa-comments',
                                6 => 'fa-leaf',
                                7 => 'fa-users',
                                8 => 'fa-rocket'
                            ];
                            $criteriaColors = [
                                1 => 'text-red-600 bg-red-50 border-red-100',
                                2 => 'text-amber-600 bg-amber-50 border-amber-100',
                                3 => 'text-purple-600 bg-purple-50 border-purple-100',
                                4 => 'text-blue-600 bg-blue-50 border-blue-100',
                                5 => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                                6 => 'text-teal-600 bg-teal-50 border-teal-100',
                                7 => 'text-indigo-600 bg-indigo-50 border-indigo-100',
                                8 => 'text-pink-600 bg-pink-50 border-pink-100'
                            ];
                            $criteriaBottomBorder = [
                                1 => 'border-b-red-500',
                                2 => 'border-b-amber-500',
                                3 => 'border-b-purple-500',
                                4 => 'border-b-blue-500',
                                5 => 'border-b-emerald-500',
                                6 => 'border-b-teal-500',
                                7 => 'border-b-indigo-500',
                                8 => 'border-b-pink-500'
                            ];
                        @endphp

                        <div class="grid gap-6 mb-8"
                             :class="{
                                'grid-cols-1': viewMode === 1,
                                'grid-cols-1 lg:grid-cols-2': viewMode === 2,
                                'grid-cols-1 md:grid-cols-2 xl:grid-cols-4': viewMode === 4
                             }">
                            @foreach($criteria as $criterion)
                                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden border-b-2 {{ $criteriaBottomBorder[$criterion->position] ?? 'border-b-gray-300' }}"
                                     x-init="initCriterion({{ $criterion->id }}, {{ $criterion->position }})">
                                    
                                    {{-- Criterion Header --}}
                                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg flex items-center justify-center border {{ $criteriaColors[$criterion->position] ?? 'text-gray-600 bg-gray-50 border-gray-200' }}">
                                                <i class="fa-solid {{ $criteriaIcons[$criterion->position] ?? 'fa-star' }} text-lg"></i>
                                            </div>
                                            <h3 class="font-bold text-gray-900 text-lg">{{ $criterion->name }}</h3>
                                        </div>
                                        
                                        {{-- Ignore Toggle --}}
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <span class="text-xs text-gray-500 font-medium uppercase">{{ __('Ignore') }}</span>
                                            <input type="checkbox" 
                                                name="appreciations[{{ $criterion->id }}][is_ignored]" 
                                                value="1"
                                                class="toggle toggle-sm toggle-secondary" 
                                                :disabled="isReadOnly"
                                                :checked="currentAppreciations[{{ $criterion->id }}] && currentAppreciations[{{ $criterion->id }}].is_ignored"
                                                @change="updateAppreciation({{ $criterion->id }}, 'is_ignored', $event.target.checked)" />
                                        </label>
                                    </div>

                                    {{-- Criterion Body --}}
                                    <div class="p-6" x-show="!currentAppreciations[{{ $criterion->id }}] || !currentAppreciations[{{ $criterion->id }}].is_ignored">
                                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                            
                                            {{-- LEFT: My Input --}}
                                            <div :class="{'lg:col-span-2': !(status === 'encours' && myVersion == myMaxVersion && otherMaxVersion > 0)}">
                                                <div class="mb-4">
                                                    <div class="flex gap-2 w-full">
                                                        <label class="flex-1 cursor-pointer group">
                                                            <input type="radio" 
                                                                name="appreciations[{{ $criterion->id }}][value]" 
                                                                value="NA" 
                                                                class="peer sr-only" 
                                                                :disabled="isReadOnly"
                                                                :checked="currentAppreciations[{{ $criterion->id }}] && currentAppreciations[{{ $criterion->id }}].value === 'NA'"
                                                                @change="updateAppreciation({{ $criterion->id }}, 'value', 'NA')"
                                                                required>
                                                            <div class="h-10 flex items-center justify-center rounded border-2 border-gray-200 bg-white text-gray-600 font-bold text-sm transition-all peer-checked:border-red-500 peer-checked:bg-red-100 peer-checked:text-red-700 peer-checked:shadow-md hover:bg-gray-50">
                                                                NA
                                                            </div>
                                                        </label>
                                                        <label class="flex-1 cursor-pointer group">
                                                            <input type="radio" 
                                                                name="appreciations[{{ $criterion->id }}][value]" 
                                                                value="PA" 
                                                                class="peer sr-only" 
                                                                :disabled="isReadOnly"
                                                                :checked="currentAppreciations[{{ $criterion->id }}] && currentAppreciations[{{ $criterion->id }}].value === 'PA'"
                                                                @change="updateAppreciation({{ $criterion->id }}, 'value', 'PA')"
                                                                required>
                                                            <div class="h-10 flex items-center justify-center rounded border-2 border-gray-200 bg-white text-gray-600 font-bold text-sm transition-all peer-checked:border-orange-500 peer-checked:bg-orange-100 peer-checked:text-orange-700 peer-checked:shadow-md hover:bg-gray-50">
                                                                PA
                                                            </div>
                                                        </label>
                                                        <label class="flex-1 cursor-pointer group">
                                                            <input type="radio" 
                                                                name="appreciations[{{ $criterion->id }}][value]" 
                                                                value="A" 
                                                                class="peer sr-only" 
                                                                :disabled="isReadOnly"
                                                                :checked="currentAppreciations[{{ $criterion->id }}] && currentAppreciations[{{ $criterion->id }}].value === 'A'"
                                                                @change="updateAppreciation({{ $criterion->id }}, 'value', 'A')"
                                                                required>
                                                            <div class="h-10 flex items-center justify-center rounded border-2 border-gray-200 bg-white text-gray-600 font-bold text-sm transition-all peer-checked:border-blue-500 peer-checked:bg-blue-100 peer-checked:text-blue-700 peer-checked:shadow-md hover:bg-gray-50">
                                                                A
                                                            </div>
                                                        </label>
                                                        <label class="flex-1 cursor-pointer group">
                                                            <input type="radio" 
                                                                name="appreciations[{{ $criterion->id }}][value]" 
                                                                value="LA" 
                                                                class="peer sr-only" 
                                                                :disabled="isReadOnly"
                                                                :checked="currentAppreciations[{{ $criterion->id }}] && currentAppreciations[{{ $criterion->id }}].value === 'LA'"
                                                                @change="updateAppreciation({{ $criterion->id }}, 'value', 'LA')"
                                                                required>
                                                            <div class="h-10 flex items-center justify-center rounded border-2 border-gray-200 bg-white text-gray-600 font-bold text-sm transition-all peer-checked:border-green-500 peer-checked:bg-green-100 peer-checked:text-green-700 peer-checked:shadow-md hover:bg-gray-50">
                                                                LA
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                                
                                                <div class="relative" x-data="{ showMenu: false, top: 0, left: 0 }">
                                                    <div class="w-full rounded border border-gray-300 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 overflow-hidden bg-white"
                                                         :class="{'border-red-300 bg-red-50': (currentAppreciations[{{ $criterion->id }}]?.value === 'NA' || currentAppreciations[{{ $criterion->id }}]?.value === 'PA') && myVersion == myMaxVersion}">
                                                        
                                                        {{-- History (Read Only) --}}
                                                        <div class="bg-gray-50 border-b border-gray-100 max-h-32 overflow-y-auto" 
                                                             x-show="currentAppreciations[{{ $criterion->id }}]?.remark_history?.length > 0">
                                                            <template x-for="comment in currentAppreciations[{{ $criterion->id }}]?.remark_history">
                                                                <div class="p-2 text-xs border-b border-gray-100 last:border-0">
                                                                    <div class="text-gray-400 mb-1" x-text="comment.date"></div>
                                                                    <div class="text-gray-600 whitespace-pre-wrap" x-text="comment.body"></div>
                                                                </div>
                                                            </template>
                                                        </div>

                                                        {{-- New Input --}}
                                                        <textarea 
                                                            name="appreciations[{{ $criterion->id }}][remark]" 
                                                            class="w-full p-3 text-sm resize-none border-0 focus:ring-0 bg-transparent h-20"
                                                            :placeholder="(currentAppreciations[{{ $criterion->id }}]?.value === 'NA' || currentAppreciations[{{ $criterion->id }}]?.value === 'PA') ? '{{ __('Justification required for NA/PA...') }}' : '{{ __('Add a new remark...') }}'"
                                                            :disabled="isReadOnly"
                                                            x-show="!isReadOnly"
                                                            :required="(currentAppreciations[{{ $criterion->id }}]?.value === 'NA' || currentAppreciations[{{ $criterion->id }}]?.value === 'PA') && myVersion == myMaxVersion"
                                                            @contextmenu.prevent="if(currentUserType === 'teacher') { showMenu = true; top = $event.clientY; left = $event.clientX; }"
                                                            @click.outside="showMenu = false"
                                                        ></textarea>
                                                    </div>
                                                    
                                                    {{-- Context Menu (Teachers only) --}}
                                                    @if($currentUserType === 'teacher')
                                                        <div x-show="showMenu" 
                                                             class="fixed bg-white border border-gray-200 rounded shadow-lg z-50 py-1 min-w-[250px]"
                                                             :style="`top: ${top}px; left: ${left}px`"
                                                             style="display: none;">
                                                            <div class="px-3 py-1 text-xs font-bold text-gray-500 uppercase border-b border-gray-100 bg-gray-50">
                                                                {{ __('Templates') }}
                                                            </div>
                                                            @foreach($templatesData[$evaluation->id][$criterion->id] as $template)
                                                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700"
                                                                   @click.prevent="updateAppreciation({{ $criterion->id }}, 'remark', '{{ addslashes($template->text) }}'); showMenu = false; $nextTick(() => { $el.closest('.relative').querySelector('textarea').value = '{{ addslashes($template->text) }}'; })">
                                                                    <div class="font-semibold">{{ $template->title }}</div>
                                                                    <div class="text-xs text-gray-500 truncate">{{ Str::limit($template->text, 50) }}</div>
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- RIGHT: Other Input (Read Only) --}}
                                            <div x-show="status === 'encours' && myVersion == myMaxVersion && otherMaxVersion > 0" 
                                                 class="bg-gray-50 rounded border border-gray-200 p-4 flex flex-col">
                                                <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-200">
                                                    <span class="text-xs font-bold text-gray-500 uppercase">
                                                        {{ $currentUserType === 'teacher' ? __('Student') : __('Teacher') }}
                                                    </span>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xl font-black" 
                                                              :class="{
                                                                  'text-red-600': getPreviousScore({{ $criterion->id }}) === 'NA',
                                                                  'text-orange-600': getPreviousScore({{ $criterion->id }}) === 'PA',
                                                                  'text-blue-600': getPreviousScore({{ $criterion->id }}) === 'A',
                                                                  'text-green-600': getPreviousScore({{ $criterion->id }}) === 'LA'
                                                              }"
                                                              x-text="getPreviousScore({{ $criterion->id }}) || '-'"></span>
                                                        <span class="text-lg text-gray-400" x-text="getComparisonArrow({{ $criterion->id }})"></span>
                                                    </div>
                                                </div>
                                                <div class="text-sm text-gray-600 italic flex-1 whitespace-pre-wrap" 
                                                     x-text="getPreviousRemark({{ $criterion->id }}) || '{{ __('No remark') }}'"></div>
                                            </div>

                                        </div>
                                    </div>

                                    <div x-show="currentAppreciations[{{ $criterion->id }}] && currentAppreciations[{{ $criterion->id }}].is_ignored" class="p-8 text-center bg-gray-50">
                                        <p class="text-sm text-gray-400 italic">{{ __('Criterion ignored') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Shared General Remarks Zone --}}
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                            <h3 class="font-bold text-gray-900 text-lg mb-4">{{ __('General Remarks') }}</h3>
                            
                            <div class="relative" x-data="{ showGeneralMenu: false, top: 0, left: 0 }">
                                <div class="w-full rounded border border-gray-300 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 overflow-hidden bg-white">
                                    
                                    {{-- Shared History (Read Only) --}}
                                    <div class="bg-gray-50 border-b border-gray-100 max-h-64 overflow-y-auto" 
                                         x-show="allGeneralComments.length > 0">
                                        <template x-for="comment in allGeneralComments">
                                            <div class="p-3 text-sm border-b border-gray-100 last:border-0"
                                                 :class="comment.isMe ? 'bg-indigo-50/30' : ''">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="font-bold text-xs uppercase" 
                                                          :class="comment.isMe ? 'text-indigo-600' : 'text-gray-500'" 
                                                          x-text="comment.author"></span>
                                                    <span class="text-gray-400 text-xs" x-text="'- ' + comment.date"></span>
                                                </div>
                                                <div class="text-gray-700 whitespace-pre-wrap" x-text="comment.body"></div>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- New Input --}}
                                    <textarea 
                                        name="general_remark" 
                                        class="w-full p-3 text-sm resize-none border-0 focus:ring-0 bg-transparent h-24"
                                        placeholder="{{ __('Add a general observation...') }}"
                                        :disabled="isReadOnly"
                                        x-show="!isReadOnly"
                                        @contextmenu.prevent="if(currentUserType === 'teacher') { showGeneralMenu = true; top = $event.clientY; left = $event.clientX; }"
                                        @click.outside="showGeneralMenu = false"
                                    ></textarea>
                                </div>
                                
                                {{-- Context Menu for General Remark (Teachers only) --}}
                                @if($currentUserType === 'teacher')
                                    <div x-show="showGeneralMenu" 
                                         class="fixed bg-white border border-gray-200 rounded shadow-lg z-50 py-1 min-w-[300px]"
                                         :style="`top: ${top}px; left: ${left}px`"
                                         style="display: none;">
                                        <div class="px-3 py-1 text-xs font-bold text-gray-500 uppercase border-b border-gray-100 bg-gray-50">
                                            {{ __('General Templates') }}
                                        </div>
                                        @foreach($templatesData[$evaluation->id]['general'] as $template)
                                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700"
                                               @click.prevent="showGeneralMenu = false; $nextTick(() => { $el.closest('.relative').querySelector('textarea').value = '{{ addslashes($template->text) }}'; })">
                                                <div class="font-semibold">{{ $template->title }}</div>
                                                <div class="text-xs text-gray-500 line-clamp-2">{{ $template->text }}</div>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Submit & PDF --}}
                        <div class="flex justify-end items-center gap-4 pb-12">
                            @if($currentUserType === 'teacher' && $evaluation->status === 'clos')
                                <a href="{{ route('eval_pulse.pdf', $evaluation->id) }}" class="btn bg-white border-indigo-600 text-indigo-600 hover:bg-indigo-50 px-8 gap-2">
                                    <i class="fa-solid fa-file-pdf"></i> {{ __('Generate PDF') }}
                                </a>
                            @endif

                            <div x-show="!isReadOnly">
                                <button type="submit" class="btn bg-indigo-600 hover:bg-indigo-700 text-white border-0 px-8">
                                    {{ __('Save Evaluation') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
