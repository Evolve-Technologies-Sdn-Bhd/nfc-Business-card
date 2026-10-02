# Pelan Tugasan: Aliran Pesanan Kad NFC

Setiap tugasan diurutkan berdasarkan kebergantungan. Selesaikan satu demi satu.

---

## Tugasan 1: Database Migration — Expand Status Enum + Tambah Columns
**Priority:** high  
**Status:** pending  
**Dependency:** tiada

### Kerja yang Perlu Dibuat
1. Buat migration baru untuk table `nfc_cards`:
   - Expand `status` enum untuk tambah nilai: `pending_payment`, `awaiting_payment_verification`, `payment_verified`, `processing`, `shipped`, `delivered`, `cancelled`
   - TAMBAH column `transaction_id` (unsignedBigInteger, nullable, index) — FK ke transactions.id
   - TAMBAH column `order_confirmed_at` (timestamp, nullable) — masa admin verify payment
   - TAMBAH column `user_received_confirmed_at` (timestamp, nullable) — masa user confirm terima
   - TAMBAH column `courier` (string, nullable) — nama kurier (PosLaju, J&T, dll)
   - TAMBAH column `cancelled_by` (unsignedBigInteger, nullable, index) — user_id yang cancel
   - TAMBAH column `cancelled_reason` (text, nullable) — sebab cancel
2. Buat migration baru untuk table `transactions`:
   - TAMBAH column `nfc_card_id` (unsignedBigInteger, nullable, index) — reverse link

### Acceptance Criteria (Task-local Test Requirements)
- rule: Migration berjaya dijalankan pada SQLite database testing tanpa error
- rule: Rows lama dalam nfc_cards yang statusnya active/inactive/expired/replacement TIDAK terjejas
- rule: Semua column baru boleh disimpan dan dibaca melalui model
- rule: Status enum baru boleh di-set menggunakan model NfcCard

### Fail Berkaitan
- Create: `backend/database/migrations/2026_09_29_000001_expand_nfc_card_status_and_add_columns.php`
- Create: `backend/database/migrations/2026_09_29_000002_add_nfc_card_id_to_transactions.php`

---

## Tugasan 2: Update Model NfcCard — Status Helpers + Relationships + Casts
**Priority:** high  
**Status:** pending  
**Dependency:** Tugasan 1

### Kerja yang Perlu Dibuat
1. Tambah ke `$fillable`: `transaction_id`, `order_confirmed_at`, `user_received_confirmed_at`, `courier`, `cancelled_by`, `cancelled_reason`
2. Tambah ke `$casts`: `order_confirmed_at` → datetime, `user_received_confirmed_at` → datetime
3. Tambah relationship `transaction()` → belongsTo(Transaction::class)
4. Tambah relationship `cancelledBy()` → belongsTo(User::class, 'cancelled_by')
5. Tambah helper methods:
   - `isPendingPayment()`: status === pending_payment
   - `isAwaitingVerification()`: status === awaiting_payment_verification
   - `isPaymentVerified()`: status === payment_verified
   - `isProcessing()`: status === processing
   - `isShipped()`: status === shipped
   - `isDelivered()`: status === delivered
   - `isCancelled()`: status === cancelled
   - `canAccessProfileBuilder()`: status ∈ {payment_verified, processing, shipped, delivered, active}
   - `canMarkAsProcessing()`: status === payment_verified
   - `canMarkAsShipped()`: status ∈ {payment_verified, processing}
   - `canMarkAsDelivered()`: status === shipped
   - `canCancel()`: status ∉ {delivered, active, cancelled}
   - `getStatusLabel()`: return label BM yang mesra manusia (e.g. "Menunggu Bayaran" untuk pending_payment)
   - `getStatusBadgeClass()`: return CSS class warna untuk badge
   - `getTimeline()`: return array step-by-step timeline untuk UI user (setiap step ada label, completed boolean, date, description)
6. Update `getStatusBadgeAttribute()` untuk handle semua status baru
7. KEKALKAN backward compatibility — `isActive()` masih return true for delivered+active

### Test Requirements
- rule: Setiap helper method return boolean yang betul untuk setiap status
- rule: `canAccessProfileBuilder()` return true untuk {payment_verified, processing, shipped, delivered, active}
- rule: Timeline array mengandungi step: Order → Payment → Verification → Processing → Shipped → Delivered dengan completed status yang betul
- rubric: Badge warna berbeza untuk setiap status (skala 0-2, threshold ≥ 2)

### Fail Berkaitan
- Edit: [NfcCard.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Models/NfcCard.php)

