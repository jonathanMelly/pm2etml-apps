@component('mail::message')
# Bonjour !

Le(s) évaluation(s) suivante(s) ont été mise(s) à jour :

@component('mail::table')
    | Classe| Élève  | Job | Avant | Maintenant  |
    | ------|--------|-----| :---- | ---------:  |
    @foreach($informations as $i)
        | {{$i['group']}} | {{$i['name']}} | {{$i['job']}} | {!! gradeHtml($i['log']->old_result) !!} {!! mdSmall(df($i['log']->old_date),true) !!} | **{!! gradeHtml($i['log']->new_result) !!}** {!! mdSmall(df($i['log']->new_date),true) !!} |
    @endforeach
@endcomponent

@component('mail::panel')
    Ce message est envoyé à titre préventif. En cas de modification non voulue, vous êtes prié de contacter le responsable de la plateforme.
@endcomponent

@component('mail::button', ['url' => url('/')])
Aller sur la plateforme
@endcomponent

Belle journée,<br>
{{ config('app.name') }}
@endcomponent
