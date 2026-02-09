document.addEventListener("DOMContentLoaded", function () {
  const toggleButton = document.getElementById("toggle-menu");
  const adminMenu = document.getElementById("admin");

  // Rétracter / ouvrir le menu
  toggleButton.addEventListener("click", function () {
    adminMenu.classList.toggle("collapsed");
  });

  // Enlever collapsed si la fenêtre est agrandie
  window.addEventListener("resize", function () {
    if (window.innerWidth >= 1054) {
      adminMenu.classList.remove("collapsed");
    }
  });
});

window.addEventListener('resize', () => {
    const admin = document.getElementById('admin');
    if (window.innerWidth >= 1054) {
        admin.classList.remove('collapsed');
    }
});

window.addEventListener('load', () => {
    if (window.innerWidth >= 1054) {
        document.getElementById('admin').classList.remove('collapsed');
    }
});
