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
                    <label for="name">Brand name <span>*</span></label>
                    <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" type="text"
                        value="{{ old('name', $brand?->name) }}" maxlength="255" required autofocus
                        placeholder="e.g. Northstar"
                        @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                    @error('name')
                        <div class="invalid-feedback d-block" id="name-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="slug">Slug</label>
                    <input class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" type="text"
                        value="{{ old('slug', $brand?->slug) }}" maxlength="255" placeholder="northstar"
                        @error('slug') aria-invalid="true" aria-describedby="slug-error" @enderror>
                    <small>Leave blank to generate it from the brand name.</small>
                    @error('slug')
                        <div class="invalid-feedback d-block" id="slug-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="description">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                        rows="5" maxlength="65535" placeholder="A short description of this brand."
                        @error('description') aria-invalid="true" aria-describedby="description-error" @enderror>{{ old('description', $brand?->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback d-block" id="description-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="logo">Logo reference</label>
                    <input class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" type="text"
                        value="{{ old('logo', $brand?->logo) }}" maxlength="255" placeholder="e.g. brands/northstar.svg"
                        @error('logo') aria-invalid="true" aria-describedby="logo-error" @enderror>
                    <small>Optional database text reference only. Logo uploading is not part of this module.</small>
                    @error('logo')
                        <div class="invalid-feedback d-block" id="logo-error">{{ $message }}</div>
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
                        @checked(old('is_active', $brand?->is_active ?? true))>
                    Make this brand active
                </label>
                <p class="small muted">Inactive brands remain linked to existing products but can be managed in admin.</p>
                @error('is_active')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </section>

            <section class="note-panel compact">
                <span class="eyebrow">READY TO SAVE</span>
                <p>Check the name, slug, optional reference, and visibility before saving.</p>
                <button class="btn btn-dark w-100" type="submit">{{ $submitLabel }}</button>
            </section>
        </aside>
    </div>
</form>
