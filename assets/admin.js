// Menangani klik pada item menu dengan submenu
function toggleSubmenu(id) {
  // Mencari submenu berdasarkan ID
  var submenu = document.getElementById(id);
  var submenuItem = submenu.parentElement; // Mendapatkan elemen menu induk (li) dari submenu

  // Toggle kelas aktif untuk menunjukkan atau menyembunyikan submenu
  submenuItem.classList.toggle("active");

  // Jika submenu terlihat, tambahkan animasi, jika tidak sembunyikan
  if (submenuItem.classList.contains("active")) {
    submenu.style.display = "block"; // Menampilkan submenu
  } else {
    submenu.style.display = "none"; // Menyembunyikan submenu
  }
}

// Untuk memastikan submenu tetap tertutup setelah halaman dimuat
window.onload = function () {
  // Cari semua submenu dan sembunyikan mereka
  var submenus = document.querySelectorAll(".submenu");
  submenus.forEach(function (submenu) {
    submenu.style.display = "none"; // Menyembunyikan submenu saat halaman pertama kali dimuat
  });
};
