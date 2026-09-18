<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIGMA Disaster Intelligence')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: {
            sigma: { red: '#C62828', orange: '#F97316', green: '#16A34A', navy: '#1F2937', cloud: '#F3F6FC' }
        }}}};
    </script>
</head>
<body class="min-h-screen bg-sigma-cloud text-sigma-navy">
    @yield('content')
</body>
</html>
