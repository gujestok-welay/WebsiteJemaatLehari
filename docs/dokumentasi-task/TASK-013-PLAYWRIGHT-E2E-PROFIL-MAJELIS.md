# TASK-013 — Tes Playwright Profil & Majelis

Tanggal: 6 November 2026.

## Tujuan

Menambahkan pengujian browser untuk tombol dan alur utama pada halaman admin Profil & Majelis tanpa menyentuh database aplikasi.

## Perubahan

- Menguji buka/tutup formulir Gambaran Umum Wilayah, Jumlah Kepala Keluarga dan Jiwa, Peta Pelayanan, Konten Profil, Data Jemaat, dan pengelolaan Majelis.
- Menguji simpan dan pemuatan ulang data, penambahan/penghapusan musim, kalkulasi jiwa, navigasi langkah sensus, serta penolakan file peta dengan tipe tidak valid.
- Menguji tambah dan aktivasi periode, tambah/edit/hapus kartu Majelis, dan alur jadwal tambah/edit/batal/aktifkan/arsip/pulihkan.
- Menguji panel Majelis pada viewport ponsel, scroll isi panel, aksesibilitas saat dibuka, dan alur tambah/hapus anggota.
- Memperbaiki siklus atribut aksesibilitas responsive modal agar panel yang terbuka tidak tetap ditandai `aria-hidden`.
- Menjalankan PHP lokal di port 8091 dengan akun dan SQLite tes tersendiri. Jika port sudah dipakai, launcher berhenti tanpa memakai server yang ada.
- Mereset hanya tabel pada `writable/playwright-test-data/profile-modules.sqlite`. Database dan sesi Playwright dikecualikan dari Git.

## Menjalankan

```console
npm run test:e2e
```

Petunjuk pengguna tersedia di [README tes E2E](../../e2e/README.md).

## Pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| Playwright Chromium pada database tes terisolasi | PASS — 15/15 tes E2E lulus. |
| PHPUnit | Dijalankan terpisah dengan coverage dinonaktifkan. |
| Sintaks launcher JavaScript dan fixture PHP | PASS. |
