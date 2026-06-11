# YogaFX Scoreboard User Flow
# MVP Production-Oriented User Flow

## 1. Document Purpose

Dokumen ini menjabarkan user flow utama untuk **YogaFX Scoreboard MVP** sebagai dasar bersama untuk:

* product thinking
* UX/screen mapping
* implementation planning
* alignment antara founder, designer, dan developer

User Flow ini disusun untuk produk **standalone assessment platform**, bukan LMS. Seluruh flow harus mengikuti filosofi:

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

Dokumen ini tidak boleh ditafsirkan sebagai learning flow, module flow, lesson flow, certificate flow, atau student progress flow.

---

## 2. Actors

### 2.1 Admin

Pengguna internal yang mengelola scoreboard dan meninjau hasil participant.

### 2.2 Participant

Pengguna publik yang mengakses scoreboard melalui form internal atau URL unik.

### 2.3 External System / Webhook Source

Sistem non-human yang mengirim lead ke YogaFX Scoreboard melalui webhook inbound.

---

## 3. Flow Overview

Flow utama YogaFX Scoreboard untuk MVP:

* Admin menyiapkan scoreboard
* Admin mempublish scoreboard
* Participant masuk melalui internal lead form atau External System mengirim lead melalui webhook
* Sistem membuat atau mencocokkan participant
* Sistem menghasilkan unique access URL
* Participant membuka scoreboard melalui URL unik
* Participant mengerjakan assessment
* Sistem menghitung scoring dan menentukan result
* Participant melihat result page
* Sistem menghasilkan PDF report
* Sistem mengirim email notification
* Admin meninjau submissions, scores, answers, result, dan tracking

---

## 4. Flow Assumptions

* satu unique access URL diasumsikan mewakili satu attempt utama untuk satu participant pada satu scoreboard
* participant tidak perlu login dengan akun tradisional
* scoreboard yang dapat diakses participant adalah scoreboard berstatus publish
* PDF dan email merupakan bagian default dari flow pasca-submit, meskipun detail fallback jika salah satu gagal masih dapat diputuskan kemudian
* cara delivery access link dari flow webhook belum dikunci sepenuhnya, tetapi URL generation wajib terjadi

---

## 5. Flow Structure Legend

Setiap flow utama ditulis dengan struktur:

* actor
* goal
* trigger
* preconditions
* happy path
* decision points
* alternate or edge flow
* outcome

---

## 6. Admin Flows

### 6.1 Admin Scoreboard Setup Flow

**Actor:** Admin  
**Goal:** Menyiapkan satu scoreboard lengkap yang siap dipublish  
**Trigger:** Admin ingin membuat assessment baru  
**Preconditions:**

* Admin memiliki akses ke area admin
* Admin sudah masuk ke sistem admin

**Happy Path:**

1. Admin membuka area scoreboard management.
2. Admin membuat scoreboard baru dengan informasi dasar seperti title, slug, dan description.
3. Admin menambahkan satu atau lebih category ke scoreboard.
4. Admin menambahkan question ke scoreboard.
5. Admin mengaitkan question ke category yang relevan.
6. Admin menambahkan option pada tiap question yang membutuhkan pilihan jawaban.
7. Admin menetapkan score untuk setiap option.
8. Admin membuat result range untuk scoreboard.
9. Admin menetapkan threshold, result title, description, dan recommendation dasar.
10. Admin meninjau konfigurasi scoreboard sebelum publish.

**Decision Points:**

* Apakah scoreboard sudah memiliki category yang cukup?
* Apakah setiap question sudah terkait ke scoreboard dan category yang benar?
* Apakah setiap question yang membutuhkan option sudah memiliki option dan score?
* Apakah result range sudah mencakup seluruh rentang score yang valid?

**Alternate / Edge Flow:**

