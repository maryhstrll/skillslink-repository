<template>
  <dialog ref="viewFormModal" class="modal">
    <div class="modal-box w-11/12 max-w-5xl max-h-[90vh] flex flex-col">
      <!-- Fixed Header -->
      <div class="flex-shrink-0 pb-4 border-b border-opacity-20 mb-4">
        <h3 class="font-bold text-lg sm:text-xl flex items-center gap-2">
          <IconFileText :size="24" class="text-primary" />
          <span class="truncate">{{ displayData.title }}</span>
        </h3>
      </div>
      
      <!-- Scrollable Content -->
      <div class="flex-1 overflow-y-auto pr-2 -mr-2">

      <!-- Form Details -->
      <div class="mb-6 p-3 sm:p-4 app-surface-alt rounded-lg app-border border">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
          <div class="flex items-center gap-2">
            <IconBarChart3 :size="16" class="text-primary" />
            <span class="font-semibold">Year:</span>
            <span>{{ displayData.year }}</span>
          </div>
          <div class="flex items-center gap-2">
            <IconCheck :size="16" class="text-primary" />
            <span class="font-semibold">Status:</span>
            <span
              v-if="displayData.is_active"
              class="badge badge-accent badge-sm ml-1"
              >Active</span
            >
            <span v-else class="badge badge-ghost badge-sm ml-1"
              >Inactive</span
            >
          </div>
          <div
            v-if="displayData.deadline"
            class="flex items-center gap-2"
          >
            <IconBarChart3 :size="16" class="text-primary" />
            <span class="font-semibold">Deadline:</span>
            <span class="text-xs sm:text-sm">{{
              new Date(displayData.deadline).toLocaleDateString()
            }}</span>
          </div>
        </div>
        <div v-if="displayData.description" class="mt-4">
          <span class="font-semibold">Description:</span>
          <p class="mt-1">{{ displayData.description }}</p>
        </div>
      </div>

      <!-- Form Preview -->
      <div class="space-y-6">
        <!-- Core Employment Questions -->
        <div class="card app-surface p-3 sm:p-4 app-border border">
          <h4 class="font-semibold mb-3 flex items-center" style="color: var(--color-primary);">
            <IconUsers class="w-5 h-5 mr-2" />
            Core Employment Questions ({{ selectedEmploymentQuestions.length }})
          </h4>
          <div class="text-sm opacity-80 mb-3">
            These employment questions are included in this form:
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div
              v-for="(q, i) in selectedEmploymentQuestions.slice(0, 6)"
              :key="q.id"
              class="p-3 app-surface-alt rounded app-border border"
            >
              <label class="label label-text font-medium text-sm">
                {{ i + 1 }}. {{ q.label }}
                <span v-if="q.required" class="text-red-500 ml-1">*</span>
              </label>
              <div class="mt-1">
                <input
                  v-if="q.type === 'text'"
                  class="input input-xs input-bordered w-full app-surface app-border"
                  :placeholder="q.placeholder || 'Preview disabled'"
                  disabled
                />
                <textarea
                  v-else-if="q.type === 'textarea'"
                  class="textarea textarea-xs textarea-bordered w-full app-surface app-border"
                  rows="2"
                  :placeholder="q.placeholder || 'Preview disabled'"
                  disabled
                ></textarea>
                <select
                  v-else-if="q.type === 'select'"
                  class="select select-xs select-bordered w-full app-surface app-border"
                  disabled
                >
                  <option>{{ q.options?.[0] || "Select option" }}</option>
                </select>
                <div v-else-if="q.type === 'radio'" class="space-y-1">
                  <label
                    v-for="opt in q.options?.slice(0, 3)"
                    :key="opt"
                    class="flex items-center gap-2 text-xs"
                  >
                    <input type="radio" disabled class="radio radio-xs radio-primary" />
                    <span>{{ opt }}</span>
                  </label>
                  <div v-if="q.options?.length > 3" class="text-xs opacity-60">
                    ... and {{ q.options.length - 3 }} more
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div
            v-if="selectedEmploymentQuestions.length > 6"
            class="mt-3 text-sm opacity-60"
          >
            ... and {{ selectedEmploymentQuestions.length - 6 }} more employment questions
          </div>
        </div>

        <!-- Additional Custom Questions -->
        <div
          v-if="displayData.questions?.length"
          class="card app-surface p-3 sm:p-4 app-border border"
        >
          <h4 class="font-semibold mb-3 flex items-center" style="color: var(--color-primary);">
            <svg
              class="w-5 h-5 mr-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
              ></path>
            </svg>
            Additional Custom Questions ({{ displayData.questions.length }})
          </h4>
          <div class="space-y-3">
            <div
              v-for="(q, i) in displayData.questions"
              :key="q.id"
              class="p-3 app-surface-alt rounded app-border border"
            >
              <label class="label label-text font-medium text-sm">
                {{ selectedEmploymentQuestions.length + i + 1 }}.
                {{ q.label || "Untitled Question" }}
              </label>
              <div class="mt-1">
                <input
                  v-if="q.type === 'text'"
                  class="input input-xs input-bordered w-full app-surface app-border"
                  :placeholder="q.placeholder || 'Preview disabled'"
                  disabled
                />
                <textarea
                  v-else-if="q.type === 'textarea'"
                  class="textarea textarea-xs textarea-bordered w-full app-surface app-border"
                  :placeholder="q.placeholder || 'Preview disabled'"
                  rows="2"
                  disabled
                ></textarea>
                <input
                  v-else-if="q.type === 'number'"
                  type="number"
                  class="input input-xs input-bordered w-full app-surface app-border"
                  :placeholder="q.placeholder || 'Number input'"
                  disabled
                />
                <div v-else-if="q.type === 'radio'" class="space-y-1">
                  <label
                    v-for="opt in q.options"
                    :key="opt"
                    class="flex items-center gap-2 text-xs"
                  >
                    <input type="radio" disabled class="radio radio-xs radio-primary" />
                    <span>{{ opt }}</span>
                  </label>
                </div>
                <div v-else-if="q.type === 'checkbox'" class="space-y-1">
                  <label
                    v-for="opt in q.options"
                    :key="opt"
                    class="flex items-center gap-2 text-xs"
                  >
                    <input type="checkbox" disabled class="checkbox checkbox-xs checkbox-primary" />
                    <span>{{ opt }}</span>
                  </label>
                </div>
                <select
                  v-else-if="q.type === 'select'"
                  class="select select-xs select-bordered w-full app-surface app-border"
                  disabled
                >
                  <option disabled selected>Select an option</option>
                  <option v-for="opt in q.options" :key="opt">{{ opt }}</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-8 opacity-60">
          <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
            ></path>
          </svg>
          <div class="text-sm">No additional custom questions defined</div>
          <div class="text-xs opacity-60">This form only includes the core employment questions</div>
        </div>
      </div>
      </div>
      
      <!-- Fixed Footer -->
      <div class="flex-shrink-0 pt-4 border-t border-opacity-20 mt-4">
        <div class="flex flex-col sm:flex-row gap-2">
          <button class="btn btn-primary-custom flex items-center gap-2" @click="$emit('editForm', displayData)">
            <IconEdit :size="16" />
            Edit Form
          </button>
          <button class="btn btn-outline flex items-center gap-2" onclick="this.closest('dialog').close()">
            <IconX :size="16" />
            Close Preview
          </button>
        </div>
      </div>
    </div>
  </dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
