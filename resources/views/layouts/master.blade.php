@props([
    'title' => 'SOLARIS - Hệ Thống Năng Lượng Mặt Trời',
    'activeTab' => 'home'
])
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link rel="icon" type="image/svg+xml" href="assets/solar-logo.svg">
    <link rel="stylesheet" href="resources/css/solaris-theme.css">
</head>
<body class="solar-bg-grid">
    <div class="glow-ambient-1"></div>
    <div class="glow-ambient-2"></div>

    <x-organisms.header />
    <x-organisms.navbar :activeTab="$activeTab" />

    <main class="sol-main-content">
        {{ $slot }}
    </main>

    <x-organisms.footer />
</body>
</html>
