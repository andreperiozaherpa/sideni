@extends('pdf.layouts.letter')

@section('document')
    <div class="judul-dokumen">SURAT PEMBERITAHUAN DAN REKOMENDASI</div>

    <table class="meta-table">
        <tr><td class="meta-label">Nomor</td><td class="meta-colon">:</td><td>{{ $letterNumber }}</td></tr>
        <tr><td>Lampiran</td><td>:</td><td>-</td></tr>
        <tr><td>Perihal</td><td>:</td><td>Pemberitahuan laporan diterima</td></tr>
    </table>

    <div class="content">
        <p>Kepada Yth.<br>
            Pemerintah Kampung {{ $report->village?->name ?? '-' }}<br>
            Kecamatan {{ $report->village?->district?->name ?? '-' }}<br>
            di Way Kanan</p>

        <p>Dengan hormat,</p>

        <p>Sehubungan dengan laporan deteksi dini potensi konflik sosial dengan nomor registrasi
            <strong>{{ $report->registration_number }}</strong> tanggal {{ $report->reported_at?->locale('id')->translatedFormat('d F Y') }}
            di Kampung {{ $report->village?->name ?? '-' }}, laporan Saudara telah <strong>diterima</strong> dan tercatat pada sistem SI-DENI
            dengan rincian: kategori kerawanan <strong>{{ $report->reportCategory?->name ?? '-' }}</strong>,
            jenis kejadian {{ $report->incidentType?->name ?? '-' }}, lokasi {{ $report->location_name }},
            tingkat risiko {{ ucfirst($report->risk_level->value) }}, status {{ str($report->status->value)->replace('_', ' ')->title() }}.</p>

        <p><strong>Rekomendasi tindakan pencegahan awal:</strong></p>
        <ol>
            <li>Melakukan pemantauan berkala di lokasi rawan konflik.</li>
            <li>Menjaga komunikasi dan koordinasi dengan tokoh masyarakat serta unsur Forkopimcam.</li>
            <li>Segera melaporkan perkembangan situasi kepada Admin Kesbangpol melalui aplikasi SI-DENI.</li>
            <li>Mengutamakan langkah persuasif dan mediasi dalam penanganan awal.</li>
        </ol>

        <p>Demikian pemberitahuan ini disampaikan. Atas perhatian dan kerja sama Saudara, kami ucapkan terima kasih.</p>
    </div>

    @include('pdf.partials.signature')
@endsection
