// Titik masuk JavaScript. Tailwind diurus lewat resources/css/app.css (Vite).
//
// Dipakai untuk menandai bahwa Vite memang berjalan — berguna saat memeriksa HMR.
document.documentElement.dataset.vite = 'aktif';

if (import.meta.env.DEV) {
    console.info('[laravel-blade-docker] Vite dev server aktif — HMR menyala.');
}
