# YogaFX Scoreboard ERD and Data Architecture
# MVP Production-Oriented Relational Data Design

## 1. Document Purpose

Dokumen ini mendefinisikan rancangan **ERD dan arsitektur data utama** untuk YogaFX Scoreboard MVP.

Tujuan dokumen ini:

* menjadi dasar desain database relasional
* menjadi referensi untuk migration planning
* menjaga konsistensi antara domain model, PRD, dan user flow
* memastikan data user, assessment, scoring, result, PDF, email, dan tracking tersimpan dengan rapi

Dokumen ini bersifat **hybrid**:

* domain-first agar tetap setia pada product model
* cukup implementation-ready agar dapat diterjemahkan ke schema database

Dokumen ini dibuat untuk repo baru yang **standalone**, bukan LMS, dan harus mengikuti filosofi:

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

---

## 2. Data Architecture Overview

Arsitektur data YogaFX Scoreboard MVP dibagi ke beberapa lapisan domain:

* admin and access control
* scoreboard configuration
* participant identity and access
* assessment execution
* scoring and result output
* follow-up delivery
* tracking and attribution

Prinsip arsitektur data:

* `participant` adalah master person record lintas scoreboard
* `scoreboard` adalah root entity untuk seluruh konfigurasi assessment
* `participant_access_links` adalah model akses unik yang menjembatani participant dengan scoreboard
* `submissions` adalah record attempt utama untuk pengerjaan scoreboard
* `submission_answers` menyimpan jawaban mentah yang harus dipertahankan
* `submission_category_scores` menyimpan hasil turunan penting yang perlu mudah di-query
* `pdf_reports` dan `email_logs` menyimpan status delivery/follow-up sebagai bagian inti produk
* tracking attribution untuk MVP terutama ditempel di `submissions`, dengan ruang berkembang ke model yang lebih terpisah bila dibutuhkan

---

## 3. ERD Summary

Relasi inti sistem secara konseptual:

```text
users/admins
    ↓
scoreboards
    ↓
categories
    ↓
questions
    ↓
question_options

scoreboards
    ↓
result_ranges

participants
    ↓
participant_access_links
    ↓
submissions
    ↓
submission_answers

submissions
    ↓
submission_category_scores

submissions
    ↓
pdf_reports

submissions
    ↓
email_logs
```

Relasi lintas domain penting:

* satu `scoreboard` memiliki banyak `categories`
* satu `scoreboard` memiliki banyak `questions`
* satu `question` dapat memiliki banyak `question_options`
* satu `scoreboard` memiliki banyak `result_ranges`
* satu `participant` dapat memiliki banyak `participant_access_links`
* satu `participant_access_link` terhubung ke satu `scoreboard`
* satu `participant_access_link` memiliki satu `primary submission` pada MVP, tetapi model tetap mengizinkan banyak `submissions` ke depan
* satu `submission` memiliki banyak `submission_answers`
* satu `submission` memiliki banyak `submission_category_scores`
* satu `submission` dapat memiliki banyak `email_logs`
* satu `submission` dapat memiliki satu atau lebih `pdf_reports`

---

## 4. Core Entity Design

### 4.1 `users`

Tujuan:

* menyimpan akun internal untuk admin management

Catatan:

* pada MVP, role utama cukup `admin`
* nama tabel dapat tetap `users` agar fleksibel untuk masa depan

Core fields:

* `id`
* `name`
* `email`
* `password_hash`
* `role`
* `status`
* `last_login_at`
* `created_at`
* `updated_at`

Candidate constraints:

* `email` unique
* `role` minimal mendukung nilai `admin`
* `status` dibatasi ke status internal yang valid

Lifecycle/status candidates:

* `active`
* `inactive`

Relationships:

* satu `user` dapat membuat banyak `scoreboards`
* satu `user` dapat memperbarui banyak konfigurasi scoreboard

---

### 4.2 `scoreboards`

Tujuan:

* root entity untuk satu assessment publik

Core fields:

* `id`
* `title`
* `slug`
* `description`
* `status`
* `created_by_user_id`
* `published_at`
* `archived_at`
* `created_at`
* `updated_at`

Candidate constraints:

* `slug` unique
* `status` wajib valid
* `created_by_user_id` wajib mereferensikan `users.id`

Lifecycle/status candidates:

* `draft`
* `published`
* `archived`

Relationships:

* satu `scoreboard` memiliki banyak `categories`
* satu `scoreboard` memiliki banyak `questions`
* satu `scoreboard` memiliki banyak `result_ranges`
* satu `scoreboard` memiliki banyak `participant_access_links`
* satu `scoreboard` memiliki banyak `submissions`

Notes:

* `scoreboard` harus selalu menjadi root untuk domain assessment
* semua entitas konten assessment harus mempertahankan `scoreboard_id`

---

### 4.3 `categories`

Tujuan:

* mengelompokkan question dan menjadi basis category scoring

Core fields:

* `id`
* `scoreboard_id`
* `name`
* `description`
* `sort_order`
* `status`
* `created_at`
* `updated_at`

Candidate constraints:

* `scoreboard_id` wajib
* kombinasi `scoreboard_id + name` sebaiknya unique
* `sort_order` integer non-negative

Lifecycle/status candidates:

* `active`
* `inactive`

Relationships:

* satu `category` milik satu `scoreboard`
* satu `category` dapat memiliki banyak `questions`
* satu `category` dapat muncul di banyak `submission_category_scores`

Notes:

* `category` sebaiknya tidak dihapus keras jika sudah dipakai submission historis

---

### 4.4 `questions`

Tujuan:

* menyimpan pertanyaan untuk scoreboard

Core fields:

* `id`
* `scoreboard_id`
* `category_id`
* `question_text`
* `question_type`
* `sort_order`
* `is_required`
* `status`
* `created_at`
* `updated_at`

Candidate constraints:

* `scoreboard_id` wajib
* `category_id` nullable hanya jika nanti produk mengizinkan question tanpa category; untuk MVP lebih aman dibuat required
* `question_type` wajib valid
* `sort_order` integer non-negative

Lifecycle/status candidates:

* `draft`
* `active`
* `inactive`

Relationships:

* satu `question` milik satu `scoreboard`
* satu `question` milik satu `category`
* satu `question` memiliki banyak `question_options`
* satu `question` memiliki banyak `submission_answers`

Notes:

* `scoreboard_id` tetap disimpan walau sudah ada `category_id` agar integritas konteks lebih kuat dan query lebih mudah

---

### 4.5 `question_options`

Tujuan:

* menyimpan pilihan jawaban dan nilai score per pilihan

Core fields:

* `id`
* `question_id`
* `label`
* `score_value`
* `sort_order`
* `status`
* `created_at`
* `updated_at`

Candidate constraints:

* `question_id` wajib
* `score_value` numeric dan required
* `sort_order` integer non-negative

Lifecycle/status candidates:

* `active`
* `inactive`

Relationships:

* satu `question_option` milik satu `question`
* satu `question_option` dapat dipakai oleh banyak `submission_answers`

Notes:

* model dasar scoring MVP adalah `option -> score_value`

---

### 4.6 `result_ranges`

Tujuan:

* memetakan score akhir menjadi result

Core fields:

* `id`
* `scoreboard_id`
* `title`
* `description`
* `recommendation`
* `min_score`
* `max_score`
* `status`
* `sort_order`
* `created_at`
* `updated_at`

Candidate constraints:

* `scoreboard_id` wajib
* `min_score <= max_score`
* result range dalam satu scoreboard tidak boleh overlap
* rentang score dalam scoreboard sebaiknya tidak memiliki gap yang tak diinginkan

Lifecycle/status candidates:

* `active`
* `inactive`

Relationships:

* satu `result_range` milik satu `scoreboard`
* satu `result_range` dapat direferensikan oleh banyak `submissions`
* satu `result_range` dapat menjadi basis template logic untuk `pdf_reports`

Notes:

* untuk MVP, `submissions` sebaiknya menyimpan referensi `result_range_id` sekaligus salinan hasil turunan penting