* Jika Admin menyimpan scoreboard tanpa category, scoreboard tetap berada di status draft dan belum siap dipublish.
* Jika Admin membuat question tanpa option pada tipe pilihan, scoreboard dianggap belum lengkap.
* Jika result range overlap atau memiliki gap, sistem harus menolak publish atau menandai konfigurasi belum valid.
* Jika slug scoreboard tidak valid atau bentrok, Admin harus memperbaikinya sebelum melanjutkan.

**Outcome:**

* Scoreboard tersimpan sebagai draft lengkap atau sebagian lengkap
* Sistem memiliki struktur assessment yang siap ditinjau untuk publish

---

### 6.2 Admin Publish Scoreboard Flow

**Actor:** Admin  
**Goal:** Mempublish scoreboard agar dapat digunakan dalam flow participant  
**Trigger:** Admin memutuskan scoreboard siap dipublikasikan  
**Preconditions:**

* Scoreboard sudah dibuat
* Konfigurasi minimum scoreboard telah terpenuhi

**Happy Path:**

1. Admin membuka detail scoreboard draft.
2. Admin meninjau elemen penting scoreboard, termasuk category, question, option, scoring, dan result range.
3. Admin memilih aksi publish.
4. Sistem memvalidasi kelengkapan minimum scoreboard.
5. Jika valid, sistem mengubah status scoreboard menjadi published.
6. Scoreboard siap dipakai oleh flow internal lead form atau inbound webhook.

**Decision Points:**

* Apakah scoreboard memenuhi syarat minimum untuk dipublish?
* Apakah seluruh scoring configuration dan result range valid?

**Alternate / Edge Flow:**

* Jika ada elemen penting yang belum lengkap, sistem menolak publish dan memberi petunjuk area yang belum valid.
* Jika scoreboard pernah memiliki submission historis dan Admin mencoba mengubah struktur besar setelah publish, sistem perlu membatasi atau memperjelas dampak perubahan tersebut.

**Outcome:**

* Scoreboard berstatus published dan siap menerima participant baru

---

### 6.3 Admin Review Submissions Flow

**Actor:** Admin  
**Goal:** Meninjau hasil assessment dan data lead secara end-to-end  
**Trigger:** Admin ingin mengevaluasi performa participant atau scoreboard  
**Preconditions:**

* Terdapat submission yang sudah masuk
* Admin memiliki akses ke area admin review

**Happy Path:**

1. Admin membuka area submissions atau participant review.
2. Admin melihat daftar submission.
3. Admin memilih satu submission untuk melihat detail.
4. Sistem menampilkan data participant.
5. Sistem menampilkan scoreboard terkait.
6. Sistem menampilkan jawaban participant per question.
7. Sistem menampilkan overall score dan category scores.
8. Sistem menampilkan result yang ditentukan.
9. Sistem menampilkan tracking attribution data yang terkait dengan participant atau submission.
10. Admin menggunakan informasi ini untuk analisis manual, review kualitas lead, atau evaluasi scoreboard.

**Decision Points:**

* Apakah Admin sedang meninjau dari perspektif participant atau scoreboard?
* Apakah submission memiliki data result, PDF, dan email yang lengkap?

**Alternate / Edge Flow:**

* Jika submission belum selesai, sistem menampilkan status incomplete atau pending.
* Jika PDF belum tersedia, admin tetap dapat melihat result dan status report.
* Jika email gagal dikirim, admin tetap dapat melihat data submission dan status notifikasi.
* Jika tracking data tidak tersedia dari sumber lead tertentu, sistem tetap menampilkan data yang ada tanpa memutus flow review.

**Outcome:**

* Admin dapat melihat participant, answers, scores, result, dan tracking information dalam satu alur review

---

## 7. Lead Intake and Access Flows

### 7.1 Internal Lead Form Flow

**Actor:** Participant  
**Goal:** Memulai scoreboard dari jalur internal YogaFX Scoreboard  
**Trigger:** Visitor membuka halaman lead form untuk scoreboard tertentu  
**Preconditions:**

* Scoreboard target sudah published
* Internal lead form tersedia untuk scoreboard tersebut

**Happy Path:**

