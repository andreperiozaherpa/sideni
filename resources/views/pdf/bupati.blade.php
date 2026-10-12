@extends('pdf.layouts.letter')

@php
    $riskLabel = match ($report->risk_level->value) {
        'low' => 'Rendah',
        'medium' => 'Sedang',
        'high' => 'Tinggi',
        default => '-',
    };
@endphp

@section('document')
    <div class="judul-dokumen">LAPORAN HASIL DETEKSI DINI (SIDENI)</div>

    <table class="meta-table">
        <tr><td class="meta-label">Yth.</td><td class="meta-colon">:</td><td>Bapak Bupati Way Kanan</td></tr>
        <tr><td>Dari</td><td>:</td><td>Kepala Badan Kesatuan Bangsa dan Politik Kab. Way Kanan</td></tr>
        <tr><td>Tanggal</td><td>:</td><td>{{ $generatedAt->locale('id')->translatedFormat('d F Y') }}</td></tr>
        <tr><td>Nomor</td><td>:</td><td>{{ $letterNumber }}</td></tr>
        <tr><td>Perihal</td><td>:</td><td>Laporan Situasi - {{ $report->reportCategory?->name ?? '-' }}</td></tr>
    </table>

    <div class="content">
        <p>Bersama ini dilaporkan hasil pantauan Sistem Informasi Deteksi Dini (SIDENI) dengan rincian sebagai berikut:</p>

        <ol>
            <li>
                <strong>Pokok Kejadian:</strong><br>
                {{ $report->description }}
            </li>
            <li>
                <strong>Lokasi &amp; Waktu:</strong><br>
                {{ $report->location_name }}, pada tanggal {{ ($report->occurred_at ?? $report->reported_at)?->locale('id')->translatedFormat('d F Y') }}.
            </li>
            <li>
                <strong>Tingkat Kerawanan:</strong><br>
                {{ $riskLabel }}
            </li>
            <li>
                <strong>Tindak Lanjut Kesbangpol:</strong><br>
                {{ $followUpSummary }}
            </li>
            <li>
                <strong>Saran Tindak:</strong><br>
                {{ $recommendation }}
            </li>
        </ol>

        <p>Demikian laporan ini disampaikan untuk menjadi maklum. Mohon petunjuk lebih lanjut.</p>
    </div>

    @include('pdf.partials.signature')
@endsection
