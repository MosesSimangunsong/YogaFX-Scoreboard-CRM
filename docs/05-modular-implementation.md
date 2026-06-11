# YogaFX Scoreboard Modular Implementation
# Execution Roadmap and AI Collaboration Playbook

## 1. Document Purpose

Dokumen ini menjadi panduan **eksekusi implementasi bertahap** untuk YogaFX Scoreboard MVP bersama AI Coding Assistant.

Dokumen ini berfungsi sebagai:

* execution roadmap
* AI collaboration playbook
* referensi progres implementasi
* pengaman scope agar pekerjaan tetap terarah per modul

Audience utama:

* Anda
* Codex
* developer team
* founder yang ingin memantau progres pada level operasional

Dokumen ini harus dipakai bersama dengan dokumen source of truth berikut:

* [00-system-overview.md](/d:/Semester6/Kerja/YogaFX-Scoreboard/docs/00-system-overview.md)
* [01-product-requirements-document.md](/d:/Semester6/Kerja/YogaFX-Scoreboard/docs/01-product-requirements-document.md)
* [02-user-flow.md](/d:/Semester6/Kerja/YogaFX-Scoreboard/docs/02-user-flow.md)
* [03-erd-data-architecture.md](/d:/Semester6/Kerja/YogaFX-Scoreboard/docs/03-erd-data-architecture.md)
* [04-design-system.md](/d:/Semester6/Kerja/YogaFX-Scoreboard/docs/04-design-system.md)

Selain source of truth dokumen, implementasi admin juga harus membaca fondasi UI nyata yang sudah ada di:

* [resources/js/Pages/Admin/Scoreboards/Index.jsx](/d:/Semester6/Kerja/YogaFX-Scoreboard/resources/js/Pages/Admin/Scoreboards/Index.jsx)
* [resources/js/Pages/Admin/Scoreboards/Create.jsx](/d:/Semester6/Kerja/YogaFX-Scoreboard/resources/js/Pages/Admin/Scoreboards/Create.jsx)
* [resources/js/Pages/Admin/Scoreboards/Edit.jsx](/d:/Semester6/Kerja/YogaFX-Scoreboard/resources/js/Pages/Admin/Scoreboards/Edit.jsx)
* [resources/js/Pages/Admin/Scoreboards/Builder.jsx](/d:/Semester6/Kerja/YogaFX-Scoreboard/resources/js/Pages/Admin/Scoreboards/Builder.jsx)

---

## 2. Core Implementation Philosophy

Seluruh modular plan ini harus mengikuti filosofi produk:

```text
Lead Qualification
↓
Assessment
↓
Scoring
↓
Segmentation
↓
Result
↓
Follow-Up
```

Bukan:

```text
Learning
↓
Module
↓
Lesson
↓
Assessment
↓
Certificate
```

Implikasinya:

* jangan membawa pola arsitektur LMS ke dalam implementasi
* jangan menciptakan konsep module, lesson, progress belajar, atau certificate
* kerjakan modul berdasarkan value produk inti, bukan analogi dari LMS

---

## 3. How to Use This Document

Dokumen ini dipakai sebagai panduan kerja per modul.

Aturan penggunaan:

* kerjakan satu modul aktif pada satu waktu
* pastikan objective modul jelas sebelum coding dimulai
* refer selalu ke PRD, User Flow, ERD, dan Design System
* selesaikan satu modul sampai verifikasi dasar sebelum pindah ke modul berikutnya
* jika ada perubahan data model, sinkronkan dengan ERD dan implikasi ke modul lain
* hindari refactor liar di luar modul aktif kecuali benar-benar diperlukan untuk unblock implementasi

---

## 4. AI Collaboration Rules

Aturan kerja dengan AI harus eksplisit:

* satu task harus memiliki satu objective utama yang jelas
* AI harus selalu membaca context dokumen yang relevan sebelum mengubah kode
* AI tidak boleh melompat ke scope modul berikutnya tanpa instruksi
* setiap modul harus selesai dengan verifikasi minimum
* perubahan schema data harus konsisten dengan ERD
* perubahan flow harus konsisten dengan PRD dan User Flow
* UI public harus konsisten dengan Design System public
* UI admin harus konsisten dengan Design System admin dan shadcn-style dashboard
* AI harus menganggap template `Admin/Scoreboards` sebagai baseline implementasi admin yang sudah hidup
* AI tidak boleh membuat fitur “tambahan kecil” di luar modul hanya karena terlihat berguna
* jika ada keputusan yang belum final, AI harus menandainya sebagai assumption atau meminta klarifikasi

