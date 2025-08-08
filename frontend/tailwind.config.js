/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        'navy': '#081F37',
        'light-blue': '#5FC9F3',
        'medium-blue': '#2E79BA',
        'dark-blue': '#1E549F',
      }
    }
  }

}



// Usage: bg-navy, text-light-blue, etc.