# Sistem Kredit

Web PHP sederhana untuk analisis kelayakan kredit koperasi.

## Mode database

Secara default aplikasi memakai SQLite supaya bisa langsung dideploy tanpa MySQL terpisah.

Environment yang didukung:

- `DB_CONNECTION=sqlite`
- `SQLITE_PATH=/var/www/html/data/database.sqlite`

Atau jika ingin memakai MySQL:

- `DB_CONNECTION=mysql`
- `DB_HOST=...`
- `DB_PORT=3306`
- `DB_DATABASE=sistemkredit`
- `DB_USERNAME=...`
- `DB_PASSWORD=...`

## Deploy cepat

### Opsi paling sederhana: Render / Railway via Docker

1. Push repo ini ke GitHub.
2. Buat service baru dari repository tersebut.
3. Pilih deploy menggunakan `Dockerfile`.
4. Tambahkan environment variable berikut:

```env
DB_CONNECTION=sqlite
SQLITE_PATH=/var/www/html/data/database.sqlite
```

5. Deploy.

Catatan: bila platform mendukung persistent disk, mount ke folder `/var/www/html/data` agar riwayat analisis tidak hilang saat redeploy.

### Deploy dengan MySQL

Gunakan environment variable:

```env
DB_CONNECTION=mysql
DB_HOST=your-mysql-host
DB_PORT=3306
DB_DATABASE=sistemkredit
DB_USERNAME=your-user
DB_PASSWORD=your-password
```

## Jalankan lokal

```bash
php -S localhost:8000
```