Checklist sebelum mulai task dengan AI:

* modul aktif sudah ditentukan
* objective task sudah sempit dan spesifik
* dependencies modul sudah tersedia
* output yang diharapkan sudah jelas
* pola pada page admin yang relevan sudah dibaca bila task menyentuh area admin

Checklist sebelum menutup task dengan AI:

* code changes selesai
* behavior utama berjalan
* test atau verifikasi minimum dilakukan
* scope tetap berada di dalam modul aktif
* dokumentasi penting atau assumption baru dicatat

---

## 5. Existing Admin Scoreboards Baseline

Folder `resources/js/Pages/Admin/Scoreboards` harus diperlakukan sebagai fondasi UI admin yang sudah aktif, bukan sekadar referensi visual.

Pola yang sudah terbentuk:

* semua page memakai `AuthenticatedLayout`
* heading area konsisten: title, supporting copy, dan action links/buttons
* layout utama memakai container lebar dengan spacing vertikal longgar
* surface utama memakai card putih, border slate, radius besar, dan shadow halus
* status feedback memakai inline alert sederhana untuk success/error
* tombol aksi utama memakai `Button` shadcn-style dengan variasi `default`, `outline`, dan `destructive`
* `Create` dan `Edit` berbagi pola form melalui komponen reusable `ScoreboardMetaForm`
* `Builder` bukan form admin biasa, tetapi workspace tiga panel: navigator kiri, canvas tengah, panel konfigurasi kanan
* `Builder` memakai `Sheet` untuk drawer tambahan seperti mobile panel, design settings, dan result ranges
* interaction pattern builder menggabungkan inline editing, manual save state, drawer-based secondary configuration, dan drag-and-drop reorder

Implikasi untuk modular implementation:

* modul admin selanjutnya harus memperluas pola yang sudah ada, bukan menciptakan shell baru
* page admin lain sebaiknya mengikuti bahasa visual dan struktur interaksi Scoreboards
* CRUD admin tidak boleh diasumsikan selalu berupa tabel + form sederhana; ada precedent kuat untuk builder/workspace style
* modular plan harus membedakan area yang sudah punya fondasi nyata versus area yang masih perlu dibangun dari nol

---

## 6. Default Technical Execution Strategy

Urutan eksekusi teknis yang direkomendasikan:

1. backend/data first untuk pondasi domain dan schema
2. extend admin CRUD dan builder foundation yang sudah ada untuk konfigurasi scoreboard
3. participant intake dan access model
4. participant assessment flow
5. scoring engine dan result output
6. follow-up delivery seperti PDF dan email
7. admin review tools
8. QA, hardening, dan stabilization

Layer implementasi yang umum disentuh per modul:

* database
* models/domain
* backend actions/controllers
* validation
* services
* jobs
* admin UI dengan baseline `Admin/Scoreboards`
* participant/public UI
* tests
* seeds/sample data

---

## 7. Module Roadmap Overview

Urutan modular implementation yang direkomendasikan:

1. Project Foundation
2. Auth and Admin Shell
3. Scoreboard Domain Core
4. Participant Intake
5. Unique Access Links
6. Participant Quiz Flow
7. Scoring and Result Engine
8. PDF and Email Delivery
9. Admin Review Submissions
10. Hardening and QA

Semua modul dibuat kecil-menengah agar cocok untuk kerja iteratif dengan AI.

Catatan penting:

* Module 2 dan Module 3 sudah memiliki fondasi UI nyata pada area `Admin/Scoreboards`
* implementasi berikutnya harus mengutamakan extension dan consistency, bukan rebuild pola admin
* area participant/public, delivery, dan review submission masih perlu disejajarkan dengan fondasi tersebut tanpa menyalin builder secara membabi buta

---

## 8. Module 1: Project Foundation

### Module Name

Project Foundation

### Objective

Menyiapkan fondasi project agar domain YogaFX Scoreboard dapat dibangun secara bersih dan standalone.

### Scope

* validasi struktur project dasar
* audit fondasi yang sudah ada agar modul berikutnya tidak membangun ulang area admin yang sudah matang
* setup baseline routing/domain separation admin vs public
* setup environment keys dasar
* setup folder/service structure awal bila diperlukan
* setup styling foundation awal untuk arah public dan admin

### Dependencies

* system overview
* PRD
* design system

### Likely Touched Areas/Files

* routes
* config files
* app service structure
* base layouts
* frontend theme/token entry points
* environment documentation
* existing admin page structure sebagai baseline audit

