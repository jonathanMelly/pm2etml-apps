<input type="checkbox" id="delete-job-modal" class="modal-toggle">
<div class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">{{__('Do you really want to delete the following job ?',)}}</h3>
        <div class="italic">({{__('Note: currently associated contracts won’t be affected')}})</div>
        <p class="py-4">

        <div class="flex flex-wrap">
            <div class="w-1/4">
                <i class="fa-solid fa-project-diagram fa-align-center mr-2"></i> <strong> {{__('Project')}}
                    :</strong>
            </div>
            <div class="w-3/4" x-text="jobNameToDelete">

            </div>
        </div>
        </p>

        <form id="delete-job-form" method="post">
            @csrf
            @method('delete')

        </form>

        <div class="modal-action">
            <button id="delete-job-modal-submit" disabled
                    @click="document.querySelector('#delete-job-form').submit()"
                    type="button" class="btn btn-outline btn-error">{{__('Yes')}}</button>

            <label for="delete-job-modal" class="btn"
                   @click="document.querySelector('#delete-job-modal-submit').disabled=true">{{__('No')}}</label>
        </div>

    </div>
</div>
