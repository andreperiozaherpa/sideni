<?php

namespace App\Support;

class TableActions
{
    /**
     * Build the standard "Ubah / Hapus" action cell for a DataTables row.
     */
    public static function editDelete(string $editUrl, string $deleteUrl, string $confirm): string
    {
        return '<div class="flex gap-3">'
            .'<a class="link" href="'.e($editUrl).'">Ubah</a>'
            .'<form method="POST" action="'.e($deleteUrl).'" data-confirm="'.e($confirm).'">'.csrf_field().method_field('DELETE')
            .'<button class="font-semibold text-rose-600" type="submit">Hapus</button></form>'
            .'</div>';
    }

    /**
     * Build a compact action button for a DataTables cell.
     */
    public static function button(string $label, string $url, ?string $gradient = null): string
    {
        $classes = $gradient === null
            ? 'border border-white/60 bg-white/70 text-slate-700 hover:bg-white'
            : 'bg-gradient-to-r '.$gradient.' text-white shadow-sm hover:brightness-110';

        return '<a class="inline-flex items-center whitespace-nowrap rounded-lg px-3 py-1.5 text-xs font-semibold transition '.$classes.'" href="'.e($url).'">'.e($label).'</a>';
    }

    /**
     * Build a compact delete form button for a DataTables cell.
     */
    public static function deleteButton(string $url, string $confirm): string
    {
        return '<form method="POST" action="'.e($url).'" data-confirm="'.e($confirm).'">'
            .csrf_field().method_field('DELETE')
            .'<button type="submit" class="inline-flex items-center whitespace-nowrap rounded-lg px-3 py-1.5 text-xs font-semibold transition bg-gradient-to-r from-rose-600 to-red-600 text-white shadow-sm shadow-rose-500/25 hover:brightness-110">Hapus</button>'
            .'</form>';
    }
}
