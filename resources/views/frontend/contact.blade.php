@extends('layouts.store')

@section('title', 'Let’s talk — sss')
@section('page-key', 'contact')

@section('content')
    <div class="container-wide section">
        <div class="page-heading">
            <div class="crumb"><a href="index.html">Home</a> / A little help? We’re here.</div>
            <h1>A little help? We’re here.</h1>
            <p></p>
        </div>
        <div class="two-col gap-large">
            <div><span class="eyebrow">CONTACT SSS</span>
                <h2>People, not just orders.</h2>
                <p>Questions about fit, delivery, or finding your next favourite? Leave a message.</p>
                <div class="panel">
                    <h3>Quick answers</h3>
                    <p><a href="shipping-returns.html">Shipping & returns ↗</a></p>
                    <p><a href="size-guide.html">Find your size ↗</a></p>
                    <p><a href="faq.html">Frequently asked questions ↗</a></p>
                </div>
            </div>
            <form class="panel"
                data-demo="Message preview complete. Connect this form to your Laravel contact controller to send messages.">
                <div class="field"><label for="contact-name">Your name</label><input class="form-control" id="contact-name"
                        name="contact-name" type="text" value="" required></div>
                <div class="field"><label for="contact-email">Email address</label><input class="form-control"
                        id="contact-email" name="contact-email" type="email" value="" required></div>
                <div class="field"><label for="contact-subject">Subject</label><input class="form-control"
                        id="contact-subject" name="contact-subject" type="text" value="" required></div>
                <div class="field"><label for="message">Your message</label>
                    <textarea id="message" name="message" class="form-control" rows="5" required></textarea>
                </div><button class="btn btn-dark">Send message ↗</button>
            </form>
        </div>
    </div>
@endsection
