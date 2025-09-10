<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeModal"></div>
    
    <!-- Modal Content -->
    <div class="relative bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b">
        <h3 class="text-xl font-semibold text-gray-900">
          {{ isEdit ? 'Edit Program' : 'Add New Program' }}
        </h3>
        <button
          @click="closeModal"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <IconX :size="24" />
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Program Code -->
          <div class="form-control">
            <label class="label">
              <span class="label-text font-medium">Program Code *</span>
            </label>
            <input
              v-model="form.program_code"
              type="text"
              class="input input-bordered w-full"
              placeholder="e.g., COPR-001"
              required
            />
          </div>

          <!-- Program Type -->
          <div class="form-control">
            <label class="label">
              <span class="label-text font-medium">Program Type *</span>
            </label>
            <select v-model="form.program_type" class="select select-bordered w-full" required>
              <option value="">Select Type</option>
              <option value="certificate">Certificate</option>
              <option value="diploma">Diploma</option>
              <option value="degree">Degree</option>
            </select>
          </div>
        </div>

        <!-- Program Name -->
        <div class="form-control mt-4">
          <label class="label">
            <span class="label-text font-medium">Program Name *</span>
          </label>
          <input
            v-model="form.program_name"
            type="text"
            class="input input-bordered w-full"
            placeholder="e.g., Computer Programming"
            required
          />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
          <!-- Department -->
          <div class="form-control">
            <label class="label">
              <span class="label-text font-medium">Department (Sector) *</span>
            </label>
            <input
              v-model="form.department"
              type="text"
              class="input input-bordered w-full"
              placeholder="e.g., Information Technology"
              required
            />
          </div>

          <!-- Duration -->
          <div class="form-control">
            <label class="label">
              <span class="label-text font-medium">Duration</span>
            </label>
            <input
              v-model="form.duration"
              type="text"
              class="input input-bordered w-full"
              placeholder="e.g., 960 hours"
            />
          </div>
        </div>

        <!-- Description -->
        <div class="form-control mt-4">
          <label class="label">
            <span class="label-text font-medium">Description</span>
          </label>
          <textarea
            v-model="form.description"
            class="textarea textarea-bordered h-24"
            placeholder="Enter program description..."
          ></textarea>
        </div>

        <!-- Active Status -->
        <div class="form-control mt-4">
          <label class="label cursor-pointer justify-start gap-3">
            <input
              v-model="form.is_active"
              type="checkbox"
              class="checkbox checkbox-primary"
            />
            <span class="label-text">Active Program</span>
          </label>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
          <button
            type="button"
            @click="closeModal"
            class="btn btn-ghost"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="btn btn-primary"
            :disabled="loading"
          >
            <span v-if="loading" class="loading loading-spinner loading-sm"></span>
            {{ isEdit ? 'Update Program' : 'Add Program' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { X as IconX } from 'lucide-vue-next'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  program: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'submit'])

const loading = ref(false)
const isEdit = ref(false)

const form = reactive({
  program_code: '',
  program_name: '',
  department: '',
  program_type: '',
  duration: '',
  description: '',
  is_active: true
})

// Reset form when modal opens/closes
watch(() => props.isOpen, (newValue) => {
  if (newValue) {
    if (props.program) {
      // Edit mode
      isEdit.value = true
      Object.assign(form, {
        program_code: props.program.program_code || '',
        program_name: props.program.program_name || '',
        department: props.program.department || '',
        program_type: props.program.program_type || '',
        duration: props.program.duration || '',
        description: props.program.description || '',
        is_active: props.program.is_active ?? true
      })
    } else {
      // Add mode
      isEdit.value = false
      Object.assign(form, {
        program_code: '',
        program_name: '',
        department: '',
        program_type: '',
        duration: '',
        description: '',
        is_active: true
      })
    }
  }
})

const closeModal = () => {
  emit('close')
}

const handleSubmit = async () => {
  loading.value = true
  try {
    const formData = { ...form }
    if (isEdit.value && props.program) {
      formData.program_id = props.program.program_id
    }
    
    await emit('submit', formData)
    closeModal()
  } catch (error) {
    console.error('Form submission error:', error)
  } finally {
    loading.value = false
  }
}
</script>