1. Participant membuka halaman awal scoreboard atau lead entry page.
2. Participant melihat form pengisian data dasar.
3. Participant mengisi first name, last name, email, dan WhatsApp.
4. Participant mengirim form.
5. Sistem memvalidasi kelengkapan dan format dasar input.
6. Sistem membuat participant baru atau menjalankan aturan matching yang berlaku.
7. Sistem menghasilkan unique access code.
8. Sistem menghasilkan unique access URL untuk participant pada scoreboard tersebut.
9. Sistem mengarahkan participant ke URL unik atau ke langkah berikutnya dalam flow assessment.

**Decision Points:**

* Apakah seluruh field wajib terisi dengan format yang valid?
* Apakah participant akan selalu dibuat baru atau dapat dicocokkan dengan data existing?

**Alternate / Edge Flow:**

* Jika form tidak lengkap, sistem menampilkan validasi dan participant tetap di halaman form.
* Jika email atau WhatsApp tidak valid, sistem meminta perbaikan sebelum lanjut.
* Jika scoreboard belum publish, participant tidak boleh masuk ke assessment flow.
* Jika pembuatan participant berhasil tetapi URL unik gagal dibuat, flow harus dihentikan secara aman dan tidak langsung masuk ke assessment.

**Outcome:**

* Participant memiliki unique access URL yang terkait ke scoreboard target

---

### 7.2 Inbound Webhook Flow

**Actor:** External System / Webhook Source  
**Goal:** Mengirim lead ke YogaFX Scoreboard agar participant dan akses unik dapat dibuat  
**Trigger:** Sistem eksternal mengirim payload webhook ke endpoint inbound Scoreboard  
**Preconditions:**

* Scoreboard target tersedia dan dapat menerima lead
* Endpoint webhook aktif dan dapat menerima request

**Happy Path:**

1. External System mengirim payload inbound ke YogaFX Scoreboard.
2. Sistem menerima payload.
3. Sistem memvalidasi struktur dan field minimum payload.
4. Sistem mengidentifikasi scoreboard target atau konteks yang relevan.
5. Sistem melakukan participant creation atau matching sesuai aturan yang dipakai.
6. Sistem menyimpan data tracking dan attribution dari payload.
7. Sistem menghasilkan unique access code.
8. Sistem menghasilkan unique access URL untuk participant pada scoreboard tersebut.
9. Sistem mengembalikan outcome yang menyatakan lead berhasil diproses dan access URL telah tersedia.

**Decision Points:**

* Apakah payload memiliki field minimum yang valid?
* Apakah scoreboard target valid dan published?
* Apakah lead dibuat sebagai participant baru atau dicocokkan dengan record existing?

**Alternate / Edge Flow:**

* Jika payload tidak valid, sistem menolak request dan tidak membuat participant.
* Jika scoreboard target tidak ditemukan atau tidak aktif, sistem mengembalikan error yang jelas.
* Jika participant matching gagal diputuskan karena aturan belum final, sistem dapat memakai creation flow default sesuai MVP.
* Jika participant berhasil dibuat tetapi unique URL gagal dihasilkan, sistem harus mengembalikan status gagal parsial atau gagal penuh sesuai desain implementasi.
* Cara pengiriman access link ke participant setelah URL dibuat belum final dan perlu ditentukan lebih lanjut.

**Outcome:**

* Participant dan unique access URL tersedia dari jalur webhook, atau sistem mengembalikan kegagalan validasi yang jelas

---

### 7.3 Unique Access URL Generation Flow

**Actor:** System  
**Goal:** Menghasilkan akses unik yang aman dan dapat menghubungkan participant ke scoreboard yang benar  
**Trigger:** Participant berhasil dibuat atau dicocokkan melalui internal form atau webhook  
**Preconditions:**

* Participant valid tersedia
* Scoreboard target valid tersedia

**Happy Path:**

