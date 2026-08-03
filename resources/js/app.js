// Memuat konfigurasi JavaScript bawaan Laravel.
import './bootstrap';

// Memuat CSS utama aplikasi.
import '../css/app.css';

// Memuat komponen JavaScript Bootstrap,
// seperti modal, dropdown, toast, dan collapse.
import * as bootstrap from 'bootstrap';

// Membuat Bootstrap dapat digunakan oleh script lain.
window.bootstrap = bootstrap;
