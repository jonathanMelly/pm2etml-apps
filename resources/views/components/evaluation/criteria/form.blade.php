<div class="form-control">
    <!-- Nom -->
    <label for="name-{{ $index }}" class="label flex justify-start items-center">
        <img class="w-6 h-6 mr-2" src="{{ asset('img/criteria-name.svg') }}" alt="name" />
        <span class="label-text text-start">{{ __('Name') }}</span>
    </label>
    <input
        type="text"
        id="name-{{ $index }}"
        name="criteria[{{ $index }}][name]"
        value="{{ old("criteria.$index.name", $criteria->name ?? '') }}"
        class="input input-bordered w-full"
        required
    />

    <!-- Catégorie -->
    <label for="category-{{ $index }}" class="label flex justify-start items-center mt-4">
        <img class="w-6 h-6 mr-2" src="{{ asset('img/criteria-category.svg') }}" alt="category" />
        <span class="label-text">{{ __('Category') }}</span>
    </label>
    <input
        type="text"
        id="category-{{ $index }}"
        name="criteria[{{ $index }}][category]"
        value="{{ old("criteria.$index.category", __($criteria->category ?? '')) }}"
        class="input input-bordered w-full"
        required
    />

    <!-- Description -->
    <label for="description-{{ $index }}" class="label flex justify-start items-center mt-4">
        <img class="w-6 h-6 mr-2" src="{{ asset('img/criteria-description.svg') }}" alt="description" />
        <span class="label-text">{{ __('Description') }}</span>
    </label>
    <textarea
        id="description-{{ $index }}"
        name="criteria[{{ $index }}][description]"
        class="textarea textarea-bordered w-full"
        required
    >{{ old("criteria.$index.description", $criteria->description ?? '') }}</textarea>
</div>
