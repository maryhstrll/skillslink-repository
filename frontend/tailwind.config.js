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
        // New color system
        'brand-primary': 'rgb(var(--color-primary-rgb) / <alpha-value>)',
        'brand-secondary': 'rgb(var(--color-secondary-rgb) / <alpha-value>)',
        'brand-ghost': 'rgb(var(--color-ghost-rgb) / <alpha-value>)',
        'brand-accent': 'rgb(var(--color-accent-rgb) / <alpha-value>)',
        'brand-danger': 'rgb(var(--color-danger-rgb) / <alpha-value>)',
        'brand-neutral': 'rgb(var(--color-neutral-rgb) / <alpha-value>)',
        'brand-surface': 'rgb(var(--color-surface-rgb) / <alpha-value>)',

        // Legacy support (keeping old colors temporarily)
        navy: 'var(--color-primary)',
        'light-blue': 'var(--color-ghost)',
        'medium-blue': 'var(--color-primary)',
        'dark-blue': 'var(--color-primary-dark)',

        // Semantic shortcuts
        bg: 'var(--color-bg)',
        surface: 'var(--color-surface-main)',
        'surface-alt': 'var(--color-surface-alt)',
        border: 'var(--color-border)',
        text: 'var(--color-text-primary)',
        'text-invert': 'var(--color-text-invert)',
        accent: 'var(--color-accent)'
      }
    }
  },
  daisyui: {
    themes: [
      {
        skillslink: {
          primary: 'var(--color-primary)',
          'primary-focus': 'var(--color-primary-dark)',
          secondary: 'var(--color-secondary)',
          accent: 'var(--color-accent)',
          neutral: 'var(--color-neutral)',
          'base-100': 'var(--color-surface-main)',
          info: 'var(--color-ghost)',
          success: 'var(--color-accent)',
          warning: 'var(--color-ghost)',
          error: 'var(--color-danger)'
        }
      },
      'light'
    ]
  },
  plugins: [require('daisyui')]
};