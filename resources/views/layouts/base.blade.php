<!DOCTYPE html>

@if (\Request::is('rtl'))
    <html lang="ar" dir="rtl">
@else
    <html lang="en">
@endif

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if (env('IS_DEMO'))
        <meta name="keywords"
            content="creative tim, updivision, html dashboard, TALL, Tailwind, Alpine.js, Livewire, html css dashboard TALL, soft ui dashboard TALL, soft ui dashboard TALL, soft ui dashboard, TALL soft ui dashboard, soft ui admin, TALL dashboard, TALL dashboard, TALL admin, web dashboard, Taildwind dashboard TALL, css3 dashboard, Tailwind admin, soft ui dashboard Tailwind, frontend, responsive Tailwind dashboard, soft ui dashboard, soft ui TALL dashboard" />
        <meta name="description"
            content="A free full stack app with dozens of UI components powered by Tailwind, Alpine.js, Laravel and Livewire" />
        <meta itemprop="name" content="Soft UI Dashboard TALL by Creative Tim & UPDIVISION " />
        <meta itemprop="description"
            content="A free full stack app with dozens of UI components powered by Tailwind, Alpine.js, Laravel and Livewire" />
        <meta itemprop="image"
            content="https://s3.amazonaws.com/creativetim_bucket/products/683/original/soft-ui-dashboard-tall.jpg" />
        <meta name="twitter:card" content="product" />
        <meta name="twitter:site" content="@creativetim " />
        <meta name="twitter:title" content="Soft UI Dashboard TALL by Creative Tim & UPDIVISION" />
        <meta name="twitter:description"
            content="A free full stack app with dozens of UI components powered by Tailwind, Alpine.js, Laravel and Livewire" />
        <meta name="twitter:creator" content="@creativetim" />
        <meta name="twitter:image"
            content="https://s3.amazonaws.com/creativetim_bucket/products/683/original/soft-ui-dashboard-tall.jpg" />
        <meta property="fb:app_id" content="655968634437471" />
        <meta property="og:title" content="Soft UI Dashboard TALL by Creative Tim & UPDIVISION" />
        <meta property="og:type" content="article" />
        <meta property="og:url" content="https://www.creative-tim.com/live/soft-ui-dashboard-tall" />
        <meta property="og:image"
            content="https://s3.amazonaws.com/creativetim_bucket/products/683/original/soft-ui-dashboard-tall.jpg" />
        <meta property="og:description"
            content="A free full stack app with dozens of UI components powered by Tailwind, Alpine.js, Laravel and Livewire" />
        <meta property="og:site_name" content="Creative Tim" />
    @endif

    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets') }}/img/apple-icon.png" />
    <link rel="icon" type="image/png" href="{{ asset('assets') }}/img/favicon.png" />
    <title>Devnex Research</title>
    
    <!-- Fonts and icons -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Nucleo Icons -->
    <link href="{{ asset('assets') }}/css/nucleo-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets') }}/css/nucleo-svg.css" rel="stylesheet" />
    <!-- Popper -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.5/umd/popper.min.js"></script>

    <!-- Main Styling -->
    <link href="{{ asset('assets') }}/css/styles.css?v=1.0.3" rel="stylesheet" />

    @vite('resources/css/app.css')
    @livewireStyles
</head>

<body class="m-0 font-sans antialiased font-normal text-size-base leading-default bg-gray-50 text-slate-500">
    {{ $slot }}

    @livewireScripts

    <!-- Your existing scripts -->
    <script src="{{ asset('assets') }}/js/plugins/chartjs.min.js" async></script>
    <script src="{{ asset('assets') }}/js/plugins/perfect-scrollbar.min.js" async></script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script src="{{ asset('assets') }}/js/soft-ui-dashboard-tailwind.js?v=1.0.3" async></script>

    <!-- Fix for sidenav error -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            const sidenav = document.getElementById('sidenav-burger');
            if (!sidenav) {
                console.log('ℹ️ Sidenav not present on this page');
            }
        }, 1000);
    });
    </script>
</body>
</html>