1. Sistem menerima konteks participant dan scoreboard.
2. Sistem membuat unique access identifier.
3. Sistem memastikan identifier belum pernah digunakan dalam konteks yang melanggar aturan uniqueness.
4. Sistem membentuk unique access URL berbasis identifier tersebut.
5. Sistem menyimpan relasi antara participant, scoreboard, dan URL akses.
6. Sistem mengembalikan URL untuk dipakai pada flow berikutnya.

**Decision Points:**

* Apakah uniqueness bersifat global atau cukup aman dalam konteks tertentu?
* Apakah participant sudah memiliki URL aktif untuk scoreboard yang sama?

**Alternate / Edge Flow:**

* Jika identifier bentrok, sistem harus membuat ulang sampai unik.
* Jika participant sudah memiliki URL aktif untuk scoreboard yang sama, sistem dapat mengembalikan URL existing atau membuat aturan baru sesuai keputusan produk lanjutan.
* Jika penyimpanan akses gagal, URL tidak boleh dianggap aktif.

**Outcome:**

* Sistem memiliki URL unik yang aman, tersimpan, dan siap dipakai participant

---

## 8. Participant Assessment and Result Flows

### 8.1 Participant Scoreboard / Quiz Access Flow

**Actor:** Participant  
**Goal:** Mengakses scoreboard yang ditujukan untuk dirinya  
**Trigger:** Participant membuka unique access URL  
**Preconditions:**

* Unique access URL tersedia
* Scoreboard target berstatus published atau masih valid untuk akses result

**Happy Path:**

1. Participant membuka unique access URL.
2. Sistem memvalidasi URL dan relasinya terhadap participant dan scoreboard.
3. Sistem menentukan status akses.
4. Jika access valid dan attempt belum selesai, sistem menampilkan scoreboard intro atau langsung ke question flow.
5. Participant masuk ke assessment experience.

**Decision Points:**

* Apakah URL valid?
* Apakah attempt utama sudah selesai?
* Apakah URL diarahkan ke assessment state atau result state?

**Alternate / Edge Flow:**

* Jika URL tidak valid, sistem menampilkan status tidak ditemukan atau akses tidak valid.
* Jika scoreboard sudah tidak menerima submission baru, sistem dapat menolak akses assessment baru.
* Jika attempt sudah selesai, sistem dapat mengarahkan participant ke result view sesuai aturan lifecycle URL.

**Outcome:**

* Participant masuk ke assessment flow yang benar atau menerima status akses yang sesuai

---

### 8.2 Participant Assessment Flow

**Actor:** Participant  
**Goal:** Menyelesaikan question dalam scoreboard  
**Trigger:** Participant berhasil masuk ke scoreboard melalui URL unik  
**Preconditions:**

* Access URL valid
* Scoreboard dapat ditampilkan
* Question telah dikonfigurasi admin

**Happy Path:**

1. Participant mulai mengerjakan scoreboard.
2. Sistem menampilkan question sesuai urutan.
3. Participant memilih jawaban pada tiap question.
4. Sistem menyimpan pilihan jawaban sesuai model interaksi yang dipakai.
5. Participant melanjutkan sampai semua question wajib selesai dijawab.
6. Participant meninjau atau langsung mengirim jawaban akhir.
7. Sistem menandai submission sebagai selesai dan meneruskan ke flow scoring.

**Decision Points:**

* Apakah setiap question wajib sudah dijawab?
* Apakah participant masih berada dalam attempt utama yang valid?
* Apakah sistem menyimpan jawaban per langkah atau saat final submit?

**Alternate / Edge Flow:**

* Jika participant mencoba submit tanpa menjawab question required, sistem menahan submit dan menampilkan validasi.
* Jika participant keluar di tengah flow, status submission dapat tetap incomplete sesuai keputusan implementasi.
* Jika URL attempt sudah dianggap selesai, participant tidak boleh memulai attempt baru tanpa aturan khusus.

**Outcome:**

* Submission selesai dengan jawaban lengkap dan siap dihitung

---

### 8.3 Scoring and Result Determination Flow

**Actor:** System  
**Goal:** Mengubah jawaban participant menjadi score dan result yang valid  
**Trigger:** Participant melakukan final submit pada assessment  
**Preconditions:**

