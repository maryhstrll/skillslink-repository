<template>
  <div class="card app-surface shadow-lg">
    <div class="p-4 app-surface-alt rounded-lg app-border">
      <h3 class="text-lg font-semibold mb-4" style="color: var(--color-text-primary);">
        {{ title }}
      </h3>
      
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
        <!-- Dynamic Filter Fields -->
        <div v-for="filter in filters" :key="filter.key" class="form-control" :class="getColumnClass(filter)">
          <!-- Field Label -->
          <label v-if="filter.label" class="label">
            <span class="label-text font-medium" style="color: var(--color-text-primary);">{{ filter.label }}</span>
          </label>

          <!-- Text Input Filter -->
          <div v-if="filter.type === 'search'" class="input-group">
            <input 
              :value="modelValue[filter.key] || ''"
              @input="updateFilter(filter.key, $event.target.value)"
              type="text" 
              :placeholder="filter.placeholder" 
              class="input input-bordered flex-1 app-surface app-border"
            />
            <button class="btn btn-primary-custom" type="button">
              <component :is="SearchIcon" class="w-4 h-4" />
            </button>
          </div>

          <!-- Select Dropdown Filter -->
          <select 
            v-else-if="filter.type === 'select'"
            :value="modelValue[filter.key] || ''"
            @change="updateFilter(filter.key, $event.target.value)"
            class="select select-bordered app-surface app-border w-full"
          >
            <option value="">{{ filter.defaultOption || `All ${filter.label || 'Options'}` }}</option>
            <option 
              v-for="option in filter.options" 
              :key="getOptionValue(option, filter)"
              :value="getOptionValue(option, filter)"
            >
              {{ getOptionLabel(option, filter) }}
            </option>
          </select>

          <!-- Number Range Filter -->
          <input 
            v-else-if="filter.type === 'number'"
            :value="modelValue[filter.key] || ''"
            @input="updateFilter(filter.key, $event.target.value)"
            type="number" 
            :placeholder="filter.placeholder"
            :min="filter.min"
            :max="filter.max"
            class="input input-bordered app-surface app-border w-full"
          />

          <!-- Date Filter -->
          <input 
            v-else-if="filter.type === 'date'"
            :value="modelValue[filter.key] || ''"
            @input="updateFilter(filter.key, $event.target.value)"
            type="date"
            class="input input-bordered app-surface app-border w-full"
          />

          <!-- Custom Filter Slot -->
          <div v-else-if="filter.type === 'custom'">
            <slot :name="`filter-${filter.key}`" :filter="filter" :value="modelValue[filter.key]" :updateFilter="updateFilter"></slot>
          </div>
        </div>

        <!-- Clear Filters Button -->
        <div class="form-control lg:col-span-2">
          <label class="label lg:hidden">
            <span class="label-text opacity-0">Clear</span> <!-- Invisible label for alignment -->
          </label>
          <button 
            class="btn btn-danger-custom btn-sm w-full"
            @click="clearAllFilters"
            :disabled="!hasActiveFilters"
          >
            <component :is="ClearIcon" class="w-4 h-4" />
            Clear
          </button>
        </div>
      </div>
      
      <!-- Filter Results Summary -->
      <div v-if="showSummary" class="mt-3 text-sm" style="color: var(--color-text-secondary);">
        <slot name="summary" :totalCount="totalCount" :filteredCount="filteredCount" :hasActiveFilters="hasActiveFilters">
          Showing {{ filteredCount }} of {{ totalCount }} {{ itemLabel }}
        </slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Search as SearchIcon, X as ClearIcon } from 'lucide-vue-next'

const props = defineProps({
  title: {
    type: String,
    default: 'Filter Options'
  },
  modelValue: {
    type: Object,
    required: true
  },
  filters: {
    type: Array,
    required: true,
    // Example filter structure:
    // [
    //   {
    //     key: 'searchQuery',
    //     type: 'search',
    //     placeholder: 'Search by name, ID...'
    //   },
    //   {
    //     key: 'program',
    //     type: 'select',
    //     defaultOption: 'All Programs',
    //     options: [], // Array of objects or strings
    //     valueKey: 'id', // Key for option value (if options are objects)
    //     labelKey: 'name', // Key for option label (if options are objects)
    //     selectClass: 'min-w-[200px]',
    //     containerClass: ''
    //   },
    //   {
    //     key: 'year',
    //     type: 'number',
    //     placeholder: 'Enter year',
    //     min: 2000,
    //     max: 2030,
    //     inputClass: 'min-w-[160px]'
    //   }
    // ]
  },
  totalCount: {
    type: Number,
    default: 0
  },
  filteredCount: {
    type: Number,
    default: 0
  },
  itemLabel: {
    type: String,
    default: 'items'
  },
  showSummary: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'clear'])

// Check if any filters are active
const hasActiveFilters = computed(() => {
  return Object.values(props.modelValue).some(value => {
    if (typeof value === 'string') return value.trim() !== ''
    return value !== null && value !== undefined && value !== ''
  })
})

// Update a specific filter
const updateFilter = (key, value) => {
  const updatedFilters = { ...props.modelValue }
  updatedFilters[key] = value
  emit('update:modelValue', updatedFilters)
}

// Clear all filters
const clearAllFilters = () => {
  const clearedFilters = {}
  props.filters.forEach(filter => {
    clearedFilters[filter.key] = ''
  })
  emit('update:modelValue', clearedFilters)
  emit('clear')
}

// Helper functions for select options
const getOptionValue = (option, filter) => {
  if (typeof option === 'object' && filter.valueKey) {
    return option[filter.valueKey]
  }
  return option
}

const getOptionLabel = (option, filter) => {
  if (typeof option === 'object' && filter.labelKey) {
    return option[filter.labelKey]
  }
  return option
}

// Helper function for grid column classes
const getColumnClass = (filter) => {
  if (filter.type === 'search') {
    return 'lg:col-span-5' // Search takes more space
  }
  if (filter.type === 'select') {
    return 'lg:col-span-3' // Select dropdowns take medium space
  }
  return 'lg:col-span-2' // Other inputs take less space
}
</script>

<style scoped>
/* Ensure consistent styling with app theme */
.input-group input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(var(--color-primary-rgb), 0.1);
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn:disabled:hover {
  transform: none;
}
</style>
