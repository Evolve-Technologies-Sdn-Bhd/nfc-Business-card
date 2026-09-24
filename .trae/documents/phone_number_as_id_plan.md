# Penggunaan Nombor Telefon sebagai NFC ID dan Profile Landing Page ID

## Repository Research

### Senibina Semasa (Current Architecture)

Sistem ini menggunakan 3 model utama untuk operasi kad NFC dan profil:

1. **NfcCard** (`backend/app/Models/NfcCard.php`)
   - Mewakili pesanan kad NFC pengguna
   - `id` (bigint PK) — FK numeric untuk `landing_pages.nfc_card_id`
   - `nfc_card_id` (varchar, unique) — format `NFC-XXXXXXXXXXXX`, ID awam untuk URL route
   - `contact_number` (varchar, required) — nombor telefon pemilik kad
   - `resolveRouteBinding()` menyokong lookup by `nfc_card_id` atau numeric `id`

2. **LandingPage** (`backend/app/Models/LandingPage.php`)
   - Data profil awam (dulu-dipanggil "Profile")
   - `nfc_card_id` (bigint FK → `nfc_cards.id`) — hubungan dengan NfcCard
   - Banyak field nombor telefon: `phone`, `phone_number`, `whatsapp_number`, `company_whatsapp` (semua nullable)

3. **NfcTag** (`backend/app/Models/NfcTag.php`)
   - Mewakili chip NFC fizikal
   - `nfc_id` (varchar, unique) — ID sebenar pada chip
   - `nfc_card_id` (varchar FK → `nfc_cards.nfc_card_id`) — hubungan STRING dengan NfcCard

4. **User** (`backend/app/Models/User.php`)
   - `phone` (varchar, nullable) — nombor telefon akaun pengguna

### Flow URL Profile Semasa

```
Frontend /profile/{nfc_cards.nfc_card_id}
  ↓
[ id ].vue: route.params.id
  ↓
API: GET /api/nfc-cards/{id}/landing-page
  ↓
NfcCard::resolveRouteBinding() — cari by nfc_card_id (prioriti 1) atau numeric id (2)
  ↓
NfcCardController@getLandingPage() — dapatkan LandingPage melalui FK numeric
```

### Flow NFC Tap Semasa

```
POST /api/nfc/tap/{nfc_tags.nfc_id}
  ↓
NfcController@tap() — cari NfcTag by nfc_id chip fizikal
```

### Penjanaan URL Team Members Semasa

Dalam `ProfileController@getBusinessTeamMembers`:
```php
'landing_page_url' => url('/profile/' . $card->nfc_card_id)
```

---

## Fail dan Modul yang Perlu Diubah

### Backend (Laravel)

| Fail | Perubahan Dijangka |
|---|---|
| `backend/app/Models/NfcCard.php` | Tambah helper normalisasi nombor telefon, tambah lookup by contact_number dalam `resolveRouteBinding()` |
| `backend/app/Models/NfcTag.php` | Tambah helper normalisasi dan lookup alternatif by nombor telefon |
| `backend/app/Http/Controllers/Api/NfcCardController.php` | Tiada perubahan besar; bergantung pada route binding model |
| `backend/app/Http/Controllers/Api/NfcController.php` | Sokong lookup NFC tap by nombor telefon (sebagai fallback/alternatif kepada nfc_id chip) |
| `backend/app/Http/Controllers/Api/ProfileController.php` | Ubah `landing_page_url` dalam `getBusinessTeamMembers` guna nombor telefon |
| `backend/routes/api.php` | Tambah route alternatif jika perlu (atau guna route binding sedia ada) |
| `backend/database/migrations/YYYY_MM_DD_XXXXXX_normalize_contact_numbers.php` | Migration untuk normalisasi data nombor telefon sedia ada dan tambah index performance |

### Frontend (Nuxt 3 SPA)

| Fail | Perubahan Dijangka |
|---|---|
| `frontend/pages/profile/[id].vue` | Tiada perubahan besar (route param kekal `id`, backend handle lookup pelbagai format); tambah logging jika perlu |
| `frontend/components/homepage/` (semua komponen) | Jika ada hardcoded URL profile format lama, semak dan kemaskini |

