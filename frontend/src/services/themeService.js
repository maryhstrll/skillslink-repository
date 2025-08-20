/**
 * Simple theme service for the application
 */
export const themeService = {
  applyTheme(theme) {
    if (!theme) theme = 'light';
    document.documentElement.setAttribute('data-theme', theme);
  },

  init(path) {
    // Apply default theme
    this.applyTheme('light');
  }
};

export default themeService;
