<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>📦 Dump Output</title>
    <link rel="stylesheet" href="@asset('assets/gemdump.css')">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/highlight.js@11.9.0/styles/github-dark.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/highlight.js@11.9.0/lib/highlight.min.js"></script>
    <script>window.addEventListener('DOMContentLoaded', () => hljs.highlightAll())</script>
</head>
<body class="bg-dark text-light p-4 font-monospace" x-data="{ search: '', matchIndex: 0, results: [] }">

<div class="container-fluid">
    <h3 class="text-warning mb-3">
        🔍 Dump from <span class="text-danger">{{value: $file }}</span> : <span
                class="text-info">{{value: $line }}</span>
    </h3>

    <div class="input-group mb-4 w-50">
        <span class="input-group-text bg-secondary text-white">Search</span>
        <input x-model="search" @input="highlightSearch()" @keydown.enter="nextMatch()"
               class="form-control bg-dark text-white border-secondary">
        <button class="btn btn-outline-light" @click="prevMatch()">◀</button>
        <button class="btn btn-outline-light" @click="nextMatch()">▶</button>
    </div>

    @foreach ($args as $arg)
        <pre class="gemdump" x-ref="dump">
                {{value: var_export(value: $arg, return: true) }}
            </pre>
    @endforeach
</div>

<script src="@asset('assets/gemdump.js')"></script>
</body>
</html>
