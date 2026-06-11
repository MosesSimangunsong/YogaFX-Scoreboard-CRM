Berikut isi file yang bisa langsung Anda simpan sebagai:

**`docs/00-system-overview.md`**

````md
# YogaFX Scoreboard System Overview
# Source of Truth — Product & System Context

## Purpose

Dokumen ini menjadi penjelasan tingkat tinggi tentang apa itu **YogaFX Scoreboard**, mengapa sistem ini dibuat, apa tujuan bisnisnya, bagaimana flow utamanya bekerja, dan apa saja komponen inti yang harus dipahami sebelum implementasi dimulai.

Dokumen ini dibuat agar AI Coding Assistant (Codex) memahami bahwa sistem ini adalah **produk baru yang berdiri sendiri**, bukan turunan langsung dari LMS YogaFX.

---

# 1. What Is YogaFX Scoreboard?

YogaFX Scoreboard adalah platform **assessment, lead qualification, dan audience segmentation** yang berdiri sendiri.

Walaupun beberapa ide scoring dan question engine dapat mengambil inspirasi dari domain assessment pada YogaFX LMS, Scoreboard **bukan LMS** dan **bukan fitur student learning**.

Scoreboard adalah sistem publik yang digunakan untuk:

- menangkap lead
- mengkualifikasi audience
- menjalankan assessment / quiz berbasis score
- menghasilkan result
- mengirim follow-up email
- mendukung funnel marketing

---

# 2. What Scoreboard Is NOT

YogaFX Scoreboard **bukan**:

- Learning Management System
- Sistem student progress
- Sistem modules dan lessons LMS
- Sistem certificates LMS
- Sistem course completion LMS

Scoreboard tidak memiliki konsep inti seperti:

- Modules
- Lessons
- Student Learning Progress
- LMS Certificates
- Continue Learning
- Course Access Tier seperti LMS

Jika suatu keputusan arsitektur cenderung mengarah ke pola LMS, maka keputusan tersebut harus ditinjau ulang.

---

# 3. Product Philosophy

Semua keputusan sistem harus mengikuti alur berpikir berikut:

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
````

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

Artinya, pusat sistem ini bukan pembelajaran, tetapi:

* lead
* score
* result
* follow-up

---

# 4. Business Goals

YogaFX Scoreboard dibangun untuk tujuan bisnis berikut:

* Lead Qualification
* Audience Segmentation
* Marketing Funnel Support
* Assessment-Based Recommendations
* Email Follow-Up Automation

Artinya, scoreboard harus bisa membantu YogaFX menjawab pertanyaan seperti:

* siapa lead yang paling potensial?
* kategori mana yang paling kuat/lemah dari seorang participant?
* hasil apa yang perlu ditampilkan setelah quiz selesai?
* siapa yang harus masuk funnel follow-up tertentu?
* scoreboard mana yang paling efektif mengumpulkan lead?

---

# 5. Public Access Model

Scoreboard adalah sistem publik.

Participant tidak harus menjadi student LMS dan tidak harus login seperti student.

Sistem harus mendukung dua jalur masuk utama:

---

## Scenario A — Internal Lead Form

Visitor datang langsung ke Scoreboard.

Flow:

```text
Visitor
↓
Lead Form
↓
Generate Participant
↓
Generate Unique Access Code
↓
Generate Unique Access URL
↓
Quiz / Scoreboard
↓
Scoring
↓
Result Page
↓
Email Notification
```

Lead form minimal berisi:

* First Name
* Last Name
* Email
* WhatsApp

Setelah form disubmit:

* sistem membuat participant
* sistem membuat unique access code
* sistem membuat unique scoreboard URL

Contoh:

```text
https://scoreboard.yogafx.com/s/8F7A9B
```

---

## Scenario B — External Webhook

Lead datang dari website atau sistem lain.

Flow:

```text
External Website / CRM / Landing Page
↓
Lead Form
↓
Webhook
↓
YogaFX Scoreboard
↓
Generate Participant
↓
Generate Unique Access Code
↓
Generate Unique Access URL
↓
Quiz / Scoreboard
↓
Scoring
↓
Result Page
↓
Email Notification
```

Contoh sumber webhook:

* Website Marketing
* Landing Page Builder
* CRM
* External Lead Capture Form

Artinya scoreboard harus bisa menerima lead dari luar, bukan hanya dari form internalnya sendiri.

---

# 6. Unique Access Principle

Setelah participant tercipta, quiz diakses melalui **URL unik**, bukan sekadar halaman umum yang sama untuk semua orang.

Contoh:

```text
scoreboard.yogafx.com/s/abc123
```

Prinsip ini penting karena:

* satu participant bisa punya satu akses unik
* tracking menjadi lebih rapi
* webhook flow dan internal flow bisa bertemu di model akses yang sama
* result dan submission lebih mudah diikat ke participant yang benar

---

# 7. Multi-Scoreboard System

Scoreboard bukan hanya satu quiz tunggal.

