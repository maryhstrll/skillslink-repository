<template>
  <div class="page-header">
    <div class="page-header-content">
      <!-- Left side: Title and description -->
      <div class="page-header-main">
        <div class="page-header-title-row">
          <component 
            :is="titleIcon" 
            v-if="titleIcon" 
            class="page-header-icon"
          />
          <h1 class="page-header-title">{{ title }}</h1>
          <div v-if="badge" class="page-header-badge" :class="getBadgeClass(badgeType)">
            {{ badge }}
          </div>
        </div>
        <p v-if="description" class="page-header-description">
          {{ description }}
        </p>
        
        <!-- Breadcrumbs slot -->
        <div v-if="$slots.breadcrumbs" class="page-header-breadcrumbs">
          <slot name="breadcrumbs"></slot>
        </div>
      </div>

      <!-- Right side: Actions -->
      <div v-if="$slots.actions" class="page-header-actions">
        <slot name="actions"></slot>
      </div>
    </div>

    <!-- Optional subtitle bar for filters/stats -->
    <div v-if="$slots.subtitle" class="page-header-subtitle">
      <slot name="subtitle"></slot>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  titleIcon: {
    type: [String, Object],
    default: null
  },
  badge: {
    type: String,
    default: ''
  },
  badgeType: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'accent', 'ghost', 'neutral', 'success', 'warning', 'error'].includes(value)
  }
})

const getBadgeClass = (type) => {
  const classes = {
    primary: 'page-header-badge--primary',
    secondary: 'page-header-badge--secondary',
    accent: 'page-header-badge--accent',
    ghost: 'page-header-badge--ghost',
    neutral: 'page-header-badge--neutral',
    success: 'page-header-badge--success',
    warning: 'page-header-badge--warning',
    error: 'page-header-badge--error'
  }
  return classes[type] || classes.primary
}
</script>
