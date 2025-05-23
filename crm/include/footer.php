    <footer class="footer">
      <div>© 2024 AK Insurance.</div>
      <div class="ms-auto">Powered by&nbsp;<a href="#">AK Insurance</a></div>
    </footer>
  </div>
  <!-- CoreUI and necessary plugins-->
  <script src="vendors/@coreui/coreui-pro/js/coreui.bundle.min.js"></script>
  <script src="vendors/simplebar/js/simplebar.min.js"></script>
  <script>
    if (document.body.classList.contains('dark-theme')) {
      var element = document.getElementById('btn-dark-theme');
      if (typeof(element) != 'undefined' && element != null) {
        document.getElementById('btn-dark-theme').checked = true;
      }
    } else {
      var element = document.getElementById('btn-light-theme');
      if (typeof(element) != 'undefined' && element != null) {
        document.getElementById('btn-light-theme').checked = true;
      }
    }

    function handleThemeChange(src) {
      var event = document.createEvent('Event');
      event.initEvent('themeChange', true, true);

      if (src.value === 'light') {
        document.body.classList.remove('dark-theme');
      }
      if (src.value === 'dark') {
        document.body.classList.add('dark-theme');
      }
      document.body.dispatchEvent(event);
    }
  </script>
  <!-- Plugins and scripts required by this view-->
  <script src="vendors/chart.js/js/chart.min.js"></script>
  <script src="vendors/@coreui/chartjs/js/coreui-chartjs.js"></script>
  <script src="vendors/@coreui/utils/js/coreui-utils.js"></script>
  <script src="js/main.js"></script>
  <!-- jQuery & DataTables JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
</body>
</html>