Sistem harus mendukung **many scoreboards**.

Contoh scoreboard yang mungkin ada:

* Bikram Teacher Readiness Scoreboard
* Teaching Potential Assessment
* Mobility Assessment
* Mindset Assessment
* Yoga Readiness Assessment

Implikasinya:

* sistem harus multi-scoreboard sejak awal
* admin dapat membuat banyak scoreboard
* participant submission harus selalu terikat ke scoreboard tertentu
* result range, category, question, dan scoring harus hidup dalam konteks scoreboard masing-masing

---

# 8. Admin-Managed Content

Konten scoreboard tidak fixed dari developer.

Admin harus bisa mengelola sendiri komponen utama scoreboard.

Admin dapat:

* Create Scoreboard
* Edit Scoreboard
* Publish Scoreboard
* Archive Scoreboard

Admin juga harus dapat:

* Create Category
* Edit Category
* Delete Category

Dan:

* Create Question
* Edit Question
* Assign Category
* Configure Scores

Serta:

* Create Result Range
* Configure Score Threshold
* Configure Result Description

Jadi sistem ini memerlukan admin panel yang benar-benar menjadi pusat pengelolaan scoreboard.

---

# 9. Core Domain Concepts

Berikut domain inti yang harus dipahami.

---

## 9.1 Scoreboard

Scoreboard mewakili satu assessment publik.

Contoh:

```text
Are You Ready To Become A Bikram Teacher?
```

Field utama secara konsep:

* id
* title
* slug
* description
* status
* created_by
* created_at
* updated_at

Scoreboard adalah root entity dari domain assessment ini.

---

## 9.2 Category

Category adalah kategori scoring dalam satu scoreboard.

Contoh:

* Personal Practice
* Mindset Alignment
* Teaching Potential
* Leadership Potential

Kategori dibuat dan dikelola oleh admin.

Kategori dipakai untuk:

* grouping question
* scoring per category
* category analytics
* result breakdown

---

## 9.3 Question

Question adalah pertanyaan yang muncul dalam scoreboard.

Contoh:

```text
How long have you been practicing Bikram Yoga?
```

Field utama secara konsep:

* id
* scoreboard_id
* category_id
* question
* question_type
* sort_order
* is_required

Question hidup di dalam satu scoreboard, dan biasanya terhubung ke satu category.

---

## 9.4 Option

Option adalah pilihan jawaban.

Contoh:

* Less than 1 year
* 1–3 years
* 3–5 years
* 5+ years

Setiap option memiliki score.

Contoh:

* Less than 1 year = 2
* 1–3 years = 5
* 3–5 years = 8
* 5+ years = 10

Jadi model scoring dasarnya adalah:

**option → score**

---

## 9.5 Result Range

Result range menentukan hasil akhir berdasarkan score.

Contoh:

* 0–30 → Beginner
* 31–60 → Committed Practitioner
* 61–80 → Future Teacher
* 81–100 → Teacher Potential Elite

Result range dibuat dan diatur oleh admin.

Result range menentukan:

* result title
* result description
* recommendation
* kemungkinan PDF template
* kemungkinan CTA atau follow-up

---

## 9.6 Participant

Participant adalah orang yang mengerjakan scoreboard.

Field utama secara konsep:

* id
* first_name
* last_name
* email
* whatsapp
* country

Participant dapat berasal dari:

* Internal Lead Form
* Webhook
* Import Data
* CRM Integration

---

## 9.7 Submission

Submission mewakili satu attempt pengerjaan scoreboard.

Field utama secara konsep:

* id
* participant_id
* scoreboard_id
* started_at
* finished_at
* time_taken
* completed
* result_key
* overall_score
* overall_percent
* result_url
* result_pdf_url

Submission adalah pusat data hasil pengerjaan scoreboard.

---

## 9.8 Submission Answer

Submission answer menyimpan seluruh jawaban participant.

Field utama secara konsep:

* id
* submission_id
* question_id
* option_id
* score

Sistem harus menyimpan seluruh jawaban untuk kebutuhan:

* Reporting
* Analytics
* Audience Segmentation
* Future Expansion

---

# 10. Scoring Engine

Saat participant submit scoreboard, engine harus melakukan proses berikut:

```text
Submission
↓
Calculate Question Scores
↓
Calculate Category Scores
↓
Calculate Overall Score
↓
Determine Result Range
↓
Generate Result
```

Artinya scoreboard bukan sekadar form survey, tetapi sistem scoring yang nyata.

---

# 11. Category Scoring

Selain overall score, sistem juga harus menghitung **score per category**.

Contoh output:

* Personal Practice Score
* Mindset Alignment Score
* Teaching Potential Score

Masing-masing category minimal harus bisa menghasilkan:

* Category Name
* Actual Score
* Percentage

Ini penting karena scoreboard client Anda sudah menunjukkan adanya banyak score summary seperti:

