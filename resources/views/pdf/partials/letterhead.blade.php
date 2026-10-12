<table class="kop-surat">
    <tr>
        <td class="logo-placeholder">
            @if (! empty($institution['logo']))
                <img src="{{ $institution['logo'] }}" alt="Logo" width="70">
            @endif
        </td>
        <td class="header-text">
            <h2>PEMERINTAH KABUPATEN WAY KANAN</h2>
            <h1>{{ strtoupper($institution['name']) }}</h1>
            @if (! empty($institution['address']))
                <p>{{ $institution['address'] }}</p>
            @endif
        </td>
    </tr>
</table>
