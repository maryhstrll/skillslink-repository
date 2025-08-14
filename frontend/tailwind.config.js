/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class', // use <html class="dark"> to toggle
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}'
  ],
  theme: {
    extend: {
      colors: {
        navy: 'rgb(var(--color-navy-rgb) / <alpha-value>)',
        'light-blue': 'rgb(var(--color-light-blue-rgb) / <alpha-value>)',
        'medium-blue': 'rgb(var(--color-medium-blue-rgb) / <alpha-value>)',
        'dark-blue': 'rgb(var(--color-dark-blue-rgb) / <alpha-value>)',

        // Semantic shortcuts
        bg: 'var(--color-bg)',
        surface: 'var(--color-surface)',
        'surface-alt': 'var(--color-surface-alt)',
        border: 'var(--color-border)',
        text: 'var(--color-text)',
        'text-invert': 'var(--color-text-invert)',
        accent: 'rgb(var(--color-accent-rgb) / <alpha-value>)'
      }
    }
  },
  daisyui: {
    themes: [
      {
        skillslink: {
          primary: 'var(--color-medium-blue)',
            'primary-focus': 'var(--color-dark-blue)',
            secondary: 'var(--color-light-blue)',
            accent: 'var(--color-accent)',
            neutral: 'var(--color-navy)',
            'base-100': 'var(--color-surface)',
            info: 'var(--color-light-blue)',
            success: '#16a34a',
            warning: '#f59e0b',
            error: '#dc2626'
        }
      },
      'light'
    ]
  }
};

// Examples:
// <div class="bg-navy/20 text-navy dark:bg-bg">...</div>
// <button class="btn btn-primary">Primary</button>