---

### 4.7 `participants`

Tujuan:

* master person record lintas scoreboard

Core fields:

* `id`
* `first_name`
* `last_name`
* `email`
* `whatsapp`
* `country`
* `source_type`
* `first_seen_at`
* `last_seen_at`
* `created_at`
* `updated_at`

Candidate constraints:

* `email` nullable jika bisnis mengizinkan, namun untuk MVP internal lead form email sebaiknya required
* `source_type` valid sesuai sumber utama
* deduplication tidak perlu kompleks pada MVP, tetapi desain harus mengizinkan pencocokan dasar di kemudian hari

Lifecycle/status candidates:

* `active`
* `suppressed`

Relationships:

* satu `participant` dapat memiliki banyak `participant_access_links`
* satu `participant` dapat memiliki banyak `submissions`

Notes:

* `participant` adalah canonical identity record
* tracking attribution tidak perlu seluruhnya ditempel di sini karena satu participant dapat datang dari konteks kampanye berbeda pada submission berbeda

---

### 4.8 `participant_access_links`

Tujuan:

* menyimpan unique access URL atau token yang menghubungkan participant dengan scoreboard

Core fields:

* `id`
* `participant_id`
* `scoreboard_id`
* `access_code`
* `access_url`
* `status`
* `issued_at`
* `expires_at`
* `last_accessed_at`
* `completed_at`
* `created_at`
* `updated_at`

Candidate constraints:

* `participant_id` wajib
* `scoreboard_id` wajib
* `access_code` unique
* `access_url` dapat diturunkan dari kode, tetapi tetap boleh disimpan untuk kenyamanan operasional
* pada MVP, dapat ada candidate constraint logis bahwa satu participant hanya memiliki satu link aktif per scoreboard

Lifecycle/status candidates:

* `active`
* `completed`
* `expired`
* `revoked`

Relationships:

* satu `participant_access_link` milik satu `participant`
* satu `participant_access_link` milik satu `scoreboard`
* satu `participant_access_link` dapat memiliki banyak `submissions`

Notes:

* meskipun MVP mengasumsikan satu link satu primary submission, desain relasi tetap `one-to-many` agar future-proof

---

### 4.9 `submissions`

Tujuan:

* record attempt utama participant terhadap scoreboard

Core fields:

* `id`
* `participant_id`
* `scoreboard_id`
* `participant_access_link_id`
* `result_range_id`
* `status`
* `started_at`
* `submitted_at`
* `finished_at`
* `time_taken_seconds`
* `overall_score`
* `overall_percent`
* `result_title_snapshot`
* `result_description_snapshot`
* `result_url`
* `result_pdf_url`
* `utm_source`
* `utm_campaign`
* `utm_medium`
* `utm_term`
* `utm_content`
* `referrer`
* `landing_page`
* `ip_address_country`
* `source_channel`
* `created_at`
* `updated_at`

Candidate constraints:

* `participant_id` wajib
* `scoreboard_id` wajib
* `participant_access_link_id` wajib untuk flow yang berbasis unique URL
* `status` wajib valid
* `overall_percent` berada pada rentang yang valid
* untuk MVP, dapat diberi logical uniqueness pada submission primary per `participant_access_link_id`

Lifecycle/status candidates:

* `pending`
* `in_progress`
* `submitted`
* `scored`
* `completed`
* `failed`

Relationships:

* satu `submission` milik satu `participant`
* satu `submission` milik satu `scoreboard`
* satu `submission` milik satu `participant_access_link`
* satu `submission` dapat mereferensikan satu `result_range`
* satu `submission` memiliki banyak `submission_answers`
* satu `submission` memiliki banyak `submission_category_scores`
* satu `submission` memiliki banyak `email_logs`
* satu `submission` memiliki banyak `pdf_reports`

Notes:

* `submissions` adalah canonical record untuk tracking attribution pada MVP
* menyimpan result snapshot membantu menjaga integritas historis bila konfigurasi result berubah di masa depan

---

### 4.10 `submission_answers`

