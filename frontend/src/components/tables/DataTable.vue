<template>
  <div class="card app-surface shadow-lg p-4">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-3">
      <h3 class="font-semibold text-xl flex items-center gap-2" style="color: var(--color-text-primary);">
        <component :is="titleIcon" v-if="titleIcon" class="w-6 h-6" />
        {{ title }}
      </h3>
      <div v-if="showCount" class="text-sm flex items-center gap-2" style="color: var(--color-text-secondary);">
        <component :is="countIcon" v-if="countIcon" class="w-4 h-4" />
        Total: {{ data.length }} {{ itemLabel }}
      </div>
    </div>

    <!-- Responsive Table with Horizontal Scroll -->
    <div class="overflow-x-auto rounded-lg border app-border" style="overflow-y: visible;">
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="loading loading-spinner loading-lg" style="color: var(--color-primary);"></div>
        <span class="ml-4 text-lg" style="color: var(--color-text-secondary);">{{ loadingText }}</span>
      </div>

      <div v-else style="position: relative; z-index: 1;">
        <table class="table w-full min-w-[800px]">
          <thead>
            <tr>
              <th 
                v-for="column in columns" 
                :key="column.key"
                :class="column.headerClass"
                class="font-semibold"
                style="color: var(--color-text-primary);"
              >
                {{ column.title }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="(item, index) in data" 
              :key="getItemKey(item, index)" 
              class="hover:bg-base-200/50 transition-colors"
            >
              <td 
                v-for="column in columns" 
                :key="column.key"
                :class="column.cellClass"
                class="py-4"
              >
                <!-- Use slot for custom cell content, fallback to default -->
                <slot 
                  :name="`cell-${column.key}`" 
                  :item="item" 
                  :value="getValue(item, column.key)"
                  :column="column"
                  :index="index"
                >
                  <!-- Default cell content -->
                  {{ getValue(item, column.key) }}
                </slot>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="!loading && data.length === 0" class="text-center py-16">
        <component :is="emptyIcon" v-if="emptyIcon" class="w-16 h-16 mx-auto mb-4" style="color: var(--color-text-light);" />
        <div class="text-xl font-medium mb-2" style="color: var(--color-text-primary);">
          {{ emptyTitle }}
        </div>
        <div class="text-sm mb-6 max-w-md mx-auto" style="color: var(--color-text-secondary);">
          {{ emptyDescription }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  // Required props
  title: {
    type: String,
    required: true
  },
  data: {
    type: Array,
    required: true
  },
  columns: {
    type: Array,
    required: true
    // Format: [{ key: 'name', title: 'Name', headerClass: '', cellClass: '' }, ...]
  },
  
  // Optional props
  titleIcon: {
    type: [String, Object],
    default: null
  },
  countIcon: {
    type: [String, Object],
    default: null
  },
  itemLabel: {
    type: String,
    default: 'items'
  },
  showCount: {
    type: Boolean,
    default: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  loadingText: {
    type: String,
    default: 'Loading data...'
  },
  emptyTitle: {
    type: String,
    default: 'No data available'
  },
  emptyDescription: {
    type: String,
    default: 'No items to display at this time.'
  },
  emptyIcon: {
    type: [String, Object],
    default: null
  },
  keyField: {
    type: String,
    default: 'id'
  }
})

// Utility functions
function getItemKey(item, index) {
  return item[props.keyField] || index
}

function getValue(item, path) {
  // Support nested paths like 'user.name'
  return path.split('.').reduce((obj, key) => obj?.[key], item)
}
</script>

<style scoped>
/* Custom table styling copied exactly from TracerFormTable */
.table th {
  background: var(--color-neutral);
  border-bottom: 2px solid var(--color-border);
  padding: 1rem 0.75rem;
  font-weight: 600;
}

.table td {
  border-bottom: 1px solid var(--color-border-light);
  padding: 1rem 0.75rem;
}

.table tr:hover {
  background: rgba(var(--color-primary-rgb), 0.05);
}

.table tr:last-child td {
  border-bottom: none;
}

/* Enhance dropdown menu styling */
.menu li > a:hover {
  background: rgba(var(--color-primary-rgb), 0.1);
  color: var(--color-primary);
}

/* Badge styling improvements */
.badge {
  font-weight: 500;
  font-size: 0.75rem;
}

/* Button hover effects */
.btn-circle:hover {
  background: var(--color-primary);
  transform: scale(1.05);
  transition: transform 0.2s ease;
}

/* Card shadow enhancement */
.card {
  border: 1px solid var(--color-border-light);
}
</style>
