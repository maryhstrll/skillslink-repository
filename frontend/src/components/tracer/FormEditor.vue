<template>
  <div>
    <transition name="fade">
      <div v-if="editing" class="card app-surface shadow p-3 sm:p-4">
        <h3 class="font-semibold text-lg sm:text-xl mb-3 flex items-center gap-2">
          <IconFileText :size="20" />
          {{ isNew ? "Create New Employment Tracer Form" : "Edit Employment Tracer Form" }}
        </h3>

        <div class="space-y-4">
          <!-- Form Details -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div>
              <label class="label">
                <span class="label-text flex items-center gap-2">
                  <IconEdit :size="16" />
                  Form Title
                </span>
              </label>
              <input
                v-model="formModel.title"
                class="input input-bordered w-full"
                placeholder="e.g. Alumni Employment Tracer 2025"
              />
            </div>

            <div>
              <label class="label">
                <span class="label-text flex items-center gap-2">
                  <IconBarChart3 :size="16" />
                  Form Year
                </span>
              </label>
              <input
                v-model.number="formModel.year"
                type="number"
                class="input input-bordered w-full"
                :min="2020"
                :max="2030"
              />
            </div>

            <div>
              <label class="label">
                <span class="label-text flex items-center gap-2">
                  <IconBarChart3 :size="16" />
                  Deadline
                </span>
              </label>
              <input
                v-model="formModel.deadline"
                type="date"
                class="input input-bordered w-full"
              />
            </div>

            <div class="flex items-center gap-2 pt-8">
              <input
                type="checkbox"
                class="checkbox checkbox-primary"
                v-model="formModel.is_active"
              />
              <label class="label-text flex items-center gap-2">
                <IconCheck :size="16" />
                <span class="text-sm">{{ formModel.is_active ? "Active" : "Inactive" }}</span>
              </label>
            </div>
          </div>

          <div>
            <label class="label">
              <span class="label-text flex items-center gap-2">
                <IconFileText :size="16" />
                Form Description
              </span>
            </label>
            <textarea
              v-model="formModel.description"
              class="textarea textarea-bordered w-full"
              rows="3"
              placeholder="Describe the purpose of this employment tracer form..."
            ></textarea>
          </div>

          <!-- Core Employment Questions Selector -->
          <div class="card app-surface p-3 sm:p-4 app-border border">
            <h4
              class="font-medium mb-3 flex items-center text-medium-blue"
            >
              <IconUsers class="w-5 h-5 mr-2" />
              Employment Questions Configuration
            </h4>
            <div class="text-sm text-text opacity-80 mb-4">
              Select which employment questions to include in this tracer form. 
              <strong>Required questions</strong> are always included automatically.
            </div>
            
            <!-- Always Included Employment Questions (Required) -->
            <div class="mb-4">
              <h5 class="font-medium text-text mb-3">Required Employment Questions (Always Included)</h5>
              <div class="space-y-2">
                <div
                  v-for="question in coreEmploymentQuestions.filter(q => q.always_include)"
                  :key="question.id"
                  class="p-3 app-secondary-bg rounded border-l-4 border-medium-blue"
                >
                  <div class="flex items-center gap-2">
                    <input
                      type="checkbox"
                      class="checkbox checkbox-primary checkbox-sm"
                      checked
                      disabled
                    />
                    <span class="font-medium text-text">{{ question.label }}</span>
                    <span class="badge badge-primary badge-xs">Required</span>
                  </div>
                  <div class="text-xs text-text opacity-70 mt-1">
                    <span v-if="question.conditional">📋 Only shown when: {{ question.conditional }}</span>
                    <span v-if="question.options">Options: {{ question.options.slice(0, 3).join(', ') }}{{ question.options.length > 3 ? '...' : '' }}</span>
                    <span v-if="question.type === 'text'">Text input</span>
                    <span v-if="question.type === 'date'">Date picker</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Optional Employment Questions -->
            <div class="space-y-2">
              <h5 class="font-medium text-text mb-3">Optional Employment Questions</h5>
              <div
                v-for="question in coreEmploymentQuestions.filter(q => q.optional)"
                :key="question.id"
                class="p-3 app-surface-alt rounded border"
              >
                <div class="flex items-start gap-3">
                  <input
                    type="checkbox"
                    class="checkbox checkbox-primary checkbox-sm mt-1"
                    :value="question.id"
                    v-model="formModel.selectedEmploymentQuestions"
                  />
                  <div class="flex-1">
                    <div class="flex items-center gap-2">
                      <span class="font-medium text-text">{{ question.label }}</span>
                      <span class="text-text opacity-60 text-xs">({{ question.type }})</span>
                    </div>
                    <div v-if="question.conditional" class="text-xs text-orange-600 mt-1">
                      📋 Only shown when: {{ question.conditional }}
                    </div>
                    <div v-if="question.options" class="text-xs text-text opacity-60 mt-1">
                      Options: {{ question.options.slice(0, 3).join(', ') }}{{ question.options.length > 3 ? '...' : '' }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="mt-3 text-sm text-text opacity-70">
              Selected: {{ enabledEmploymentQuestions.length }} employment questions will be included
            </div>
          </div>

          <!-- Additional Questions Builder -->
          <div
            class="card app-surface border-2 border-dashed app-border p-4"
          >
            <div class="flex items-center justify-between mb-3">
              <h4 class="font-medium flex items-center text-medium-blue">
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
                Additional Survey Questions
              </h4>
              <div class="flex flex-col sm:flex-row gap-2">
                <button
                  class="btn btn-sm btn-success flex items-center gap-2"
                  @click="addQuestion"
                >
                  <IconPlus :size="16" />
                  Add Question
                </button>
                <button
                  class="btn btn-sm btn-ghost flex items-center gap-2"
                  @click="togglePreview"
                >
                  <IconEye :size="16" v-if="!preview" />
                  <IconEyeOff :size="16" v-else />
                  {{ preview ? "Hide Preview" : "Show Preview" }}
                </button>
              </div>
            </div>

            <div class="text-sm text-text opacity-80 mb-4">
              Add custom questions specific to this year's employment
              tracer. These will be stored as flexible survey data.
            </div>

            <draggable
              v-model="formModel.questions"
              item-key="id"
              class="space-y-3"
              :disabled="formModel.questions.length === 0"
            >
              <template #item="{ element: q, index }">
                <div class="card card-compact app-surface-alt p-4 app-border border">
                  <div class="flex justify-between items-start gap-3">
                    <div class="w-full space-y-3">
                      <div class="flex gap-3 items-center">
                        <span class="badge badge-outline font-semibold text-medium-blue border-medium-blue">
                          Q{{ index + 1 }}
                        </span>
                        <input
                          v-model="q.label"
                          class="input input-sm input-bordered app-surface app-border flex-1 text-text"
                          placeholder="Enter your question here..."
                        />
                      </div>

                      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <select
                          v-model="q.type"
                          class="select select-sm select-bordered app-surface app-border text-text"
                          @change="handleQuestionTypeChange(q)"
                        >
                          <option value="text">Text Input</option>
                          <option value="textarea">Long Text (Textarea)</option>
                          <option value="number">Number Input</option>
                          <option value="radio">Single Choice (Radio)</option>
                          <option value="checkbox">Multiple Choice (Checkbox)</option>
                          <option value="select">Dropdown Menu</option>
                        </select>

                        <input
                          v-if="['text', 'textarea', 'number'].includes(q.type)"
                          v-model="q.placeholder"
                          class="input input-sm input-bordered app-surface app-border text-text"
                          placeholder="Placeholder text (optional)"
                        />

                        <div v-if="['radio', 'checkbox', 'select'].includes(q.type)" class="flex gap-2">
                          <input
                            v-model="q.optionInput"
                            @keyup.enter.prevent="addOption(q)"
                            class="input input-sm input-bordered app-surface app-border text-text flex-1"
                            placeholder="Add option then press Enter"
                          />
                          <button
                            @click="addOption(q)"
                            class="btn btn-sm btn-outline border-medium-blue text-medium-blue hover:bg-medium-blue hover:text-white"
                            :disabled="!q.optionInput"
                          >
                            Add
                          </button>
                        </div>
                      </div>

                      <!-- Options Display -->
                      <div v-if="q.options && q.options.length" class="mt-2">
                        <div class="text-sm text-text-muted mb-2">Options:</div>
                        <div class="flex flex-wrap gap-2">
                          <span
                            v-for="(opt, oi) in q.options"
                            :key="oi"
                            class="badge app-secondary text-white gap-2"
                          >
                            {{ opt }}
                            <button
                              class="btn btn-xs btn-ghost text-white hover:bg-red-500"
                              @click="removeOption(q, oi)"
                            >
                              ×
                            </button>
                          </span>
                        </div>
                      </div>
                    </div>

                    <div class="flex flex-col gap-1">
                      <button
                        class="btn btn-xs btn-warning"
                        @click="duplicateQuestion(index)"
                        title="Duplicate"
                      >
                        📋
                      </button>
                      <button
                        class="btn btn-xs btn-error"
                        @click="removeQuestion(index)"
                        title="Delete"
                      >
                        🗑️
                      </button>
                    </div>
                  </div>
                </div>
              </template>
              <template #fallback>
                <div class="text-center p-8 text-text-muted">
                  <div class="text-4xl mb-2">📝</div>
                  <div>No additional questions yet</div>
                  <div class="text-sm">Click "Add Question" to get started</div>
                </div>
              </template>
            </draggable>

            <div class="mt-4 flex flex-col sm:flex-row gap-2">
              <button
                class="btn btn-primary flex items-center gap-2 justify-center"
                @click="saveForm"
                :disabled="!isFormValid"
              >
                <IconSave :size="16" />
                {{ isNew ? "Create Employment Tracer" : "Update Employment Tracer" }}
              </button>
              <button class="btn btn-outline flex items-center gap-2 justify-center" @click="cancelEdit">
                <IconX :size="16" />
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Form Preview component -->
    <FormPreview 
      v-if="preview && editing"
      :form="formModel" 
      :coreEmploymentQuestions="coreEmploymentQuestions"
    />
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import FormPreview from './FormPreview.vue'
import draggable from 'vuedraggable'
// Fix for UUID import issue
import * as uuid from 'uuid'
const uuidv4 = uuid.v4

const props = defineProps({
  form: {
    type: Object,
    required: true
  },
  isNew: {
    type: Boolean,
    default: true
  },
  editing: {
    type: Boolean,
    default: false
  },
  coreEmploymentQuestions: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['update:form', 'save', 'cancel', 'togglePreview'])

// Local form model that we'll edit (clone of props.form)
const formModel = ref({...props.form})

// Watch for changes to props.form and update local formModel
watch(() => props.form, (newForm) => {
  formModel.value = {...newForm}
}, { deep: true })

// Watch for changes to formModel and emit update:form event
watch(formModel, (newForm) => {
  emit('update:form', newForm)
}, { deep: true })

const preview = ref(false)

// Computed properties
const isFormValid = computed(() => {
  return formModel.value.title.trim() !== "" && 
    formModel.value.year >= 2020 && 
    formModel.value.year <= 2030;
})

const enabledEmploymentQuestions = computed(() => {
  // Get all the employment questions that are always included
  const alwaysIncluded = props.coreEmploymentQuestions.filter(q => q.always_include).map(q => q.id)
  
  // Combine with selected employment questions
  return [...new Set([...alwaysIncluded, ...formModel.value.selectedEmploymentQuestions])].length
})

// Functions
function togglePreview() {
  preview.value = !preview.value
  emit('togglePreview', preview.value)
}

function handleQuestionTypeChange(question) {
  if (['radio', 'checkbox', 'select'].includes(question.type) && !question.options) {
    question.options = []
  }
  if (!['radio', 'checkbox', 'select'].includes(question.type)) {
    delete question.options
  }
}

function addQuestion() {
  formModel.value.questions.push({
    id: uuidv4(),
    type: 'text',
    label: '',
    placeholder: ''
  })
}

function removeQuestion(index) {
  formModel.value.questions.splice(index, 1)
}

function duplicateQuestion(index) {
  const original = formModel.value.questions[index]
  const duplicate = JSON.parse(JSON.stringify(original))
  duplicate.id = uuidv4()
  formModel.value.questions.splice(index + 1, 0, duplicate)
}

function addOption(question) {
  if (!question.optionInput) return
  if (!question.options) question.options = []
  question.options.push(question.optionInput)
  question.optionInput = ''
}

function removeOption(question, index) {
  question.options.splice(index, 1)
}

function saveForm() {
  emit('save', formModel.value)
}

function cancelEdit() {
  emit('cancel')
}
</script>