// Import icons
import { 
  FileText as IconFileText,
  Edit as IconEdit,
  BarChart3 as IconBarChart3,
  Check as IconCheck,
  Users as IconUsers,
  X as IconX
} from 'lucide-vue-next'

const props = defineProps({
  viewFormData: {
    type: Object,
    required: true,
    default: () => ({})
  },
  formData: {
    type: Object,
    required: false,
    default: () => ({})
  },
  coreEmploymentQuestions: {
    type: Array,
    required: true
  },
  selectedEmploymentQuestionsForView: {
    type: Array,
    required: false,
    default: () => []
  },
  visible: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['editForm'])

const viewFormModal = ref(null)

// Watch for changes to visible prop and show/hide modal accordingly
watch(() => props.visible, (newValue) => {
  if (newValue && viewFormModal.value) {
    viewFormModal.value.showModal()
  } else if (!newValue && viewFormModal.value) {
    viewFormModal.value.close()
  }
})

// Create a computed property that uses either viewFormData or formData
const displayData = computed(() => {
  // Use viewFormData by default, fallback to formData if needed
  return props.viewFormData || props.formData || {};
})

// Expose the showModal method to the parent component
const showModal = () => {
  if (viewFormModal.value) {
    viewFormModal.value.showModal()
  }
}

// Expose methods to the parent component
defineExpose({
  showModal
})

const selectedEmploymentQuestions = computed(() => {
  // Use the selectedEmploymentQuestionsForView if provided
  if (props.selectedEmploymentQuestionsForView && props.selectedEmploymentQuestionsForView.length > 0) {
    return props.selectedEmploymentQuestionsForView;
  }
  
  // Otherwise get from formData or viewFormData
  const data = displayData.value;
  if (!data.selectedEmploymentQuestions) return []
  
  let selectedIds = []
  if (typeof data.selectedEmploymentQuestions === 'string') {
    try {
      selectedIds = JSON.parse(data.selectedEmploymentQuestions)
    } catch (e) {
      selectedIds = []
    }
  } else if (Array.isArray(data.selectedEmploymentQuestions)) {
    selectedIds = data.selectedEmploymentQuestions
  }

  // Always include required questions
  const alwaysIncluded = props.coreEmploymentQuestions
    .filter(q => q.always_include)
    .map(q => q.id)
  
  // Combine all IDs (removing duplicates)
  const allIds = [...new Set([...alwaysIncluded, ...selectedIds])]
  
  // Return the questions objects that match these IDs
  return props.coreEmploymentQuestions.filter(q => allIds.includes(q.id))
})
</script>

<style scoped>
.modal-box {
  background: var(--color-surface-main);
  color: var(--color-text-primary);
  padding: 1.5rem;
}

.input, .textarea, .select {
  background: var(--color-text-invert);
  border-color: var(--color-border);
}

.input:disabled, .textarea:disabled, .select:disabled {
  background: var(--color-surface-alt);
  opacity: 0.7;
}

.radio:disabled, .checkbox:disabled {
  opacity: 0.5;
}

/* Ensure consistent styling with the rest of the app */
.card {
  border-radius: 0.75rem;
}

.badge-accent {
  background: var(--color-accent);
  color: var(--color-text-invert);
}

/* Custom scrollbar for the content area */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: var(--color-surface-alt);
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: rgb(var(--color-primary-rgb) / 0.4);
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: rgb(var(--color-primary-rgb) / 0.6);
}

/* Fixed footer styling */
.border-t {
  border-color: var(--color-border);
}
</style>
