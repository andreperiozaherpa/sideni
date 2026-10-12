<style>
    @page { margin: 1.8cm 2cm; }

    body {
        font-family: "Times New Roman", Times, serif;
        font-size: 11pt;
        line-height: 1.4;
    }

    /* Kop Surat */
    .kop-surat { width: 100%; border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
    .kop-surat td { vertical-align: middle; }
    .logo-placeholder { width: 80px; text-align: center; }
    .header-text { text-align: center; }
    .header-text h2, .header-text h1 { margin: 0; font-weight: bold; }
    .header-text h2 { font-size: 14pt; }
    .header-text h1 { font-size: 16pt; }
    .header-text p { margin: 0; font-size: 10pt; }

    /* Judul Dokumen */
    .judul-dokumen { text-align: center; font-weight: bold; text-decoration: underline; margin-bottom: 16px; }

    /* Tabel Metadata */
    .meta-table { width: 100%; margin-bottom: 16px; }
    .meta-table td { vertical-align: top; padding: 2px 0; }
    .meta-label { width: 100px; }
    .meta-colon { width: 15px; text-align: center; }

    /* Isi Konten */
    .content { text-align: justify; }
    .content ol { padding-left: 20px; margin-top: 10px; }
    .content li { margin-bottom: 6px; }

    /* Tanda Tangan */
    .signature-container { width: 100%; margin-top: 24px; border-collapse: collapse; }
    .signature-container td { vertical-align: top; }
    .signature { text-align: center; }
    .signature-name { font-weight: bold; text-decoration: underline; margin-top: 58px; margin-bottom: 0; }
    .signature-nip { margin-top: 0; }

    /* Footer */
    .footer-note { position: fixed; bottom: 0; left: 0; font-size: 10pt; font-style: italic; color: #555; }

    /* Clearfix */
    .clearfix::after { content: ""; clear: both; display: table; }

    /* Tabel kotak (lembar disposisi) */
    .box-table { width: 100%; border-collapse: collapse; }
    .box-table td { border: 1px solid #000; padding: 6px; vertical-align: top; }
    .box-table .box-label { width: 175px; background: #f2f2f2; font-weight: bold; }
</style>
