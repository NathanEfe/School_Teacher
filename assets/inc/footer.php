      </div>
    </div>
    <!-- [ Main Content ] end -->
    <footer class="pc-footer">
      <div class="footer-wrapper container-fluid mx-10">
        <div class="grid grid-cols-12 gap-1.5">
          <div class="col-span-12 sm:col-span-6 my-1">
            <p class="m-0"></p>
            DELSU Staff School
            <p></p>
          </div>
          <div class="col-span-12 sm:col-span-6 my-1">
            <ul class="mb-0 ltr:sm:text-right rtl:sm:text-left *:text-theme-bodycolor dark:*:text-themedark-bodycolor hover:*:text-primary-500 dark:hover:*:text-primary-500">
              <li class="inline-block max-sm:mr-2 sm:ml-2">
                <a href="index.php">Dashboard</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </footer>
    <!-- [Page Specific JS] start -->
    <script data-cfasync="false" src="cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script src="assets/js/plugins/apexcharts.min.js"></script>
    <script src="assets/js/plugins/jsvectormap.min.js"></script>
    <script src="assets/js/plugins/world.js"></script>
    <!-- custom widgets js -->
    <script src="assets/js/widgets/world-low.js"></script>
    <script src="assets/js/widgets/Widget-line-chart.js"></script>
    <!-- [Page Specific JS] end -->
    <!-- Required Js -->
    <script src="assets/js/plugins/simplebar.min.js"></script>
    <script src="assets/js/plugins/popper.min.js"></script>
    <script src="assets/js/icon/custom-icon.js"></script>
    <script src="assets/js/plugins/feather.min.js"></script>
    <script src="assets/js/plugins/i18next.min.js"></script>
    <script src="assets/js/plugins/i18nextHttpBackend.min.js"></script>
    <script src="assets/js/multi-lang.js"></script>
    <script src="assets/js/component.js"></script>
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
      layout_change('false');
    </script>
    <script>
      layout_theme_sidebar_change('dark');
    </script>
    <script>
      change_box_container('false');
    </script>
    <script>
      layout_caption_change('true');
    </script>
    <script>
      layout_rtl_change('false');
    </script>
    <script>
      preset_change('preset-1');
    </script>
    <script>
      main_layout_change('vertical');
    </script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>


<script>
  feather.replace();
</script>

<script>
  function exportTableToExcel(tableID, filename = 'export.xlsx') {
    const table = document.getElementById(tableID);
    const workbook = XLSX.utils.table_to_book(table, { sheet: "Sheet 1" });
    XLSX.writeFile(workbook, filename);
  }
</script>


  </body>
  <!-- [Body] end -->
</html>