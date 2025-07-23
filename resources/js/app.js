import "./bootstrap";
import { createApp } from "vue";
import App from "@/components/layout/App.vue";
import router from "@/router";
import { THEME } from "@/constants";

// Always apply dark theme
function initializeDarkMode() {
    document.documentElement.classList.remove("light-theme", "dark-theme");
    document.documentElement.classList.add(THEME.DARK);
    document.documentElement.setAttribute("data-theme", "dark");
}

// Initialize dark theme immediately
initializeDarkMode();

const app = createApp(App);
app.use(router);
app.mount("#app");
