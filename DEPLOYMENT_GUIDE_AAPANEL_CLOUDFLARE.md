# PANDUAN DEPLOYMENT: NFC Business Card (aaPanel Sedia Ada)
## Server: aaPanel (159.138.233.253) | Domain: Cloudflare
> Catatan: Server ini sudah mempunyai banyak aplikasi yang dideploy. Dokumen ini **fokus pada PEMERIKSAAN (VERIFY) apa yang sedia ada** BUKAN install semula semua komponen. Hanya install komponen YANG TIDAK ADA sahaja.

---

# Isi Kandungan
- [QUICK DEPLOY (SENIOR ADMIN CHEATSHEET) – 10 LANGKAH LAJU](#quick-deploy-senior-admin-cheatsheet--10-langkah-laju)
1. [BAHAGIAN 0: PRE-FLIGHT CHECKLIST – SEBELUM MULA](#bahagian-0-pre-flight-checklist--sebelum-mula)
2. [BAHAGIAN 1: SETUP DOMAIN DI CLOUDFLARE](#bahagian-1-setup-domain-di-cloudflare)
3. [BAHAGIAN 2: VERIFIKASI AAPANEL SEDIA ADA (UI + SSH)](#bahagian-2-verifikasi-aapanel-sedia-ada-ui--ssh)
4. [BAHAGIAN 3: VERIFIKASI STACK PELAYAN & COMPLIANCE LARAVEL 12](#bahagian-3-verifikasi-stack-pelayan--compliance-laravel-12)
5. [BAHAGIAN 4: CREATE WEBSITE + DATABASE MELALUI UI AAPANEL](#bahagian-4-create-website--database-melalui-ui-aapanel)
6. [BAHAGIAN 5: SETUP SSL (HTTPS) MELALUI UI AAPANEL + CLOUDFLARE](#bahagian-5-setup-ssl-https-melalui-ui-aapanel--cloudflare)
7. [BAHAGIAN 6: DEPLOY PROJECT LARAVEL MELALUI UI AAPANEL](#bahagian-6-deploy-project-laravel-melalui-ui-aapanel)
8. [BAHAGIAN 7: KONFIGURASI .ENV PRODUCTION](#bahagian-7-konfigurasi-env-production)
9. [BAHAGIAN 8: MIGRATION, SYMLINK & OPTIMASI LARAVEL](#bahagian-8-migration-symlink--optimasi-laravel)
10. [BAHAGIAN 9: QUEUE WORKER + CRON JOB MELALUI UI AAPANEL](#bahagian-9-queue-worker--cron-job-melalui-ui-aapanel)
11. [BAHAGIAN 10: VERIFIKASI DEPLOYMENT & UJIAN](#bahagian-10-verifikasi-deployment--ujian)
12. [BAHAGIAN 11: TROUBLESHOOTING](#bahagian-11-troubleshooting)
- [LAMPIRAN A: UPDATE CODE KEMUDIAN HARI](#lampiran-a-update-code-kemudian-hari)
- [LAMPIRAN B: BACKUP STRATEGY](#lampiran-b-backup-strategy)

---

# QUICK DEPLOY (SENIOR ADMIN CHEATSHEET) – 10 LANGKAH LAJU
>Langkaj untuk admin yang sudah biasa dengan aaPanel. Kalau stuck, rujuk bahagian detail dibawah.

| # | LANGKAH | CARA (UI AAPANEL KECUALI DITULIS SSH) |
|---|---------|--------------------------------------|
| 1 | **Cloudflare** | Add Site → Tukar NS → DNS A record @ + www → 159.138.233.253 Proxied |
| 2 | **Login aaPanel** | Buka URL panel → Login |
| 3 | **Verify Stack** | **Software** → Pastikan: Nginx ✅, MySQL 8+ ✅, **PHP 8.2** ✅, Redis ✅, PM2/Node ✅ |
| 4 | **Verify PHP 8.2 Extensions** | App Store → PHP 8.2 → Settings → Extensions: install yg missing (senarai di 3.3) |
| 5 | **Create Website + DB** | **Website** → Add site → Domain + create MySQL DB (PHP 8.2) → Save DB credentials |
| 6 | **Setup SSL** | Website → Settings → SSL → Let's Encrypt → Apply → Force HTTPS ON |
| 7 | **Upload Code** | **Files** → /www/wwwroot/domain/ → Delete default files → Upload zip project (tanpa vendor/node_modules) → Extract → Move files ke root |
| 8 | **Composer + NPM** | Website → Settings → **Terminal** (atau SSH) → `cd /www/wwwroot/domain` → `composer install --optimize-autoloader --no-dev` → `npm ci && npm run build` → `chown -R www:www .` → `chmod -R 775 storage bootstrap/cache` |
| 9 | **Config + Migrate** | Copy `env.production.template` → `.env` → Edit isi DB, Redis, Mail, Payments → `php artisan key:generate --force` → `php artisan migrate --force` → `php artisan storage:link` → `php artisan config:cache && route:cache && view:cache` |
| 10 | **Directory + Queue** | Website Settings → **Site directory** → Running dir TAMBAH `/public` di hujung → Save. **Process Manager** → Add daemon queue worker (command di 9.1). **Cron** → Add schedule:run 1-min interval. |

✅ Test https://domainanda → Siap.

---

# BAHAGIAN 0: PRE-FLIGHT CHECKLIST – SEBELUM MULA

Pastikan anda ada SEMUA perkara ini sebelum memulakan deployment:

### Kelayakan Akses:
- [ ] URL login aaPanel beserta username + password (jika terlupa: SSH `bt default`)
- [ ] SSH access ke 159.138.233.253: `ssh root@159.138.233.253` (password + port)
- [ ] Akaun Cloudflare (Login untuk DNS + SSL management)
- [ ] Access ke registrar domain (Namecheap/Exabytes/GoDaddy) untuk tukar Nameserver (jika domain baru)

### Credentials (Tempatkan dalam notepad selamat untuk copy-paste):
- [ ] Nama domain yang akan digunakan (contoh: `nfccard.com`)
- [ ] Email untuk Let's Encrypt SSL
- [ ] SMTP credentials (Gmail/Mailgun/Postmark untuk email hantar)
- [ ] Payment gateway API keys (Stripe, Fiuu/MOLPay, Billplz)
- [ ] Google OAuth Client ID + Secret (untuk Social Login)

### Persediaan Project (Local Machine):
- [ ] Dalam folder `backend/`, pastikan tiada folder `vendor/` dan `node_modules/` dalam zip upload
- [ ] Build Vite dahulu (atau buat di server): `cd backend && npm ci && npm run build`
- [ ] Zip kandungan folder `backend/` → nama fail `backend-deploy.zip`

---

# BAHAGIAN 1: SETUP DOMAIN DI CLOUDFLARE

## 1.1 Login & Add Site ke Cloudflare
1. Buka https://dash.cloudflare.com/ → Login
2. Klik **Add a domain** (kanan atas)
3. Taip domain anda → **Continue**
4. Pilih pelan **Free** → **Continue**

## 1.2 Verify DNS Records
1. Cloudflare auto-scan DNS records. Pastikan 2 records ini wujud (jika tidak, add manual):

| Type | Name | Content | Proxy status | TTL |
|------|------|---------|--------------|-----|
| A    | @    | 159.138.233.253 | **Proxied (oren)** | Auto |
| A    | www  | 159.138.233.253 | **Proxied (oren)** | Auto |

2. Klik **Continue**

## 1.3 Tukar Nameserver Domain
> Ini PALING PENTING. Anda perlu login ke tempat beli domain (Namecheap/Exabytes/GoDaddy).

**Contoh di Namecheap:**
- Domain List → Manage domain → Nameservers → Custom DNS
- Masukkan 2 nameserver yang diberikan oleh Cloudflare (contoh: `aragorn.ns.cloudflare.com`, `mai.ns.cloudflare.com`)
- Save

**Contoh di Exabytes:**
- Domains → My Domains → Nameservers → Use custom nameservers
- Masukkan Cloudflare nameservers → Change Nameservers

3. Kembali ke Cloudflare → **Done, check nameservers**
4. **Tunggu propagation (5 min - 24 jam, biasa 10-30 min)**. Sambil menunggu, teruskan ke Bahagian 2.

## 1.4 Cloudflare SSL/TLS (Sedia ada di dashboard, config ringkas)
1. Cloudflare → SSL/TLS → **Overview**
2. Pilih encryption mode: **Full**
3. Tab **Edge Certificates**:
   - ✅ Always Use HTTPS = ON
   - ✅ Automatic HTTPS Rewrites = ON
   - Minimum TLS Version = TLS 1.2

---

# BAHAGIAN 2: VERIFIKASI AAPANEL SEDIA ADA (UI + SSH)
> ⚠️ JANGAN INSTALL APA-APA SEHINGGA ANDA TAHU APA YANG SUDAH ADA. Kita verify dahulu.

## 2.1 Login aaPanel UI
1. Buka URL aaPanel anda (contoh: `http://159.138.233.253:8888/xxxxxx`)
2. Jika terlupa URL → SSH ke server dan run:
   ```bash
   ssh root@159.138.233.253
   bt default
   ```
   (output akan tunjuk URL + Username + Password)
3. Masukkan credentials → Login
4. Jika diminta tukar password default → tukar ke password kuat (SAVE INI)

## 2.2 Semak Versi aaPanel
- Dashboard aaPanel → Look di bahagian atas/bawah atau **Settings**
- Pastikan aaPanel versi terkini (jika outdated, klik **Update** notification jika ada, TAPI JANGAN FORCE UPDATE jika server sedang melayan traffic tinggi)

## 2.3 Semak Service Status melalui UI
- Dashboard aaPanel → **System Status** widget
- Atau menu kiri → **Services** (atau Monitor)
- Semak senarai perkhidmatan dibawah, TANDAKAN status:

| Perkhidmatan | STATUS (Running/Stopped/Tiada) | ACTION JIKA TIDAK RUNNING |
|--------------|-------------------------------|---------------------------|
| Nginx        | [ ] Running | Services → Nginx → Start |
| MySQL        | [ ] Running | Services → MySQL → Start |
| PHP-FPM (semua versi, terutama 8.2) | [ ] Running | Start |
| Redis        | [ ] Running | Start |
| Pure-Ftpd    | [ ] Running (optional) | — |
| PM2          | [ ] Running (optional) | — |

> Jika service TIADA dalam senarai langsung → kita install di Bahagian 3.

## 2.4 (Optional) Verify melalui SSH
Jika anda lebih selesa command line:
```bash
ssh root@159.138.233.253

# Semak software installed via aaPanel
ls /www/server/
# Anda akan nampak folder: nginx, mysql, redis, php (dengan nombor versi)

# Semak nginx
nginx -v

# Semak mysql
mysql -V

# Semak PHP versions available
ls /www/server/php/
# contoh output: 74  81  82  83  -> PHP 7.4, 8.1, 8.2, 8.3 ada

# Semak node
node -v
npm -v

# Semak composer
composer --version

# Semak redis
redis-cli ping    # jawapan PONG = OK
```

---

# BAHAGIAN 3: VERIFIKASI STACK PELAYAN & COMPLIANCE LARAVEL 12
> Laravel 12 keperluan WAJIB: **PHP >= 8.2** dengan extensions tertentu. Server anda MESTI lulus checklist ini.

## 3.1 Verify PHP 8.2 WUJUD & Aktif (UI aaPanel)
1. Menu kiri → **App Store** (atau "Installed")
2. Tab **Installed**
3. Cari **PHP** dalam senarai. Anda PERLUKAN sekurang-kurangnya:
   - ❌ Jika tiada PHP 8.2 → Klik **App Store** tab **Available** → Cari PHP 8.2 → Install (pilih fast install, ~5 minit)
4. PHP 8.2 wajib ada; jika ada PHP 8.3 pun OK.

## 3.2 Tetapkan PHP CLI Default ke 8.2 (SSH)
Pastikan command `php` guna version yang betul (sesetengah server ada multi PHP version):
```bash
php -v
# Jika output < 8.2 atau bukan version yang project guna:
# Buat symlink atau guna path penuh: /www/server/php/82/bin/php
# Contoh:
# ln -sf /www/server/php/82/bin/php /usr/bin/php
# (Rujuk nama folder dalam ls /www/server/php/ tadi)
```

## 3.3 Verify & Install PHP 8.2 Extensions (WAJIB)
1. **App Store** → **Installed** → Cari **PHP 8.2** → Klik **Settings**
2. Tab **Install extensions**
3. Install extensions ini YANG TIDAK ADA status "Installed":
   - ✅ bcmath
   - ✅ ctype (biasanya default)
   - ✅ curl (biasanya default)
   - ✅ fileinfo (INSTALL JIKA TAK ADA)
   - ✅ gd
   - ✅ intl
   - ✅ json (default)
   - ✅ mbstring (default)
   - ✅ openssl (default)
   - ✅ pdo (default)
   - ✅ pdo_mysql (default)
   - ✅ redis (INSTALL JIKA TAK ADA)
   - ✅ tokenizer (default)
   - ✅ xml
   - ✅ zip
   - ✅ pcntl
   - ✅ posix
   - ✅ imagick (INSTALL JIKA TAK ADA - untuk PDF/image)
   - ✅ exif
4. Setiap extension yang missing → klik butang **Install** di sebelah kanan.
5. Tunggu install selesai (1-2 min per extension).

## 3.4 Semak PHP Disabled Functions (PENTING untuk Laravel + Composer)
1. Masih dalam PHP 8.2 Settings → Tab **Disable functions**
2. Cari senarai function dibawah. **Jika WUJUD dalam list disable → BUANG (delete) dari senarai, kemudian Save**:
   - `putenv` (Laravel .env perlukan)
   - `proc_open` + `proc_close` (Composer install perlukan)
   - `passthru`
   - `shell_exec` (jika ada)
   - `symlink` (untuk `storage:link`)
3. Klik **Save**
4. **Restart PHP 8.2**: Tab **Status** (atau Overview) → Klik **Restart** butang untuk PHP 8.2

## 3.5 Verify Composer
1. SSH ke server:
   ```bash
   composer --version
   ```
2. Jika "command not found":
   ```bash
   cd ~
   curl -sS https://getcomposer.org/installer | /www/server/php/82/bin/php
   mv composer.phar /usr/local/bin/composer
   chmod +x /usr/local/bin/composer
   # Test semula: composer --version
   ```

## 3.6 Verify Node.js + NPM (Vite Build)
1. UI: **App Store** → Installed → Cari **PM2 Manager** atau **Node.js version manager**
   - Jika tiada → Install **PM2 Manager** dari App Store (~2 min)
2. SSH verify:
   ```bash
   node -v   # perlu >= 18.x
   npm -v    # perlu >= 9.x
   ```
3. Jika node/npm tak jumpa:
   ```bash
   # Install nvm
   curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash
   source ~/.bashrc
   nvm install 20
   nvm use 20
   ```

## 3.7 Verify MySQL / MariaDB
1. UI: **Services** → Pastikan MySQL Running
2. SSH verify version:
   ```bash
   mysql --version   # perlu >= 8.0 atau MariaDB 10.5+
   ```
3. Connect test (jika tahu root password):
   ```bash
   mysql -u root -p -e "SELECT VERSION();"
   ```
4. Jika MySQL TAK RUNNING: **Services → MySQL → Start**

## 3.8 Verify Redis
1. UI: **Services** → Redis status Running?
2. Jika Redis TIADA langsung dalam system: **App Store → Available → Cari Redis → Install**
3. Verify:
   ```bash
   redis-cli ping
   # Jawapan PONG = OK. Jika dapat PONG tanpa auth, password tak diset (OK untuk local server)
   ```
4. (Optional tapi disyorkan): App Store → Redis → Settings → Tab Security → Set Redis password → Save → Restart. Simpan password untuk .env.

---

# BAHAGIAN 4: CREATE WEBSITE + DATABASE MELALUI UI AAPANEL
> Semua langkah dalam BAHAGIAN ini melalui PANEL ADMIN AAPANEL (tiada SSH perlu).

## 4.1 Add Website Baru
1. Menu kiri aaPanel → **Website**
2. Klik butang hijau besar **Add site** (atas sebelah kanan)
3. Isi borang berikut (rujuk jadual; jika tidak pasti, ikut nasihat "Disyorkan"):

| Field dalam Add Site | Nilai Disyorkan | Nota |
|---------------------|-----------------|------|
| **Domain** | Baris 1: `nfccardanda.com` <br> Klik "+ Add domain", Baris 2: `www.nfccardanda.com` | Ganti domain sebenar. Pastikan kedua-dua wujud di Cloudflare DNS. |
| **Root directory** | `/www/wwwroot/nfccardanda.com` | Default auto-isi mengikut domain → OK. **JANGAN tukar ke /public LAGI - kita tukar selepas upload code di Bahagian 6.6** |
| **FTP** | OFF | Tutup FTP. Kita guna File Manager / SSH. |
| **Database** | ✅ MySQL | PILIH MySQL. |
| **DB Name** | `nfccard_prod` | Nama database, auto-suggest ikut domain. |
| **DB Username** | `nfccard_user` | Nama user database. |
| **DB Password** | Klik IKON KUNCI sebelah field untuk AUTO-GENERATE password KUAT | ⚠️ **SAVE SEGERA PASSWORD INI KE NOTEPAD ANDA.** Anda perlukan nanti untuk .env. Copy sebelum tutup popup! |
| **PHP version** | **PHP-82** atau **PHP 8.2.xx** | Pilih PHP 8.2 (bukan 7.x, bukan 8.0/8.1). |
| **Site category** | Default | OK |
| **Description** | `NFC Business Card [Production]` | Untuk rujukan masa depan |
| ~~Create e-mail~~ | Biarkan unchecked | — |

4. Semak semula 2 perkara penting:
   - PHP version betul: **PHP 8.2** ✅
   - DB password telah disimpan ✅
5. Klik butang **Submit** (hijau, bawah kanan borang)
6. Popup "Site created successfully!" akan muncul dengan credentials database.
   > **SAVE POPUP INI**: Screenshot / copy semua field (DB Name, Username, Password). Ini adalah last chance aaPanel tunjuk plaintext password DB.

## 4.2 Verify Website + DB Telah Wujud
1. **Website** menu → Pastikan domain anda muncul dalam senarai. Status harus Running.
2. Menu kiri → **Database**:
   - Database baru anda (`nfccard_prod`) muncul dalam list.
   - Klik butang **Admin** sebelah DB → phpMyAdmin akan buka tab baru.
   - Cuba login dengan username + password DB dari Langkah 4.1.
   - Jika berjaya masuk phpMyAdmin → DB connection OK. Tutup tab.

---

# BAHAGIAN 5: SETUP SSL (HTTPS) MELALUI UI AAPANEL + CLOUDFLARE
> Sambungan Cloudflare ↔ aaPanel. Semua melalui UI, kecuali troubleshoot.

## 5.1 Tunggu DNS Propagation (jika perlu)
Sebelum apply SSL, pastikan domain anda sudah resolve ke server:
- Buka command prompt/terminal local: `ping nfccardanda.com`
- Jika reply dari `159.138.233.253` (atau Cloudflare IP), teruskan.
- Jika masih tak resolve → Tunggu 15 min lagi atau check https://dnschecker.org/

## 5.2 Apply Let's Encrypt SSL melalui aaPanel UI
1. **Website** menu → Cari domain anda → Klik butang **Settings** (icon gear)
2. Popup Settings terbuka. Tab kiri → Pilih **SSL**
3. Tabs atas SSL: Pilih **Let's Encrypt**
4. Isi:
   - **Domain**: Tick ✅ kedua-dua domain (`nfccardanda.com` DAN `www.nfccardanda.com`)
   - **Email**: Taip email aktif anda (untuk renewal reminder)
5. Klik **Apply** (butang hijau)
6. Tunggu 30-60 saat. Status bar akan loading...

### Jika Berjaya:
- Status: ✅ **Certificate issued successfully**
- Expiry date: 90 hari dari hari ini (auto-renew oleh aaPanel)

### Jika Gagal (error challenge):
Solusi paling berkesan:
1. Buka Cloudflare dashboard → DNS
2. Tukar Proxy status A record `@` dan `www` kepada **DNS Only (kelabu)** (tutup proxy sementara)
3. Tunggu 2 minit
4. Balik aaPanel SSL → Apply semula Let's Encrypt
5. Setelah berjaya: Balik Cloudflare → Tukar semula Proxy status kepada **Proxied (oren)**
6. (Backup solution): Jika masih gagal → guna Cara 2 Origin Certificate di bawah.

## 5.3 Cara 2 (Backup): Cloudflare Origin Certificate
(Guna HANYA jika Let's Encrypt terus gagal)
1. Cloudflare → domain anda → **SSL/TLS → Origin Server → Create Certificate**
2. Hostnames: `*.nfccardanda.com, nfccardanda.com` → Key Type RSA 2048 → Validity 15 years → **Create**
3. COPY 2 blok teks KE NOTEPAD (jangan tutup tab Cloudflare lagi sehingga saved ke aaPanel):
   - Blok 1: **Origin Certificate** (-----BEGIN CERTIFICATE----- s/d -----END CERTIFICATE-----)
   - Blok 2: **Private Key** (-----BEGIN PRIVATE KEY----- s/d -----END PRIVATE KEY-----)
4. Balik aaPanel Website Settings → SSL → Tukar tab kepada **Other certificate** / **Paste Certificate**
5. Paste Origin Certificate ke medan **Certificate (PEM)**
6. Paste Private Key ke medan **Private key (KEY)**
7. Klik **Save**

## 5.4 Enable Force HTTPS + Redirect (WAJIB)
1. Masih dalam SSL Settings (Website → Settings → SSL)
2. Cari toogle **Force HTTPS** → TUKAR KEPADA **ON** (hijau)
3. (Optional): Klik tab **Redirect** dalam site settings → Add redirect: `www.nfccardanda.com` → `https://nfccardanda.com` (301 permanent) untuk konsisten URL.

## 5.5 Test HTTPS
Buka browser private/incognito window:
- ✅ `https://nfccardanda.com` → Page default aaPanel "Welcome to LNMP" ke page placeholder → Gembok hijau ✅
- ✅ `https://www.nfccardanda.com` → sama → Gembok hijau ✅
- ✅ `http://nfccardanda.com` → Auto redirect ke https:// ✅

---

# BAHAGIAN 6: DEPLOY PROJECT LARAVEL MELALUI UI AAPANEL
> Fokus: Menggunakan **aaPanel File Manager** (web UI) untuk upload + extract. Kemudian Terminal built-in aaPanel untuk artisan commands.

## 6.1 Bersihkan Default Files dalam Document Root
1. Menu aaPanel → **Files** (icon folder dalam menu kiri)
2. File Manager dibuka dalam tab baru. Navigate ke:
   ```
   /www/wwwroot/nfccardanda.com/
   ```
3. Dalam folder ini, aaPanel akan letak fail default. **DELETE SEMUA FAIL/FOLDER DEFAULT INI**:
   - `404.html`
   - `index.html`
   - `.htaccess`
   - `.user.ini`
   - `favicon.ico` (jika ada)
   - folder `stats/` dll.
   > Cara: Pilih semua fail (checkbox atas kiri senarai) → Klik butang **Delete** (icon tong sampah, toolbar atas) → Confirm delete
4. Pastikan `/www/wwwroot/nfccardanda.com/` sekarang KOSONG tiada fail langsung.

## 6.2 Upload Zip Project (Pilih Satu Cara)
### Cara A: Zip Upload melalui File Manager UI (disyorkan untuk beginner)
1. Pastikan di local machine anda sudah sediakan `backend-deploy.zip`:
   - Pergi ke `C:\Users\USER\Desktop\nfc-Business-card\`
   - Masuk folder `backend`, select SEMUA kandungan (Ctrl+A), kecuali:
     - ❌ folder `vendor`
     - ❌ folder `node_modules`
     - ❌ fail `.env` (jangan upload .env local!)
   - Extract ZIP fail: Click kanan → Send to → Compressed (zipped) folder → Nama `backend-deploy.zip`
   - **Pastikan zip buka terus ke fail project (bukan ada folder `backend` dalaman)**. Untuk verify: Double click zip → jika pertama kali nampak `app/`, `bootstrap/`, `artisan` dll = OK. Jika pertama nampak folder `backend/` → buka folder itu, select all, zip semula.
2. Balik File Manager → `/www/wwwroot/nfccardanda.com/`
3. Klik toolbar atas → butang **Upload**
4. Drag & drop `backend-deploy.zip` ke window upload → tunggu 100%
5. Selepas upload 100%: Klik kanan `backend-deploy.zip` dalam senarai → **Extract**
6. Popup extract: Folder destination biarkan default `/www/wwwroot/nfccardanda.com/` → **Extract**
7. Tunggu extract selesai → delete fail `backend-deploy.zip` (click kanan → Delete)
8. Verify: Kini dalam `/www/wwwroot/nfccardanda.com/` anda patut nampak:
   ```
   app/  bootstrap/  config/  database/  public/  resources/  routes/  storage/
   artisan  composer.json  composer.lock  package.json  package-lock.json
   vite.config.js  .env.example  env.production.template  ...dll
   ```
   ✅ OK. Tiada lagi folder `backend` dalaman.

### Cara B: Git Clone melalui SSH (untuk project di GitHub/GitLab)
1. Upload project backend anda ke private repo GitHub
2. aaPanel Website → Settings → butang **Terminal** (atau SSH terus)
3. Run:
   ```bash
   cd /www/wwwroot/
   # Pastikan folder domain kosong
   rm -rf nfccardanda.com
   git clone https://github.com/yourusername/nfccard-project.git nfccardanda.com
   cd nfccardanda.com
   # Jika backend adalah subfolder dalam repo:
   # mv backend/* backend/.* . ; rm -rf backend
   ```
4. Verify struktur fail seperti cara A.

## 6.3 Install Composer Dependencies (melalui aaPanel Terminal)
1. **Website** → Settings domain anda → cari butang **Terminal** (kadangkala bersebelahan dengan butang Backup/Restore)
2. ATAU: SSH langsung ke server:
   ```bash
   cd /www/wwwroot/nfccardanda.com
   ```
3. Install production dependencies:
   ```bash
   # Guna PHP 8.2 path penuh jika php default salah versi:
   # /www/server/php/82/bin/php /usr/local/bin/composer install --optimize-autoloader --no-dev
   
   composer install --optimize-autoloader --no-dev
   ```
4. Jika dapat error "Memory size exhausted":
   ```bash
   php -d memory_limit=-1 /usr/local/bin/composer install --optimize-autoloader --no-dev
   ```
5. Tunggu 2-5 minit. Akhir sekali mesti ada output "Generating optimized autoload files" → ✅ OK.

## 6.4 Install NPM & Build Vite Frontend
1. Dalam terminal folder yang sama:
   ```bash
   cd /www/wwwroot/nfccardanda.com
   npm ci
   npm run build
   ```
2. Tunggu `npm ci` install package → kemudian `build` output:
   ```
   ✓ built in ...s
   ```
3. Verify folder wujud:
   ```bash
   ls public/build/
   # Patut ada: manifest.json, assets/
   ```

## 6.5 Set Permissions Fail (CRUCIAL STEP)
Tanpa permission betul, akan dapat Error 500 "Permission denied" pada log laravel.
Dalam terminal SSH / aaPanel Terminal:
```bash
cd /www/wwwroot/nfccardanda.com

# 1. Set ownership SEMUA fail kepada user web server aaPanel
chown -R www:www .

# 2. Set standard permissions
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

# 3. WRITE ACCESS WAJIB untuk 2 folder LARAVEL:
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# 4. Confirm owner semula 2 folder critical:
chown -R www:www storage/
chown -R www:www bootstrap/cache/
```
✅ Selesai permissions.

## 6.6 TUKAR RUNNING DIRECTORY KE /public (LANGKAH PALING PENTING!)
> Laravel DOCROOT BUKAN root project tapi FOLDER PUBLIC. Ini kesilapan #1 deploy Laravel di aaPanel.
> Semua melalui UI aaPanel:

1. Menu kiri → **Website**
2. Cari domain anda → Klik **Settings** (gear icon)
3. Dalam popup Settings → Tab **Site directory** (atau "Configuration" dalam sesetengah versi)
4. Cari field yang labelnya **Running directory**, **Site path**, atau **Root directory**
5. Nilai asal adalah: `/www/wwwroot/nfccardanda.com`
6. **TAIPKAN `/public` DI HUJUNG**, supaya nilai akhir JADI:
   ```
   /www/wwwroot/nfccardanda.com/public
   ```
   > ⚠️ JANGAN guna butang browse untuk pilih folder public (kadangkala aaPanel tak benarkan pick subfolder). TAIP SECARA MANUAL di akhir value field.
7. Klik **Save** / **Submit** (hijau, bawah kanan popup)
8. Confirm popup jika ada → Running directory updated.

## 6.7 Verify Nginx Rewrite (Pseudo-static)
1. Masih dalam Website Settings popup → Tab **Rewrite** (atau **Pseudo-static** dalam tab kiri)
2. Dalam text editor, PASTEKAN rule ni jika kosong atau tak sama:
   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```
3. Klik **Save**
4. (Jika sudah ada rule Laravel default yang auto-generated oleh aaPanel → verify sama, tak perlu tukar jika sepadan)

---

# BAHAGIAN 7: KONFIGURASI .ENV PRODUCTION
> Edit .env BOLEH melalui File Manager UI aaPanel (paling senang - click kanan Edit). Copy template production yang sedia ada.

## 7.1 Copy .env Template ke .env (Terminal / File Manager)
Cara UI File Manager:
1. Buka `/www/wwwroot/nfccardanda.com/`
2. Cari fail `env.production.template` → Click kanan → **Copy**
3. Paste di lokasi sama → rename fail copy daripada `env.production.template(copy)` kepada `.env`
4. ATAU cara SSH:
   ```bash
   cd /www/wwwroot/nfccardanda.com
   cp env.production.template .env
   ```

## 7.2 Edit .env dengan Production Values (File Manager Edit UI)
1. Klik kanan fail `.env` (ia hidden file - pastikan "Show hidden files" ON dalam File Manager settings jika tak nampak) → **Edit**
2. Editor code terbuka. Edit field berikut MENGIKUT NOMBOR. Jangan lupa SAVE setiap beberapa minit.

> **PETUA**: Guna CTRL+F dalam editor untuk search nama field.

### BARISAN APP ASAS
```dotenv
APP_NAME="NFC Business Card"
APP_ENV=production
APP_KEY=                              # KOSONGKAN - kita generate di 7.4
APP_DEBUG=false                       # WAJIB = false
APP_URL=https://nfccardanda.com       # TUKAR domain anda, tanpa / di hujung
FRONTEND_URL=https://nfccardanda.com  # SAMA seperti APP_URL
```

### BARISAN DATABASE (GUNAKAN CREDENTIAL DARI BAHAGIAN 4.1)
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nfccard_prod             # Nama DB dari 4.1
DB_USERNAME=nfccard_user             # User DB dari 4.1
DB_PASSWORD=password_dari_bahagian_4_1  # PASSWORD DB dari 4.1 - SALIN TEPAT
```

### BARISAN CACHE / QUEUE / SESSION (GUNA REDIS JIKA ADA)
```dotenv
# Jika Redis verified OK di Bahagian 3.8:
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=                       # KOSONG jika anda tak set password, atau isi jika ada
REDIS_PORT=6379

# FALLBACK JIKA REDIS TIADA (uncomment 3 line dibawah, comment 3 line atas CACHE/QUEUE/SESSION):
# CACHE_STORE=database
# QUEUE_CONNECTION=database
# SESSION_DRIVER=database
```

### BARISAN EMAIL SMTP
```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com                 # atau smtp.mailgun.org dll
MAIL_PORT=587
MAIL_USERNAME=youremail@yourdomain.com   # email sebenar untuk hantar
MAIL_PASSWORD=your_app_password          # Gmail: APP PASSWORD (bukan password biasa google)
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@nfccardanda.com
MAIL_FROM_NAME="NFC Business Card"
```

### BARISAN SECURITY
```dotenv
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
SANCTUM_STATEFUL_DOMAINS=nfccardanda.com   # domain tanpa https://
```

### BARISAN PAYMENT GATEWAYS (ISI YANG DIGUNAKAN SAHAJA)
```dotenv
# STRIPE
STRIPE_ENABLED=true
STRIPE_SECRET_KEY=sk_live_xxx_YOUR_SECRET
STRIPE_PUBLISHABLE_KEY=pk_live_xxx_YOUR_PUBLISHABLE
STRIPE_WEBHOOK_SECRET=whsec_xxx_YOUR_WEBHOOK_KEY

# FIUU / MOLPAY
FIUU_ENABLED=true
FIUU_MERCHANT_ID=your_fiuu_merchant_id
FIUU_VERIFY_KEY=your_fiuu_verify_key
FIUU_SECRET_KEY=your_fiuu_secret_key
FIUU_SANDBOX=false
FIUU_RETURN_URL=https://nfccardanda.com/payment/return-url.php
FIUU_NOTIFICATION_URL=https://nfccardanda.com/payment/notification-url.php
FIUU_CALLBACK_URL=https://nfccardanda.com/payment/callback-url.php

# BILLPLZ
BILLPLZ_ENABLED=true
BILLPLZ_API_KEY=your_billplz_api_key
BILLPLZ_COLLECTION_ID=your_collection_id
BILLPLZ_X_SIGNATURE_KEY=your_x_signature_key
BILLPLZ_SANDBOX=false
```

### BARISAN OAUTH (GOOGLE LOGIN)
```dotenv
GOOGLE_CLIENT_ID=apps.googleusercontent.com_ID_ANDA
GOOGLE_CLIENT_SECRET=google_client_secret_anda
GOOGLE_REDIRECT_URI=https://nfccardanda.com/auth/google/callback
```

### BARISAN INVOICE / SYARIKAT
```dotenv
COMPANY_NAME="NFC Business Card"
COMPANY_ADDRESS="Alamat penuh syarikat anda"
COMPANY_CITY="Kuala Lumpur"
COMPANY_STATE="Wilayah Persekutuan"
COMPANY_POSTAL_CODE="50000"
COMPANY_COUNTRY="Malaysia"
COMPANY_PHONE="+60 XX-XXX XXXX"
COMPANY_EMAIL="billing@nfccardanda.com"
COMPANY_WEBSITE="${APP_URL}"

INVOICE_PAYMENT_DUE_DAYS=30
INVOICE_TAX_RATE=0
INVOICE_CURRENCY=MYR
INVOICE_NUMBER_PREFIX=INV
```

### BARISAN LOGGING + VITE
```dotenv
LOG_CHANNEL=stack
LOG_LEVEL=error
VITE_APP_NAME="${APP_NAME}"
```

## 7.3 SAVE .env File
Klik butang **Save** (atau CTRL+S) dalam editor code. Tunggu "Save successfully".

## 7.4 Generate APP_KEY (WAJIB!)
```bash
cd /www/wwwroot/nfccardanda.com
php artisan key:generate --force
# Output: Application key set successfully.
```
Verify APP_KEY telah diisi dalam .env:
```bash
cat .env | grep APP_KEY
# Patut nampak base64:xxxx
```

---

# BAHAGIAN 8: MIGRATION, SYMLINK & OPTIMASI LARAVEL

## 8.1 Backup Database Sebelum Migrate (baik punya amalan)
1. aaPanel menu → **Database** → Cari DB anda → Click **Backup** butang → Create backup
2. Atau guna terminal:
   ```bash
   mysqldump -u nfccard_user -p nfccard_prod > /root/nfccard_pre_migration_backup.sql
   # Masukkan DB password bila diminta
   ```

## 8.2 Run Laravel Migrations
```bash
cd /www/wwwroot/nfccardanda.com
php artisan migrate --force
# --force WAJIB di production (Laravel akan skip tanpa ini)
```
Anda akan nampak output seperti:
```
INFO  Preparing migrations.
Running migration: 2025_07_28_052221_create_users_table ........................... 12ms DONE
Running migration: 2025_07_28_052223_create_nfc_tags_table ........................ 8ms DONE
...
```
✅ Semua migration hijau / DONE = OK.

## 8.3 (Opsyenal) Seeders (JANGAN run MOCK seeder!)
Jalankan HANYA jika anda perlukan data default:
```bash
php artisan db:seed --class=AdminUserSeeder --force
php artisan db:seed --class=DatabaseSeeder --force
# ATAU spesifik plan/features:
# php artisan db:seed --class=PlanPriceSeeder --force
# php artisan db:seed --class=ChatbotQuestionSeeder --force
# ❌ JANGAN run MockUserSeeder di production!
```

## 8.4 Storage:link Symlink
```bash
cd /www/wwwroot/nfccardanda.com
php artisan storage:link
# Output: The [public/storage] link has been connected to [storage/app/public].
```
Verify dalam File Manager: `public/` folder kini ada symlink `storage/` (arrow icon).

## 8.5 Cache Everything untuk Performance Production
Cache untuk laju response + kurangkan I/O:
```bash
cd /www/wwwroot/nfccardanda.com
php artisan config:cache
php artisan route:cache
php artisan view:cache
# Opsyenal jika ada events:
php artisan event:cache
```
> **INGAT**: Setiap kali UBAH apa-apa dalam `.env` selepas ini, anda MESTI clear cache dahulu:
> ```bash
> php artisan optimize:clear
> php artisan config:cache
> ```

## 8.6 Verify DB + Laravel Connection
```bash
cd /www/wwwroot/nfccardanda.com
php artisan tinker

# Dalam prompt tinker:
>>> DB::connection()->getPdo();
# Jika takde error, output PDO object = DB Connection OK ✅

>>> \App\Models\User::count();
# Jika 0 (atau seed berjaya) = Model DB OK

>>> exit
```

---

# BAHAGIAN 9: QUEUE WORKER + CRON JOB MELALUI UI AAPANEL
> Project ini guna QUEUE untuk generate invoice, hantar email, notifications dll. Tanpa worker, jobs akan stuck.

## 9.1 Verify Process Manager (Supervisor) Sedia Ada
1. Menu aaPanel → **App Store** → Tab Installed
2. Cari **Process Manager** atau **Supervisor Manager**
3. Jika TIADA → App Store → Available → Cari "Process Manager" atau "Supervisor" → Install (~2 minit)

## 9.2 Add Queue Worker Daemon melalui UI Process Manager
1. Menu aaPanel → **Process Manager** (akan muncul sebagai menu kiri selepas install)
2. Klik **Add daemon** / **Add process** butang
3. Isi borang berikut:

| Field Process Manager | Nilai untuk NFC Project |
|-----------------------|------------------------|
| **Name** | `nfc-queue-worker` |
| **Startup user** | `www` (pilih dari dropdown; JANGAN guna root) |
| **Run command** | Copy paste command dibawah (pastikan path domain betul!) |
| | `php /www/wwwroot/nfccardanda.com/artisan queue:work --sleep=3 --tries=3 --timeout=120 --queue=invoices,default,emails,notifications` |
| **Number of processes** | `3` (server low spec OK 2, tinggi OK 5) |
| **Directory** | `/www/wwwroot/nfccardanda.com` |
| **AutoStart** | ✅ ON / Checked |
| **AutoRestart** | ✅ ON / Checked |

4. Klik **Confirm** / **Submit**
5. Dalam senarai Process Manager, pastikan status process `nfc-queue-worker` = **Running** (hijau) dan Processes menunjukkan `x/3` (contoh: `3/3` running)

## 9.3 Setup Laravel Scheduler dengan Cron Job (10 saat melalui UI)
1. Menu aaPanel → **Cron** (atau "Scheduled Tasks" - icon jam dalam menu kiri)
2. Klik **Add task**
3. Isi borang:

| Field Cron Task | Nilai |
|-----------------|-------|
| **Task type** | Shell Script |
| **Task name** | `NFC Laravel Scheduler` |
| **Execution cycle** | N minute = 1 (setiap 1 minit, Laravel perlukan ini) |
| **Script** | Copy paste: |
| | `cd /www/wwwroot/nfccardanda.com && /www/server/php/82/bin/php artisan schedule:run >> /dev/null 2>&1` |
| **User** | `root` |

> ⚠️ **Guna path FULL PHP 8.2** dalam cron (contoh: `/www/server/php/82/bin/php`) ELAK guna `php` sahaja sebab cron environment tak selalu ada PATH yang betul.

4. Klik **Submit**
5. Dalam senarai Cron, pastikan status = Enabled ✅

## 9.4 Verify Queue + Cron Berfungsi
```bash
cd /www/wwwroot/nfccardanda.com

# Verify scheduled tasks ada:
php artisan schedule:list
# Contoh output: CleanupExpiredRememberTokens, dll (jika ada)

# Test dispatch job & semak queue worker process:
php artisan tinker
>>> \App\Jobs\GenerateInvoiceJob::dispatch(1);
>>> exit

# Check processes running:
ps aux | grep "queue:work" | grep -v grep
# Patut nampak 3 proses (ikut num processes set tadi) user www
```

---

# BAHAGIAN 10: VERIFIKASI DEPLOYMENT & UJIAN

## 10.1 Initial Smoke Test
Buka browser incognito window, test secara BERURUTAN:

| Test | Cara | Result yang Diharapkan |
|------|------|------------------------|
| Homepage | Buka `https://nfccardanda.com` | Page load tanpa error. Tiada 500/404. Gembok hijau ✅ |
| Assets | Inspect Element → Network → Refresh | Tiada baris MERAH 404 untuk CSS / JS / .woff (Vite public/build assets OK) |
| HTTPS redirect | Taip `http://nfccardanda.com` | Auto redirect ke `https://` ✅ |
| www redirect | Taip `https://www.nfccardanda.com` | Redirect ke non-www (atau vice versa, mesti KONSISTEN) ✅ |

## 10.2 Functional Test
1. **Auth Flow**:
   - Klik **Register** → Buat user test dengan email anda sendiri
   - Jika berjaya register & auto-login → Auth OK
   - Logout → Login semula → OK
   - Click "Forgot password" → Email patut sampai (jika SMTP configured betul)

2. **Profile / Card**:
   - Create 1 NFC card test dalam dashboard
   - Upload gambar profile (test storage upload)
   - Save profile → Pastikan data tersimpan (refresh page, data masih ada)

3. **Payments (Sandbox dulu jika boleh)**:
   - Pilih subscription plan → Proceed payment
   - Jika sandbox: Simulate success callback
   - After payment: Check dalam DB, subscription table should have new record with status active

4. **Admin / Invoice PDF**:
   - Login sebagai admin (seeded AdminUserSeeder)
   - Generate 1 invoice → Download PDF → PDF loads tanpa corrupt ✅
   - Export employee/user CSV → Maatwebsite Excel working ✅

5. **Queue Test**:
   - Trigger mana-mana job → Check dalam DB table `jobs` → Jobs masuk, kemudian hilang (diproses). Kalau jobs bertambah dan tak berkurang = worker tidak jalan.
   - Check table `failed_jobs` mesti KOSONG

## 10.3 Final Deployment Checklist
- [ ] HTTPS gembok hijau muncul, force HTTPS ON
- [ ] Nginx Running directory = `/www/wwwroot/domain/public` (verifikasi Website Settings)
- [ ] Semua migration telah run (php artisan migrate:status → semua baris = Yes)
- [ ] APP_DEBUG=false dalam .env
- [ ] Config/route/view cache dijalankan
- [ ] storage:link symlink wujud, upload gambar berjaya
- [ ] Queue Process Manager Running (3/3), failed_jobs kosong
- [ ] Cron scheduler setiap 1 minit wujud & enabled
- [ ] Permissions: storage & bootstrap/cache = www:www 775
- [ ] Log file latest tanpa critical errors:
  ```bash
  tail -n 30 /www/wwwroot/nfccardanda.com/storage/logs/laravel.log
  # Cari pattern ERROR, CRITICAL - patut tiada berkaitan deployment
  ```
- [ ] Payment callback URLs dikemaskini di Stripe dashboard / Fiuu dashboard ke domain production

---

# BAHAGIAN 11: TROUBLESHOOTING
> Solution untuk 10 masalah paling selalu berlaku ketika deploy.

## T1: 500 Internal Server Error (Blank page / Error screen)
**Diagnosis**: Check laravel log:
```bash
tail -n 50 /www/wwwroot/nfccardanda.com/storage/logs/laravel.log
```
**Fix 1 - Permission storage / cache**:
```bash
cd /www/wwwroot/nfccardanda.com
chown -R www:www storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
php artisan optimize:clear
```
**Fix 2 - APP_KEY tak generate**:
```bash
php artisan key:generate --force
php artisan config:cache
```
**Fix 3 - .env DB password salah**: Log kata `SQLSTATE Access denied` → Edit .env DB_PASSWORD, save, then `config:cache`

## T2: 404 Not Found untuk semua routes kecuali `/`
**Punca**: Running directory tak set ke `/public` ATAU Nginx rewrite rule tak betul.
1. Website → Settings → **Site directory** → Pastikan value berakhir dengan `/public`
2. Website → Settings → **Rewrite** → Pastikan rule Laravel `try_files` ada (Bahagian 6.7)
3. Restart Nginx: **Services → Nginx → Restart**

## T3: Vite manifest not found at: /www/wwwroot/.../public/build/manifest.json
**Punca**: npm run build tak dijalankan
```bash
cd /www/wwwroot/nfccardanda.com
npm ci
npm run build
# Verify: ls public/build/manifest.json wujud
```

## T4: Composer install error "Allowed memory size of X bytes exhausted"
```bash
php -d memory_limit=-1 /usr/local/bin/composer install --no-dev
```

## T5: Queue worker tak process jobs (jobs bertambah dalam jobs table)
1. **Process Manager** → status nfc-queue-worker Running? → Jika stopped → **Restart**
2. Command path betul? Buka daemon settings → verify command path `/www/wwwroot/nfccardanda.com/artisan` betul
3. Check failed jobs:
   ```bash
   cd /www/wwwroot/nfccardanda.com
   php artisan queue:failed
   php artisan queue:retry all
   php artisan queue:restart   # signal worker restart
   ```

## T6: HTTPS Redirect Loop (Too many redirects)
1. Cloudflare → SSL/TLS → Overview → Pastikan mode **Full** (bukan Flexible - Flexible cause loop)
2. aaPanel → Website → SSL → Force HTTPS ON (state ON kedua-dua tempat, tak masalah)
3. Clear browser cache, test incognito window

## T7: Storage upload error / gambar profile tak display
1. Verify symlink wujud:
   ```bash
   ls -la /www/wwwroot/nfccardanda.com/public/storage
   # Patut tunjuk arrow -> ke storage/app/public
   ```
2. Jika missing:
   ```bash
   cd /www/wwwroot/nfccardanda.com
   rm -f public/storage
   php artisan storage:link
   chown -h www:www public/storage   # -h untuk symlink ownership
   ```
3. Pastikan `storage/app/public` permission writeable: `chmod -R 775 storage`

## T8: Email tak dihantar (Forgot password / Register)
1. Test SMTP dengan tinker:
   ```bash
   cd /www/wwwroot/nfccardanda.com
   php artisan tinker
   >>> Mail::raw('Deployment test email', fn($m) => $m->to('youremail@test.com')->subject('NFC Deploy Test'));
   ```
2. Jika error:
   - Gmail port 587/465 terbuka di firewall server?
   - Gmail gunakan **App Password** (bukan password login biasa). Enable 2FA dahulu di google account, kemudian create App Password.
   - Check .env MAIL_ENCRYPTION = tls (port 587) / ssl (port 465)

## T9: Redis tidak connect
1. **Services → Redis → Start**
2. Test CLI:
   ```bash
   redis-cli ping     # PONG = OK tanpa auth
   # Jika ada password: redis-cli -a yourpass ping
   ```
3. Jika Redis connection error persist sementara tu, fallback dalam .env kepada database cache/queue:
   ```dotenv
   CACHE_STORE=database
   QUEUE_CONNECTION=database
   SESSION_DRIVER=database
   ```
   Save, run: `php artisan config:cache`

## T10: Semasa migrate: "Table already exists" / "Duplicate column"
**Punca**: Ada sisa migration lama atau manual edit DB. Solusi hanya jika FRESH DEPLOY DB MASIH KOSONG:
```bash
cd /www/wwwroot/nfccardanda.com
php artisan migrate:fresh --force   # HAPUS SEMUA table, recreate dari migration
# ⚠️ WARNING: Jangan guna ini jika DB sudah ada production data!
```

---

# LAMPIRAN A: UPDATE CODE KEMUDIAN HARI
> Untuk deployment patch versi baru, guna ringkasan command ini:
```bash
cd /www/wwwroot/nfccardanda.com

# 1. (Cara Git) Pull latest code
git pull origin main

# 2. Install dependencies baru (jika composer.json/package.json berubah)
composer install --optimize-autoloader --no-dev
npm ci
npm run build

# 3. Migrate baru (jika ada migration baru)
php artisan migrate --force

# 4. Recache SEMUA (wajib lepas apa-apa perubahan fail config/env)
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Restart queue worker (penting - worker bina code lama dalam memory)
php artisan queue:restart

# 6. Betulkan permissions semula (selalu rosak lepas git pull dengan user root)
chown -R www:www .
chmod -R 775 storage bootstrap/cache
```

---

# LAMPIRAN B: BACKUP STRATEGY
Lindungi data dari hilang:

1. **Auto Backup melalui aaPanel UI** (TERBAIK):
   - Website → Settings domain anda → Tab **Backup**
   - Klik **Add Backup Schedule** (atau Schedule Backup)
   - Frekuensi: **Daily** → Retention: 7 salinan terbaru
   - Destination: Pilih **Cloud Storage** jika ada (S3/FTP) atau Local default
   - Submit. Klik "Backup now" untuk test sekali.

2. **Database Backup otomatis melalui Cron**:
   ```bash
   # Cron setiap hari pukul 2 pagi:
   0 2 * * * mysqldump -u nfccard_user -pDBPASSWORD nfccard_prod | gzip > /backup/nfccard_$(date +\%Y\%m\%d).sql.gz && find /backup -name "nfccard_*.gz" -mtime +7 -delete
   ```

3. **Storage folder**: Selalu backup `/www/wwwroot/nfccardanda.com/storage/app/` (guna upload profile user, generated invoices)

---

## TAKEAWAY PENTING AKHIR
1. **Jangan sekali-kali commit .env production ke GitHub.**
2. Sebelum apa-apa perubahan besar: **BACKUP WEBSITE + DATABASE** dahulu melalui aaPanel Settings → Backup → Create (30 saat sahaja, nyawa anda diselamatkan).
3. APP_DEBUG **FALSE** sentiasa di production (jangan expose stacktrace kepada user).
4. Monitor CPU/RAM/Disk usage dari aaPanel Dashboard widget → jika tinggi 80%+ 24/7, pertimbangkan upgrade server RAM/CPU.
5. Simpan semua credential (aaPanel, SSH, DB, Redis, Stripe, SMTP) dalam password manager (Bitwarden, 1Password), JANGAN simpan dalam plaintext notepad tanpa encrypt.

---

🎉 **SELAMAT, ANDA TELAH BERJAYA DEPLOY NFC BUSINESS CARD KE PRODUCTION!** 🎉
