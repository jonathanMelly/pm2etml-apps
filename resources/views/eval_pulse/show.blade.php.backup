<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 py-12" x-data="{ activeTab: 0 }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Card -->
            <div class="bg-white/80 backdrop-blur-sm overflow-hidden shadow-2xl rounded-2xl mb-8 border border-indigo-100">
                <div class="p-6 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="font-bold text-3xl text-white leading-tight flex items-center gap-3">
                                <i class="fa-solid fa-heart-pulse text-4xl"></i>
                                {{ __('Pulse Evaluation') }}
                            </h2>
                            <p class="text-indigo-100 mt-2">{{ __('Professional Skills Assessment') }}</p>
                        </div>
                        <a href="{{ route('dashboard') }}" class="btn btn-sm bg-white text-indigo-600 hover:bg-indigo-50 border-0">
                            <i class="fa-solid fa-arrow-left mr-2"></i>{{ __('Back to Dashboard') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Content Card -->
            <div class="bg-white/90 backdrop-blur-sm overflow-hidden shadow-2xl rounded-2xl border border-indigo-100">
                <div class="p-8">
                    {{-- TABS --}}
                    <div class="flex gap-2 mb-8 overflow-x-auto pb-2">
                        @foreach($evaluations as $index => $evaluation)
                            <button 
                                class="px-6 py-3 rounded-xl font-semibold transition-all duration-300 whitespace-nowrap"
                                :class="activeTab === {{ $index }} ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg scale-105' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                @click="activeTab = {{ $index }}">
                                <i class="fa-solid fa-user mr-2"></i>
                                {{ $evaluation->student->firstname }} {{ $evaluation->student->lastname }}
                            </button>
                        @endforeach
                    </div>

                    @foreach($evaluations as $index => $evaluation)
                        <div x-show="activeTab === {{ $index }}" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-data="{ 
                            version: {{ $evaluation->versions->count() + 1 }}, 
                            maxVersion: {{ $evaluation->versions->count() + 1 }},
                            versions: {{ $evaluation->versions->map(function($v) {
                                return [
                                    'number' => $v->version_number,
                                    'date' => $v->created_at->format('d.m.Y H:i'),
                                    'creator' => $v->creator->firstname . ' ' . $v->creator->lastname,
                                    'general_remark' => $v->generalRemark ? $v->generalRemark->text : '',
                                    'appreciations' => $v->appreciations->mapWithKeys(function($a) {
                                        return [$a->criterion_id => [
                                            'value' => $a->value,
                                            'is_ignored' => $a->is_ignored ?? false,
                                            'remark' => $a->remark ? $a->remark->text : ''
                                        ]];
                                    })
                                ];
                            })->toJson() }}
                        }">
                            
                            <!-- Student Info -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8 p-6 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl border border-indigo-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                                        <i class="fa-solid fa-user text-white"></i>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-500 block">{{ __('Student') }}</span>
                                        <span class="font-bold text-gray-800">{{ $evaluation->student->firstname }} {{ $evaluation->student->lastname }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center">
                                        <i class="fa-solid fa-briefcase text-white"></i>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-500 block">{{ __('Project') }}</span>
                                        <span class="font-bold text-gray-800">{{ $evaluation->jobDefinition->title }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-pink-600 rounded-lg flex items-center justify-center">
                                        <i class="fa-solid fa-calendar-plus text-white"></i>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-500 block">{{ __('Start Date') }}</span>
                                        <span class="font-bold text-gray-800">{{ $evaluation->start_date ? $evaluation->start_date->format('d.m.Y') : '-' }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center">
                                        <i class="fa-solid fa-calendar-check text-white"></i>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-500 block">{{ __('End Date') }}</span>
                                        <span class="font-bold text-gray-800">{{ $evaluation->end_date ? $evaluation->end_date->format('d.m.Y') : '-' }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- SLIDER --}}
                            <div class="mb-8 px-6 py-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl" x-show="maxVersion > 1">
                                <div class="flex items-center gap-4 mb-4">
                                    <i class="fa-solid fa-clock-rotate-left text-2xl text-indigo-600"></i>
                                    <h3 class="font-bold text-lg">{{ __('Version History') }}</h3>
                                </div>
                                <input type="range" min="1" :max="maxVersion" x-model="version" class="range range-primary w-full" step="1" />
                                <div class="w-full flex justify-between text-xs px-2 mt-2">
                                    <template x-for="i in maxVersion">
                                        <span class="font-semibold" :class="version == i ? 'text-indigo-600 scale-110' : 'text-gray-400'" x-text="i === maxVersion ? 'New' : 'v' + i"></span>
                                    </template>
                                </div>
                                <div class="mt-4 text-center p-3 bg-white rounded-lg shadow-sm">
                                    <span x-show="version == maxVersion" class="text-indigo-600 font-bold text-lg">✨ {{ __('New Evaluation') }}</span>
                                    <span x-show="version < maxVersion" class="text-gray-700">
                                        <strong>{{ __('Version') }} <span x-text="version"></span></strong> - 
                                        <span x-text="versions[version-1].date"></span> 
                                        <span class="text-indigo-600">({{ __('by') }} <span x-text="versions[version-1].creator"></span>)</span>
                                    </span>
                                </div>
                            </div>

                            @if(session('success'))
                                <div class="alert alert-success mb-6 shadow-lg">
                                    <i class="fa-solid fa-circle-check"></i>
                                    {{ session('success') }}
                                </div>
                            @endif

                            {{-- FORM --}}
                            <form method="POST" action="{{ route('eval_pulse.update', $evaluation->id) }}" x-data="{
                                currentAppreciations: {},
                                status: '{{ $evaluation->status }}',
                                init() {
                                    if (this.version < this.maxVersion) {
                                        this.currentAppreciations = this.versions[this.version-1].appreciations;
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
                                updateAppreciation(criterionId, field, value) {
                                    if (!this.currentAppreciations[criterionId]) {
                                        this.currentAppreciations[criterionId] = { value: 'A', is_ignored: false, remark: '' };
                                    }
                                    this.currentAppreciations[criterionId][field] = value;
                                },
                                initCriterion(criterionId, position) {
                                    if (this.version == this.maxVersion && !this.currentAppreciations[criterionId]) {
                                        this.currentAppreciations[criterionId] = {
                                            value: 'A',
                                            is_ignored: position === 7,
                                            remark: ''
                                        };
                                    }
                                }
                            }" x-effect="
                                if (version < maxVersion) {
                                    currentAppreciations = versions[version-1].appreciations;
                                }
                            ">
                                @csrf
                                
                                {{-- STATUS & SCORE CARD --}}
                                <div class="mb-8 p-8 rounded-2xl shadow-2xl transform transition-all duration-300 hover:scale-[1.02] cursor-pointer" 
                                     :class="status === 'clos' ? 'bg-gradient-to-br from-red-500 to-pink-600' : 'bg-gradient-to-br from-blue-500 to-indigo-600'"
                                     @click="if(version == maxVersion) { status = status === 'clos' ? 'encours' : 'clos' }"
                                     x-show="version == maxVersion">
                                    <div class="flex flex-col md:flex-row justify-between items-center gap-6 text-white">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-4">
                                                <i class="fa-solid text-4xl" :class="status === 'clos' ? 'fa-graduation-cap' : 'fa-book-open'"></i>
                                                <div>
                                                    <h2 class="text-3xl font-black" x-text="status === 'clos' ? '{{ __('Sommative') }}' : '{{ __('Formative') }}'"></h2>
                                                    <p class="text-white/80 text-sm mt-1" x-text="status === 'clos' ? 'Évaluation finale' : 'Évaluation formative'"></p>
                                                </div>
                                            </div>
                                            <input type="hidden" name="status" :value="status">
                                        </div>
                                        <div class="text-center bg-white/20 backdrop-blur-sm p-8 rounded-2xl min-w-[200px]">
                                            <div class="text-sm uppercase tracking-wider font-semibold mb-2 text-white/90">{{ __('Global Score') }}</div>
                                            <div class="text-7xl font-black mb-2" x-text="score"></div>
                                            <div class="h-2 bg-white/30 rounded-full overflow-hidden">
                                                <div class="h-full bg-white transition-all duration-500" 
                                                     :style="`width: ${score === 'LA' ? '100' : score === 'A' ? '75' : score === 'PA' ? '50' : score === 'NA' ? '25' : '0'}%`"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

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
                                        1 => 'from-red-500 to-orange-500',
                                        2 => 'from-yellow-500 to-amber-500',
                                        3 => 'from-purple-500 to-pink-500',
                                        4 => 'from-blue-500 to-cyan-500',
                                        5 => 'from-green-500 to-emerald-500',
                                        6 => 'from-teal-500 to-green-600',
                                        7 => 'from-indigo-500 to-purple-500',
                                        8 => 'from-pink-500 to-rose-500'
                                    ];
                                @endphp

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                                    @foreach($criteria as $criterion)
                                        <div class="group relative bg-white rounded-2xl shadow-lg border-2 border-transparent transition-all duration-300 hover:shadow-2xl hover:-translate-y-2 overflow-hidden" 
                                             :class="{'opacity-60 grayscale': currentAppreciations[{{ $criterion->id }}] && currentAppreciations[{{ $criterion->id }}].is_ignored, 'border-indigo-200': version < maxVersion}"
                                             x-init="initCriterion({{ $criterion->id }}, {{ $criterion->position }})">
                                            
                                            {{-- Gradient Header --}}
                                            <div class="h-2 bg-gradient-to-r {{ $criteriaColors[$criterion->position] ?? 'from-gray-400 to-gray-600' }}"></div>
                                            
                                            <div class="p-6">
                                                <div class="flex justify-between items-start mb-4">
                                                    <div class="flex items-start gap-4 flex-1">
                                                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br {{ $criteriaColors[$criterion->position] ?? 'from-gray-400 to-gray-600' }} flex items-center justify-center flex-shrink-0 shadow-lg">
                                                            <i class="fa-solid {{ $criteriaIcons[$criterion->position] ?? 'fa-star' }} text-white text-2xl"></i>
                                                        </div>
                                                        <div class="flex-1">
                                                            <h3 class="font-bold text-lg text-gray-800 mb-1 leading-tight">{{ $criterion->name }}</h3>
                                                            @if($criterion->description)
                                                                <p class="text-sm text-gray-600 leading-relaxed">{{ $criterion->description }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    {{-- IGNORE TOGGLE --}}
                                                    <div class="ml-2">
                                                        <label class="flex flex-col items-center gap-1 cursor-pointer group">
                                                            <input type="checkbox" 
                                                                name="appreciations[{{ $criterion->id }}][is_ignored]" 
                                                                value="1"
                                                                class="toggle toggle-sm toggle-secondary" 
                                                                :disabled="version < maxVersion"
                                                                :checked="currentAppreciations[{{ $criterion->id }}] && currentAppreciations[{{ $criterion->id }}].is_ignored"
                                                                @change="updateAppreciation({{ $criterion->id }}, 'is_ignored', $event.target.checked)" />
                                                            <span class="text-xs text-gray-400 group-hover:text-gray-600">{{ __('Ignored') }}</span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div x-show="!currentAppreciations[{{ $criterion->id }}] || !currentAppreciations[{{ $criterion->id }}].is_ignored">
                                                    {{-- Radio Buttons --}}
                                                    <div class="grid grid-cols-4 gap-3 mb-4">
                                                        @foreach(['NA' => ['label' => 'Non Acquis', 'color' => 'red'], 'PA' => ['label' => 'Partiellement Acquis', 'color' => 'orange'], 'A' => ['label' => 'Acquis', 'color' => 'blue'], 'LA' => ['label' => 'Largement Acquis', 'color' => 'green']] as $val => $config)
                                                            <label class="relative cursor-pointer group/radio">
                                                                <input type="radio" 
                                                                    name="appreciations[{{ $criterion->id }}][value]" 
                                                                    value="{{ $val }}" 
                                                                    class="peer sr-only" 
                                                                    :disabled="version < maxVersion"
                                                                    :checked="currentAppreciations[{{ $criterion->id }}] && currentAppreciations[{{ $criterion->id }}].value === '{{ $val }}'"
                                                                    @change="updateAppreciation({{ $criterion->id }}, 'value', '{{ $val }}')"
                                                                    required>
                                                                <div class="w-full p-3 rounded-xl border-2 border-gray-200 bg-gray-50 peer-checked:border-{{ $config['color'] }}-500 peer-checked:bg-{{ $config['color'] }}-50 peer-checked:shadow-lg transition-all duration-200 hover:border-{{ $config['color'] }}-300 text-center">
                                                                    <div class="text-lg font-black text-gray-700 peer-checked:text-{{ $config['color'] }}-600">{{ $val }}</div>
                                                                    <div class="text-xs text-gray-500 peer-checked:text-{{ $config['color'] }}-600 mt-1 hidden lg:block">{{ explode(' ', $config['label'])[0] }}</div>
                                                                </div>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                    
                                                    {{-- Textarea with Context Menu --}}
                                                    <div class="relative" x-data="{ showMenu: false, top: 0, left: 0 }">
                                                        <textarea 
                                                            name="appreciations[{{ $criterion->id }}][remark]" 
                                                            class="textarea textarea-bordered w-full h-24 resize-none bg-gray-50 border-2 border-gray-200 focus:border-indigo-400 focus:bg-white rounded-xl transition-all" 
                                                            placeholder="{{ __('Remark for') }} {{ $criterion->name }}"
                                                            :disabled="version < maxVersion"
                                                            x-text="currentAppreciations[{{ $criterion->id }}] ? currentAppreciations[{{ $criterion->id }}].remark : ''"
                                                            @contextmenu.prevent="showMenu = true; top = $event.clientY; left = $event.clientX"
                                                            @click.outside="showMenu = false"
                                                        ></textarea>

                                                        {{-- CONTEXT MENU --}}
                                                        <div x-show="showMenu" 
                                                             class="fixed bg-white border-2 border-indigo-200 rounded-xl shadow-2xl z-50 py-2 min-w-[200px] overflow-hidden"
                                                             :style="`top: ${top}px; left: ${left}px`"
                                                             style="display: none;">
                                                            <div class="px-4 py-2 text-xs font-bold text-indigo-600 border-b-2 border-indigo-100 bg-indigo-50">
                                                                <i class="fa-solid fa-wand-magic-sparkles mr-1"></i> {{ __('Templates') }}
                                                            </div>
                                                            @foreach($templates as $template)
                                                                <a href="#" class="block px-4 py-3 text-sm hover:bg-indigo-50 transition-colors border-l-4 border-transparent hover:border-indigo-500"
                                                                   @click.prevent="updateAppreciation({{ $criterion->id }}, 'remark', '{{ addslashes($template->text) }}'); showMenu = false; $nextTick(() => { $el.closest('.relative').querySelector('textarea').value = '{{ addslashes($template->text) }}'; })">
                                                                    <div class="font-semibold text-gray-800">{{ $template->title }}</div>
                                                                    <div class="text-xs text-gray-500 truncate">{{ Str::limit($template->text, 40) }}</div>
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div x-show="currentAppreciations[{{ $criterion->id }}] && currentAppreciations[{{ $criterion->id }}].is_ignored" class="text-center py-6">
                                                    <i class="fa-solid fa-eye-slash text-4xl text-gray-300 mb-2"></i>
                                                    <p class="text-sm italic text-gray-400 font-medium">{{ __('This criterion is excluded from the evaluation.') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- General Remark --}}
                                <div class="bg-gradient-to-br from-amber-50 to-orange-50 border-2 border-amber-200 p-8 rounded-2xl shadow-lg mb-8">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-500 rounded-xl flex items-center justify-center">
                                            <i class="fa-solid fa-message-lines text-white text-xl"></i>
                                        </div>
                                        <h3 class="font-bold text-2xl text-gray-800">{{ __('General Remark') }}</h3>
                                    </div>
                                    <textarea 
                                        name="general_remark" 
                                        class="textarea textarea-bordered w-full h-32 resize-none bg-white border-2 border-amber-200 focus:border-amber-400 rounded-xl text-base" 
                                        placeholder="{{ __('General observation about the student\'s performance...') }}"
                                        :disabled="version < maxVersion"
                                        x-text="version < maxVersion ? versions[version-1].general_remark : ''"
                                    ></textarea>
                                </div>

                                {{-- Submit Button --}}
                                <div class="flex justify-end" x-show="version == maxVersion">
                                    <button type="submit" class="btn btn-lg bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white border-0 shadow-xl px-8 transform transition-all duration-300 hover:scale-105">
                                        <i class="fa-solid fa-save mr-2 text-xl"></i> 
                                        <span class="text-lg font-bold">{{ __('Save Evaluation') }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