### Expected Artifacts

* base route structure
* dokumentasi baseline admin shell yang sudah ada
* public shell placeholder
* theme/token baseline
* initial config scaffolding

### AI Prompting Guidance

Gunakan objective sempit seperti:

* “Siapkan base route dan layout terpisah untuk admin dan public scoreboard.”
* “Tambahkan theme foundation awal untuk admin dan public sesuai design system tanpa membangun feature screens.”

### Definition of Done

* project punya baseline struktur untuk public dan admin
* fondasi admin nyata yang sudah ada telah dipetakan dan tidak akan dibangun ulang tanpa alasan
* tidak ada domain LMS yang ikut terbawa
* dasar theme/layout siap dipakai modul berikutnya

### Verification / Test

* route dasar dapat diakses
* shell admin dan public dapat dirender
* tidak ada error konfigurasi dasar

### Non-Goals

* belum membangun ulang CRUD scoreboard yang sudah ada
* belum membangun flow participant
* belum membangun scoring

---

## 9. Module 2: Auth and Admin Shell

### Module Name

Auth and Admin Shell

### Objective

Menyiapkan akses admin dan kerangka dashboard backoffice yang konsisten dengan YogaFX ecosystem.

### Scope

* autentikasi admin dasar
* proteksi route admin
* reuse `AuthenticatedLayout` dan pola header admin yang sudah dipakai Scoreboards
* navigasi dasar untuk module placeholders
* standardisasi shell admin yang mengikuti baseline page Scoreboards

### Dependencies

* Module 1 selesai

### Likely Touched Areas/Files

* auth routes/controllers/actions
* middleware/guards
* admin layout components
* navigation config
* admin UI primitives
* `AuthenticatedLayout`

### Expected Artifacts

* login flow admin
* protected admin area
* shadcn-based dashboard shell
* navigation placeholders untuk scoreboard, participants, submissions
* aturan layout/header yang sejalan dengan Scoreboards pages

### AI Prompting Guidance

* “Bangun admin auth dan shell dashboard tanpa masuk ke CRUD domain.”
* “Fokus hanya ke proteksi route dan layout admin yang reusable.”

### Definition of Done

* admin dapat login dan mengakses area admin
* non-admin/public tidak dapat mengakses route admin
* dashboard shell siap dipakai modul CRUD
* halaman admin baru dapat mengikuti baseline layout yang sama tanpa membuat pola baru

### Verification / Test

* auth flow dasar berjalan
* protected route bekerja
* layout admin konsisten di beberapa halaman placeholder

### Non-Goals

* belum ada CRUD scoreboard lengkap
* belum ada participant/public flow
* belum ada analytics

---

## 10. Module 3: Scoreboard Domain Core

### Module Name

Scoreboard Domain Core

### Objective

Membangun fondasi data dan admin CRUD untuk domain inti scoreboard.

### Scope

* migrations untuk scoreboards, categories, questions, question_options, result_ranges
* models dan relasi inti
* admin CRUD dasar yang sinkron dengan `Index`, `Create`, `Edit`, dan `Builder`
* status lifecycle draft/published/archived
* validasi relasi dan konfigurasi minimum
* pertahankan pola builder workspace tiga panel untuk authoring question flow

### Dependencies

* Module 2 selesai
* ERD finalized enough untuk domain inti

### Likely Touched Areas/Files

* migrations
* models
* request validation
* controllers/actions
* admin screens/forms
* seeds/sample scoreboard data
* `ScoreboardMetaForm`
* `resources/js/Pages/Admin/Scoreboards/*`

### Expected Artifacts

* database schema domain core
* model relationships
* admin create/edit/list flows
* publish readiness validation
* builder authoring flow yang tetap konsisten dengan fondasi UI yang ada

### AI Prompting Guidance

* “Implementasikan atau sesuaikan domain core scoreboard dengan memperluas page Index/Create/Edit/Builder yang sudah ada.”
* “Jangan membuat admin pattern baru jika page Scoreboards yang ada sudah memberi pola yang cukup.”
* “Jangan masuk ke participant, submission, atau scoring engine dulu.”

### Definition of Done

* admin dapat membuat scoreboard draft
* admin dapat mengelola category, question, option, result range
* aturan konteks scoreboard terjaga
* extension pada builder tetap mengikuti navigator kiri, canvas tengah, config panel kanan, dan drawer sekunder

### Verification / Test

