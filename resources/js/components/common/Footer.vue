<template>
       <footer class="pc-footer">
      <div class="footer-wrapper container-fluid">
        <div class="row">
          <div class="col-sm-6 my-1">
            <p class="m-0">Made with &#9829; by Team <a href="https://themeforest.net/user/phoenixcoded" target="_blank"> Phoenixcoded</a></p>
          </div>
          <div class="col-sm-6 ms-auto my-1">
            <ul class="list-inline footer-link mb-0 justify-content-sm-end d-flex">
              <li class="list-inline-item"><a href="../index.html">Home</a></li>
              <li class="list-inline-item"><a href="https://pcoded.gitbook.io/light-able/" target="_blank">Documentation</a></li>
              <li class="list-inline-item"><a href="https://phoenixcoded.support-hub.io/" target="_blank">Support</a></li>
            </ul>
          </div>
        </div>
      </div>
    </footer>
</template>
<script setup>
import { onMounted } from "vue";

function loadScriptAsync(url) {
  return new Promise((resolve, reject) => {
    const script = document.createElement("script");
    script.src = url;
    script.async = true;
    script.onload = resolve;
    script.onerror = reject;
    document.head.appendChild(script);
  });
}

onMounted(() => {
  loadScriptAsync("/assets/js/plugins/popper.min.js")
    .then(() => loadScriptAsync("/assets/js/plugins/bootstrap.min.js"))
    .then(() => loadScriptAsync("/assets/js/plugins/simplebar.min.js"))
    .then(() => loadScriptAsync("/assets/js/plugins/i18next.min.js"))
    .then(() => loadScriptAsync("/assets/js/plugins/i18nextHttpBackend.min.js"))
    .then(() => loadScriptAsync("/assets/js/plugins/apexcharts.min.js"))
    .then(() => loadScriptAsync("/assets/js/plugins/jsvectormap.min.js"))
    .then(() => loadScriptAsync("/assets/js/plugins/world.js"))
    .then(() => loadScriptAsync("/assets/js/plugins/world-merc.js"))
    .then(() => loadScriptAsync("/assets/js/widgets/earnings-users-chart.js"))
    .then(() => loadScriptAsync("/assets/js/widgets/world-map-markers.js"))
    .then(() => loadScriptAsync("/assets/js/theme.js"))
    .then(() => loadScriptAsync("/assets/js/script.js"))
    .then(() => loadScriptAsync("/assets/js/multi-lang.js"))
    .then(() => loadScriptAsync("/assets/js/plugins/feather.min.js"))
    .catch((error) => console.error("Error loading scripts:", error));
});
</script>


