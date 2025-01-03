<div class="form-control">
    <label for="name-{{ $index }}" class="label flex justify-start">

        <img class="w-6 h-6 mr-2" src="{{ asset('img/criteria-name.svg') }}" alt="name">

        <span class="label-text text-start">{{ __('Name') }}</span>
    </label>

    <input type="text" id="name-{{ $index }}" name="criterias[{{ $index }}][name]"
        value="{{ $criteria->name ?? '' }}" class="input input-bordered w-full" required>

    <label for="category-{{ $index }}" class="label flex items-center mt-2 justify-start">
        <img class="w-6 h-6 mr-2" src="{{ asset('img/criteria-category.svg') }}" alt="category">

        <span class="label-text">{{ __('Category') }}</span>
    </label>
    <input type="text" id="category-{{ $index }}" name="criterias[{{ $index }}][category]"
        value="{{ __($criteria->category) }}" class="input input-bordered w-full" required>

    <label for="description-{{ $index }}" class="label flex items-center mt-2 justify-start">
        <img class="w-6 h-6 mr-2" src="{{ asset('img/criteria-description.svg') }}" alt="description">
        <span class="label-text">{{ __('Description') }}</span>
    </label>
    <textarea id="description-{{ $index }}" name="criterias[{{ $index }}][description]"
        class="textarea textarea-bordered w-full" required>{{ $criteria->description ?? '' }}</textarea>
</div>
