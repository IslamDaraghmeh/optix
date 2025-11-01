const defaultTheme = require("tailwindcss/defaultTheme");

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Inter", "Cairo", "system-ui", "sans-serif"],
                display: ["Poppins", "Cairo", "system-ui", "sans-serif"],
                arabic: ["Cairo", "system-ui", "sans-serif"],
            },
            colors: {
                primary: {
                    50: "#E8F7F5",   // Very light teal
                    100: "#D1EFE9",  // Light teal
                    200: "#A3DFD4",  // Lighter teal
                    300: "#75CFBE",  // Medium-light teal
                    400: "#47BFA9",  // Medium teal
                    500: "#2BB3A3",  // Primary Teal (brand color)
                    600: "#229082",  // Darker teal
                    700: "#1A6C62",  // Dark teal
                    800: "#17877B",  // Dark Teal (brand color)
                    900: "#0D3B35",  // Very dark teal
                    950: "#061D1A",  // Almost black teal
                },
                secondary: {
                    50: "#FAFAFA",   // Very light gray
                    100: "#F4F4F4",  // Background light (brand color)
                    200: "#E5E5E5",  // Light gray
                    300: "#D4D4D4",  // Medium-light gray
                    400: "#A3A3A3",  // Medium gray
                    500: "#737373",  // Gray
                    600: "#525252",  // Dark gray
                    700: "#404040",  // Darker gray
                    800: "#262626",  // Very dark gray
                    900: "#171717",  // Almost black
                    950: "#0A0A0A",  // Near black
                },
                accent: {
                    50: "#E8F7F5",   // Very light teal (matching primary)
                    100: "#D1EFE9",  // Light teal
                    200: "#A3DFD4",  // Lighter teal
                    300: "#75CFBE",  // Medium-light teal
                    400: "#47BFA9",  // Medium teal
                    500: "#2BB3A3",  // Primary Teal (brand color)
                    600: "#229082",  // Darker teal
                    700: "#1A6C62",  // Dark teal
                    800: "#17877B",  // Dark Teal (brand color)
                    900: "#0D3B35",  // Very dark teal
                    950: "#061D1A",  // Almost black teal
                },
                success: {
                    50: "#f0fdf4",
                    100: "#dcfce7",
                    200: "#bbf7d0",
                    300: "#86efac",
                    400: "#4ade80",
                    500: "#22c55e",
                    600: "#16a34a",
                    700: "#15803d",
                    800: "#166534",
                    900: "#14532d",
                    950: "#052e16",
                },
                warning: {
                    50: "#fffbeb",
                    100: "#fef3c7",
                    200: "#fde68a",
                    300: "#fcd34d",
                    400: "#fbbf24",
                    500: "#f59e0b",
                    600: "#d97706",
                    700: "#b45309",
                    800: "#92400e",
                    900: "#78350f",
                    950: "#451a03",
                },
                error: {
                    50: "#fef2f2",
                    100: "#fee2e2",
                    200: "#fecaca",
                    300: "#fca5a5",
                    400: "#f87171",
                    500: "#ef4444",
                    600: "#dc2626",
                    700: "#b91c1c",
                    800: "#991b1b",
                    900: "#7f1d1d",
                    950: "#450a0a",
                },
            },
            animation: {
                "fade-in": "fadeIn 0.5s ease-in-out",
                "slide-up": "slideUp 0.3s ease-out",
                "slide-down": "slideDown 0.3s ease-out",
                "scale-in": "scaleIn 0.2s ease-out",
            },
            keyframes: {
                fadeIn: {
                    "0%": { opacity: "0" },
                    "100%": { opacity: "1" },
                },
                slideUp: {
                    "0%": { transform: "translateY(10px)", opacity: "0" },
                    "100%": { transform: "translateY(0)", opacity: "1" },
                },
                slideDown: {
                    "0%": { transform: "translateY(-10px)", opacity: "0" },
                    "100%": { transform: "translateY(0)", opacity: "1" },
                },
                scaleIn: {
                    "0%": { transform: "scale(0.95)", opacity: "0" },
                    "100%": { transform: "scale(1)", opacity: "1" },
                },
            },
        },
    },

    plugins: [require("@tailwindcss/forms")],
};