---

## Tugasan 3: Update Model Transaction — Reverse Link
**Priority:** medium  
**Status:** pending  
**Dependency:** Tugasan 1

### Kerja yang Perlu Dibuat
1. Tambah ke `$fillable`: `nfc_card_id`
2. Tambah relationship `nfcCard()` → belongsTo(NfcCard::class)

### Test Requirements
- rule: Transaction boleh link ke NfcCard melalui `$transaction->nfcCard`
- rule: Bila NfcCard di-load dengan `with('transaction')`, relationship berjaya dimuatkan

### Fail Berkaitan
- Edit: [Transaction.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Models/Transaction.php)

---

## Tugasan 4: Update NfcCardController@store — Status Default Pending + Upload Proof Endpoint
**Priority:** high  
**Status:** pending  
**Dependency:** Tugasan 2

### Kerja yang Perlu Dibuat
1. **Modify `store()` method**:
   - Status default TUKAR dari `active` → `pending_payment` (line 164-177)
   - JANGAN terus activate user subscription pada line 180-185 — comment out / remove
   - Hantar notification ke user tentang order diterima + arahan bayaran
   - Create placeholder Transaction (payment_rail=manual_bank, status=pending) jika belum ada — link kan ke NfcCard.transaction_id
2. **Add new endpoint: `POST /nfc-cards/{nfcCard}/upload-payment-proof`**:
   - User upload bukti bayaran untuk order yang pending_payment
   - Simpan file ke storage seperti PaymentController
   - Tukar NfcCard.status ke `awaiting_payment_verification`
   - Jika transaction wujud, update transaction.payment_proof_url + payment_proof_uploaded_at
   - Hantar notification ke admin: "Bukti bayaran baru menunggu pengesahan"
   - Create activity_log entry
3. **Add new endpoint: `POST /nfc-cards/{nfcCard}/confirm-received`**:
   - User sahkan terima kad (bila status shipped)
   - Tukar status ke `delivered` kemudian `active`
   - Set `user_received_confirmed_at = now()`
   - Set `delivered_date = now()` jika kosong
   - AKTIFKAN subscription user (code asal dari store() line 180-185 pindah ke sini)
   - Hantar notifikasi ke admin + user
   - Create activity_log entry

### Test Requirements
- rule: `store()` mencipta NfcCard dengan status `pending_payment`
- rule: `store()` TIDAK mengubah `subscription_active` user kepada true
- rule: Upload payment proof berjaya tukar status ke `awaiting_payment_verification` dan simpan payment_proof_url
- rule: Confirm received hanya boleh untuk status `shipped` — return 400 untuk status lain
- rule: Selepas confirm received, user.subscription_active = true

### Fail Berkaitan
- Edit: [NfcCardController.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Http/Controllers/Api/NfcCardController.php)
- Edit: [api.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/routes/api.php) (tambah routes baru)

---

## Tugasan 5: AdminController — Tambah Order Management Endpoints
**Priority:** high  
**Status:** pending  
**Dependency:** Tugasan 2

### Kerja yang Perlu Dibuat
Tambah endpoint-endpoint berikut dalam AdminController (atau create AdminCardOrderController jika lebih bersih):

1. **`GET /admin/nfc-cards/orders`** — list orders dengan filter status (sedia ada getNfcCards, tapi ensure filter support status baru)
2. **`POST /admin/nfc-cards/{cardId}/verify-payment`** — admin verify payment:
   - Body: { notes?: string }
   - Validation: mesti admin, status mesti `awaiting_payment_verification`
   - Tukar NfcCard.status → `payment_verified`
   - Set `order_confirmed_at = now()`
   - Update Transaction (jika ada) → status = succeeded, verified_by = admin.id, verified_at = now()
   - Notifikasi ke user: bayaran disahkan, boleh mula design profile
   - Activity log entry
3. **`POST /admin/nfc-cards/{cardId}/reject-payment`** — admin reject payment proof:
   - Body: { reason: string }
   - Status kekal `pending_payment` ATAU tukar ke `awaiting_payment_verification` (user perlu upload proof baru)
   - Update transaction → status = failed, failure_code = payment_rejected, failure_message = reason
   - Notifikasi ke user: bukti ditolak, sebab
4. **`POST /admin/nfc-cards/{cardId}/mark-processing`** — mark processing:
   - Body: { nfc_card_id?: string } (physical NFC chip ID yang admin encode)
   - Validation: status ∈ {payment_verified}
   - Tukar status → `processing`
   - Jika ada `nfc_card_id` (physical), update column nfc_card_id
   - Notifikasi user
