<template>
  <div class="card bg-base-100 shadow-xl">
    <div class="card-body">
      <h2 class="card-title">
        <i class="fas fa-palette"></i>
        Theme Settings
      </h2>
      
      <div class="space-y-4">
        <div class="form-control">
          <label class="label">
            <span class="label-text">Choose Theme</span>
          </label>
          <select v-model="selectedTheme" @change="applyTheme" class="select select-bordered w-full max-w-xs">
            <option value="light">Light</option>
            <option value="dark">Dark</option>
            <option value="auto">Auto (System)</option>
          </select>
        </div>
        
        <div class="form-control">
          <label class="cursor-pointer label">
            <span class="label-text">Dark Mode</span>
            <input 
              type="checkbox" 
              :checked="isDarkMode" 
              @change="toggleTheme"
              class="toggle toggle-primary" 
            />
          </label>
        </div>

        <!-- Theme Preview -->
        <div class="divider">Preview</div>
        <div class="grid grid-cols-2 gap-4">
          <div class="mockup-window border bg-base-300">
            <div class="flex justify-center px-4 py-16 bg-base-200">
              <div class="text-base-content">Light Theme</div>
            </div>
          </div>
          <div class="mockup-window border bg-neutral">
            <div class="flex justify-center px-4 py-16 bg-neutral-focus">
              <div class="text-neutral-content">Dark Theme</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

// Props (optional)
const props = defineProps({
  // Allow parent to override default theme
  defaultTheme: {
    type: String,
    default: 'light'
  }
});

// Emits
const emit = defineEmits(['theme-changed']);

// State
const selectedTheme = ref(props.defaultTheme);

// Computed
const isDarkMode = computed(() => {
  if (selectedTheme.value === 'auto') {
    return window.matchMedia('(prefers-color-scheme: dark)').matches;
  }
  return selectedTheme.value === 'dark';
});

// Methods
const applyTheme = () => {
  const theme = getEffectiveTheme();
  document.documentElement.setAttribute('data-theme', theme);
  localStorage.setItem('theme', selectedTheme.value);
  
  // Emit event to parent
  emit('theme-changed', {
    selected: selectedTheme.value,
    effective: theme
  });
};

const toggleTheme = (event) => {
  selectedTheme.value = event.target.checked ? 'dark' : 'light';
  applyTheme();
};

const getEffectiveTheme = () => {
  if (selectedTheme.value === 'auto') {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }
  return selectedTheme.value;
};

const loadTheme = () => {
  // Load theme from localStorage
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme && ['light', 'dark', 'auto'].includes(savedTheme)) {
    selectedTheme.value = savedTheme;
  }
  applyTheme();
};

// Listen for system theme changes when auto is selected
const handleSystemThemeChange = (e) => {
  if (selectedTheme.value === 'auto') {
    applyTheme();
  }
};

onMounted(() => {
  loadTheme();
  
  // Listen for system theme changes
  const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
  mediaQuery.addEventListener('change', handleSystemThemeChange);
  
  // Cleanup on unmount
  return () => {
    mediaQuery.removeEventListener('change', handleSystemThemeChange);
  };
});
</script>

<style scoped>
/* Optional: Add transition effects */
:deep([data-theme]) {
  transition: background-color 0.3s ease, color 0.3s ease;
}
</style>
