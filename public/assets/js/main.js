document.addEventListener('DOMContentLoaded', function () {
  const navbar = document.querySelector('.navbar-viefit');
  if (navbar) {
    const toggleScrolled = () => {
      navbar.classList.toggle('scrolled', window.scrollY > 30);
    };
    toggleScrolled();
    window.addEventListener('scroll', toggleScrolled);
  }

  // Ferme le menu mobile après clic sur un lien
  document.querySelectorAll('.navbar-collapse .nav-link').forEach((link) => {
    link.addEventListener('click', () => {
      const collapseEl = document.querySelector('.navbar-collapse');
      if (collapseEl && collapseEl.classList.contains('show') && window.bootstrap) {
        window.bootstrap.Collapse.getOrCreateInstance(collapseEl).hide();
      }
    });
  });
});
