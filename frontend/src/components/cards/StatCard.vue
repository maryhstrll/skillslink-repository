<template>
  <div class="stat-card" :class="cardClass">
    <div class="flex items-center justify-between">
      <div class="flex-1">
        <div class="stat-title" :class="titleClass">{{ title }}</div>
        <div class="stat-value" :class="valueClass">
          <slot name="value">{{ formattedValue }}</slot>
        </div>
        <div class="stat-desc" :class="descClass">{{ description }}</div>
      </div>
      <div class="stat-figure" :class="iconWrapperClass">
        <component :is="icon" v-if="icon" class="w-8 h-8" />
      </div>
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
  value: {
    type: [String, Number],
    required: true
  },
  description: {
    type: String,
    required: true
  },
  icon: {
    type: [String, Object],
    default: null
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'accent', 'ghost', 'danger', 'neutral'].includes(value)
  },
  format: {
    type: String,
    default: 'none',
    validator: (value) => ['none', 'percentage', 'currency', 'decimal'].includes(value)
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const cardClass = computed(() => `stat-card-${props.variant}`)

const iconWrapperClass = computed(() => {
  switch (props.variant) {
    case 'primary':
    case 'secondary':
    case 'accent':
    case 'danger':
      return 'text-white'
    case 'ghost':
    case 'neutral':
    default:
      return ''
  }
})

const titleClass = computed(() => {
  switch (props.variant) {
    case 'primary':
    case 'secondary':
    case 'accent':
    case 'danger':
      return 'text-white/80'
    case 'ghost':
    case 'neutral':
    default:
      return ''
  }
})

const valueClass = computed(() => {
  switch (props.variant) {
    case 'primary':
    case 'secondary':
    case 'accent':
    case 'danger':
      return 'text-white'
    case 'ghost':
    case 'neutral':
    default:
      return ''
  }
})

const descClass = computed(() => {
  switch (props.variant) {
    case 'primary':
    case 'secondary':
    case 'accent':
    case 'danger':
      return 'text-white/70'
    case 'ghost':
    case 'neutral':
    default:
      return ''
  }
})

const formattedValue = computed(() => {
  if (props.loading) return '...'
  
  const value = props.value
  
  switch (props.format) {
    case 'percentage':
      return `${value}%`
    case 'currency':
      return `₱${Number(value).toLocaleString()}`
    case 'decimal':
      return Number(value).toFixed(1)
    default:
      return value
  }
})
</script>

<style scoped>
.stat-card {
  background: var(--color-surface-main);
  border: 1px solid var(--color-border-light);
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  min-height: 120px;
  display: flex;
  align-items: center;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.stat-card-primary {
  background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light));
  color: var(--color-text-invert);
  border: none;
}

.stat-card-secondary {
  background: linear-gradient(135deg, var(--color-secondary), var(--color-secondary-light));
  color: var(--color-text-invert);
  border: none;
}

.stat-card-ghost {
  background: linear-gradient(135deg, var(--color-ghost), var(--color-ghost-light));
  color: var(--color-text-primary);
  border: none;
}

.stat-card-accent {
  background: linear-gradient(135deg, var(--color-accent), var(--color-accent-light));
  color: var(--color-text-invert);
  border: none;
}

.stat-card-danger {
  background: linear-gradient(135deg, var(--color-danger), var(--color-danger-light));
  color: var(--color-text-invert);
  border: none;
}

.stat-card-neutral {
  background: var(--color-neutral);
  color: var(--color-text-primary);
  border: 1px solid var(--color-border);
}

.stat-figure {
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0.6;
  flex-shrink: 0;
}

.stat-title {
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 0.5rem;
  opacity: 0.8;
  line-height: 1.2;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
  line-height: 1.1;
}

.stat-desc {
  font-size: 0.75rem;
  opacity: 0.7;
  line-height: 1.2;
}
</style>