* Submission memiliki jawaban yang valid
* Konfigurasi scoring dan result range tersedia

**Happy Path:**

1. Sistem mengambil seluruh jawaban submission.
2. Sistem menghitung score untuk setiap question berdasarkan option yang dipilih.
3. Sistem menghitung total score per category.
4. Sistem menghitung overall score.
5. Sistem menghitung persentase yang relevan.
6. Sistem mencocokkan hasil score dengan result range scoreboard.
7. Sistem menyimpan hasil scoring dan result pada submission.
8. Sistem menandai submission siap untuk result page, PDF, dan email flow.

**Decision Points:**

* Apakah semua jawaban yang dibutuhkan tersedia?
* Apakah result range dapat memetakan overall score secara tunggal?
* Apakah category score perlu ditampilkan untuk seluruh category atau hanya category yang memiliki question?

**Alternate / Edge Flow:**

* Jika result range tidak dapat memetakan score secara valid, flow result tidak boleh dianggap selesai sempurna.
* Jika ada konfigurasi scoring yang rusak, sistem harus menandai submission gagal diproses atau pending review.
* Jika category tertentu tidak memiliki jawaban karena konfigurasi salah, sistem perlu menangani hasil dengan aman.

**Outcome:**

* Submission memiliki overall score, category scores, dan result yang tersimpan

---

### 8.4 Result Page Flow

**Actor:** Participant  
**Goal:** Melihat hasil assessment yang relevan dan mudah dipahami  
**Trigger:** Scoring dan result determination selesai  
**Preconditions:**

* Submission selesai
* Result berhasil ditentukan

**Happy Path:**

1. Sistem menyiapkan data result dari submission.
2. Participant diarahkan ke result page.
3. Result page menampilkan overall score.
4. Result page menampilkan category scores.
5. Result page menampilkan result title dan description.
6. Result page menampilkan recommendation dan CTA dasar bila tersedia.
7. Participant memahami hasil dan dapat melanjutkan ke tindakan berikutnya.

**Decision Points:**

* Apakah semua elemen result tersedia?
* Apakah result page hanya menampilkan skor inti atau juga ringkasan tambahan?

**Alternate / Edge Flow:**

* Jika sebagian data tambahan seperti PDF belum siap, result page tetap dapat ditampilkan dengan hasil utama.
* Jika result berhasil dihitung tetapi beberapa komponen presentasi belum lengkap, sistem tetap memprioritaskan visibilitas hasil utama.

**Outcome:**

* Participant melihat hasil assessment yang telah dipersonalisasi berdasarkan submission

---

## 9. Follow-Up Flows

### 9.1 PDF Generation Flow

**Actor:** System  
**Goal:** Menghasilkan report PDF dari hasil submission  
**Trigger:** Submission telah memiliki result yang valid  
**Preconditions:**

* Result tersedia
* Template PDF relevan tersedia atau dikonfigurasi

**Happy Path:**

1. Sistem mengambil data submission dan result.
2. Sistem menentukan template PDF yang sesuai.
3. Sistem menghasilkan PDF report.
4. Sistem menyimpan referensi PDF pada submission atau result record yang relevan.
5. Sistem menandai PDF sebagai tersedia untuk flow berikutnya.

**Decision Points:**

* Apakah scoreboard atau result mewajibkan PDF?
* Apakah template PDF tersedia untuk scoreboard tersebut?

**Alternate / Edge Flow:**

* Jika template PDF belum tersedia, sistem dapat menandai report sebagai unavailable atau pending sesuai kebijakan produk.
* Jika proses generate PDF gagal, result utama tetap tidak boleh hilang.
* Jika PDF gagal tetapi email tetap perlu dikirim, keputusan fallback harus mengikuti aturan implementasi yang nanti dikunci.

**Outcome:**

* PDF report tersedia atau status kegagalan/pending tercatat dengan jelas

---

### 9.2 Email Notification Flow