* migration berjalan
* relasi model benar
* CRUD dasar dapat dipakai end-to-end
* builder workspace tetap konsisten secara visual dan interaksi
* validasi mencegah data domain yang tidak konsisten

### Non-Goals

* belum ada participant
* belum ada unique URL
* belum ada submission scoring

---

## 11. Module 4: Participant Intake

### Module Name

Participant Intake

### Objective

Membangun intake lead dari jalur internal form dan inbound webhook.

### Scope

* migrations untuk participants
* flow internal lead form
* inbound webhook endpoint dasar
* participant creation atau matching dasar
* penyimpanan identity utama

### Dependencies

* Module 3 selesai

### Likely Touched Areas/Files

* migrations
* participant model
* public controllers/actions
* webhook endpoint handlers
* request validation
* public lead form UI

### Expected Artifacts

* participants table/model
* public lead capture screen
* inbound webhook receiver
* participant creation service

### AI Prompting Guidance

* “Bangun participant intake dari form internal dan webhook, tanpa membangun access link dan quiz flow dulu.”
* “Fokus ke validasi input dan penyimpanan participant.”

### Definition of Done

* participant dapat dibuat dari internal form
* participant dapat dibuat dari webhook valid
* data identity utama tersimpan rapi

### Verification / Test

* validasi form bekerja
* validasi webhook bekerja
* participant tersimpan sesuai sumber input

### Non-Goals

* belum ada unique access link final
* belum ada assessment flow
* belum ada scoring

---

## 12. Module 5: Unique Access Links

### Module Name

Unique Access Links

### Objective

Membangun model akses unik yang menghubungkan participant ke scoreboard.

### Scope

* migrations untuk participant_access_links
* access code generation
* unique URL generation
* lifecycle dasar access link
* integrasi dengan internal form dan webhook outcome

### Dependencies

* Module 4 selesai
* scoreboard published flow sudah tersedia

### Likely Touched Areas/Files

* migrations
* access link model/service
* participant intake services
* URL generation helpers
* public route handlers

### Expected Artifacts

* participant_access_links schema/model
* access code generation service
* public URL resolution flow
* internal form dan webhook menghasilkan URL unik

### AI Prompting Guidance

* “Implementasikan access link unik dan resolusi URL tanpa membangun question flow dulu.”
* “Jaga agar 1 URL = 1 primary submission sebagai assumption MVP, tapi desain tetap future-proof.”

### Definition of Done

* participant yang valid mendapatkan unique access URL
* URL dapat di-resolve ke participant dan scoreboard yang benar
* lifecycle dasar access link tercatat

### Verification / Test

* kode akses unik tidak bentrok
* access URL valid membuka context yang benar
* invalid URL menghasilkan status yang aman

### Non-Goals

* belum ada answering flow
* belum ada result
* belum ada email/PDF

---

## 13. Module 6: Participant Quiz Flow

### Module Name

Participant Quiz Flow

### Objective

Membangun pengalaman participant untuk mengakses scoreboard dan mengirim jawaban.

### Scope

* migrations untuk submissions dan submission_answers
* access gating dari unique URL
* public question screens
* answer capture
* required validation
* submission lifecycle dasar

### Dependencies

* Module 5 selesai
* scoreboard domain core stabil

### Likely Touched Areas/Files

* migrations
* submission models
* public quiz screens/components
* controllers/actions
* validation
* progress UI

### Expected Artifacts

* submissions schema/model
* submission_answers schema/model
* public quiz UI
* answer persistence flow
* completion submit action

### AI Prompting Guidance

* “Bangun participant quiz flow end-to-end dari URL unik sampai jawaban tersimpan, tanpa scoring result dulu.”
* “Fokus pada access state, question rendering, validation, dan submission save.”

### Definition of Done

* participant dapat membuka quiz dari URL unik
* question ditampilkan sesuai scoreboard
* jawaban tersimpan
* submit final menghasilkan submission valid

### Verification / Test

* happy path quiz berjalan
* required validation muncul benar
* submission dan answers tersimpan sesuai scoreboard

### Non-Goals

* belum ada perhitungan score final
* belum ada result page final
* belum ada PDF/email

---

## 14. Module 7: Scoring and Result Engine

### Module Name

Scoring and Result Engine

### Objective

Mengubah submission mentah menjadi hasil score dan result yang bermakna.

### Scope

* submission_category_scores
* scoring service
* overall score calculation
* category score calculation
* result range matching
* result snapshots pada submission
* result page dasar

### Dependencies

* Module 6 selesai
* result ranges dan options scoring sudah stabil

