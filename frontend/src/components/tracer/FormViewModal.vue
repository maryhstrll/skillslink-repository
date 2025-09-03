<template>
  <dialog ref="viewFormModal" class="modal">
    <form method="dialog" class="modal-box w-11/12 max-w-5xl max-h-[90vh] overflow-y-auto">
      <h3 class="font-bold text-lg sm:text-xl mb-4 flex items-center gap-2">
        <IconFileText :size="24" class="text-primary" />
        <span class="truncate">{{ displayData.title }}</span>
      </h3>

      <!-- Form Details -->
      <div class="mb-6 p-3 sm:p-4 app-surface-hover rounded-lg">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
          <div class="flex items-center gap-2">
            <IconBarChart3 :size="16" class="text-text-muted" />
            <span class="font-semibold text-text">Year:</span>
            <span class="text-text">{{ displayData.year }}</span>
          </div>
          <div class="flex items-center gap-2">
            <IconCheck :size="16" class="text-text-muted" />
            <span class="font-semibold">Status:</span>
            <span
              v-if="displayData.is_active"
              class="badge badge-success badge-sm ml-1"
              >Active</span
            >
            <span v-else class="badge badge-ghost badge-sm ml-1 text-text"
              >Inactive</span
            >
          </div>
          <div
            v-if="displayData.deadline"
            class="flex items-center gap-2"
          >
            <IconBarChart3 :size="16" class="text-text-muted" />
            <span class="font-semibold text-text">Deadline:</span>
            <span class="text-text text-xs sm:text-sm">{{
              new Date(displayData.deadline).toLocaleDateString()
            }}</span>
          </div>
        </div>
        <div v-if="displayData.description" class="mt-4">
          <span class="font-semibold text-text">Description:</span>
          <p class="mt-1 text-text">{{ displayData.description }}</p>
        </div>
      </div>

      <!-- Form Preview -->
      <div class="space-y-6">
        <!-- Core Employment Questions -->
        <div class="card app-secondary-bg border border-medium-blue p-4">
          <h4 class="font-semibold text-medium-blue mb-3 flex items-center">
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
                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 002 2h4a2 2 0 002-2V4"
              ></path>
            </svg>
            Core Employment Questions ({{ selectedEmploymentQuestions.length }})
          </h4>
          <div class="text-sm text-text mb-3">
            These employment questions are included in this form:
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div
              v-for="(q, i) in selectedEmploymentQuestions.slice(0, 6)"
              :key="q.id"
              class="p-3 app-surface rounded border-l-4 border-medium-blue"
            >
              <label class="label label-text font-medium text-sm text-text">
                {{ i + 1 }}. {{ q.label }}
                <span v-if="q.required" class="text-red-500 ml-1">*</span>
              </label>
              <div class="mt-1">
                <input
                  v-if="q.type === 'text'"
                  class="input input-xs input-bordered w-full"
                  :placeholder="q.placeholder || 'Preview disabled'"
                  disabled
                />
                <textarea
                  v-else-if="q.type === 'textarea'"
                  class="textarea textarea-xs textarea-bordered w-full"
                  rows="2"
                  :placeholder="q.placeholder || 'Preview disabled'"
                  disabled
                ></textarea>
                <select
                  v-else-if="q.type === 'select'"
                  class="select select-xs select-bordered w-full"
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
                  <div v-if="q.options?.length > 3" class="text-xs text-gray-400">
                    ... and {{ q.options.length - 3 }} more
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div
            v-if="selectedEmploymentQuestions.length > 6"
            class="mt-3 text-sm text-gray-500"
          >
            ... and {{ selectedEmploymentQuestions.length - 6 }} more employment questions
          </div>
        </div>

        <!-- Additional Custom Questions -->
        <div
          v-if="displayData.questions?.length"
          class="card bg-base-200/50 border border-gray-200 p-4"
        >
          <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
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
              class="p-3 bg-white rounded"
            >
              <label class="label label-text font-medium text-sm">
                {{ selectedEmploymentQuestions.length + i + 1 }}.
                {{ q.label || "Untitled Question" }}
              </label>
              <div class="mt-1">
                <input
                  v-if="q.type === 'text'"
                  class="input input-xs input-bordered w-full"
                  :placeholder="q.placeholder || 'Preview disabled'"
                  disabled
                />
                <textarea
                  v-else-if="q.type === 'textarea'"
                  class="textarea textarea-xs textarea-bordered w-full"
                  :placeholder="q.placeholder || 'Preview disabled'"
                  rows="2"
                  disabled
                ></textarea>
                <input
                  v-else-if="q.type === 'number'"
                  type="number"
                  class="input input-xs input-bordered w-full"
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
                  class="select select-xs select-bordered w-full"
                  disabled
                >
                  <option disabled selected>Select an option</option>
                  <option v-for="opt in q.options" :key="opt">{{ opt }}</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-8 text-gray-500">
          <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
            ></path>
          </svg>
          <div class="text-sm">No additional custom questions defined</div>
          <div class="text-xs text-gray-400">This form only includes the core employment questions</div>
        </div>
      </div>

      <div class="modal-action flex flex-col sm:flex-row gap-2">
        <button class="btn btn-outline flex items-center gap-2" @click="$emit('editForm', displayData)">
          <IconEdit :size="16" />
          Edit Form
        </button>
        <button class="btn flex items-center gap-2">
          <IconX :size="16" />
          Close Preview
        </button>
      </div>
    </form>
  </dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

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
