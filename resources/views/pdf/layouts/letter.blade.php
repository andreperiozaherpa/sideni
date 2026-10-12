<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    @include('pdf.partials.styles')
</head>
<body>
    @include('pdf.partials.letterhead')

    @yield('document')

    <div class="footer-note">Dokumen dicetak otomatis dari Sistem Informasi Deteksi Dini (SIDENI)</div>
</body>
</html>
