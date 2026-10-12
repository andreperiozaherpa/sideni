@php
    $signer = $signer ?? [];
    $signerTitle = $signer['title'] ?? 'KEPALA BADAN KESATUAN BANGSA DAN POLITIK';
    $signerSubtitle = $signer['subtitle'] ?? 'KABUPATEN WAY KANAN,';
    $signerName = $signer['name'] ?? ($institution['head_name'] ?? null);
    $signerRank = $signer['rank'] ?? ($institution['head_rank'] ?? null);
    $signerNip = $signer['nip'] ?? ($institution['head_nip'] ?? null);
@endphp

<table class="signature-container">
    <tr>
        <td style="width: 55%;"></td>
        <td class="signature">
            <p>{{ $signerTitle }}<br>{{ $signerSubtitle }}</p>

            <p class="signature-name">{{ $signerName ?? '..............................' }}</p>
            <p class="signature-nip">
                @if ($signerRank){{ $signerRank }}<br>@endif
                @if ($signerNip)NIP. {{ $signerNip }}@endif
            </p>
        </td>
    </tr>
</table>
