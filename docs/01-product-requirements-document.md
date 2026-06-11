# YogaFX Scoreboard Product Requirements Document
# MVP Production-Oriented PRD

## 1. Document Purpose

Dokumen ini mendefinisikan kebutuhan produk untuk **YogaFX Scoreboard MVP** yang production-oriented, dengan arsitektur dan boundary yang siap dikembangkan ke fase berikutnya tanpa mengubah filosofi inti sistem.

PRD ini ditujukan untuk:

* founder
* developer team
* designer/developer

Dokumen ini menjadi penghubung antara konteks bisnis tingkat tinggi pada system overview dengan implementasi produk, desain flow, dan keputusan teknis tingkat dasar yang relevan untuk MVP.

---

## 2. Product Context

YogaFX Scoreboard adalah platform assessment publik yang berdiri sendiri untuk:

* menangkap lead
* mengkualifikasi participant
* menghitung score
* melakukan segmentasi berbasis hasil
* menampilkan result
* menjalankan follow-up melalui email

Sistem ini **bukan LMS** dan tidak boleh dibentuk mengikuti pola arsitektur LMS.

Filosofi sistem yang harus menjadi acuan seluruh keputusan produk:

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

Implikasi praktis:

* pusat sistem adalah lead, participant, score, result, dan follow-up
* assessment bukan bagian dari learning journey
* scoreboard bukan turunan dari student progress system
* result page, PDF, dan email adalah output inti produk, bukan fitur tambahan

---

## 3. Product Vision for MVP

MVP YogaFX Scoreboard harus memungkinkan tim YogaFX meluncurkan satu atau lebih scoreboard publik yang dapat:

* menerima lead dari form internal maupun webhook inbound
* memberikan akses unik per participant
* menjalankan assessment berbasis score
* menghitung overall score dan category score
* menentukan result berdasarkan result range
* menampilkan result page
* menghasilkan PDF report
* mengirim email follow-up
* menyimpan jawaban dan data tracking untuk kebutuhan admin dan pengembangan berikutnya

MVP ini harus cukup stabil untuk dipakai secara nyata, namun tetap dibangun dengan fondasi yang siap berkembang ke fase lanjutan seperti analytics lebih dalam, automation, dan integrasi eksternal.

---

## 4. Goals

### 4.1 Business Goals

* meningkatkan kemampuan lead qualification
* mendukung audience segmentation berbasis assessment
* membantu marketing funnel dengan data result dan attribution
* memberi rekomendasi hasil yang relevan setelah quiz selesai
* memungkinkan follow-up email dari aplikasi Scoreboard sendiri

### 4.2 Product Goals

* memungkinkan admin membuat dan mengelola banyak scoreboard
* memastikan participant dapat mengakses assessment melalui unique access URL
* memastikan setiap submission menghasilkan data yang lengkap dan terstruktur
* menyediakan result output yang berguna untuk participant dan admin
* menjaga boundary sistem tetap fokus pada assessment funnel, bukan LMS

### 4.3 Success Indicators

Untuk MVP, indikator keberhasilan utama dapat dinilai dari:

* scoreboard dapat dipublish dan diakses publik
* lead dari internal form dan webhook dapat menjadi participant valid
* participant dapat menyelesaikan assessment tanpa hambatan mayor
* sistem menghasilkan score, result, PDF, dan email secara konsisten
* admin dapat meninjau submission, jawaban, score, result, dan tracking data

---

## 5. Users and Roles

### 5.1 Admin

Admin adalah pengguna internal yang mengelola konten scoreboard dan meninjau data hasil participant.

Kebutuhan utama Admin:

* membuat dan mengelola scoreboard
* mengelola category, question, option, dan result range
* mempublikasikan scoreboard
* melihat participant, submissions, answers, scores, result, dan tracking data

### 5.2 Participant

Participant adalah orang yang menerima akses ke scoreboard dan mengerjakan assessment.

Kebutuhan utama Participant:

* mengisi lead form jika datang dari jalur internal
* mengakses scoreboard melalui URL unik
* menjawab assessment dengan jelas
* menerima result yang mudah dipahami
* menerima email follow-up dan PDF report jika tersedia

---

## 6. Scope

### 6.1 In Scope

