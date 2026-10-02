# Spesifikasi: Aliran Pesanan Kad NFC (Sebelum Payment Gateway)

## Masalah
Flow pesanan kad NFC semasa TIDAK lengkap:
1. Bila user submit order kad, status terus jadi `active` tanpa sebarang semakan bayaran
2. Admin tiada cara tersusun untuk menerima order, semak bayaran, confirm order, dan update status penghantaran
3. User tiada cara untuk confirm kad telah diterima
4. Tiada kebenaran (permission gate) yang memastikan user hanya boleh design profile SELEPAS order disahkan oleh admin
5. Status NfcCard hanya ada `active, inactive, expired, replacement` — tidak mencukupi untuk order lifecycle

## Pengguna & Matlamat
- **Pengguna Akhir (End User)**: Order kad premium/basic, bayar, tunggu pengesahan, design profile, tunggu kad sampai, confirm terima
- **Admin**: Terima senarai order, semak bukti bayaran (manual bank transfer dalam sistem luar), confirm order, assign NFC ID, update status shipment, track delivered
- **Kedua-dua pihak**: Dapat notifikasi pada setiap peringkat

## Matlamat Bukan (Non-Goals)
- ❌ Tidak mengintegrasikan payment gateway sebenar (FIUU/Stripe) — flow manual sahaja untuk fasa ini
- ❌ Tidak buat tracking kurier sebenar — cuma simpan tracking number dan status shipped/delivered
- ❌ Tidak buat refund flow
- ❌ Tidak buat business quota flow (sedia ada)

---

## Keperluan Fungsian

### 1. Status Lifecycle Kad
Status `nfc_cards.status` hendaklah DIPERKLUASKAN kepada enum berikut (dengan urutan lifecycle):
- `pending_payment` — User telah submit order, belum buat bayaran / belum upload bukti bayaran
- `awaiting_payment_verification` — User dah upload bukti bayaran, tunggu admin verify
- `payment_verified` — Admin telah sahkan bayaran, order dalam proses (user BOLEH mula design profile pada peringkat ini)
- `processing` — Admin sedang sediakan kad / encode NFC tag
- `shipped` — Kad telah diposkan, ada tracking number
- `delivered` — User telah confirm terima kad (atau admin tandakan delivered)
- `active` — Kad digunakan secara normal (legacy status)
- `inactive` — Kad dinyahaktifkan (legacy)
- `expired` — Langganan tamat (legacy)
- `cancelled` — Order dibatalkan
- `replacement` — Ganti kad (legacy)

**Urutan Transisi Status:**
```
pending_payment → awaiting_payment_verification → payment_verified → processing → shipped → delivered → active
                 ↓                                      ↓           ↓         ↓
              (cancel)                              (cancel)    (cancel)    (cancel) → cancelled
```

### 2. Flow User (End User)
#### 2a. Create Free Account
- User register → plan auto `free` → terus access Profile Builder (free version)
- Tiada physical card, tiada order flow

#### 2b. Proceed Order Premium/Basic dengan Physical Card
- User pilih plan + isi butiran (owner, billing, shipping, contact)
- Order di-create dengan status `pending_payment`
- Paparkan arahan bayaran (manual bank transfer: akaun bank, jumlah, reference code = order.card_id)
- User boleh upload bukti bayaran (payment proof)
- Setelah upload → status tukar ke `awaiting_payment_verification`
- User nampak timeline order pada Card Management:
  - 🔴 Order Dihantar → 🟡 Menunggu Pengesahan Bayaran → 🟢 Bayaran Disahkan (boleh design profile!) → 🟤 Memproses → 🔵 Dihantar (tracking) → 🟢 Diterima (user click confirm)

#### 2c. Selepas Payment Verified
- BUTTON "Design Profile" muncul / Profile Builder accessible
- Card masih tak "active" lagi tapi profile builder dah boleh guna

#### 2d. Selepas Card Disahkan Diterima (Delivered)
- Status jadi `delivered` → auto `active`
- NFC tag dah encoded, boleh tap guna

---

### 3. Flow Admin
#### 3a. Senarai Order (Dashboard Admin NFC Cards)
- Filter berdasarkan status: `pending_payment`, `awaiting_payment_verification`, `payment_verified`, `processing`, `shipped`, `delivered`, `cancelled`
- Setiap row menunjukkan:
  - Maklumat user, plan, amaun
  - Status semasa
  - Bukti bayaran (jika ada) — boleh click untuk view
  - Actions button bergantung pada status:
    - `awaiting_payment_verification` → 2 butang: ✅ Verify Payment (approve) / ❌ Reject (dengan alasan)
    - `payment_verified` → Butang "Mark as Processing" + borang untuk assign NFC Card ID (physical chip identifier)
    - `processing` → Butang "Mark as Shipped" + input tracking number + shipping date
    - `shipped` → Butang "Mark as Delivered" (untuk kes tak sampai user confirm)
    - Semua status kecuali delivered/active → Butang Cancel Order

#### 3b. Kesan Sampingan Admin Verify Payment
- Transaction (jika ada link manual_bank) dikemaskini ke `succeeded`
- NfcCard status → `payment_verified`
- Notification dihantar ke user: "Bayaran anda telah disahkan! Anda kini boleh mula design profile anda."
- User subscription_plan / subscription_active TIDAK ditukar lagi — tunggu delivered. TAPI access ke Profile Builder untuk plan premium/basic DIBENARKAN berdasarkan `payment_verified` status.

#### 3c. Admin Mark as Shipped
- Isi tracking number, shipped_date, (optional courier)
- Notification ke user: "Kad NFC anda telah dihantar! No. Tracking: XXX"

