@php
    $codeValue = old('code', $color?->code);
    $pickerValue = is_string($codeValue) && preg_match('/^#[0-9A-F]{6}$/i', $codeValue)
        ? strtoupper($codeValue)
        : '#000000';
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
                    <label for="name">Color name <span>*</span></label>
                    <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" type="text"
                        value="{{ old('name', $color?->name) }}" maxlength="255" required autofocus
                        placeholder="e.g. Navy blue"
                        @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                    <small>Whitespace is trimmed and repeated spaces are normalized.</small>
                    @error('name')
                        <div class="invalid-feedback d-block" id="name-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="two-col">
                    <div class="field">
                        <label for="color-code-picker">Color picker</label>
                        <input class="form-control" id="color-code-picker" type="color" value="{{ $pickerValue }}"
                            aria-describedby="color-code-help">
                    </div>

                    <div class="field">
                        <label for="code">Color code</label>
                        <input class="form-control @error('code') is-invalid @enderror" id="code" name="code" type="text"
                            value="{{ $codeValue }}" maxlength="255" inputmode="text" autocomplete="off"
                            placeholder="#1A2B3C" aria-describedby="color-code-help"
                            @error('code') aria-invalid="true" aria-describedby="color-code-help code-error" @enderror>
                        <small id="color-code-help">Optional hex code. Use #RRGGBB or #RGB.</small>
                        @error('code')
                            <div class="invalid-feedback d-block" id="code-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </section>
        </div>

        <aside>
            <section class="panel">
                <h2>Visibility</h2>
                <input name="is_active" type="hidden" value="0">
                <label class="check" for="is_active">
                    <input id="is_active" name="is_active" type="checkbox" value="1"
                        @checked(old('is_active', $color?->is_active ?? true))>
                    Make this color active
                </label>
                <p class="small muted">Inactive colors remain available in admin but should not be offered to shoppers later.</p>
                @error('is_active')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </section>

            <section class="note-panel compact">
                <span class="eyebrow">READY TO SAVE</span>
                <p>Check the name, code, and visibility before saving.</p>
                <button class="btn btn-dark w-100" type="submit">{{ $submitLabel }}</button>
            </section>
        </aside>
    </div>
</form>

<script>
    (() => {
        const picker = document.getElementById('color-code-picker');
        const code = document.getElementById('code');

        if (!picker || !code) {
            return;
        }

        picker.addEventListener('input', () => {
            code.value = picker.value.toUpperCase();
        });

        code.addEventListener('input', () => {
            const value = code.value.trim();

            if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
                picker.value = value;
            }
        });
    })();
</script>
