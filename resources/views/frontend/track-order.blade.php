@extends('layouts.store')

@section('title', 'Track your order — sss')
@section('page-key', 'track-order')

@section('content')
<div class="container-wide section"><div class="page-heading"><div class="crumb"><a href="index.html">Home</a> / Good things are on the way.</div><h1>Good things are on the way.</h1><p></p></div><div class="auth-card"><h2>Track your order</h2><p>Use your demo order number and the email entered at checkout.</p><form id="track-form"><div class="field"><label for="track-id">Order number</label><input class="form-control" id="track-id" name="track-id" type="text" value="" required placeholder="SSS-…"></div><div class="field"><label for="track-email">Email address</label><input class="form-control" id="track-email" name="track-email" type="email" value="" required ></div><button class="btn btn-dark w-100">Find my order ↗</button></form><div id="track-result" class="mt-4" aria-live="polite"></div></div></div>
@endsection