* internal lead form
* inbound webhook
* unique access URL generation
* multi-scoreboard support
* admin panel untuk scoreboard management
* categories
* questions
* options dan scoring
* result ranges
* participant records
* submissions
* submission answers
* result page
* PDF report
* email notification
* tracking attribution fields
* admin view untuk submissions, scores, answers, dan tracking

### 6.2 Out of Scope

* CRM outbound integration
* advanced analytics dashboard
* outbound webhooks
* campaign automation kompleks
* multi-tenant support
* public API
* full landing page builder
* LMS features
* student learning flow
* modules
* lessons
* certificates
* payment
* subscription

---

## 7. Core Product Principles

### 7.1 Standalone Product Principle

YogaFX Scoreboard adalah repo dan produk yang berdiri sendiri. Struktur produk, domain model, dan terminology tidak boleh bergantung pada LMS.

### 7.2 Multi-Scoreboard by Design

Sistem tidak boleh diasumsikan hanya memiliki satu quiz. Semua entitas inti harus mempertahankan konteks scoreboard.

### 7.3 Unique Participant Access

Assessment harus diakses melalui URL unik per participant agar tracking, ownership submission, dan flow internal maupun webhook dapat bertemu pada model yang sama.

### 7.4 Full Submission Traceability

Setiap submission harus menyimpan seluruh jawaban dan hasil scoring yang dibutuhkan untuk reporting, segmentation, dan audit data.

### 7.5 Admin-Managed Content

Konten scoreboard tidak boleh hardcoded sebagai konfigurasi developer-only. Admin adalah pusat pengelolaan konten assessment.

### 7.6 Result-Driven Output

Sistem harus menghasilkan output bermakna setelah assessment selesai, termasuk result page, PDF report, dan email follow-up.

---

## 8. Core Flows

### 8.1 Flow A: Internal Lead Form to Result

```text
Visitor
↓
Internal Lead Form
↓
Participant Created
↓
Unique Access Code Generated
↓
Unique Access URL Generated
↓
Participant Opens Scoreboard
↓
Participant Answers Questions
↓
Submission Scored
↓
Result Determined
↓
Result Page Displayed
↓
PDF Generated
↓
Email Sent
```

### 8.2 Flow B: Inbound Webhook to Result

```text
External Website / CRM / Landing Page
↓
Webhook Payload Received
↓
Participant Created
↓
Unique Access Code Generated
↓
Unique Access URL Generated
↓
Participant Opens Scoreboard
↓
Participant Answers Questions
↓
Submission Scored
↓
Result Determined
↓
Result Page Displayed
↓
PDF Generated
↓
Email Sent
```

### 8.3 Flow C: Admin Scoreboard Management

```text
Admin Creates Scoreboard
↓
Admin Creates Categories
↓
Admin Creates Questions
↓
Admin Configures Options and Scores
↓
Admin Configures Result Ranges
↓
Admin Reviews Configuration
↓
Admin Publishes Scoreboard
```

---

## 9. Functional Requirements

### 9.1 Scoreboard Management

Admin harus dapat:

* membuat scoreboard baru
* mengedit scoreboard
* mengubah status scoreboard
* mempublish scoreboard
* mengarsipkan scoreboard

Setiap scoreboard minimal memiliki:

* title
* slug
* description
* status

High-level acceptance criteria:

* admin dapat membuat lebih dari satu scoreboard
* setiap scoreboard memiliki identitas dan konteks data sendiri
* scoreboard yang belum publish tidak dapat diakses sebagai assessment publik
* scoreboard yang diarsipkan tidak digunakan untuk submission baru

### 9.2 Category Management

Admin harus dapat:

* membuat category per scoreboard
* mengedit category
* menghapus category bila belum menimbulkan konflik bisnis yang tidak diizinkan

High-level acceptance criteria:

* category selalu berada di bawah scoreboard tertentu
* category dapat dipakai untuk pengelompokan question dan perhitungan score
* admin dapat melihat urutan dan daftar category dalam scoreboard

### 9.3 Question Management

Admin harus dapat:

* membuat question
* mengedit question
* menentukan question type
* menentukan sort order
* menandai question sebagai required atau optional
* mengaitkan question ke category

High-level acceptance criteria:

* question selalu terhubung ke scoreboard
* question dapat dikaitkan ke category yang valid dalam scoreboard yang sama
* urutan question dapat diatur admin
* participant hanya melihat question aktif yang sesuai dengan scoreboard