5. **`POST /admin/nfc-cards/{cardId}/mark-shipped`** — mark shipped:
   - Body: { tracking_number: string, courier?: string, shipped_date?: date }
   - Validation: status ∈ {payment_verified, processing}
   - Tukar status → `shipped`
   - Update tracking_number, courier, shipped_date
   - Notifikasi user dengan tracking number
6. **`POST /admin/nfc-cards/{cardId}/mark-delivered`** — admin mark delivered:
   - Body: { delivered_date?: date }
   - Status tukar → delivered → active
   - Aktifkan subscription user
   - Notifikasi user
7. **`POST /admin/nfc-cards/{cardId}/cancel`** — cancel order:
   - Body: { reason: string }
   - Validation: status ∉ {delivered, active, cancelled}
   - Status → `cancelled`
   - Set cancelled_by, cancelled_reason
   - Notifikasi user + admin
   - Transaction (jika ada) → status = cancelled

### Test Requirements
- rule: Semua endpoint admin memerlukan auth:sanctum + admin middleware
- rule: Setiap endpoint validate status sebelum transit — return 400 jika tidak dibenarkan
- rule: Setiap endpoint menjana notifikasi ke user yang sesuai
- rule: Verify payment mengaktifkan access ProfileBuilder untuk user (bukan subscription, cuma canAccessProfileBuilder)
- rule: Cancel order mengembalikan status subscription user kepada free jika ia satu-satunya kad

### Fail Berkaitan
- Edit: [AdminController.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Http/Controllers/Api/AdminController.php) ATAU create new Admin/NfcCardOrderController.php
- Edit: [api.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/routes/api.php) (tambah routes baru ke admin group)

---

## Tugasan 6: Modify PaymentService/ManualBankTransferController — Auto-verify linked NfcCard
**Priority:** medium  
**Status:** pending  
**Dependency:** Tugasan 3, 5

### Kerja yang Perlu Dibuat
1. Dalam `ManualBankTransferController@verifyTransfer` (line 88-181):
   - Bila admin approve transaction yang mempunyai `transaction.nfc_card_id` wujud, panggil juga logic untuk mark NfcCard sebagai payment_verified
   - Re-use logic dari Tugasan 5 untuk consistency
2. Dalam `PaymentController@uploadPaymentProof`:
   - Jika transaction ada nfc_card_id, update juga NfcCard.status ke awaiting_payment_verification

### Test Requirements
- rule: Bila admin verify manual bank transfer yang linked ke NfcCard, NfcCard tukar ke payment_verified
- rule: Bila user upload payment proof pada transaction yang linked, NfcCard tukar ke awaiting_payment_verification

### Fail Berkaitan
- Edit: [ManualBankTransferController.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Http/Controllers/Api/Admin/ManualBankTransferController.php)
- Edit: [PaymentController.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Http/Controllers/Api/PaymentController.php)

---

## Tugasan 7: Update Subscription/Access Gates — ProfileBuilder
**Priority:** high  
**Status:** pending  
**Dependency:** Tugasan 2

### Kerja yang Perlu Dibuat
1. Dalam User model, check method `hasPremiumSubscription()` dan `hasPhysicalCard()`:
   - Modifikasi supaya user dengan NfcCard status ∈ {payment_verified, processing, shipped, delivered, active} juga dianggap "ada access premium/basic profile builder"
   - ATAU create method baru `canAccessPaidProfileBuilder($plan)` untuk gate yang lebih tepat
2. Dalam NfcCardController@index, @show, @analytics, dll — check `hasPremiumSubscription()` yang telah dikemaskini, ATAU guna gate baru `canAccessPaidProfileBuilder`
3. Pastikan middleware/guard untuk pages premium TIDAK block user sebelum payment_verified — TAPI masih block user free

### Test Requirements
- rule: User dengan 1 kad berstatus `payment_verified` BOLEH load NFC cards list (status 200, bukan 403 upgrade_required)
- rule: User dengan semua kad `pending_payment` TIDAK BOLEH load (masih 403)
- rule: User legacy dengan subscription_active=true masih boleh access seperti biasa

### Fail Berkaitan
- Edit: [User.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Models/User.php) (cari hasPremiumSubscription, hasBasicSubscription, hasPhysicalCard)
- Edit: [NfcCardController.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Http/Controllers/Api/NfcCardController.php)

---

## Tugasan 8: Frontend — Update CardManagement.vue (User)
**Priority:** high  
**Status:** pending  
**Dependency:** Tugasan 4, 7

