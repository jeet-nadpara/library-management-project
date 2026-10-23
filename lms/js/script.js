/**
 * Library Management System - Global JS
 * - Mobile sidebar toggle
 * - Bootstrap client-side form validation
 * - Delete confirmation prompts
 */

document.addEventListener('DOMContentLoaded', function () {

  // ---- Mobile sidebar toggle ----
  const toggleBtn = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', function () {
      sidebar.classList.toggle('show');
    });
    document.addEventListener('click', function (e) {
      if (window.innerWidth < 992 && sidebar.classList.contains('show')) {
        if (!sidebar.contains(e.target) && e.target !== toggleBtn) {
          sidebar.classList.remove('show');
        }
      }
    });
  }

  // ---- Bootstrap validation styles ----
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });

  // ---- Delete confirmation ----
  document.querySelectorAll('.confirm-delete').forEach(function (link) {
    link.addEventListener('click', function (e) {
      const msg = link.getAttribute('data-confirm') || 'Are you sure you want to delete this record?';
      if (!confirm(msg)) {
        e.preventDefault();
      }
    });
  });

  // ---- Auto-dismiss alerts after 5s ----
  document.querySelectorAll('.alert').forEach(function (alertEl) {
    setTimeout(function () {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
      if (bsAlert) bsAlert.close();
    }, 5000);
  });

});
