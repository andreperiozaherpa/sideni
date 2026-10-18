@props(['id', 'url', 'columns', 'footerSearch' => true])
<div class="overflow-x-auto rounded-xl border border-white/50 bg-white/40">
    <table id="{{ $id }}" class="datatable min-w-full text-left text-sm" data-url="{{ $url }}" data-columns='@json($columns)'>
        <thead class="text-slate-500">
            <tr>
                @foreach ($columns as $column)
                    <th class="px-4 py-3">{{ $column['title'] }}</th>
                @endforeach
            </tr>
        </thead>
        @if ($footerSearch)
            <tfoot>
                <tr>
                    @foreach ($columns as $column)
                        <th class="px-2 pb-3">
                            @if (($column['searchable'] ?? true) !== false)
                                <input type="text" class="field px-2 py-1 text-xs" placeholder="Cari…" data-column-search>
                            @endif
                        </th>
                    @endforeach
                </tr>
            </tfoot>
        @endif
    </table>
</div>
