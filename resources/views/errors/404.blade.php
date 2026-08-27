@php
    $status = 404;
    $title = __('Page not found');
    $message = __('The page you are looking for does not exist or may have been moved.');
    $actionUrl = auth()->check() ? route('dashboard') : route('login');
    $actionLabel = auth()->check() ? __('Go to dashboard') : __('Go to login');
@endphp

@include('errors.layout')
