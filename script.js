// script.js

document.addEventListener('DOMContentLoaded', () => {
  const navLinks = document.querySelectorAll('.sidebar ul li a');
  const sections = document.querySelectorAll('section');
  const sidebar = document.querySelector('.sidebar');

  // Create toggle button
  const toggleButton = document.createElement('button');
  toggleButton.className = 'sidebar-toggle';
  toggleButton.textContent = '☰';
  document.body.appendChild(toggleButton);

  // Toggle sidebar
  toggleButton.addEventListener('click', () => {
    sidebar.classList.toggle('active');
  });

  // Close sidebar on outside click (mobile)
  document.addEventListener('click', (e) => {
    if (
      window.innerWidth <= 768 &&
      !sidebar.contains(e.target) &&
      !toggleButton.contains(e.target)
    ) {
      sidebar.classList.remove('active');
    }
  });

  // Navigation
  navLinks.forEach((link) => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const targetId = link.getAttribute('href').substring(1);

      // Update active states
      navLinks.forEach((l) => l.classList.remove('active'));
      sections.forEach((s) => s.classList.remove('active'));

      link.classList.add('active');
      document.getElementById(targetId).classList.add('active');

      // Scroll to top
      window.scrollTo({ top: 0, behavior: 'smooth' });

      // Close sidebar on link click (mobile)
      if (window.innerWidth <= 768) {
        sidebar.classList.remove('active');
      }
    });
  });
});