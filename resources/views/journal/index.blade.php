<x-app-layout>
    <div class="sm:mx-6 flex flex-col gap-4">
        <div class="bg-base-200 bg-opacity-40 overflow-hidden shadow-sm sm:rounded-lg border-secondary border-2 border-opacity-20 hover:border-opacity-30">
            <div class="p-6">
                <script>
                    window.journalActivityMeta = @json(config('journal.activity_types'));
                    window.journalActivityFirstKey = Object.keys(window.journalActivityMeta || {})[0] || '';
                </script>
                <div class="prose pb-2 p-1 min-w-full bg-base-100/50 rounded-box">
                    <h1 class="text-base-content">
                        <i class="fa-solid fa-book mr-2"></i>{{ __('Journal de journée') }} – {{ $workerContract->contract?->jobDefinition?->title }}
                    </h1>
                    @if($isTeacher)
                        <div class="text-sm opacity-75">
                            {{ __('Élève') }}: {{ $workerContract->groupMember?->user?->getFirstnameL() }}
                            <span class="ml-3">{{ __('Group') }}: {{ $workerContract->groupName?->name }}</span>
                        </div>
                    @elseif($isStudent)
                        <div class="text-sm opacity-75">
                            {{ __('Chef de projet') }}:
                            {{ optional($workerContract->contract?->clients)->map(fn($u)=>$u->getFirstnameL())->join(', ') }}
                        </div>
                    @endif
                </div>

                {{-- Légende des types d'activités --}}
                @php($activityTypes = config('journal.activity_types', []))
                @if(!empty($activityTypes))
                    <div class="mt-3 flex flex-wrap gap-2 items-center">
                        <div class="font-semibold mr-2">{{ __('Types d\'activités') }}:</div>
                        @foreach($activityTypes as $label => $meta)
                            <span class="badge {{ $meta['color'] ?? 'badge-outline' }}">
                                <i class="fa-solid {{ $meta['icon'] ?? 'fa-circle' }} mr-1"></i>{{ __($label) }}
                            </span>
                        @endforeach
                    </div>
                @endif

                @if(session('success'))
                    <div role="alert" class="alert alert-success mt-3">
                        <i class="fa-solid fa-check"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if ($errors->any())
                    <div role="alert" class="alert alert-error mt-3">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-2 flex gap-2 items-center">
                    <a class="btn btn-outline btn-info btn-sm" target="_blank" href="{{ route('worker-contracts.journal.export', $workerContract) }}">
                        <i class="fa-solid fa-file-pdf mr-1"></i>{{ __('Exporter PDF') }}
                    </a>
                    @isset($allocated)
                        <div class="badge badge-outline">
                            {{ __('Alloué') }}: {{ $allocated }}p — {{ __('Utilisé') }}: {{ $used }}p — {{ __('Reste') }}: {{ $remaining }}p ({{ __('max') }} {{ $allowedMax }}p)
                        </div>
                    @endisset
                </div>

                @if($isStudent)
                    <div class="mt-4">
                        <h2 class="font-semibold">{{ __('Saisir une journée') }}</h2>
                        <form class="mt-2" method="post" action="{{ route('worker-contracts.journal.store', $workerContract) }}" x-data="{ periodsCount: 5, precision: 15 }">
                            @csrf
                            <div class="grid md:grid-cols-3 gap-4">
                                <label class="input-group">
                                    <span>{{ __('Date') }}</span>
                                    <input type="date" name="date" value="{{ old('date', now()->format(\App\DateFormat::HTML_FORMAT)) }}" class="input input-bordered input-primary w-full" />
                                </label>
                                <label class="input-group">
                                    <span>{{ __('Périodes') }}</span>
                                    <input type="number" name="periods_count" min="1" max="6" x-model.number="periodsCount" value="{{ old('periods_count', 5) }}" class="input input-bordered input-secondary w-full" />
                                </label>
                                <label class="input-group">
                                    <span>{{ __('Précision (min)') }}</span>
                                    <select name="precision_minutes" x-model.number="precision" class="select select-bordered w-full">
                                        <option value="3">3</option>
                                        <option value="5">5</option>
                                        <option value="15" selected>15</option>
                                    </select>
                                </label>
                            </div>

                            <label class="form-control mt-3">
                                <div class="label"><span class="label-text">{{ __('Notes générales (optionnel)') }}</span></div>
                                <textarea class="textarea textarea-bordered" name="student_notes">{{ old('student_notes') }}</textarea>
                            </label>

                            <div class="divider"></div>

                            <template x-for="i in periodsCount" :key="i">
                                <div class="card bg-base-100 shadow-sm mb-3">
                                    <div class="card-body">
                                        <h3 class="card-title text-sm">
                                            {{ __('Période') }} <span x-text="i"></span> – 45 {{ __('minutes') }}
                                        </h3>
                                        <div class="mt-2">
                                            <template x-for="j in Math.floor(45/precision)" :key="j">
                                                <div class="flex items-center gap-2 mb-1" x-data="{ t: window.journalActivityFirstKey, editType:false }">
                                                    <input type="hidden" :name="`periods[${i}][lines][${j}][type]`" :value="t">
                                                    <button type="button" class="btn btn-ghost btn-xs" @click="editType = !editType" :aria-expanded="editType" title="{{ __('Type') }}">
                                                        <span class="badge" :class="window.journalActivityMeta[t]?.color">
                                                            <i class="fa-solid" :class="window.journalActivityMeta[t]?.icon"></i>
                                                        </span>
                                                    </button>
                                                    <template x-if="editType">
                                                        <select class="select select-bordered select-sm w-48" x-model="t" @change="editType=false" @blur="editType=false">
                                                            @php($activityTypesSelect = config('journal.activity_types', []))
                                                            @foreach($activityTypesSelect as $type => $meta)
                                                                <option value="{{$type}}">{{ __($type) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </template>
                                                    <input type="text" class="input input-bordered input-sm w-full flex-1" :name="`periods[${i}][lines][${j}][text]`" placeholder="{{ __('Activité / remarque') }}" />
                                                </div>
                                            </template>
                                        </div>
                                        <textarea class="textarea textarea-bordered w-full mt-2" :name="`periods[${i}][note]`" placeholder="{{ __('Remarque (optionnel)') }}"></textarea>
                                    </div>
                                </div>
                            </template>

                            <div class="card-actions justify-end mt-2">
                                <button class="btn btn-primary">
                                    <i class="fa-solid fa-save mr-1"></i>{{ __('Enregistrer') }}
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                <div class="mt-6">
                    <h2 class="font-semibold">{{ __('Historique') }}</h2>
                    <div class="overflow-x-auto">
                        <table class="table table-compact w-full">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Détail') }}</th>
                                    <th>{{ __('Appréciation') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td class="whitespace-nowrap">{{ $log->date->format(\App\SwissFrenchDateFormat::DATE) }}</td>
                                    <td>
                                        @if(is_array($log->periods))
                                            <div class="ml-2">
                                                @foreach($log->periods as $p)
                                                    <div class="mb-1">
                                                        <div class="font-semibold inline-flex items-center gap-2 mr-2">
                                                            <span class="badge badge-outline">P{{ $p['index'] }}</span>
                                                        </div>
                                                        @if(!empty($p['lines']))
                                                            <div class="inline-flex flex-col gap-1 align-top">
                                                                @foreach($p['lines'] as $line)
                                                                    @php($meta = config('journal.activity_types.' . ($line['type'] ?? ''), []))
                                                                    <div class="inline-flex items-center gap-2">
                                                                        @if(!empty($line['type']))
                                                                            <div class="tooltip" data-tip="{{ __($line['type']) }}">
                                                                                <span class="badge badge-sm {{ $meta['color'] ?? 'badge-outline' }}">
                                                                                    <i class="fa-solid {{ $meta['icon'] ?? 'fa-circle' }}"></i>
                                                                                </span>
                                                                            </div>
                                                                        @endif
                                                                        <span>{{ $line['text'] ?? '' }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        @if(!empty($p['note']))
                                                            <div class="mt-1 opacity-60 italic">{{ $p['note'] }}</div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if($log->student_notes)
                                            <div class="mt-1 opacity-75">{{ $log->student_notes }}</div>
                                        @endif
                                    </td>
                                    <td class="align-top">
                                        @if($isTeacher)
                                            <form method="post" action="{{ route('day-logs.appreciation.update', $log) }}" x-data="{
                                                sel: '',
                                                lastSel: '',
                                                recent: JSON.parse(localStorage.getItem('jr_recent_{{ auth()->id() }}')||'[]'),
                                                pinned: JSON.parse(localStorage.getItem('jr_pinned_{{ auth()->id() }}')||'[]'),
                                                insert(txt){
                                                    if(!txt) return;
                                                    this.lastSel = txt;
                                                    $refs.ta.value = ($refs.ta.value?($refs.ta.value + '\n'):'') + txt;
                                                    if(!this.recent.includes(txt)){
                                                        this.recent.unshift(txt);
                                                        this.recent = this.recent.slice(0,7);
                                                    } else {
                                                        this.recent = [txt].concat(this.recent.filter(x=>x!==txt)).slice(0,7);
                                                    }
                                                    localStorage.setItem('jr_recent_{{ auth()->id() }}', JSON.stringify(this.recent));
                                                    $refs.ta.focus();
                                                }
                                            }">
                                                @method('PATCH')
                                                @csrf
                                                @php($groups = config('journal.appreciations', []))
                                                @if(!empty($groups))
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <!-- Universelles (toujours visible) -->
                                                        <div class="relative" x-data="{openU:false}">
                                                            <button type="button" class="btn btn-outline btn-xs" @click="openU = !openU">{{ __('Universelles') }}</button>
                                                            <ul x-show="openU" @click.outside="openU=false" x-transition
                                                                class="absolute left-0 right-0 z-50 menu p-2 shadow bg-base-100 rounded-box w-[90vw] md:w-[48rem] max-h-[70vh] overflow-auto mt-1">
                                                                @foreach($groups as $groupLabel => $items)
                                                                    <li class="menu-title mt-1"><span>{{ __($groupLabel) }}</span></li>
                                                                    @foreach($items as $it)
                                                                        <li><a href="#" @click.prevent="insert($el.dataset.val); openU=false" data-val="{{ e(__($it)) }}">{{ __($it) }}</a></li>
                                                                    @endforeach
                                                                @endforeach
                                                            </ul>
                                                        </div>

                                                        <!-- Mes favoris (visible si user a des favoris) -->
                                                        <div class="relative" x-data="{openF:false}" x-show="pinned.length>0">
                                                            <button type="button" class="btn btn-outline btn-xs" @click="openF = !openF">{{ __('Mes favoris') }}</button>
                                                            <ul x-show="openF" @click.outside="openF=false" x-transition
                                                                class="absolute z-50 menu p-2 shadow bg-base-100 rounded-box w-72 max-h-64 overflow-auto mt-1">
                                                                <template x-for="p in pinned" :key="p">
                                                                    <li><a href="#" @click.prevent="insert($el.dataset.val); openF=false" x-text="p" :data-val="p"></a></li>
                                                                </template>
                                                            </ul>
                                                        </div>

                                                        <!-- Épingler/Désépingler la dernière insertion -->
                                                        <button type="button" class="btn btn-ghost btn-xs" :disabled="!lastSel" :title="(pinned.includes(lastSel)?'{{ __('Désépingler') }}':'{{ __('Épingler') }}')"
                                                                @click="
                                                                    if(!lastSel) return;
                                                                    if(!pinned.includes(lastSel)){
                                                                        pinned.unshift(lastSel);
                                                                        pinned = [...new Set(pinned)].slice(0,15);
                                                                    } else {
                                                                        pinned = pinned.filter(x=>x!==lastSel);
                                                                    }
                                                                    localStorage.setItem('jr_pinned_{{ auth()->id() }}', JSON.stringify(pinned));
                                                                ">
                                                            <i class="fa-solid" :class="pinned.includes(lastSel)?'fa-star text-yellow-400':'fa-star-half-stroke'"></i>
                                                        </button>
                                                        <span class="text-xs opacity-70">{{ __('Cliquer pour insérer') }}</span>
                                                    </div>
                                                @endif
                                                <label class="label mt-1"><span class="label-text">{{ __('Appréciation (enseignant)') }}</span></label>
                                                <textarea class="textarea textarea-bordered w-full" name="appreciation" rows="2" x-ref="ta">{{ $log->appreciation }}</textarea>
                                                <button class="btn btn-xs btn-outline btn-success mt-1"><i class="fa-solid fa-comment mr-1"></i>{{ __('Sauver') }}</button>
                                            </form>
                                        @else
                                            {{ $log->appreciation }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center italic">{{ __('Aucun journal saisi pour l’instant.') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
