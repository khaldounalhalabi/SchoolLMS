@php
    $status = 403;
    $title = __('Access denied');
    $message = __('You do not have permission to access this page.');
    $actionUrl = auth()->check() ? route('dashboard') : route('login');
    $actionLabel = auth()->check() ? __('Go to dashboard') : __('Go to login');
@endphp

@include('errors.layout')
