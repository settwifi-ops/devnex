<!-- resources/views/payment/success.blade.php -->
@extends('layouts.app')

@section('title', 'Payment Successful - Premium App')

@section('content')
<div style="max-width: 600px; margin: 0 auto; text-align: center;">
    <div style="font-size: 4rem; color: #28a745;">✅</div>
    <h2>Payment Successful!</h2>
    <p>Your premium subscription has been activated successfully.</p>
    <p>You now have full access to all premium features.</p>
    
    <a href="{{ route('dashboard') }}" style="display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 1rem;">
        Go to Dashboard
    </a>
</div>
@endsection