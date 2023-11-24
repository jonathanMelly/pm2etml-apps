<x-app-layout>
    @push('custom-scripts')
        @once
        <script type="text/javascript" src="{{ URL::asset ('js/helper.js') }}"></script>
        @endonce
    @endpush

    <div class="sm:mx-6 flex flex-col gap-4">

            {{-- MY CONTRACTS --}}
        @hasanyrole(\App\Constants\RoleName::TEACHER.'|'.\App\Constants\RoleName::STUDENT)
            <div class="bg-base-200 bg-opacity-40 overflow-hidden shadow-sm sm:rounded-lg border-secondary border-2 border-opacity-20 hover:border-opacity-30">
                <div class="p-6">
                    <div class="prose pb-2 -p-6">
                        <h1 class="text-base-content">{{__('My contracts')}}</h1>
                    </div>

                    {{-- CONTRACTS AS A WORKER --}}
                    @role(\App\Constants\RoleName::STUDENT)
                    @if($contracts->isEmpty())
                        <p>{{__('No contracts, you may apply at')}} <a class="link-secondary" href="{{route('marketplace')}}">{{__('Market place')}}</a></p>
                    @else
                        <x-worker-contract-list :contracts="$contracts" />
                    @endempty
                    @endrole

                    {{-- CLIENTS CONTRACT (current workers) --}}
                    @role(\App\Constants\RoleName::TEACHER)
                    @if($jobs->isEmpty())
                        <p>{{__('No contracts')}}</p>
                    @else
                        <x-client-job-list :periodId="$periodId" :jobs="$jobs" />
                    @endempty
                    @endrole

                </div>
            </div>
        @endhasanyrole

            {{-- RESULTS --}}
        @if($evaluationsSummaryJsObject!="{}")
            <div x-data="{showSummary:$persist(false)}" class="bg-base-200 bg-opacity-60 overflow-hidden shadow-sm sm:rounded-lg border-secondary border-2 border-opacity-20 hover:border-opacity-30">
                <div class="p-6">
                    <div class="flex">
                        <i class="fa-solid fa-xl hover:cursor-pointer mt-5 mr-2" :class="showSummary?'fa-caret-down':'fa-caret-right'" @click="showSummary = !showSummary">
                        </i>
                        <div class="prose pb-2 -p-6">
                            <h1 class="text-base-content">{{__('Evaluation summary')}}</h1>
                        </div>
                        <div class="ml-2 mt-1">
                            <a class="btn btn-info btn-sm bg-opacity-50 hover:bg-opacity-100" target="_blank" href="{{route('evaluation-export')}}" >
                                <i class="fa-solid fa-download mr-1"></i>{{__('Export')}}
                            </a>
                        </div>
                    </div>
                    <div x-show="showSummary">
                        <x-summaries.evaluations :summary="$evaluationsSummaryJsObject"></x-summaries.evaluations>
                    </div>
                </div>
            </div>
        @endif

            {{-- TOOLS --}}
            <div class="bg-base-200 overflow-hidden shadow-sm sm:rounded-lg border-neutral border-2 border-opacity-20 hover:border-opacity-30">
                <div class="p-6">
                    <div class="prose pb-2 -p-6">
                        <h1 class="text-base-content">{{__('Internal tools')}}</h1>
                    </div>
                    <x-tools />
                </div>
            </div>

            {{-- JOKE--}}
            <div class="bg-gradient-to-r from-secondary/50 to-base-100 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="prose">
                        <h1 class="text-secondary-content">{{__('A bit of inspiration')}}</h1>
                    </div>
                    <p class="mt-2">
                        <i class="fa-solid fa-quote-left"></i>
                        Quand les membres d’une tribu se rassemblent en un même lieu, les inspirations
                        mutuelles s’intensifient. Dans tous les domaines, des groupes d’individus ont
                        suscité l’innovation sous l’effet de leurs influences réciproques et de l’impulsion collective.
                        <i class="fa-solid fa-quote-right"></i>
                    </p>
                    <i class="text-xs">Sir Ken Robinson (L’Élément, p.143)</i>
{{--                    <x-joke />--}}
                </div>
            </div>
        </div>

</x-app-layout>
