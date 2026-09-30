@php
    $value = static function (string $field, string $fallback = '') use ($address): string {
        $oldValue = old($field);

        if (is_scalar($oldValue)) {
            return (string) $oldValue;
        }

        $addressValue = $address?->{$field} ?? $fallback;

        return is_scalar($addressValue) ? (string) $addressValue : '';
    };
    $isDefault = old('is_default', $address?->is_default ?? $isFirstAddress);
@endphp

<form method="POST" action="{{ $formAction }}" class="panel" novalidate>
    @csrf
    @if ($formMethod !== 'POST')
        @method($formMethod)
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h2 class="mb-0">{{ $heading }}</h2>
        <a class="text-link" href="{{ route('store.addresses') }}">← Back to addresses</a>
    </div>

    <div class="two-col">
        <div class="field">
            <label for="address-type">Address label</label>
            <input class="form-control @error('type') is-invalid @enderror" id="address-type" name="type" type="text"
                value="{{ $value('type', 'home') }}" maxlength="255" required autocomplete="off" placeholder="Home, work, or other">
            @error('type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label for="address-name">Full name</label>
            <input class="form-control @error('name') is-invalid @enderror" id="address-name" name="name" type="text"
                value="{{ $value('name', $customer->name) }}" maxlength="255" required autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="field">
        <label for="address-phone">Phone number</label>
        <input class="form-control @error('phone') is-invalid @enderror" id="address-phone" name="phone" type="tel"
            value="{{ $value('phone', $customer->phone ?? '') }}" maxlength="20" required autocomplete="tel">
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="field">
        <label for="address-line-1">Address line 1</label>
        <input class="form-control @error('address_line_1') is-invalid @enderror" id="address-line-1" name="address_line_1" type="text"
            value="{{ $value('address_line_1') }}" maxlength="255" required autocomplete="address-line1">
        @error('address_line_1')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="field">
        <label for="address-line-2">Address line 2 <span class="muted">(optional)</span></label>
        <input class="form-control @error('address_line_2') is-invalid @enderror" id="address-line-2" name="address_line_2" type="text"
            value="{{ $value('address_line_2') }}" maxlength="255" autocomplete="address-line2">
        @error('address_line_2')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="field">
        <label for="address-landmark">Landmark <span class="muted">(optional)</span></label>
        <input class="form-control @error('landmark') is-invalid @enderror" id="address-landmark" name="landmark" type="text"
            value="{{ $value('landmark') }}" maxlength="255" autocomplete="off">
        @error('landmark')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="two-col">
        <div class="field">
            <label for="address-city">City</label>
            <input class="form-control @error('city') is-invalid @enderror" id="address-city" name="city" type="text"
                value="{{ $value('city') }}" maxlength="255" required autocomplete="address-level2">
            @error('city')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label for="address-state">State</label>
            <input class="form-control @error('state') is-invalid @enderror" id="address-state" name="state" type="text"
                value="{{ $value('state') }}" maxlength="255" required autocomplete="address-level1">
            @error('state')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="two-col">
        <div class="field">
            <label for="address-postal-code">Postal code</label>
            <input class="form-control @error('postal_code') is-invalid @enderror" id="address-postal-code" name="postal_code" type="text"
                value="{{ $value('postal_code') }}" maxlength="20" required autocomplete="postal-code">
            @error('postal_code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label for="address-country">Country</label>
            <input class="form-control @error('country') is-invalid @enderror" id="address-country" name="country" type="text"
                value="{{ $value('country', 'India') }}" maxlength="255" required autocomplete="country-name">
            @error('country')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <label class="check mb-4">
        <input name="is_default" type="checkbox" value="1" @checked($isDefault)>
        Make this my default delivery address
    </label>
    @error('is_default')
        <p class="small text-danger">{{ $message }}</p>
    @enderror

    @if ($isFirstAddress)
        <p class="small muted">Your first saved address is automatically made the default.</p>
    @endif

    <button class="btn btn-dark" type="submit">Save address ↗</button>
</form>