### 9.4 Option and Scoring Configuration

Admin harus dapat:

* menambahkan option pada question
* mengubah label option
* menetapkan score pada tiap option

High-level acceptance criteria:

* setiap option menyimpan nilai score yang dipakai engine
* question berbasis pilihan tidak dapat berfungsi tanpa option yang valid
* scoring dihitung dari option yang dipilih participant

### 9.5 Result Range Management

Admin harus dapat:

* membuat result range
* menetapkan batas score atau threshold
* menentukan result title
* menentukan result description
* menentukan recommendation dasar

High-level acceptance criteria:

* setiap scoreboard dapat memiliki banyak result range
* result range tidak boleh ambigu untuk rentang score yang sama
* submission yang selesai harus dapat dipetakan ke satu result range yang valid

### 9.6 Internal Lead Form

Sistem harus menyediakan lead form internal untuk visitor publik.

Minimal field:

* first_name
* last_name
* email
* whatsapp

Field tambahan dapat berkembang kemudian, namun MVP minimal harus menangkap data dasar identitas lead.

High-level acceptance criteria:

* visitor dapat mengirim lead form tanpa login
* submit form yang valid membuat participant baru
* setelah participant dibuat, sistem menghasilkan unique access URL
* participant dapat diarahkan ke flow berikutnya melalui URL unik tersebut

### 9.7 Inbound Webhook

Sistem harus mendukung pembuatan participant dari sumber eksternal melalui webhook inbound.

Minimal data yang didukung:

* first_name
* last_name
* email
* whatsapp
* country
* utm_source
* utm_campaign
* utm_medium
* utm_term
* utm_content
* referrer
* landing_page

High-level acceptance criteria:

* sistem dapat menerima payload inbound untuk scoreboard yang relevan
* payload valid menghasilkan participant dan unique access URL
* data tracking dari payload tersimpan dan dapat ditinjau admin
* jalur webhook dan jalur internal berujung pada model participant access yang sama

### 9.8 Participant and Unique Access URL

Setelah participant tercipta, sistem harus menyediakan akses unik ke scoreboard.

High-level acceptance criteria:

* setiap participant memiliki identifier akses yang unik
* URL tidak mudah ditebak
* URL dapat digunakan untuk mengaitkan participant dengan scoreboard yang benar
* model akses mendukung participant dari internal form maupun webhook

### 9.9 Submission and Submission Answers

Sistem harus membuat submission saat participant memulai atau mengerjakan scoreboard sesuai desain implementasi yang dipilih.

Sistem harus menyimpan:

* submission metadata
* seluruh jawaban participant
* score per jawaban
* overall score
* category score
* result outcome

High-level acceptance criteria:

* setiap submission terikat ke participant dan scoreboard
* seluruh jawaban yang dipilih participant tersimpan
* submission yang selesai memiliki hasil scoring yang lengkap
* data submission dapat dipakai untuk admin review dan future analytics

### 9.10 Scoring Engine

Saat submission selesai, sistem harus:

* menghitung score per question
* menghitung score per category
* menghitung overall score
* menghitung persentase yang relevan
* menentukan result range

High-level acceptance criteria:

* score dihitung secara konsisten dari konfigurasi option
* category score tersedia minimal dalam actual score dan percentage
* overall score tersedia minimal dalam actual score dan percentage
* setiap submission selesai mendapat satu hasil akhir yang valid

### 9.11 Result Page

Result page harus menampilkan hasil assessment secara bermakna, bukan hanya status selesai.

Konten yang minimal harus dapat didukung:

* overall score
* category scores
* result title
* result description
* recommendation
* CTA dasar bila diperlukan

High-level acceptance criteria:

* participant melihat result yang sesuai dengan hasil submission
* result page dapat disesuaikan per scoreboard
* result page menggunakan data dari scoring dan result range yang tersimpan

### 9.12 PDF Report

Sistem harus dapat menghasilkan PDF report untuk result submission.

Prinsip:

* PDF adalah artefak terpisah dari result page
* PDF menggunakan template report tersendiri

High-level acceptance criteria:

* submission yang memenuhi flow selesai dapat memiliki PDF report
* PDF tidak harus identik secara layout dengan result page
* sistem menyimpan referensi report yang dihasilkan untuk akses atau distribusi lanjutan

### 9.13 Email Notification

Sistem harus mengirim email setelah participant menyelesaikan scoreboard.

