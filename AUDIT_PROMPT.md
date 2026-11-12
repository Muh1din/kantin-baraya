Kamu bertindak sebagai Senior Laravel Engineer.

Lakukan **satu kali audit menyeluruh** terhadap codebase Kantin Baraya (Laravel 12, Blade, Tailwind, MySQL) untuk persiapan refactoring fitur Guest menjadi Customer.

## Target Arsitektur

- Hapus fitur pemesanan Guest tanpa login.
- Customer wajib login untuk melihat menu, membuat preorder, upload bukti pembayaran, dan melihat riwayat/status pesanan miliknya.
- Customer hanya boleh membayar dengan QRIS. Pembayaran tetap dilakukan di luar aplikasi, tanpa payment gateway baru.
- Kasir tetap menangani POS, verifikasi bukti pembayaran, konfirmasi/penolakan preorder, dan transaksi tunai.
- Koki tetap memproses pesanan.
- Owner dan seluruh fitur operasionalnya tidak boleh terganggu.

## Konteks Teknis

- `orders` sudah memiliki `customer_id` (FK users), `created_by`, `tracking_token`, `order_channel`, dan informasi pesanan.
- `payments` sudah memiliki `order_id`, `payment_method`, `payment_status`, `proof_image`, dan `paid_at`.
- `Order` memiliki relasi `items()`, `payment()`, dan `statusHistories()`.
- `Payment` memiliki relasi `order()`.
- `User` belum memiliki relasi Order.
- `GuestOrderController` menangani pembuatan preorder dan upload bukti QRIS.
- `Cashier\OrderController` menangani pesanan online, verifikasi pembayaran, konfirmasi, dan penolakan.
- `OrderService` digunakan untuk POS. Pertahankan agar POS tidak rusak.
- `MenuCatalogController` sudah berada di namespace Customer.

Tetap verifikasi seluruh informasi tersebut dengan kode aktual.

## Area Audit

Periksa seluruh:
1. Routes dan middleware.
2. Controllers Guest, Customer, Cashier, Chef, Owner, Home, dan Pelanggan.
3. Models, relationships, migrations, dan foreign keys.
4. Views, Blade components, JavaScript, AJAX/fetch, dan named routes.
5. Alur preorder, pembayaran QRIS, upload bukti, dan verifikasi Kasir.
6. Order lifecycle, pengurangan stok, dan proses Koki.
7. POS, checkout, receipt, riwayat transaksi, dan laporan Owner.
8. Authorization, ownership order, validasi, mass assignment, dan keamanan upload.

Cari seluruh referensi Guest dan identifikasi dependency sebelum merekomendasikan penghapusan file.

## Aturan Mutlak

**AUDIT SAJA. JANGAN IMPLEMENTASI.**

Dilarang mengubah, membuat, memindahkan, atau menghapus file, migration, database, route, maupun kode apa pun.

Jangan menjalankan perintah yang mengubah repository atau data.

Jangan menganggap file tidak digunakan sebelum menelusuri seluruh referensinya.

## Format Laporan

Berikan satu laporan final yang berisi:

1. Ringkasan kondisi arsitektur saat ini.
2. Daftar seluruh file Guest beserta dependency dan rekomendasinya.
3. Pemetaan fitur yang dipindahkan ke Customer.
4. Target arsitektur setelah Guest dihapus.
5. Analisis kebutuhan perubahan database.
6. Analisis alur pembayaran dan order lifecycle.
7. Temuan security dan authorization beserta prioritas.
8. Risiko regresi terhadap POS, Kasir, Koki, dan Owner.
9. Urutan refactoring bertahap beserta file terdampak.
10. Checklist testing dan regression.
11. Rekomendasi akhir: apa yang harus dipertahankan, dipindahkan, diubah, atau dihapus.

Gunakan bukti kode aktual, sertakan path file dan nama method. Bedakan fakta, risiko, dan rekomendasi.

Prioritaskan keamanan data, backward compatibility, dan pencegahan regresi.

**Selesaikan seluruh audit dalam satu kali proses dan berikan laporan final. Jangan melakukan perubahan kode.**