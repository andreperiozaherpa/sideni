@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ \App\Support\Branding::logoUrl() }}" class="logo" alt="Lambang Kabupaten Way Kanan">
<div style="margin-top: 6px;">{{ config('app.name') }}</div>
</a>
</td>
</tr>
