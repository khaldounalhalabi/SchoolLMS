@php
    $status = 500;
    $title = __('Something went wrong');
    $message = __('We could not complete your request. Please try again later.');
    $actionUrl = auth()->check() ? route('dashboard') : route('login');
    $actionLabel = auth()->check() ? __('Go to dashboard') : __('Go to login');
@endphp

@include('errors.layout')