---

## Implementation Steps (Berteraskan Kebergantungan)

### Langkah 1: Helper Normalisasi Nombor Telefon
**Objektif**: Wujudkan fungsi biasa untuk menormalkan nombor telefon (buang simbol, standard format)
- Tambah static method `normalizePhoneNumber(string $number): string` pada model NfcCard (dan/atau trait biasa)
- Normalisasi:
  - Buang semua aksara bukan digit: `+`, `-`, ` ` (spasi), `(`, `)`, `.`
  - Contoh: `+60 12-345 6789` → `60123456789`
  - Contoh: `012-345 6789` (MY local) → `60123456789` (jika ingin tambah country code; atau biarkan `0123456789` — perlu decide)

**Nota**: Keputusan untuk normalisasi country code perlu dibuat. Cadangan: simpan digit terakhir 10-11 tanpa country code untuk keserasian tempatan, tetapi lookup cuba kedua-dua format (dengan dan tanpa 60/+60).

### Langkah 2: Kemaskini `NfcCard::resolveRouteBinding()`
**Objektif**: Tambah lookup by nombor telefon sebagai prioriti ke-2 (selepas nfc_card_id, sebelum numeric id)
- Urutan lookup baharu:
  1. By `nfc_card_id` (format `NFC-XXXXXXXXXXXX`) — **prioriti 1, backward compatible**
  2. By `contact_number` dinormalisasi — **prioriti 2, ciri baharu**
  3. By numeric `id` — **prioriti 3, backward compatible**

Logik untuk lookup by contact_number:
1. Normalisasi `$value` → `$normalized`
2. Dapatkan semua NfcCard yang `contact_number` selepas normalisasi sepadan dengan `$normalized`
3. Jika tiada direct match, cuba pelbagai variasi:
   - Tambah `60` di hadapan untuk MY nombor (jika bermula dengan `0`)
   - Buang digit pertama (jika bermula dengan `60`)
4. Jika berbilang match:
   - Keutamaan: `status = 'active'` dahulu, kemudian `created_at` terkini, kemudian yang mempunyai LandingPage
5. Return match pertama yang sah, atau `null` jika tiada

### Langkah 3: Migration Normalisasi Data dan Index Performance
**Objektif**: Pastikan data sedia ada konsisten dan lookup pantas
- Migration baru:
  - Lakukan normalisasi ke atas semua `nfc_cards.contact_number` sedia ada (update batch)
  - Lakukan perkara sama pada field telefon lain (users.phone, landing_pages.phone, landing_pages.phone_number, landing_pages.whatsapp_number) sebagai data rujukan
  - **TIDAK** tambah unique constraint pada contact_number (satu nombor mungkin dikongsi ahli keluarga / rakan sekerja)
  - Tambah **index** pada `nfc_cards.contact_number` untuk performance lookup
  - Tambah composite index `(status, contact_number)` untuk mempercepatkan query berfilter

### Langkah 4: Kemaskini `ProfileController@getBusinessTeamMembers`
**Objektif**: URL profile team members kini guna nombor telefon
- Ubah baris:
  ```php
  'landing_page_url' => url('/profile/' . $card->nfc_card_id),
  ```
  Kepada:
  ```php
  'landing_page_url' => url('/profile/' . NfcCard::normalizePhoneNumber($card->contact_number ?? $card->nfc_card_id)),
  ```
- Fallback: Jika contact_number tiada (null/empty), guna `nfc_card_id` lama sebagai safety net.

### Langkah 5: Kemaskini `NfcController@tap`
**Objektif**: NFC tap menyokong nombor telefon sebagai ID alternatif
- Lookup sedia ada by `nfc_id` chip fizikal — **KEKALKAN** sebagai prioriti 1 (untuk NFC sebenar)
- Tambah fallback lookup:
  1. Jika `$nfcId` tidak match mana-mana NfcTag `nfc_id`
  2. Cuba normalize `$nfcId` sebagai nombor telefon
  3. Cari NfcCard by nombor tersebut (guna logik Langkah 2)
  4. Jika jumpa NfcCard, return `landing_page` dan NFC tag berkaitan (jika ada)
  5. Kembalikan 404 jika masih tiada match