### Kerja yang Perlu Dibuat
1. **Update status badge mapping** dalam `getStatusBadgeClass()`:
   - pending_payment → merah/warning (bg-orange-100 text-orange-800)
   - awaiting_payment_verification → kuning (bg-yellow-100 text-yellow-800)
   - payment_verified → hijau muda (bg-emerald-100 text-emerald-800)
   - processing → ungu (bg-purple-100 text-purple-800)
   - shipped → biru (bg-blue-100 text-blue-800)
   - delivered → hijau tua (bg-green-100 text-green-800)
   - cancelled → merah gelap (bg-red-100 text-red-800)
   - sedia ada: active, inactive, expired, replacement KEKAL
2. **Tambah Timeline component** untuk setiap card:
   - Papar 6 step order timeline berdasarkan `card.getTimeline()` (dari API)
   - Setiap step: Icon completed/in-progress/pending + label BM
3. **Upload Payment Proof Modal/Button**:
   - Jika status === pending_payment ATAU awaiting_payment_verification (proof dah ada tapi rejected), papar butang "Upload Bukti Bayaran"
   - Modal dengan file input, submit ke endpoint upload-payment-proof
4. **Confirm Received Button**:
   - Jika status === shipped, papar butang prominent hijau "Saya Telah Terima Kad"
   - Confirm dialog sebelum submit
   - Select call confirm-received endpoint
5. **Butang Design Profile Konteks**:
   - Jika `canAccessProfileBuilder()` = true → butang "Design Profile" aktif dan navigate ke ProfileBuilder
   - Jika false → butang disabled dengan tooltip "Sila tunggu pengesahan bayaran untuk mula design profile"
6. **Buang atau kemaskini gate on line 1033-1060**:
   - User dengan status payment_verified+ patut boleh access page ini, bukan cuma yang subscription_active=true
7. **Show Order Info + Payment Instructions**:
   - Untuk status pending_payment, papar kotak prominent dengan:
     - Jumlah perlu dibayar
     - Akaun bank (dapatkan dari payment rails endpoint)
     - Reference number = card.card_id
     - Arahan "Sila upload bukti bayaran selepas membuat pemindahan"

### Test Requirements
- rule: Status badge warna unik untuk sekurang-kurangnya 7 status baru
- rule: Timeline show completed steps yang betul untuk card berstatus payment_verified (step 1-3 completed, 4-6 pending)
- rule: Upload payment proof hanya muncul pada status yang betul
- rule: Butang "Design Profile" enable hanya bila status ∈ {payment_verified, processing, shipped, delivered, active}
- rule: Selepas confirm received success, butang hilang dan status jadi delivered/active

### Fail Berkaitan
- Edit: [CardManagement.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/UserDashboard/CardManagement.vue)

---

## Tugasan 9: Frontend — Update Admin NFC Cards Page
**Priority:** high  
**Status:** pending  
**Dependency:** Tugasan 5

### Kerja yang Perlu Dibuat
1. **Update Status Filter** (line 41-57 dropdown):
   - Tambah semua pilihan status baru: pending_payment, awaiting_payment_verification, payment_verified, processing, shipped, delivered, cancelled
   - Label BM: "Menunggu Bayaran", "Menunggu Sahkan Bayaran", "Bayaran Disahkan", "Memproses", "Dihantar", "Diterima", "Dibatalkan"
2. **Update Status Badge** (line 1183-1192):
   - Mapping sama dengan user page (warna konsisten)
3. **Action Butang Kontekstual** (replace line 216-232 toggle status):
   - Berdasarkan status semasa, render butang yang sesuai:
     - `awaiting_payment_verification`:
       - Butang hijau "Verify Payment" → buka modal (optional notes), call verify-payment endpoint
       - Butang merah "Reject" → modal input reason, call reject-payment
       - Link untuk view payment proof image (buka dalam new tab / modal preview)
     - `payment_verified`:
       - Butang ungu "Mark Processing" → modal untuk input NFC Card ID (physical chip), call mark-processing
       - Butang merah "Cancel Order"
     - `processing`:
       - Butang biru "Mark Shipped" → modal input tracking_number, courier, date, call mark-shipped
       - Butang merah "Cancel Order"
     - `pending_payment`:
       - Butang merah "Cancel Order"
       - (optional) Butang "Send Payment Reminder" notification
     - `shipped`:
       - Butang hijau "Mark Delivered" → call mark-delivered
       - Butang "Send Delivery Reminder"
     - `delivered` / `active`:
       - Butang sedia ada: Deactivate
     - `cancelled`:
       - Tiada action button, cuma view
