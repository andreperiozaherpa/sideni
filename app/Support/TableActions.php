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
}
