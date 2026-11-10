# Aplikasi Stock Barang — PABWE 2026 SK P7

Paket source Laravel 13 + Livewire 4 dengan arsitektur Feature-First.

## Instalasi
1. Pastikan PHP >= 8.3, Composer, MySQL, dan ekstensi PHP `fileinfo`, `gd`, `zip`, `xml`, `mbstring` tersedia.
2. Jalankan `composer install`.
3. Salin `.env.example` menjadi `.env`, lalu atur DB_DATABASE=`pabwe_p7_stock`, DB_USERNAME, dan DB_PASSWORD.
4. Jalankan `php artisan key:generate`.
5. Buat database MySQL `pabwe_p7_stock`.
6. Jalankan `php artisan migrate --seed`.
7. Jalankan `php artisan storage:link`.
8. Jalankan `php artisan serve`.

Akun demo:
- Admin: `admin` / `password`
- Member: `member` / `password`

## Catatan fitur
- Login/register, role admin/member, ownership stok.
- CRUD stok, pencarian, filter status, pagination, penyesuaian quantity.
- Status dihitung otomatis: quantity 0 = out_of_stock; quantity <= min_stock = low_stock; selebihnya available.
- Upload cover dan profil.
- Repository/service, policy, middleware, Excel service, DTO dan AI generator.
- Admin hanya mendapatkan tampilan daftar stok tanpa aksi perubahan.

## Pekerjaan integrasi yang perlu diselesaikan sebelum penilaian
Paket ini merupakan source project awal yang mengikuti struktur modul, bukan salinan latihan Todolist. Sesuaikan skeleton Laravel 13 sesuai hasil `composer create-project` bila diperlukan. Integrasikan endpoint/UI upload-download Excel dan UI preview AI ke komponen Livewire, daftarkan policy sesuai mekanisme Laravel 13, dan lengkapi suite test untuk seluruh kelas. Coverage 100% dan audit bersih harus dibuktikan pada environment proyek; keduanya tidak dapat dijamin hanya dari pembuatan source ZIP. Toast UI Editor/Markdown rendering juga perlu dipasang sesuai versi modul praktikum.
