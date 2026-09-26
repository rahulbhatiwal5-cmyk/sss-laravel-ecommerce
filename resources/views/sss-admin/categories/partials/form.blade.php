@php
    $selectedParentId = (string) old('parent_id', $category?->parent_id ?? '');
@endphp

<form method="POST" action="{{ $formAction }}">
    @csrf

    @if ($formMethod !== 'POST')
        @method($formMethod)
    @endif

    <div class="form-layout">
        <div>
            <section class="panel">
                <div class="panel-heading">
                    <div>
                        <h2>{{ $formHeading }}</h2>
                        <p>{{ $formDescription }}</p>
                    </div>
                </div>

                <div class="field">
                    <label for="name">Name <span>*</span></label>
                    <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" type="text"
                        value="{{ old('name', $category?->name) }}" maxlength="255" required autofocus
                        placeholder="e.g. Summer essentials"
                        @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                    @error('name')
                        <div class="invalid-feedback d-block" id="name-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="slug">Slug</label>
                    <input class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" type="text"
                        value="{{ old('slug', $category?->slug) }}" maxlength="255" placeholder="summer-essentials"
                        @error('slug') aria-invalid="true" aria-describedby="slug-error" @enderror>
                    <small>Leave blank to generate it from the name.</small>
                    @error('slug')
                        <div class="invalid-feedback d-block" id="slug-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="parent_id">Parent category</label>
                    <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id"
                        @error('parent_id') aria-invalid="true" aria-describedby="parent-id-error" @enderror>
                        <option value="">No parent (root category)</option>
                        @foreach ($parentCategories as $parentCategory)
                            <option value="{{ $parentCategory->id }}"
                                @selected($selectedParentId === (string) $parentCategory->id)>
                                {{ $parentCategory->name }} ({{ $parentCategory->slug }})
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <div class="invalid-feedback d-block" id="parent-id-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="description">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                        rows="5" placeholder="A short introduction to this collection."
                        @error('description') aria-invalid="true" aria-describedby="description-error" @enderror>{{ old('description', $category?->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback d-block" id="description-error">{{ $message }}</div>
                    @enderror
                </div>
            </section>
        </div>

        <aside>
            <section class="panel">
                <h2>Visibility</h2>
                <input name="is_active" type="hidden" value="0">
                <label class="check" for="is_active">
                    <input id="is_active" name="is_active" type="checkbox" value="1"
                        @checked(old('is_active', $category?->is_active ?? true))>
                    Make this category active
                </label>
                <p class="small muted">Inactive categories stay in admin, ready to use later.</p>
                @error('is_active')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </section>

            <section class="panel">
                <div class="field">
                    <label for="sort_order">Sort order <span>*</span></label>
                    <input class="form-control @error('sort_order') is-invalid @enderror" id="sort_order"
                        name="sort_order" type="number" min="0" max="2147483647" step="1"
                        value="{{ old('sort_order', $category?->sort_order ?? 0) }}" required
                        @error('sort_order') aria-invalid="true" aria-describedby="sort-order-error" @enderror>
                    <small>Lower numbers appear first.</small>
                    @error('sort_order')
                        <div class="invalid-feedback d-block" id="sort-order-error">{{ $message }}</div>
                    @enderror
                </div>
            </section>

            <section class="note-panel compact">
                <span class="eyebrow">READY TO SAVE</span>
                <p>Images will be added in a later category module.</p>
                <button class="btn btn-dark w-100" type="submit">{{ $submitLabel }}</button>
            </section>
        </aside>
    </div>
</form>