#### 3d. Admin / User Mark as Delivered
- User klik "Saya telah terima kad" → delivered_date diisi, status → delivered
- Atau admin mark delivered dari panel
- Selepas delivered → subscription_active = true, subscription_start_date = now, end_date = +1 tahun (atau plan interval)

---

### 4. Permission / Access Gates
#### 4a. Profile Builder Access
User boleh akses Premium/Basic Profile Builder JIKA:
```
status kad ∈ {payment_verified, processing, shipped, delivered, active}
ATAU
user.subscription_active === true (legacy user)
```

#### 4b. Card Management Page Access
- User free plan: redirect ke Plan Selection dengan mesej "Sila pilih plan berbayar untuk order kad fizikal"
- User ada order dengan status mana-mana → boleh view timeline

---

### 5. Notifications (Activity Log + In-App Notifications)
Pada setiap perubahan status, create:
- `activity_logs` entry (audit trail)
- In-app notification untuk user relevan
- (Optional) Email notification

Peristiwa trigger:
| Event | Penerima | Teks Notifikasi |
|---|---|---|
| Order created (pending_payment) | User | "Order anda telah diterima. Sila buat bayaran dan upload bukti bayaran." |
| Proof uploaded | Admin | "Bukti bayaran baharu untuk pesanan [card_id] menunggu pengesahan." |
| Payment verified | User | "Bayaran anda disahkan! Kini anda boleh mula design profile." |
| Payment rejected | User | "Bukti bayaran anda ditolak: [alasan]. Sila upload semula." |
| Mark as processing | User | "Pesanan anda sedang diproses. Kami sedang menyediakan kad NFC anda." |
| Mark as shipped | User | "Kad anda telah dihantar! No. Tracking: [tracking_number]" |
| Delivered (user confirm) | Admin | "Pengguna telah mengesahkan penerimaan kad untuk [card_id]." |
| Delivered (admin mark) | User | "Kami telah merekodkan kad anda sebagai telah diterima. Selamat menggunakan NFCGo!" |
| Order cancelled | User + Admin | "Pesanan [card_id] telah dibatalkan." |

---

### 6. Hubungan NfcCard ↔ Transaction
- Tambah column `nfc_cards.transaction_id` (nullable FK) untuk link dengan `transactions` table
- Bila user upload payment proof, create Transaction dengan payment_rail=manual_bank jika belum ada
- Bila admin verify order, update juga Transaction status ke `succeeded`

---

## Keperluan Bukan Fungsian
- **Backward Compatibility**: Semua user sedia ada dengan status `active` mesti terus berfungsi tanpa gangguan
- **Database Migration**: Migration mesti selamat — guna ALTER TABLE yang menambah enum values, buang yang lama
- **Kejelasan UI**: Setiap status mempunyai badge warna yang berbeza dan jelas
- **Debounce / Optimistic UI**: Frontend show loading state pada setiap action
- **Error Handling**: Setiap API mesti return response envelope `{success, message, errors}` yang konsisten

## Batasan / Andaian
1. Pembayaran dilakukan SECARA MANUAL — admin akan check dalam sistem bank/eperbankan sendiri, kemudian confirm dalam panel admin
2. Tiada penjanaan invois automatik untuk flow ini (boleh guna sedia ada jika ada)
3. Tiada pengecaman AI untuk bukti bayaran — admin verify manually
4. User free account TIDAK melalui flow order — mereka terus dapat profile builder free version

## Soalan Terbuka (Telah Diputuskan)
- ✅ Payment proof URL disimpan dalam Transaction (sedia ada), NfcCard boleh link ke Transaction via transaction_id
- ✅ User access ProfileBuilder selepas payment_verified — BUKAN selepas shipped. Supaya user ada masa design sebelum kad sampai.
- ✅ Bila user confirm delivered → status NfcCard tukar ke active + subscription activated
- ✅ Admin NFC Cards page filter akan menunjukkan semua status baru

---

## Kriteria Penerimaan (Acceptance Criteria)

### Rule: Status Lifecycle
- rule: Bila user create order, NfcCard.status mestilah `pending_payment`, BUKAN terus `active`
- rule: Bila user upload payment proof, status tukar ke `awaiting_payment_verification` dan notifikasi admin dijana
- rule: Bila admin verify payment, status tukar ke `payment_verified`, notifikasi user dijana, dan access ke ProfileBuilder premium DIBENARKAN
- rule: Bila admin mark shipped, tracking_number dan shipped_date mesti diisi dan notifikasi user dijana
- rule: Bila user/admin mark delivered, delivered_date diisi dan status → delivered + kemudian active
- rule: Mana-mana status sebelum delivered boleh di-cancel oleh admin
- rule: Order cancelled TIDAK boleh access ProfileBuilder premium

### Rubric: Kualiti UI
- rubric: Kejelasan status badges pada Card Management — setiap 8+ status baru ada badge warna yang unik dan mudah dibezakan (skala 0-2, threshold ≥ 2)
- rubric: Timeline visual untuk user menunjukkan semua langkah order dengan indicator completed/in-progress/pending (skala 0-2, threshold ≥ 2)
- rubric: Admin actions button context-aware — hanya button yang relevan muncul berdasarkan status semasa (skala 0-2, threshold ≥ 2)

### Rule: Data Integrity
- rule: Migration mesti berjaya run tanpa gugurkan data sedia ada — rows lama dengan status `active/inactive/expired/replacement` kekal tidak berubah
- rule: Semua API endpoint baru ada auth:sanctum dan admin middleware untuk admin actions
- rule: Setiap perubahan status menjana activity_logs entry dengan action_type yang sesuai
- rule: Link NfcCard ↔ Transaction wujud melalui transaction_id column (nullable)