**Actor:** System  
**Goal:** Mengirim follow-up email setelah participant menyelesaikan scoreboard  
**Trigger:** Result tersedia, dan bila diwajibkan, PDF telah siap  
**Preconditions:**

* Participant memiliki email yang valid
* Submission selesai

**Happy Path:**

1. Sistem menyiapkan email berdasarkan hasil submission.
2. Sistem menyusun konten seperti result summary, score summary, result link, CTA, dan PDF bila tersedia.
3. Sistem mengirim email ke participant.
4. Sistem mencatat status pengiriman.

**Decision Points:**

* Apakah email menunggu PDF selesai atau dapat dikirim lebih dulu?
* Apakah PDF akan dilampirkan atau hanya direferensikan melalui link?

**Alternate / Edge Flow:**

* Jika email participant tidak valid, sistem menandai email tidak dapat dikirim.
* Jika provider atau proses kirim gagal, status pengiriman harus tercatat agar bisa ditinjau admin.
* Jika PDF belum tersedia, email dapat tertunda atau tetap dikirim tanpa PDF sesuai keputusan implementasi akhir.

**Outcome:**

* Participant menerima follow-up email, atau sistem memiliki catatan status kegagalan yang dapat ditinjau

---

## 10. End-to-End Experience Summary

### 10.1 Participant Journey from Internal Form

```text
Participant
↓
Lead Form
↓
Participant Record
↓
Unique Access URL
↓
Assessment Access
↓
Answer Questions
↓
Submit
↓
Scoring
↓
Result
↓
PDF
↓
Email Follow-Up
```

### 10.2 Participant Journey from Webhook

```text
External System
↓
Inbound Webhook
↓
Payload Validation
↓
Participant Record / Matching
↓
Unique Access URL
↓
Participant Access
↓
Assessment
↓
Scoring
↓
Result
↓
PDF
↓
Email Follow-Up
```

### 10.3 Admin Journey

```text
Admin Creates Scoreboard
↓
Admin Configures Categories / Questions / Options / Scores / Result Ranges
↓
Admin Publishes Scoreboard
↓
Participant Submissions Occur
↓
Admin Reviews Participant, Answers, Scores, Result, and Tracking
```

---

## 11. UX Notes for Screen Mapping

Dokumen ini dapat dipakai sebagai dasar screen mapping minimal untuk area berikut:

* admin scoreboard list
* admin scoreboard create/edit
* admin category management
* admin question and option management
* admin result range management
* admin publish validation state
* participant lead form
* participant scoreboard intro or access state
* participant question flow
* participant validation state
* participant result page
* admin submission list
* admin submission detail

Prinsip UX yang perlu dijaga:

* participant flow harus terasa singkat, jelas, dan tidak seperti sistem belajar
* result adalah momen utama, jadi transisi dari submit ke result harus terasa meyakinkan
* admin perlu melihat relasi data secara utuh, bukan terpecah-pecah tanpa konteks
* error state harus membantu, terutama pada invalid URL, incomplete configuration, dan failed follow-up

---

## 12. Open Points

* aturan final participant matching pada lead form dan webhook belum dikunci
* aturan final satu URL satu attempt masih berupa assumption
* lifecycle URL setelah submission selesai belum dikunci penuh
* strategi final jika PDF gagal tetapi result sudah tersedia belum dikunci
* strategi final jika email gagal tetapi submission berhasil belum dikunci
* cara delivery access link dari webhook source ke participant masih open point

---

## 13. Final Summary

User Flow YogaFX Scoreboard MVP harus selalu dibaca sebagai flow untuk:

* lead capture
* participant access
* assessment completion
* scoring and segmentation
* result delivery
* follow-up
* admin review

Bukan sebagai flow untuk:

* student learning
* progress tracking LMS
* module completion
* lesson navigation
* certificates

Struktur flow ini dirancang agar dapat langsung dipakai sebagai landasan untuk dokumen lanjutan seperti screen mapping, wireframe planning, dan domain/ERD discussion tanpa menggeser produk ke pola LMS.