### Likely Touched Areas/Files

* migrations
* scoring services
* result mapping logic
* public result screens
* submission update flows
* tests untuk scoring logic

### Expected Artifacts

* submission_category_scores schema/model
* scoring engine service
* result determination logic
* participant result screen

### AI Prompting Guidance

* “Implementasikan scoring engine dan result flow berdasarkan submission answers yang sudah ada.”
* “Jangan masuk ke PDF/email dulu; fokus pada score, category breakdown, dan result page.”

### Definition of Done

* submission menghasilkan overall score
* category scores tersimpan
* result range dapat ditentukan
* participant dapat melihat result page dasar

### Verification / Test

* test scoring option-to-score benar
* test category aggregation benar
* test result range mapping benar
* result page menampilkan data yang sesuai

### Non-Goals

* belum ada PDF generation
* belum ada email delivery
* belum ada admin review detail

---

## 15. Module 8: PDF and Email Delivery

### Module Name

PDF and Email Delivery

### Objective

Membangun follow-up inti setelah result tersedia.

### Scope

* migrations untuk pdf_reports dan email_logs
* PDF generation flow dasar
* email notification flow dasar
* jobs/queue integration jika digunakan
* delivery status persistence

### Dependencies

* Module 7 selesai

### Likely Touched Areas/Files

* migrations
* jobs
* mail classes/templates
* PDF service/template layer
* delivery status models
* environment/email config integration

### Expected Artifacts

* pdf_reports schema/model
* email_logs schema/model
* PDF generation service
* email sending service/job
* basic email template

### AI Prompting Guidance

* “Bangun PDF dan email follow-up setelah result tersedia, dengan status delivery tersimpan.”
* “Jangan memperluas ke campaign automation atau outbound integration.”

### Definition of Done

* result submission dapat memicu PDF generation
* email follow-up dapat dikirim
* status PDF dan email dapat ditelusuri

### Verification / Test

* test/service check PDF generation
* mail flow berjalan di environment pengembangan
* status gagal/berhasil tercatat

### Non-Goals

* belum ada automation campaign kompleks
* belum ada outbound webhook
* belum ada analytics mendalam

---

## 16. Module 9: Admin Review Submissions

### Module Name

Admin Review Submissions

### Objective

Menyediakan visibilitas admin terhadap participant, answers, scores, result, dan tracking.

### Scope

* admin list/detail submissions
* participant detail yang relevan
* jawaban per question
* category scores dan overall score
* result data
* tracking attribution display
* delivery state display untuk PDF/email
* visual dan interaction pattern harus turunan dari baseline admin Scoreboards

### Dependencies

* Module 8 selesai

### Likely Touched Areas/Files

* admin controllers/actions
* data queries/view models
* admin tables/detail screens
* filters/search
* badges/status display
* shared admin header/card/alert patterns

### Expected Artifacts

* submissions list screen
* submission detail screen
* participant-result-tracking review UI
* status badges untuk result/PDF/email
* layout yang terasa satu family dengan Scoreboards index/edit/builder

### AI Prompting Guidance

* “Bangun admin review submissions dari sisi observability operasional, bukan analytics dashboard lanjutan.”
* “Gunakan pola heading, cards, badges, alerts, dan density yang konsisten dengan page Scoreboards.”
* “Fokus pada read visibility, bukan laporan agregat kompleks.”

### Definition of Done

* admin dapat melihat daftar submissions
* admin dapat membuka detail submission end-to-end
* admin dapat melihat jawaban, score, result, tracking, dan delivery state
* page review terasa konsisten dengan admin Scoreboards sebagai baseline UI

### Verification / Test

* list dan detail data tampil benar
* filters dasar bekerja jika diimplementasikan
* status visual konsisten dengan design system admin
* struktur halaman konsisten dengan fondasi `Admin/Scoreboards`

### Non-Goals

* belum ada advanced analytics dashboard
* belum ada cohort analysis
* belum ada CRM sync

---

## 17. Module 10: Hardening and QA

### Module Name

Hardening and QA

### Objective

Menstabilkan sistem agar MVP production-oriented siap dipakai dengan risiko operasional yang lebih rendah.

### Scope

* edge case validation
* auth and access review
* invalid URL handling
* publish guardrails
* webhook failure cases
* email/PDF failure handling
* test coverage tambahan
* seed/sample data refinement
* UX polish prioritas tinggi
* consistency audit untuk page admin yang mengikuti template Scoreboards

### Dependencies

* Module 1-9 selesai atau cukup stabil

