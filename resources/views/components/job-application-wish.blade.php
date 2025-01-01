<div>
    <label class="input-group flex justify-between">
        <div class="self-center justify-self-end">{{__('Wish priority')}}</div>
        <input class="input w-24 text-lg" name="wish_priority"
            min="{{\App\Models\JobDefinition::MIN_WISH_PRIORITY}}"
            max="{{\App\Models\JobDefinition::MAX_WISH_PRIORITY}}"
            type="number" x-model="time" value="1">
    </label>
</div>