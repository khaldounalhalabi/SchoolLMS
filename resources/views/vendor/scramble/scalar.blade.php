<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ $config->get('ui.title') ?? config('app.name') . ' - API Docs' }}</title>
</head>
<body>
<div id="app"></div>
<script src="{{ asset('vendor/scramble/scalar-api-reference.js') }}"></script>

<script>
    Scalar.createApiReference('#app', {
        content: @json($spec),
        ...@json($config->renderer()->all(except: ['cdn'])),
        customFetch: (input, init) => window.fetch(input, { ...init, credentials: 'omit' })
    })
</script>
</body>
</html>
