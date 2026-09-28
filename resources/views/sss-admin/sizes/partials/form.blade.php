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
                    <label for="name">Size label <span>*</span></label>
                    <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" type="text"
                        value="{{ old('name', $size?->name) }}" maxlength="255" required autofocus
                        placeholder="e.g. M or One size"
                        @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                    <small>Whitespace is trimmed and repeated spaces are normalized.</small>
                    @error('name')
                        <div class="invalid-feedback d-block" id="name-error">{{ $message }}</div>
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
                        @checked(old('is_active', $size?->is_active ?? true))>
                    Make this size active
                </label>
                <p class="small muted">Inactive sizes remain available in admin but should not be offered to shoppers later.</p>
                @error('is_active')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </section>

            <section class="panel">
                <div class="field">
                    <label for="sort_order">Sort order <span>*</span></label>
                    <input class="form-control @error('sort_order') is-invalid @enderror" id="sort_order"
                        name="sort_order" type="number" min="0" max="2147483647" step="1"
                        value="{{ old('sort_order', $size?->sort_order ?? 0) }}" required
                        @error('sort_order') aria-invalid="true" aria-describedby="sort-order-error" @enderror>
                    <small>Lower numbers appear first.</small>
                    @error('sort_order')
                        <div class="invalid-feedback d-block" id="sort-order-error">{{ $message }}</div>
                    @enderror
                </div>
            </section>

            <section class="note-panel compact">
                <span class="eyebrow">READY TO SAVE</span>
                <p>Check the label, visibility, and display order before saving.</p>
                <button class="btn btn-dark w-100" type="submit">{{ $submitLabel }}</button>
            </section>
        </aside>
    </div>
</form>
