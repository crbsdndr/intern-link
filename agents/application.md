# agents/application.md (This document's not ready)

CRUD Application digunakan untuk melakukan operasi pada tabel **application**.

> Sebelum membaca dokumen ini, pastikan Anda sudah membaca **AGENTS.md** untuk memahami konteks.

---

## Hak Akses

* **Create**: Role Student hanya bisa membuat application untuk dirinya sendiri, tetapi role di atasnya bisa melakukan operasi Create secara penuh.
* **Read**: Role Student hanya bisa melihat application untuk dirinya sendiri, tetapi role di atasnya bisa melakukan operasi Read secara penuh.
* **Update**: Role Student hanya bisa melakukan Update jika kolom Student Access bernilai True, tetapi role di atasnya bisa melakukan operasi Update secara penuh.
* **Delete**: Role Student hanya bisa melakukan Delete jika diberi izin oleh role diatasnya, tetapi role di atasnya bisa melakukan operasi Delete secara penuh.

---

## List – `/applications/`
1. Judul halaman: **Applications**.

2. **Search Input**
   * Mencari record berdasarkan semua kolom yang tampil di tabel (tanpa batasan 10 record).
   * Pencarian berjalan otomatis setiap input berubah.
   * Tombol **Search** disediakan untuk antisipasi pencarian otomatis tidak berfungsi.

3. **Filter** (sidebar muncul dari kanan setelah tombol filter diklik):
   * Judul: **Filter Applications**
   * Tombol **X** untuk menutup sidebar
   * Input:
     * Student Name (text)
     * Institution Name (text)
     * Year Period (date) (Ambil tahunnya saja)
     * Term Period (number)
     * Status Application (Dropdown) (Tanpa Tom Select)
     * Student Access (Radio: True / False)
     * Submitted At (Date)
     * Have Notes? (radio: True / False)
   * Tombol **Reset** untuk menghapus filter
   * Tombol **Apply** untuk menerapkan filter
   * Catatan: Filter dapat digabungkan untuk hasil pencarian yang lebih spesifik.

4. **Tabel** dengan kolom: Student Name, Institution Name, Year, Term, Status Application, Student Access, Submitted At.

5. Tampilkan **10 record per halaman**, dengan navigasi **Next** dan **Back**.

6. Tampilkan jumlah total application.

7. Tampilkan informasi halaman dalam format: `Page X out of N` (X = halaman aktif, N = total halaman).

---

## Create – `/applications/create/`
1. Judul halaman: **Create Application**.

2. Input: 
   * Student Name (Dropdown) (Tom Select),
   * Institution Name (Dropdown) (Tom Select),
   * Period Year (Number),
   * Period Term (Number),
   * Status Application (Dropdown) (Tanpa Tom Select),
   * Student Access (Radio: True / False),
   * Submitted At (Date),
   * Notes (TextArea),

3. Catatan:
   * Input Student Name tampilkan namanya bukan ID nya tapi di database yang terhubung adalah ID nya.
   * Input Institution Name tampilkan namanya bukan ID nya tapi di database yang terhubung adalah ID nya.
   * Jika, Input Period Year dan Period Term belum pernah ada di tabel Period buatkan yang baru, dan hubungkan recod period itu dengan ID-nya. Jika sudah ada, langsung hubungkan dengan ID-nya.

4. Tombol **Cancel** untuk kembali.

5. Tombol **Save** untuk menyimpan data baru.

---

## Read – `/applications/[id]/read/`
Detail data application ditampilkan sebagai:
   * Student Photo: {value}
   * Student Name: {value} (Bisa diklik mengarah ke `/students/[id]/read/`)
   * Student Phone: {value}
   * Student Number: {value}
   * National Student Number: {value}
   * Student Major: {value}
   * Student Class: {value}
   * Student Batch: {value}
   * Student Notes: {value}
   * Institution Photo: {value}
   * Institution Name: {value} (Bisa diklik mengarah ke `/institutions/[id]/read/`)
   * Institution Address: {value}
   * Institution City: {value}
   * Institution Province: {value}
   * Institution Website: {value}
   * Institution Industry: {value}
   * Institution Contact Name: {value}
   * Institution Contact Email: {value}
   * Institution Contact Phone: {value}
   * Institution Contact Position: {value}
   * Institution Contact Primary: {value}
   * Institution Quota: {value}
   * Institution Quota Used: {value}
   * Institution Quota Period Year: {value}
   * Institution Quota Period Term: {value}
   * Institution Quota Notes: {value}
   * Period Year: {value}
   * Period Term: {value}
   * Status Application: {value}
   * Student Access: {value}
   * Submitted At: {value}
   * Notes: {value}

---

## Update – `/applications/[id]/update/`
1. Judul halaman: **Update Application**.

2. Input: 
   * Student Name (Dropdown) (Tom Select),
   * Institution Name (Dropdown) (Tom Select),
   * Period Year (Number),
   * Period Term (Number),
   * Status Application (Dropdown) (Tanpa Tom Select),
   * Student Access (Radio: True / False),
   * Submitted At (Date),
   * Notes (TextArea),

3. Catatan:
   * Input Student Name tampilkan namanya bukan ID nya tapi di database yang terhubung adalah ID nya.
   * Input Institution Name tampilkan namanya bukan ID nya tapi di database yang terhubung adalah ID nya.
   * Jika, Input Period Year dan Period Term belum pernah ada di tabel Period buatkan yang baru, dan hubungkan recod period itu dengan ID-nya. Jika sudah ada, langsung hubungkan dengan ID-nya.

3. Tombol **Cancel** untuk kembali.

4. Tombol **Save** untuk menyimpan perubahan.

---

## Delete

Hapus record melalui tombol **Delete** pada tabel di endpoint `/developers/`.

---