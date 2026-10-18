@php
    $seoTitle = $title ?? config('app.name');
    $seoDescription = 'SI-DENI (Sistem Deteksi Dini Konflik Sosial) Kabupaten Way Kanan — kanal pelaporan dan deteksi dini potensi konflik sosial yang cepat, faktual, terverifikasi, dan berbasis bukti.';
    $seoCanonical = url()->current();
    $seoImage = asset('og-image.png');
@endphp

<meta name="description" content="{{ $seoDescription }}">
<meta name="keywords" content="SI-DENI, deteksi dini konflik, konflik sosial, Way Kanan, Kesbangpol Way Kanan, laporan konflik, sistem pelaporan, kesbangpol">
<meta name="author" content="Badan Kesbangpol Kabupaten Way Kanan">
<meta name="theme-color" content="#0284c7">
<meta name="robots" content="index, follow">
<link rel="canonical" href="{{ $seoCanonical }}">

<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon-192x192.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoCanonical }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:locale" content="id_ID">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:image" content="{{ $seoImage }}">