### Langkah 6: Semakan Dasar Frontend
**Objektif**: Pastikan frontend tidak rosak dengan ID format baharu
- `[id].vue` route param `id` akan menerima pelbagai format: `NFC-XXX`, `60123456789`, numeric id
- Semua API call masih ke endpoint yang sama (`/nfc-cards/{id}/landing-page`) — route binding handle perbezaan
- **Tiada perubahan besar** pada fail `[id].vue` kecuali logging untuk debug jika perlu
- Semakan tambahan:
  - Cari semua hardcode `url('/profile/' . ...)` dalam kod frontend
  - Jika ada `navigateTo(\`/profile/...\`)` dengan andaian format `NFC-XXX`, pastikan ia boleh menerima nombor telefon juga (biasanya tak perlu ubah sebab ia guna pembolehubah)

### Langkah 7: Bagi tempat yang guna `nfc_card_id` sebagai rujukan dalaman
**Objektif**: Kekalkan integriti data dalaman
- `nfc_tags.nfc_card_id` (string FK) — **JANGAN UBAH**. Ia masih merujuk `nfc_cards.nfc_card_id` secara dalaman. Kita cuma tukar lookup awam URL.
- `landing_pages.nfc_card_id` (bigint FK) — **JANGAN UBAH**. Ia masih merujuk `nfc_cards.id` secara dalaman.
- Perubahan HANYA pada layer lookup untuk URL awam dan NFC tap entry point.

---

## Dependencies and Considerations

1. **Backward Compatibility WAJIB**:
   - Semua URL lama format `/profile/NFC-XXXXXXXXXXXX` MESTI terus berfungsi (keutamaan 1 dalam lookup)
   - Numeric id juga mesti berfungsi (keutamaan 3)

2. **Keunikan Nombor Telefon TIDAK DIPAKSA**:
   - Satu nombor telefon mungkin dipunyai oleh berbilang NfcCard (contoh: pasangan suami isteri, rakan sekerja yang share company line)
   - Strategi conflict resolution:
     1. Pilih kad `status = 'active'` dahulu
     2. Kemudian yang terbaru (`created_at DESC`)
     3. Kemudian yang mempunyai LandingPage lengkap
   - Log warning jika conflict berlaku untuk memudahkan debug

3. **Normalisasi Country Code**:
   - Kebanyakan user MY guna format `012-345 6789` atau `+60 12-345 6789`
   - Cadangan: Normalize ke digit sahaja tanpa country code (contoh: `0123456789`) dan cuba lookup dengan + tanpa prefix 60
   - Atau: Normalize ke format E.164 `60123456789` (standard antarabangsa tanpa `+`)

4. **Data Entry Validation**:
   - Masa hadapan: Perlu tambah validasi pada input `contact_number` semasa create NfcCard (store endpoint) untuk memastikan format lebih konsisten
   - Namun, tidak termasuk dalam scope perubahan ini untuk elakkan regression

5. **Performance**:
   - Tanpa index pada `contact_number`, lookup by nombor telefon akan buat full table scan yang perlahan bila data banyak
   - Migration index di Langkah 3 WAJIB dijalankan

---

## Validation (Selepas Implementasi)

### Ujian Manual Backend (menggunakan Postman/curl)

1. **URL dengan nfc_card_id lama**:
   ```
   GET /api/nfc-cards/NFC-BY1CINMFLU7X/landing-page
   ```
   ✅ JANGAN ROSAK — mesti return landing page seperti biasa

2. **URL dengan numeric id**:
   ```
   GET /api/nfc-cards/5/landing-page
   ```
   ✅ JANGAN ROSAK

3. **URL dengan nombor telefon (normalized)**:
   ```
   GET /api/nfc-cards/60123456789/landing-page
   ```
   ✅ BARU — mesti return landing page untuk kad yang mempunyai contact_number sepadan

4. **URL dengan nombor telefon (format raw user)**:
   ```
   GET /api/nfc-cards/012-345%206789/landing-page
   ```
   ✅ SEPATUTNYA BERJAYA jika route param encode betul (normalisasi dalam resolveRouteBinding)

