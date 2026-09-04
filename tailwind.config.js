import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Inter", "Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // SIAKAD Custom Palette
                siakad: {
                    dark: "#1B3C53",
                    primary: "#234C6A",
                    secondary: "#456882",
                    light: "#E3E3E3",
                    50: "#f0f7fb",
                    100: "#dceef6",
                    200: "#b9dded",
                    300: "#86c5e0",
                    400: "#4da5cc",
                    500: "#234C6A",
                    600: "#1B3C53",
                    700: "#163247",
                    800: "#122839",
                    900: "#0d1e2b",
                },
            },
            boxShadow: {
                saas: "0 1px 3px 0 rgb(0 0 0 / 0.05), 0 1px 2px -1px rgb(0 0 0 / 0.05)",
                "saas-md":
                    "0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05)",
                "saas-lg":
                    "0 10px 15px -3px rgb(0 0 0 / 0.05), 0 4px 6px -4px rgb(0 0 0 / 0.05)",
                card: "0 0 0 1px rgb(0 0 0 / 0.03), 0 2px 4px rgb(0 0 0 / 0.05)",
            },
            borderRadius: {
                saas: "0.625rem",
            },
            animation: {
                "fade-in": "fadeIn 0.2s ease-out",
                "slide-in": "slideIn 0.2s ease-out",
            },
            keyframes: {
                fadeIn: {
                    "0%": { opacity: "0" },
                    "100%": { opacity: "1" },
                },
                slideIn: {
                    "0%": { opacity: "0", transform: "translateY(-4px)" },
                    "100%": { opacity: "1", transform: "translateY(0)" },
                },
            },
        },
    },

    plugins: [forms],
};
