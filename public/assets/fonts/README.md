# Font publik — TASK-006E

Diunduh 26 Oktober 2026 dari distribusi Fontsource **5.3.0** melalui jsDelivr.
Semua file WOFF2 disalin tanpa modifikasi. Tidak ada dependensi npm yang ditambahkan.

| File lokal | Sumber unduhan | Ukuran |
| --- | --- | --- |
| `cormorant-garamond/latin-600-normal.woff2` | https://cdn.jsdelivr.net/npm/@fontsource/cormorant-garamond@5.3.0/files/cormorant-garamond-latin-600-normal.woff2 | 23.396 byte |
| `dm-sans/latin-400-normal.woff2` | https://cdn.jsdelivr.net/npm/@fontsource/dm-sans@5.3.0/files/dm-sans-latin-400-normal.woff2 | 14.200 byte |
| `dm-sans/latin-600-normal.woff2` | https://cdn.jsdelivr.net/npm/@fontsource/dm-sans@5.3.0/files/dm-sans-latin-600-normal.woff2 | 14.144 byte |
| `dm-sans/latin-700-normal.woff2` | https://cdn.jsdelivr.net/npm/@fontsource/dm-sans@5.3.0/files/dm-sans-latin-700-normal.woff2 | 14.348 byte |

Total font: **66.088 byte**. Subset Latin mencakup teks Bahasa Indonesia dan tanda baca
yang sekarang dipakai. Karakter di luar cakupan memakai fallback sistem.
Hanya gaya tegak yang dimuat karena itulah penggunaan halaman saat audit.

Cormorant: copyright The Cormorant Project Authors, https://github.com/CatharsisFonts/Cormorant.
DM Sans: copyright The DM Sans Project Authors, https://github.com/googlefonts/dm-fonts.
Keduanya berlisensi **SIL Open Font License 1.1**, disertakan utuh di masing-masing folder:

- `cormorant-garamond/OFL.txt`, dari https://cdn.jsdelivr.net/npm/@fontsource/cormorant-garamond@5.3.0/LICENSE
- `dm-sans/OFL.txt`, dari https://cdn.jsdelivr.net/npm/@fontsource/dm-sans@5.3.0/LICENSE

SHA-256:

```text
ae062b6d5ae308e7edf61b28b07b9984bbb6e961b1f34d9b2c2f4389c33f21ea  cormorant-garamond/latin-600-normal.woff2
4ab51eb2cd7305d177187908d6397474d4520663f6c6e572feb0a64f4fa80006  dm-sans/latin-400-normal.woff2
6bb2b2645ba5eeaecf56322c543fa3a75b87b927977b9c03b1dabc4205089120  dm-sans/latin-600-normal.woff2
35c5efa0e5daa52ee5c6500f5be354bf751fb65c4e49e1d6806c6eb5883e8fe9  dm-sans/latin-700-normal.woff2
```

`publik.css` memakai URL relatif ke folder ini dan `font-display: swap`.
Layout publik melakukan preload Cormorant 600 dan DM Sans 400 dengan `crossorigin`.
DM Sans 600/700 diminta ketika digunakan. **Tidak ada permintaan font ke pihak ketiga
saat halaman dibuka**; koneksi ke penyedia font hanya diperlukan saat pengambilan
aset pengembangan, bukan pada runtime website. Tetap sertakan lisensi saat distribusi.