5. **NFC Tap dengan nfc_id chip biasa**:
   ```
   POST /api/nfc/tap/CHIP0123456789
   ```
   ✅ JANGAN ROSAK

6. **NFC Tap dengan nombor telefon (sebagai alternative ID)**:
   ```
   POST /api/nfc/tap/60123456789
   ```
   ✅ BARU — mesti return profile data jika lookup berjaya

7. **Team Members API**:
   ```
   GET /api/user/business-team-members
   ```
   ✅ Semak field `landing_page_url` dalam response menggunakan nombor telefon

8. **Conflict Resolution test**:
   - Wujudkan 2 NfcCard dengan contact_number yang sama
   - Satu aktif, satu tidak aktif
   - Lookup by nombor telefon → mesti return yang aktif
   - Jika kedua-dua aktif → return yang terbaru

### Ujian Frontend (Browser)

9. **Buka URL `/profile/NFC-BY1CINMFLU7X` (lama)**
   ✅ Profile load seperti biasa

10. **Buka URL `/profile/60123456789` (baharu — nombor telefon)**
    ✅ Profile load seperti biasa

11. **Team section dalam profile — klik "View Profile" ahli team**
    ✅ URL patut guna format nombor telefon (jika contact_number wujud)

### Ujian Performance

12. Jalankan query lookup by contact_number pada db dengan data banyak:
    ```sql
    EXPLAIN SELECT * FROM nfc_cards WHERE contact_number = '60123456789';
    ```
    ✅ Patut guna index (bukan ALL full scan)

---

## Risiko dan Pengendalian

| Risiko | Kesan | Pengendalian / Fallback |
|---|---|---|
| **Lookup by nombor telefon berbilang match** | User terbuka profile salah | Prioriti: active > latest > ada landing page; log warning untuk disiasat admin |
| **Nombor telefon tiada dalam format standard** | Lookup gagal untuk data sedia ada yang tak konsisten | Migration Langkah 3 untuk normalize batch sedia ada |
| **User kongsi nombor telefon dengan card owner lain** | Conflict, profile yang "lebih popular" muncul dulu | Ini adalah choice reka bentuk yang diterima; jika jadi isu, boleh enforce unique constraint kemudian (breaking change) |
| **Index tambah ruang storage** | Saiz DB meningkat sedikit | Diterima — tradeoff untuk performance lookup |
| **Perubahan URL team members breaking bookmark external** | User yang bookmark profile staff sebelum ini guna NFC-XXX mungkin terkejut URL berubah | URL NFC-XXX LAMA MASIH BERFUNGSI (backward compat), jadi cuma URL paparan baru berubah |
| **Perubahan resolveRouteBinding menjejaskan API admin/dalaman yang gunakan route model binding** | Endpoint lain mungkin rosak jika logic lookup terlalu agresif | LOOKUP NOMBOR TELEFON DALAM resolveRouteBinding HANYA JIKA $value Nampak macam nombor telefon (semak regex digit sahaja, tak ada NFC- prefix, tak pure numeric kecil). Atau lebih selamat: tambah lookup tapi hanya trigger jika pola match. |

### Keputusan Reka Bentuk Kritikal (Perlu Luluskan)

**A**: Normalisasi Country Code
  - Pilihan 1 (Disyorkan): `0123456789` (10-11 digit, format MY tempatan tanpa country code)
  - Pilihan 2: `60123456789` (E.164 tanpa +)
  - Pilihan 3: Cuba kedua-dua format dalam lookup (lebih selamat tapi 2x query)

**B**: Bila trigger lookup nombor telefon dalam `resolveRouteBinding`
  - Pilihan 1 (Disyorkan): Selalu cuba — nfc_card_id dahulu, gagal → nombor telefon, gagal → numeric id
  - Pilihan 2: Hanya cuba jika $value match regex digit sahaja (panjang 9-15 digit), skip terus lookup nombor telefon jika ada aksara

**C**: Enforce unique contact_number atau tidak
  - Pilihan 1 (Disyorkan): TIDAK enforce unique; guna conflict resolution
  - Pilihan 2: Enforce unique (breaking change untuk user yang share nombor; perlu migration untuk handle duplicates dulu)

