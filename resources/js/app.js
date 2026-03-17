import { createApp } from "vue";
import App from "./App.vue";
import store from './store';
import router from "./router";
import { initTheme, getTheme } from "./utils/theme";
import "bootstrap/dist/css/bootstrap.min.css";
import "../css/style.css";
import "../css/theme-override.css"; /* after style.css so dark theme works on all pages */
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

// Apply saved light/dark theme before mount and sync to store so layout can bind it
initTheme();
store.commit('SET_THEME', getTheme());
const options = {
    // Customize options here
    position: "top-right",
    timeout: 3000,
    closeOnClick: true,
    pauseOnHover: true
  };
const app = createApp(App);
app.use(store);
app.use(router);
app.use(Toast, options);
app.mount("#app");
