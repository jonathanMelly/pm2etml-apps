{{-- 
    Optimisation de la vue d'évaluation des contrats.
    - Simplification du JavaScript de gestion du toggle.
    - Suppression du JS inutilisé (contractsEvaluations, DOMContentLoaded pour toggle).
    - Clarification de la soumission du formulaire.
    - Nettoyage des commentaires obsolètes.
--}}

<x-app-layout>
    @push('custom-scripts')
        <script>
            // Fonction simplifiée pour basculer les classes CSS du toggle.
            // Elle n'est plus responsable de la logique d'état (gérée par Alpine.js).
            function updateToggleClass(element, isChecked) {
                element.classList.remove('bg-error', 'bg-success');
                element.classList.add('bg-' + (isChecked ? 'success' : 'error'));
            }

            // Si nécessaire, une fonction globale pour gérer la soumission du formulaire
            // pourrait être ajoutée ici, mais pour l'instant, le comportement par défaut suffit.
        </script>
    @endpush

    <form id="eval" action="{{ route('contracts.evaluate') }}" method="post">
        @csrf
        {{-- L'input hidden contractsEvaluations était présent mais inutilisé, il est supprimé. --}}
        
        <div class="sm:mx-6 bg-base-200 bg-opacity-50 rounded-box sm:p-3 p-1 flex flex-col items-center">

            <div class="stats shadow mb-4">
                <div class="stat">
                    <div class="stat-figure text-secondary">
                        <div class="avatar">
                            <div class="w-24 rounded">
                                <img src="{{ route('dmz-asset', ['file' => $job->image?->storage_path]) }}" alt="{{ $job->title }}">
                            </div>
                        </div>
                    </div>
                    <div class="stat-title">
                        <i class="fa-solid fa-calendar-day"></i> 
                        {{ \Illuminate\Support\Carbon::parse($contracts->min('start'))->format(\App\SwissFrenchDateFormat::DATE) }}
                        <i class="fa-solid fa-arrow-right"></i>
                        <i class="fa-solid fa-calendar-days"></i> 
                        {{ \Illuminate\Support\Carbon::parse($contracts->max('end'))->format(\App\SwissFrenchDateFormat::DATE) }}
                    </div>
                    <div class="stat-value">{{ $job->title }}</div>
                    <div class="stat-desc">
                        {{ trans_choice(":number evaluation|:number evaluations", sizeof($contracts), ['number' => sizeof($contracts)]) }}
                    </div>
                </div>
            </div>

            <table class="table table-compact table-zebra w-auto">
                <thead>
                    <tr>
                        <th>{{ __('Worker(s)') }}</th>
                        <th class="w-96 text-center">{{ __('Gave satisfaction') }}</th>
                        <th>{{ __('Last evaluated') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contracts as $contract)
                        @foreach($contract->workersContracts as $workerContract)
                            @php
                                /* @var $contract \App\Models\Contract */
                                /* @var $workerContract \App\Models\WorkerContract */

                                $commentName = 'comment-' . $workerContract->id;
                                $successName = 'success-' . $workerContract->id;

                                // Par défaut, les contrats sont considérés comme validés (moins de travail pour l'enseignant)
                                // Utilisation de old() pour persister la valeur en cas de validation échouée
                                $isChecked = old($successName, $workerContract->alreadyEvaluated() ? $workerContract->success : true);
                                // S'assurer que la valeur est un booléen pour Alpine.js
                                $isChecked = filter_var($isChecked, FILTER_VALIDATE_BOOLEAN);
                            @endphp
                            <tr class="h-16" x-data="{ checked: {{ $isChecked ? 'true' : 'false' }} }">
                                <td class="">{{ $workerContract->groupMember->user->getFirstnameL() }}</td>
                                <td class="text-center w-64">
                                    {{-- ID du WorkerContract --}}
                                    <input type="hidden" name="workersContracts[]" value="{{ $workerContract->id }}">

                                    {{-- Champ caché pour la valeur de succès --}}
                                    <input type="hidden" :value="checked" name="{{ $successName }}">

                                    {{-- Toggle UI. L'état est géré par Alpine.js (:checked, @change). --}}
                                    <input 
                                        type="checkbox" 
                                        class="toggle"
                                        :checked="checked"
                                        @change="checked = $event.target.checked; updateToggleClass($el, checked)"
                                        name="toggle-{{ $workerContract->id }}" 
                                        value="{{ $workerContract->id }}"
                                    >

                                    {{-- Zone de texte pour le commentaire --}}
                                    <textarea 
                                        placeholder="{{ __('What must be improved') }}..."
                                        class="textarea h-10 pl-1 border-error border text-xs @error($commentName) border-2 border-dashed @enderror"
                                        name="{{ $commentName }}"
                                        x-show="!checked"
                                    >{{ old($commentName, $workerContract->success_comment) }}</textarea>
                                    
                                    @error($commentName)
                                    <br/><i class="text-xs text-error">{{ $errors->first($commentName) }}</i>
                                    @enderror
                                </td>
                                <td class="text-center">
                                    {{ $workerContract->alreadyEvaluated() 
                                        ? $workerContract->success_date->format(\App\SwissFrenchDateFormat::DATE_TIME) 
                                        : __('-') 
                                    }}
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3"></th>
                    </tr>
                </tfoot>
            </table>

            {{-- Bouton de soumission standard. Le formulaire se soumet normalement. --}}
            <button type="submit" class="btn my-2 btn-primary">
                {{ __('Save evaluation results') }}
            </button>
        </div>
    </form>
</x-app-layout>