Tujuan:

* menyimpan jawaban mentah participant per question

Core fields:

* `id`
* `submission_id`
* `question_id`
* `question_option_id`
* `category_id`
* `question_text_snapshot`
* `option_label_snapshot`
* `score_value`
* `answered_at`
* `created_at`
* `updated_at`

Candidate constraints:

* `submission_id` wajib
* `question_id` wajib
* untuk MVP pilihan tunggal, kombinasi `submission_id + question_id` sebaiknya unique
* `score_value` wajib bila jawaban valid telah dipilih

Relationships:

* satu `submission_answer` milik satu `submission`
* satu `submission_answer` mereferensikan satu `question`
* satu `submission_answer` dapat mereferensikan satu `question_option`
* satu `submission_answer` dapat mereferensikan satu `category`

Notes:

* snapshot teks disarankan agar histori tetap terbaca bila konten question atau option berubah di masa depan
* `submission_answers` adalah canonical raw answer record

---

### 4.11 `submission_category_scores`

Tujuan:

* menyimpan hasil agregasi score per category untuk satu submission

Core fields:

* `id`
* `submission_id`
* `category_id`
* `category_name_snapshot`
* `actual_score`
* `max_score`
* `percentage_score`
* `created_at`
* `updated_at`

Candidate constraints:

* `submission_id` wajib
* `category_id` wajib
* kombinasi `submission_id + category_id` unique

Relationships:

* satu `submission_category_score` milik satu `submission`
* satu `submission_category_score` mereferensikan satu `category`

Notes:

* ini adalah derived-but-persisted data
* disimpan karena sangat penting untuk result view, admin review, dan reporting dasar

---

### 4.12 `pdf_reports`

Tujuan:

* menyimpan status dan artefak PDF report untuk submission

Core fields:

* `id`
* `submission_id`
* `scoreboard_id`
* `result_range_id`
* `template_key`
* `file_path`
* `file_url`
* `status`
* `generated_at`
* `failed_at`
* `failure_reason`
* `created_at`
* `updated_at`

Candidate constraints:

* `submission_id` wajib
* `status` wajib valid

Lifecycle/status candidates:

* `pending`
* `generated`
* `failed`

Relationships:

* satu `pdf_report` milik satu `submission`
* satu `pdf_report` dapat terhubung ke satu `scoreboard`
* satu `pdf_report` dapat terhubung ke satu `result_range`

Notes:

* walau MVP mungkin hanya memerlukan satu PDF utama per submission, relasi sebaiknya tetap fleksibel untuk multiple generated reports di masa depan

---

### 4.13 `email_logs`

Tujuan:

* menyimpan status pengiriman email follow-up

Core fields:

* `id`
* `submission_id`
* `participant_id`
* `email_to`
* `email_type`
* `subject`
* `status`
* `provider_message_id`
* `sent_at`
* `failed_at`
* `failure_reason`
* `created_at`
* `updated_at`

Candidate constraints:

* `submission_id` wajib
* `participant_id` wajib
* `email_to` wajib pada saat pengiriman dicoba
* `status` wajib valid

Lifecycle/status candidates:

* `pending`
* `sent`
* `failed`

Relationships:

* satu `email_log` milik satu `submission`
* satu `email_log` milik satu `participant`

Notes:

* email follow-up adalah bagian inti produk, jadi state delivery perlu tersimpan, bukan hanya event sementara

---

## 5. Recommended Additional Entity

### 5.1 Recommendation: `scoreboard_public_entries`

Tujuan:

* memisahkan entry configuration publik dari scoreboard inti jika nanti dibutuhkan banyak jalur masuk per scoreboard

Mengapa mungkin penting:

* satu scoreboard di masa depan bisa memiliki beberapa entry page atau source mapping
* memudahkan pengelolaan per landing source tanpa mengubah `scoreboards`

Untuk MVP:

* belum wajib
* dapat ditunda sampai kebutuhan entry variants benar-benar muncul

---

## 6. Relationship Notes and Cardinality

Relasi yang paling penting:

* `users (1) -> (n) scoreboards`
* `scoreboards (1) -> (n) categories`
* `scoreboards (1) -> (n) questions`
* `categories (1) -> (n) questions`
* `questions (1) -> (n) question_options`
* `scoreboards (1) -> (n) result_ranges`
* `participants (1) -> (n) participant_access_links`
* `scoreboards (1) -> (n) participant_access_links`
* `participant_access_links (1) -> (n) submissions`
* `participants (1) -> (n) submissions`
* `scoreboards (1) -> (n) submissions`
* `submissions (1) -> (n) submission_answers`
* `submissions (1) -> (n) submission_category_scores`
* `submissions (1) -> (n) email_logs`
* `submissions (1) -> (n) pdf_reports`

Important implementation note:

* meskipun beberapa relasi secara bisnis terlihat `one-to-one` pada MVP, desain database sebaiknya tidak terlalu sempit agar phase berikutnya tidak memaksa refactor besar

---

## 7. Data Ownership and Storage Decisions

### 7.1 Canonical Data

Data canonical utama:

* identity participant disimpan di `participants`
* definisi scoreboard disimpan di `scoreboards`
* definisi category disimpan di `categories`
* definisi question disimpan di `questions`
* definisi option dan score disimpan di `question_options`
* definisi result mapping disimpan di `result_ranges`
* access model unik disimpan di `participant_access_links`
* attempt utama disimpan di `submissions`
* jawaban mentah disimpan di `submission_answers`

### 7.2 Derived but Persisted Data

Data turunan yang tetap layak disimpan:

* `overall_score` dan `overall_percent` di `submissions`
* `submission_category_scores`
* `result_title_snapshot` dan `result_description_snapshot` di `submissions`
* `result_pdf_url` di `submissions` sebagai shortcut operasional

Mengapa disimpan:

* mempercepat query result
* menjaga histori saat konfigurasi berubah
* memudahkan admin review
* mengurangi perhitungan ulang untuk output yang sering dipakai

### 7.3 Permanently Stored Data

Data yang sebaiknya disimpan permanen:

* participant identity
* participant access history penting
* submissions
* raw answers
* category scores
* tracking attribution yang melekat pada submission
* PDF generation state
* email delivery state

Alasan:

* kebutuhan audit
* segmentation
* reporting
* future analytics
* operasional follow-up

### 7.4 Content Snapshots for Historical Integrity

Untuk MVP tanpa versioning formal, histori tetap perlu dilindungi dengan snapshot field pada data hasil.

Snapshot yang direkomendasikan:

* `question_text_snapshot` di `submission_answers`
* `option_label_snapshot` di `submission_answers`
* `category_name_snapshot` di `submission_category_scores`
* `result_title_snapshot` di `submissions`
* `result_description_snapshot` di `submissions`

Alasan:

* jika admin mengubah konten setelah submission lama terjadi, histori lama tetap dapat dibaca sesuai konteks saat submit

### 7.5 Attribution Storage Decision

Keputusan MVP yang direkomendasikan:

* simpan attribution utama langsung di `submissions`

Field terkait:

* `utm_source`
* `utm_campaign`
* `utm_medium`
* `utm_term`
* `utm_content`
* `referrer`
* `landing_page`
* `ip_address_country`
* `source_channel`

Alasan:

* satu participant dapat mengikuti beberapa scoreboard atau beberapa konteks acquisition
* attribution lebih dekat ke event submission daripada identity person permanen
* query funnel MVP lebih sederhana

Scalability recommendation:

* jika nanti diperlukan histori multi-touch attribution atau lead-event timeline yang lebih kaya, sistem dapat menambah entity terpisah seperti `participant_attribution_events` tanpa mengubah fondasi inti

---

## 8. Candidate Constraint Recommendations

Constraint yang penting untuk dipertimbangkan sejak awal:

* `scoreboards.slug` unique
* `participant_access_links.access_code` unique
* `categories.scoreboard_id + name` unique
* `submission_category_scores.submission_id + category_id` unique
* `submission_answers.submission_id + question_id` unique untuk MVP single-answer
* `result_ranges` tidak boleh overlap dalam scoreboard yang sama
* `questions.category_id` harus berasal dari scoreboard yang sama dengan `questions.scoreboard_id`
* `participant_access_links.scoreboard_id` harus konsisten dengan `submissions.scoreboard_id`
* `submissions.participant_id` harus konsisten dengan `participant_access_links.participant_id`

Business-level constraints yang mungkin tidak seluruhnya cukup hanya dengan database constraint:

* satu participant hanya punya satu access link aktif per scoreboard pada MVP
* satu access link hanya punya satu primary submission pada MVP
* scoreboard tidak boleh dipublish jika konfigurasi inti belum lengkap

---

## 9. Lifecycle and Status Summary

### 9.1 `scoreboards.status`

* `draft`
* `published`
* `archived`

### 9.2 `categories.status`

* `active`
* `inactive`

### 9.3 `questions.status`

* `draft`
* `active`
* `inactive`

### 9.4 `question_options.status`

* `active`
* `inactive`

### 9.5 `result_ranges.status`

* `active`
* `inactive`

### 9.6 `participants.status`

Recommendation:

* tambahkan `status` meskipun minimal

Candidate values:

* `active`
* `suppressed`

### 9.7 `participant_access_links.status`

* `active`
* `completed`
* `expired`
* `revoked`

### 9.8 `submissions.status`

* `pending`
* `in_progress`
* `submitted`
* `scored`
* `completed`
* `failed`

### 9.9 `pdf_reports.status`

* `pending`
* `generated`
* `failed`

### 9.10 `email_logs.status`

* `pending`
* `sent`
* `failed`

---

## 10. Historical Integrity Recommendations

Karena MVP belum memakai versioning formal, integritas data historis harus dijaga dengan pendekatan berikut:

* jangan mengandalkan join ke konten live saja untuk membaca submission lama
* simpan snapshot teks penting pada answer, category score, dan result
* hindari hard delete untuk category, question, option, atau result range yang sudah pernah dipakai submission
* jika konten diubah setelah publish, pertimbangkan pembatasan atau audit trail meskipun belum ada versioning penuh

Ini penting agar:

* admin dapat meninjau submission lama dengan konteks yang benar
* PDF dan email historis tetap dapat dijelaskan
* analisis performa tidak rusak karena perubahan konfigurasi kemudian hari

---

## 11. Open Points

* apakah `participants.status` benar-benar diperlukan di MVP pertama, atau cukup ditambahkan bila mulai ada suppression use case?
* apakah `result_url` dan `result_pdf_url` perlu disimpan langsung di `submissions` atau cukup diturunkan dari relasi?
* apakah `access_url` perlu disimpan penuh atau cukup menyimpan `access_code` dan membentuk URL di application layer?
* apakah `participant matching` dasar akan memakai email, WhatsApp, atau kombinasi keduanya?
* apakah `pdf_reports` perlu satu row per generation attempt atau hanya menyimpan state akhir?
* apakah `email_logs` perlu menyimpan salinan body email atau cukup metadata pengiriman?

---

## 12. Final Summary

ERD YogaFX Scoreboard MVP harus dibangun di atas prinsip berikut:

* `scoreboards` adalah root entity assessment
* `participants` adalah master person record
* `participant_access_links` adalah jembatan akses unik ke scoreboard
* `submissions` adalah pusat lifecycle pengerjaan, scoring, result, dan tracking
* `submission_answers` menyimpan raw answers yang wajib dipertahankan
* `submission_category_scores` menyimpan derived score penting untuk result dan review
* `pdf_reports` dan `email_logs` adalah bagian inti arsitektur data follow-up
* tracking attribution untuk MVP paling masuk akal disimpan di level submission

Desain ini menjaga sistem tetap:

* standalone
* multi-scoreboard
* admin-managed
* scoring-driven
* result-oriented
* attribution-aware
* siap scale ke fase berikutnya

Tanpa menggeser arsitektur ke pola LMS, student progress, module, lesson, atau certificate system.