* Overall Score %
* Overall Score - Actual
* Personal Practice Score %
* Personal Practice Score - Actual
* Mindset Alignment Score %
* Mindset Alignment Score - Actual
* Teaching Potential Score %
* Teaching Potential Score - Actual

Jadi arsitektur scoring harus mendukung **multi-category score output**.

---

# 12. Result Page

Result page ditampilkan setelah quiz selesai.

Result page bukan sekadar “terima kasih, Anda selesai”.

Informasi yang mungkin ditampilkan:

* Overall Score
* Category Scores
* Statistics Benar dan Salah
* Result Title
* Result Description
* Recommendations
* CTA

Result page harus cukup fleksibel untuk dikustomisasi sesuai scoreboard.

---

# 13. PDF Report

Setiap result dapat memiliki PDF report.

PDF **bukan** sekadar export mentah dari result page.

PDF menggunakan template report tersendiri.

Contoh:

* Teacher Potential Report
* Mobility Assessment Report
* Mindset Assessment Report

Admin nantinya dapat menentukan template PDF yang digunakan untuk result tertentu.

Jadi:

* result page ≠ PDF template
* PDF adalah artefak tersendiri

---

# 14. Email Notification

Setelah participant menyelesaikan scoreboard, sistem harus dapat mengirim email.

Flow:

```text
Submit
↓
Calculate Result
↓
Generate PDF
↓
Send Email
```

Email dikirim oleh **aplikasi Scoreboard sendiri**, bukan oleh platform eksternal.

Email bisa berisi:

* Result Summary
* Score Summary
* PDF Report
* Follow-Up CTA

Email adalah bagian inti produk, bukan add-on sekunder.

---

# 15. Webhook Support

Sistem harus mendukung webhook inbound.

Contoh data inbound:

* First Name
* Last Name
* Email
* WhatsApp
* UTM Source
* UTM Campaign
* UTM Medium
* UTM Term
* UTM Content
* Country
* Referrer
* Landing Page

Data webhook dipakai untuk:

* Lead Creation
* Participant Creation
* Attribution Tracking

---

# 16. Tracking & Attribution Fields

Sistem perlu mendukung field berikut:

* utm_source
* utm_campaign
* utm_medium
* utm_term
* utm_content
* referrer
* landing_page
* ip_address_country

Field-field ini dipakai untuk:

* analisis marketing
* attribution
* funnel performance
* segmentation
* reporting

Artinya scoreboard bukan sekadar quiz engine, tetapi juga alat marketing intelligence.

---

# 17. Data Visibility for Admin

Admin nantinya harus dapat melihat:

```text
Lead
↓
Submission
↓
All Answers
↓
Scores
↓
Result
↓
Tracking Information
```

Jadi admin tidak hanya melihat “score total”, tetapi seluruh jejak pengerjaan participant.

---

# 18. Current Product Boundaries

Untuk fase awal, sistem ini hanya menangani **quiz / scoreboard**, bukan landing page builder penuh.

Artinya:

* hero section
* benefits
* testimonials
* CTA marketing page

dapat tetap hidup di website lain.

Scoreboard fokus pada:

* lead intake
* participant creation
* unique access
* quiz
* scoring
* result
* email

---

# 19. Future Expansion

Arsitektur harus memungkinkan pengembangan ke fitur berikut di masa depan:

* Audience Segmentation
* CRM Integration
* Advanced Analytics
* Email Campaign Automation
* Multiple PDF Templates
* Advanced Recommendation Engine
* Public API
* Outbound Webhooks
* Multi-Tenant Support

Artinya desain awal tidak boleh terlalu sempit hanya untuk satu quiz sederhana.

---

# 20. What Codex Must Understand

Codex harus memahami hal-hal berikut sebelum implementasi:

1. Ini adalah repo baru yang berdiri sendiri.
2. Ini bukan LMS dan tidak boleh mengikuti arsitektur LMS.
3. Fokus sistem adalah lead qualification dan scoring.
4. Sistem harus multi-scoreboard.
5. Admin mengelola scoreboard, category, question, option, dan result range.
6. Participant bisa datang dari internal form atau webhook.
7. Quiz diakses melalui unique URL.
8. Sistem harus menyimpan seluruh jawaban.
9. Hasil harus mendukung overall score dan category score.
10. Result page, PDF, dan email adalah bagian inti.
11. Tracking marketing harus menjadi bagian desain data dari awal.

---

# 21. Final Summary

YogaFX Scoreboard adalah:

* standalone assessment platform
* public-facing
* multi-scoreboard
* admin-managed
* lead-oriented
* scoring-driven
* result-oriented
* email-enabled
* attribution-aware

Produk ini dibangun untuk:

* menangkap lead
* mengkualifikasi peserta
* menghitung score
* melakukan segmentation
* menampilkan hasil
* menjalankan follow-up

Bukan untuk:

* learning flow
* modules
* lessons
* certificates
* student progress

Dokumen ini menjadi source of truth tingkat tinggi untuk memahami maksud sistem sebelum masuk ke PRD, User Flow, ERD, dan implementasi.

```
