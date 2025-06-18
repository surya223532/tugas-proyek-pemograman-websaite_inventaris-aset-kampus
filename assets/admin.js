// Menangani klik pada item menu dengan submenu
function toggleSubmenu(id) {
  const submenu = document.getElementById(id);
  const submenuItem = submenu.parentElement;
  
  // Toggle kelas aktif
  submenuItem.classList.toggle("active");
  
  // Simpan state aktif di localStorage
  const isActive = submenuItem.classList.contains("active");
  localStorage.setItem(`submenu_${id}_active`, isActive);
  
  // Gunakan max-height untuk animasi CSS
  if (isActive) {
    submenu.style.maxHeight = submenu.scrollHeight + "px";
  } else {
    submenu.style.maxHeight = "0";
  }
}

// Set menu aktif saat halaman dimuat
window.addEventListener('DOMContentLoaded', function() {
  // Set semua submenu ke state yang disimpan
  document.querySelectorAll('.submenu').forEach(submenu => {
    const id = submenu.id;
    const isActive = localStorage.getItem(`submenu_${id}_active`) === 'true';
    const submenuItem = submenu.parentElement;
    
    if (isActive) {
      submenuItem.classList.add('active');
      submenu.style.maxHeight = submenu.scrollHeight + "px";
    } else {
      submenu.style.maxHeight = "0";
    }
  });

  // Set menu aktif berdasarkan halaman saat ini
  const currentPage = window.location.pathname.split('/').pop();
  document.querySelectorAll('.submenu a').forEach(link => {
    const linkPage = link.getAttribute('href').split('/').pop();
    if (linkPage === currentPage) {
      link.classList.add('active');
      const submenu = link.closest('.submenu');
      if (submenu) {
        const submenuItem = submenu.parentElement;
        submenuItem.classList.add('active');
        submenu.style.maxHeight = submenu.scrollHeight + "px";
        localStorage.setItem(`submenu_${submenu.id}_active`, 'true');
      }
    }
  });
});