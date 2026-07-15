import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/css/filament/admin/theme.css",
                "resources/css/pdf/expense-report.css",
                "resources/css/pdf/department-descriptions.css",
                "resources/js/app.js",
            ],
            assets: ["resources/images/**"],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