Konten email dapat mencakup:

* ringkasan result
* ringkasan score
* link result
* PDF report
* follow-up CTA

High-level acceptance criteria:

* email dikirim oleh aplikasi Scoreboard sendiri
* email dipicu setelah result tersedia
* jika flow PDF diwajibkan dalam implementasi, email dikirim setelah PDF siap
* status pengiriman perlu dapat ditelusuri pada level operasional dasar

### 9.14 Admin Submission Review

Admin harus dapat meninjau data hasil participant.

Minimal data yang dapat dilihat admin:

* participant identity
* scoreboard
* submission status
* all answers
* overall score
* category scores
* result
* tracking attribution data

High-level acceptance criteria:

* admin dapat melihat submission secara terhubung end-to-end
* admin tidak hanya melihat total score, tetapi juga jawaban dan tracking
* data yang ditampilkan cukup untuk audit submission dan analisis manual dasar

---

## 10. Core User Stories

### 10.1 Admin Stories

* Sebagai Admin, saya ingin membuat scoreboard baru agar saya bisa menjalankan assessment publik baru tanpa bergantung pada developer.
* Sebagai Admin, saya ingin mengelola category, question, option, dan score agar struktur penilaian dapat disesuaikan dengan kebutuhan bisnis.
* Sebagai Admin, saya ingin mengatur result range agar participant menerima hasil yang relevan dengan performanya.
* Sebagai Admin, saya ingin mempublish scoreboard agar assessment dapat diakses participant.
* Sebagai Admin, saya ingin melihat participant, submission, jawaban, score, result, dan tracking data agar saya dapat menilai kualitas lead dan performa assessment.

### 10.2 Participant Stories

* Sebagai Participant, saya ingin mengisi form atau menerima akses scoreboard agar saya bisa memulai assessment dengan mudah.
* Sebagai Participant, saya ingin mengakses assessment melalui link unik agar hasil saya terkait dengan data saya sendiri.
* Sebagai Participant, saya ingin melihat hasil saya setelah selesai agar saya memahami posisi atau kategori saya.
* Sebagai Participant, saya ingin menerima email dan report agar saya bisa meninjau hasil saya kembali setelah meninggalkan halaman result.

---

## 11. Business Rules

* Scoreboard harus selalu diperlakukan sebagai entitas root dalam domain assessment.
* Semua category, question, option, dan result range harus berada dalam konteks satu scoreboard.
* Participant tidak harus login untuk mengakses assessment.
* Participant harus masuk melalui access model yang dapat diikat ke identitas participant.
* Unique access URL harus unik per participant dan cukup aman untuk penggunaan publik.
* Submission harus menyimpan seluruh jawaban, bukan hanya hasil akhir.
* Result harus ditentukan dari scoring engine dan result range, bukan input manual admin.
* Email dikirim oleh aplikasi sendiri, bukan bergantung pada platform eksternal sebagai core flow.
* PDF report harus diperlakukan sebagai template report tersendiri, bukan sekadar print dari result page.
* Tracking attribution fields harus menjadi bagian data model sejak MVP.
* Sistem tidak boleh memperkenalkan konsep modules, lessons, certificates, atau student progress.

---

## 12. Important Edge Cases

* participant membuka unique URL yang tidak valid
* participant membuka unique URL yang sudah tidak aktif atau tidak terkait scoreboard yang publish
* webhook masuk dengan payload tidak lengkap atau scoreboard target tidak valid
* admin membuat result range yang overlap atau meninggalkan gap yang membuat score tidak terpetakan
* question required tidak dijawab saat submission final
* scoreboard dipublish tanpa konfigurasi minimum yang diperlukan
* participant mengulang akses pada URL yang sama setelah submission selesai
* email gagal terkirim walau submission sudah selesai
* PDF gagal dibuat walau result sudah tersedia
* category dihapus ketika sudah dipakai oleh question atau submission historis
* perubahan konfigurasi scoreboard dilakukan setelah sudah ada submission historis

PRD ini tidak memaksa semua solusi detail edge case pada tahap ini, tetapi implementasi harus mempertimbangkan perilaku yang aman dan konsisten.

---

## 13. Non-Functional Requirements

### 13.1 Security and Access

