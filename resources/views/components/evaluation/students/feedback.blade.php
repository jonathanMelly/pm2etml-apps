<div id="id-{{ $studentId }}-small_finalResult"
    class="text-white text-sm rounded-xl shadow-lg p-3 absolute top-10 right-24 hidden transition-all duration-500 ease-in-out transform scale-95 hover:scale-100">
    <h3 class="text-xs font-semibold" id="smallResultTitle-{{ $studentId }}"></h3>
    <p id="smallResultContent" class="text-lg font-medium"></p>
</div>

<div id="errors-{{ $studentId }}"
    class="error-messages text-sm text-red-600 bg-red-50 border border-red-100 rounded-md m-2 p-4 hidden" role="alert">
    <!-- Le message d'erreur sera inséré dynamiquement ici -->
</div>