4. **Update Edit Modal Status Pilihan** (line 528-534):
   - Tambah semua status baru dalam dropdown edit form

### Test Requirements
- rule: Untuk card berstatus `awaiting_payment_verification`, admin nampak Verify + Reject buttons
- rule: Untuk card berstatus `payment_verified`, admin nampak Mark Processing + Cancel
- rule: Untuk card berstatus `processing`, admin nampak Mark Shipped
- rule: Untuk card berstatus `shipped`, admin nampak Mark Delivered
- rule: Untuk card berstatus `delivered` / `active`, admin nampak Deactivate seperti asal
- rule: Filter dropdown mempunyai semua 10+ status pilihan

### Fail Berkaitan
- Edit: [nfc-cards.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/AdminManagement/nfc-cards.vue)

---

## Tugasan 10: Frontend — Update stores + authStore permission logic
**Priority:** medium  
**Status:** pending  
**Dependency:** Tugasan 7

### Kerja yang Perlu Dibuat
1. Kemaskini auth store / nfcCard store supaya:
   - User dengan NfcCard payment_verified+ dianggap mempunyai access ke profile builder premium/basic
2. Pastikan side navigation "Profile Builder" dan "Card Management" TIDAK disembunyikan untuk user dengan status payment_verified (sekarang mungkin hanya show jika subscription_active=true)

### Test Requirements
- rule: Sidebar menu "Profile Builder" muncul untuk user dengan card berstatus payment_verified (walaupun subscription_active=false)
- rule: Sidebar menu "Card Management" muncul untuk user yang baru create order (pending_payment status)

### Fail Berkaitan
- Edit: [auth.js](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/stores/auth.js)
- Edit: [nfc.js](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/stores/nfc.js) atau [nfcCard.js](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/stores/nfcCard.js)
- Edit: UserDashboard layout sidebar (jika ada hardcoded permission check)

---

## Tugasan 11: Notification Types + Activity Log Integration
**Priority:** medium  
**Status:** pending  
**Dependency:** Tugasan 4, 5

### Kerja yang Perlu Dibuat
1. Tambah notification types baru dalam Notification model / NotificationService:
   - `nfc_card_order_created`
   - `nfc_card_payment_proof_uploaded`
   - `nfc_card_payment_verified`
   - `nfc_card_payment_rejected`
   - `nfc_card_processing`
   - `nfc_card_shipped`
   - `nfc_card_delivered`
   - `nfc_card_cancelled`
2. Pastikan setiap notification mempunyai:
   - title ringkas
   - message BM yang lengkap
   - link ke page relevan (Card Management untuk user, Admin NFC Cards untuk admin)
3. Activity logs:
   - action_type enum sedia ada (string flexible) OK, cuma pastikan setiap action di-log

### Test Requirements
- rule: Setiap perubahan status melalui API mencipta sekurang-kurangnya 1 record notifications untuk target user
- rubric: Kandungan notifikasi jelas, dalam BM, ada action button link yang betul (skala 0-2, threshold ≥ 1)

### Fail Berkaitan
- Edit: [NotificationService.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Services/NotificationService.php)
- Edit: [Notification.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Models/Notification.php) (jika ada type enum)

---

## Tugasan 12: Backward Compatibility Test + Manual Run
**Priority:** high  
**Status:** pending  
**Dependency:** Semua tugasan sebelum ini

### Kerja yang Perlu Dibuat
1. Run migration pada database sedia ada (backup dahulu!)
2. Verify:
   - User lama dengan status active masih boleh login dan access semua page
   - Kad lama dengan status active masih muncul
   - Subscription user legacy TIDAK terjejas
3. Test end-to-end flow secara manual:
   - Register user baru → login → order premium card → status pending_payment ✓
   - User upload proof → awaiting ✓
   - Login admin → verify payment → payment_verified ✓
   - Login user → access Profile Builder (sebelum shipped) ✓
   - Admin mark processing → processing ✓
   - Admin mark shipped with tracking → shipped ✓
   - User click confirm received → delivered + active ✓
   - Subscription user active = true ✓
   - Admin cancel order pada status pending_payment → cancelled ✓

### Test Requirements
- rule: Semua step E2E flow dari user register sampai delivered berjaya tanpa error
- rule: User legacy (10+ accounts) masih boleh login dan guna system seperti biasa
- rule: Tiada card sedia ada yang hilang atau tukar status secara salah

### Fail Berkaitan
- Tiada fail baru — verification step
