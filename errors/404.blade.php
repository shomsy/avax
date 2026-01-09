{{-- File: resources/views/errors/404.blade.php --}}
        <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>404 – Page Not Found</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Roboto, sans-serif;
            background-color: #f9fafb;
            color: #374151;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            text-align: center;
            max-width: 500px;
        }

        h1 {
            font-size: 6rem;
            margin-bottom: 0;
            color: #1f2937;
        }

        p {
            font-size: 1.25rem;
            margin-top: 0.25rem;
            margin-bottom: 1.5rem;
            color: #6b7280;
        }

        a {
            text-decoration: none;
            color: #3b82f6;
            font-weight: 600;
        }

        a:hover {
            text-decoration: underline;
            color: #2563eb;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>404</h1>
    <p>The page you’re looking for doesn’t exist.</p>
    <a href="{{value: route(name: 'cashback.index') }}">← Back to Home</a>
</div>
</body>
</html>