* hanya Admin yang dapat mengelola konten dan melihat data admin
* unique access URL harus cukup aman dan tidak trivial untuk ditebak
* data participant dan submission harus diperlakukan sebagai data sensitif operasional

### 13.2 Reliability

* flow submit hingga result harus stabil untuk penggunaan publik
* penyimpanan jawaban tidak boleh hilang pada submission yang valid
* proses email dan PDF harus dirancang agar kegagalan dapat ditelusuri dan ditangani

### 13.3 Scalability Readiness

* arsitektur harus mendukung penambahan banyak scoreboard
* desain data harus mendukung pertumbuhan participant dan submission
* komponen result, email, dan PDF sebaiknya tidak dirancang terlalu terikat pada satu scoreboard khusus

### 13.4 Maintainability

* konfigurasi scoreboard harus dikelola admin, bukan hardcoded
* domain model harus tetap bersih dari terminology LMS
* struktur sistem harus memudahkan penambahan integrasi dan analytics di fase berikutnya

### 13.5 Traceability

* setiap submission harus dapat dilacak ke participant, scoreboard, answers, scores, result, dan tracking fields
* admin harus dapat meninjau jejak data secara masuk akal tanpa akses ke tool teknis backend

---

## 14. Product Constraints

* sistem ini adalah repo baru yang benar-benar standalone
* sistem ini bukan LMS
* sistem harus mendukung banyak scoreboard sejak awal
* webhook inbound harus didukung pada MVP
* email dikirim oleh aplikasi Scoreboard sendiri
* submission harus menyimpan seluruh jawaban
* PDF harus menggunakan template report terpisah
* tracking fields harus disimpan sebagai bagian model data
* arsitektur MVP harus siap berkembang ke fase berikutnya

---

## 15. Assumptions

* satu participant dapat memiliki akses unik untuk scoreboard tertentu tanpa perlu akun login tradisional
* satu scoreboard memiliki kumpulan category, question, option, dan result range yang dikelola independen
* scoring utama MVP berbasis option selection dengan nilai numerik yang telah dikonfigurasi admin
* email dan PDF adalah bagian default flow pasca-submit, meskipun detail penyajian per scoreboard dapat berkembang
* admin panel dan public assessment experience berada dalam satu sistem produk yang sama

---

## 16. Open Questions

* apakah satu participant boleh memiliki lebih dari satu submission untuk scoreboard yang sama, atau MVP dibatasi satu attempt per unique access URL?
* kapan submission dianggap dibuat: saat unique URL dibuka pertama kali, saat participant mulai menjawab, atau saat submit final?
* bagaimana aturan lifecycle unique URL setelah submission selesai: tetap aktif untuk melihat result, atau dibatasi?
* apakah semua scoreboard wajib memiliki PDF report pada MVP, atau PDF dapat bersifat optional per scoreboard?
* apakah email wajib menunggu PDF selesai dibuat, atau dapat dikirim terpisah jika PDF gagal?
* bagaimana aturan perubahan konten scoreboard yang sudah memiliki submission historis?
* apakah admin membutuhkan draft preview result sebelum publish?
* apakah participant perlu melihat progress indicator atau save progress pada MVP?

---

## 17. Risks

* risiko scope bergeser ke pola LMS jika terminologi domain tidak dijaga
* risiko kompleksitas meningkat jika flow internal dan webhook tidak disatukan dalam participant access model yang sama
* risiko hasil tidak konsisten jika result range dan scoring rules tidak tervalidasi dengan baik
* risiko operasional jika email dan PDF diperlakukan sinkron tanpa strategi penanganan kegagalan
* risiko technical debt jika implementasi awal terlalu spesifik untuk satu scoreboard pertama

---

## 18. MVP Delivery Summary

MVP YogaFX Scoreboard harus menghadirkan sistem assessment publik yang:

* menangkap lead dari internal form dan webhook
* membuat participant dan unique access URL
* menjalankan multi-scoreboard assessment berbasis category, question, option, dan score
* menyimpan submission beserta seluruh jawaban
* menghasilkan overall score, category scores, dan result
* menampilkan result page
* menghasilkan PDF report
* mengirim email follow-up
* memberi admin visibilitas penuh atas submission dan tracking

MVP ini tidak bertujuan menjadi LMS, landing page builder, atau platform automation penuh. Fokusnya adalah membangun fondasi assessment funnel yang production-oriented, bersih secara domain, dan siap dikembangkan.
