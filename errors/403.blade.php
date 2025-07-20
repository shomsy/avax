{{-- File: resources/views/errors/403.blade.php --}}
        <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>403 – Access Denied</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Bootstrap 5 CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Optional: Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="text-center px-3">
    <h1 class="display-1 fw-bold text-danger">403</h1>
    <p class="fs-4 text-danger-emphasis fw-semibold">
        <i class="bi bi-shield-exclamation me-2"></i>
        {{ $message ?? 'You do not have permission to access this page.' }}
    </p>
    <a href="{{ route('cashback.index') }}" class="btn btn-primary mt-3">
        ← Back to Home
    </a>
</div>

</body>
</html>
