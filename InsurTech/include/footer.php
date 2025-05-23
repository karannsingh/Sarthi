    <footer class="footer">
      <div>© 2025 Sarthi Enterprises.</div>
      <div class="ms-auto">Powered by&nbsp;<a href="https://www.sarthii.co.in" target="_blank">Sarthi Enterprises</a></div>
    </footer>
  </div>
  <!-- CoreUI and necessary plugins-->
  <script src="assets/@coreui/coreui-pro/js/coreui.bundle.min.js"></script>
  <script src="assets/simplebar/js/simplebar.min.js"></script>
  <!-- Bootstrap Icons CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
  <!-- Protecting From Inspect / Console Opening -->
  <!-- <script>
    // Redirect URL (change as you want)
    const redirectURL = "https://sarthii.co.in/InsurTech/logout.php";

    // Disable right-click
    document.addEventListener('contextmenu', function (e) {
      e.preventDefault();
    });

    // Block F12, Ctrl+Shift+I/J/C/U
    document.addEventListener('keydown', function (e) {
      if (e.keyCode == 123 || // F12
          (e.ctrlKey && e.shiftKey && (e.keyCode == 73 || e.keyCode == 74 || e.keyCode == 67)) || // Ctrl+Shift+I/J/C
          (e.ctrlKey && e.keyCode == 85)) { // Ctrl+U
        window.location.href = redirectURL;
      }
    });

    // Clear console every 100ms
    setInterval(function() {
      console.clear();
    }, 100);

    // Detect if DevTools are open (based on size)
    const detectDevTools = () => {
      const widthThreshold = window.outerWidth - window.innerWidth > 160;
      const heightThreshold = window.outerHeight - window.innerHeight > 160;
      if (widthThreshold || heightThreshold) {
        window.location.href = redirectURL;
      }
    };
    setInterval(detectDevTools, 500);

    // Advanced method: Detect devtools with timing attack
    (function() {
      const devtools = {open: false};
      const threshold = 160;
      const emitEvent = (state) => {
        if (state !== devtools.open) {
          devtools.open = state;
          if (state) {
            window.location.href = redirectURL;
          }
        }
      };
      setInterval(() => {
        const start = new Date();
        debugger;
        const end = new Date();
        emitEvent(end - start > threshold);
      }, 500);
    })();
  </script> -->
  <!-- Plugins and scripts required by this view-->
  <!-- <script src="vendor/chart.js/js/chart.min.js"></script>
  <script src="vendor/@coreui/chartjs/js/coreui-chartjs.js"></script> -->
  <script src="assets/@coreui/utils/js/coreui-utils.js"></script>
  <!-- <script src="js/main.js"></script> -->
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
</body>
</html>