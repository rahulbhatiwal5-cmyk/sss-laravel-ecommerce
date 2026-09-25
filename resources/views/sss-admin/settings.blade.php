@extends('layouts.sss-admin')
@section('title', 'Store settings')
@section('subtitle', 'A few details that make this store yours.')
@section('actions')

@endsection
@section('content')
    <form onsubmit="return false"
        data-preview-form="Settings validated. This preview does not change your store configuration.">
        <div class="form-layout">
            <div>
                <section class="panel">
                    <div class="panel-heading">
                        <div>
                            <h2>Store profile</h2>
                            <p>The essentials your customers should know.</p>
                        </div>
                    </div>
                    <div class="field"><label for="store-name">Store name <span>*</span></label><input class="form-control"
                            id="store-name" value="sss" required></div>
                    <div class="two-col">
                        <div class="field"><label for="store-email">Contact email <span>*</span></label><input
                                type="email" class="form-control" id="store-email" value="hello@example.test" required>
                        </div>
                        <div class="field"><label for="store-phone">Contact phone</label><input type="tel"
                                class="form-control" id="store-phone" placeholder="Add your customer support number"></div>
                    </div>
                    <div class="field"><label for="store-tagline">Tagline</label><input class="form-control"
                            id="store-tagline" value="Simple pieces. Strong style."></div>
                </section>
                <section class="panel">
                    <h2>Shipping defaults</h2>
                    <div class="two-col">
                        <div class="field"><label for="shipping-charge">Standard shipping (₹)</label><input
                                class="form-control" type="number" min="0" step="0.01" id="shipping-charge"
                                value="99" required></div>
                        <div class="field"><label for="free-shipping">Free shipping threshold (₹)</label><input
                                class="form-control" type="number" min="0" step="0.01" id="free-shipping"
                                value="2999" required></div>
                    </div>
                    <div class="field"><label for="return-window">Return window (days)</label><input class="form-control"
                            type="number" min="0" max="365" id="return-window" value="14" required></div>
                </section>
            </div>
            <aside>
                <section class="panel">
                    <h2>Regional settings</h2>
                    <div class="field"><label for="currency">Currency</label><select class="form-select" id="currency">
                            <option>INR — Indian Rupee (₹)</option>
                        </select></div>
                    <div class="field"><label for="timezone">Time zone</label><select class="form-select" id="timezone">
                            <option>Asia/Kolkata (UTC +05:30)</option>
                        </select></div>
                </section>
                <section class="note-panel compact"><span class="eyebrow">MAKE IT YOUR OWN</span>
                    <p>These are layout defaults. Connect this form to your store settings model to save them.</p><button
                        class="btn btn-dark w-100">Preview settings ↗</button>
                </section>
            </aside>
        </div>
    </form>
@endsection
