<?php

namespace App\Services;

class FonnteMessageService
{
   
            //untuk yg di tuju//
    public static function pengajuanBaru($pengajuan)
    {
        
        $halName = $pengajuan->masterhal->nama_jenis ?? '-';

        return 
        "📢 *Pemberitahuan Pengajuan Baru*\n\n" .
        "Halo *{$pengajuan->mengetahui_name}*,\n\n" .
        "Ada pengajuan baru yang membutuhkan persetujuan Anda.\n\n" .
        "📄 *Detail Pengajuan:*\n" .
        "• No Surat  : {$pengajuan->no_surat}\n" .
        "• Hal       : {$halName}\n" .
        "• Pelapor   : {$pengajuan->name_pelapor}\n" .
        "• NPP       : {$pengajuan->npp_pelapor}\n\n" .
        "Silakan buka aplikasi untuk menindaklanjuti.\n\n" .
        "Terima kasih 🙏";
    }
            //ke pelapor\\
    public static function pengajuanBerhasilDikirim($pengajuan)
{
        $halName = $pengajuan->masterhal->nama_jenis ?? '-';

    return
        "📄 *Pengajuan Berhasil Dikirim*\n\n" .
        "Yth. *{$pengajuan->name_pelapor}*,\n" .
        "Pengajuan Anda telah berhasil direkam dalam sistem dengan rincian:\n\n" .
        "*Nomor Surat* : {$pengajuan->no_surat}\n" .
        "*Hal*         : {$halName}\n" .
        "*Keterangan*  : " . ($pengajuan->keterangan ?? '-') . "\n\n" .
        "Pengajuan Anda sedang menunggu proses persetujuan dari yang terkait.\n" .
        "Terima kasih atas kerja sama Anda.";
}


                //update status pengajuan//
  public static function statuspengajuan($pengajuan)
{
    $halName = $pengajuan->masterhal->nama_jenis ?? '-';

    return 
        "📢 *Status Pengajuan Anda*\n\n" .
        "Halo *{$pengajuan->name_pelapor}*,\n\n" .
        "Status pengajuan Anda telah diperbarui.\n\n" .
        "📄 *Detail Pengajuan:*\n" .
        "• No Surat  : {$pengajuan->no_surat}\n" .
        "• Hal       : {$halName}\n" .
        "• Status    : " . strtoupper($pengajuan->status) . "\n\n" .
        "Silakan cek aplikasi untuk detail lebih lengkap.\n\n" .
        "Terima kasih 🙏";
}

    //pesan untuk daftar staf yg di tugaskan\\
public static function pesanPenugasan($spk, $listStaf)
    {
        return
            "*Pemberitahuan Penugasan*\n\n" .
            "Pengajuan Anda dengan nomor surat:\n" .
            "*{$spk->no_surat}*\n\n" .
            "Telah ditugaskan kepada:\n\n" .
            $listStaf .
            "\nSilakan menunggu tindak lanjut dari petugas.";
    }

        //pesan untuk staf//
    public static function pesanUntukStaf($spk, $staf)
{
    return
        "📢 *Pemberitahuan Penugasan SPK*\n\n" .
        "Yth. *{$staf['nama']}* ({$staf['npp']}),\n" .
        "Anda telah ditugaskan pada pekerjaan berikut:\n\n" .
        "*Nomor Surat* : {$spk->no_surat}\n" .
        "*Jenis Pekerjaan* : " . ($spk->jenis_pekerjaan ?? '-') . "\n" .
        "*Kode Barang* : " . ($spk->kode_barang ?? '-') . "\n\n" .
        "Dimohon untuk segera menindaklanjuti penugasan ini.\n\n" .
        "Terima kasih.";
}
 // Pesan untuk PIC
public static function pesanPenugasanPIC($spk, $penanggung_jawab, $stafList)
{
    return
        "📌 *Penetapan Penanggung Jawab (PIC)*\n\n" .
        "Yth. *{$penanggung_jawab['name']}* ({$penanggung_jawab['npp']}),\n\n" .
        "Anda telah ditetapkan sebagai *Penanggung Jawab (PIC)* untuk pekerjaan berikut:\n\n" .

        "*Nomor Surat* : {$spk->no_surat}\n" .
        "*Jenis Pekerjaan* : " . ($spk->jenis_pekerjaan ?? '-') . "\n" .
        "*Kode Barang* : " . ($spk->kode_barang ?? '-') . "\n" .
        "*Uraian* : " . ($spk->uraian_pekerjaan ?? '-') . "\n\n" .

        "👥 *Staf yang Membantu Pekerjaan Ini:*\n" .
        ($stafList ?: "- Tidak ada staf pembantu") . "\n\n" .

        "Mohon untuk segera melakukan koordinasi dan tindak lanjut sesuai SOP.\n\n" .
        "Terima kasih.";
}


        //update status spk//
 public static function pesanUpdateStatusPekerjaan($spk, $pengajuan)
{
    return
        "📢 *Pemberitahuan Pembaruan Status Pekerjaan*\n\n" .
        "Yth. *{$pengajuan->name_pelapor}*,\n" .
        "Kami informasikan bahwa status pekerjaan pada pengajuan Anda telah mengalami pembaruan.\n\n" .
        "*Nomor Surat* : {$spk->no_surat}\n" .
        "*Status Terbaru* : {$spk->status}\n" .
        "*Kode Barang* : " . ($spk->kode_barang ?? '-') . "\n" .
        "*Jenis Pekerjaan* : " . ($spk->jenis_pekerjaan ?? '-') . "\n" .
        "*Uraian Pekerjaan* : " . ($spk->uraian_pekerjaan ?? '-') . "\n\n" .
        "Terima kasih atas perhatian dan kerja samanya.";
}

        //pesan ke yg menugaskan spk//
public static function pesanUntukYangMenugaskan($spk, $listStaf, $picName)
{
    return
        "📬 *Konfirmasi Penugasan SPK*\n\n" .
        "Penugasan telah berhasil dilakukan untuk SPK:\n" .
        "*Nomor Surat* : {$spk->no_surat}\n\n" .

        "📌 *PIC*: {$picName}\n" .
        "👥 *Staf yang Ditugaskan:*\n" . ($listStaf ?: '-') . "\n\n" .

        "Pesan telah dikirim kepada:\n" .
        "• Pelapor\n" .
        "• PIC\n" .
        "• Seluruh staf yang terkait\n\n" .

        "Terima kasih telah melakukan penugasan.";
}
  
       //update status pengajuan ke yg mengetahui//
public static function pesanUpdateStatusMengetahui($pengajuan)
{
    return
        "✔️ *Update Status Berhasil*\n\n" .
        "Status pengajuan dengan nomor surat:\n" .
        "*{$pengajuan->no_surat}*\n\n" .
        "Telah berhasil diperbarui menjadi:\n" .
        "*{$pengajuan->status}*\n\n" .
        "Pesan notifikasi juga telah dikirim kepada pelapor.\n\n" .
        "Terima kasih.";
}


}