### Likely Touched Areas/Files

* tests
* validations
* guards/middleware
* service error handling
* admin/public polish
* seeds and fixtures

### Expected Artifacts

* expanded tests
* failure handling improvements
* data fixtures/sample scoreboard
* bug fixes
* QA checklist results
* consistency pass untuk admin pages baru versus baseline Scoreboards

### AI Prompting Guidance

* “Fokus pada stabilisasi, edge case, dan verifikasi lintas modul.”
* “Jangan menambah fitur baru kecuali benar-benar diperlukan untuk menutup gap MVP.”

### Definition of Done

* flow inti internal form sampai result berjalan stabil
* flow webhook sampai access URL berjalan stabil
* admin CRUD dan review cukup aman
* kegagalan umum memiliki handling minimum yang jelas

### Verification / Test

* regression checks lintas flow
* invalid path handling
* access control verification
* end-to-end checks untuk jalur internal dan webhook
* admin UI consistency check terhadap pola di `Admin/Scoreboards`

### Non-Goals

* bukan fase fitur baru besar
* bukan redesign visual total
* bukan ekspansi ke multi-tenant atau CRM automation

---

## 18. Recommended Task Granularity with AI

Agar efektif bersama AI, pecah tiap modul menjadi task kecil-menengah seperti:

* satu migration set
* satu model + relation set
* satu admin CRUD screen family
* satu public flow slice
* satu service logic
* satu verification/fix cycle
* satu extension kecil pada builder bila modul memang menyentuh builder

Contoh task yang baik:

* “Implementasikan migration dan model untuk scoreboards, categories, questions, question_options.”
* “Bangun admin screen untuk create/edit scoreboard beserta validasi dasar.”
* “Tambahkan section baru ke builder dengan tetap mengikuti navigator/canvas/config panel pattern yang ada.”
* “Tambahkan public lead form dan simpan participant.”
* “Bangun service scoring dari submission answers ke category scores.”

Contoh task yang terlalu luas:

* “Bangun semua fitur scoreboard sampai selesai.”
* “Buat semua backend dan frontend participant flow.”
* “Refactor total builder admin sambil menambah satu fitur kecil.”

---

## 19. Expected Artifact Types Across Modules

Jenis artefak yang kemungkinan akan muncul sepanjang implementasi:

* migrations
* models
* policies or middleware
* controllers/actions
* request validation
* services
* jobs
* mails
* PDF generators/templates
* admin screens
* participant screens
* reusable UI components
* tests
* seed/sample data
* small documentation updates bila diperlukan
* extension pada shared admin components atau builder substructures bila relevan

---

## 20. Progress Tracking Recommendation

Status progress sederhana yang direkomendasikan:

* `not started`
* `in progress`
* `blocked`
* `needs verification`
* `done`

Cara tracking modul:

* catat modul aktif
* catat task aktif di dalam modul
* catat blockers atau assumptions baru
* catat hasil verifikasi sebelum menandai done

---

## 21. Common Implementation Risks

Risiko yang perlu dijaga selama eksekusi:

* AI melebar ke scope modul lain
* data model berubah tanpa sinkron ke ERD
* admin UI dan public UI tercampur secara visual
* page admin baru tidak mengikuti baseline `Admin/Scoreboards`
* builder pattern yang sudah matang diabaikan lalu diganti pola CRUD generik
* analogi LMS masuk ke nama model, flow, atau UI
* scoring engine dibuat terlalu cepat tanpa submission foundation yang stabil
* PDF/email dibangun sebelum result model matang
* hardening ditunda terlalu lama

---

## 22. Final Summary

Modular Implementation YogaFX Scoreboard harus dijalankan sebagai rangkaian modul kecil-menengah yang disiplin, terverifikasi, dan selalu kembali ke source of truth.

Kunci keberhasilannya:

* satu modul aktif pada satu waktu
* objective task yang sempit dan jelas
* fondasi data dibangun lebih dulu
* admin CRUD dan participant flow dikembangkan bertahap
* fondasi nyata pada `Admin/Scoreboards` diperlakukan sebagai baseline, bukan diabaikan
* scoring, result, PDF, dan email dibangun setelah fondasi submission matang
* setiap modul ditutup dengan verifikasi dan tanpa refactor liar

Dengan pola ini, Codex dapat dipakai bukan hanya untuk “menulis kode”, tetapi untuk membantu membangun YogaFX Scoreboard secara bertahap, aman, dan tetap setia pada filosofi produk yang sudah ditetapkan.
