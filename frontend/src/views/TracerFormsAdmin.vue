<template>
  <Layout @logout="handleLogout">
    <div class="space-y-4 sm:space-y-6 p-3 sm:p-4">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0">
        <h2 class="text-xl sm:text-2xl font-bold">Employment Tracer Forms Management</h2>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
          <button class="btn btn-primary flex items-center gap-2 text-sm" @click="openCreate()">
            <IconPlus :size="16" />
            <span class="hidden sm:inline">Create New Employment Tracer</span>
            <span class="sm:hidden">Create New</span>
          </button>
          <button class="btn btn-outline flex items-center gap-2 text-sm" @click="fetchForms">
            <IconRefreshCw :size="16" />
            Refresh
          </button>
        </div>
      </div>

      <div class="space-y-4 sm:space-y-6">
        <!-- Form Builder / Editor (collapses when not editing) -->
        <div>
          <transition name="fade">
            <div v-if="editing" class="card app-surface shadow p-3 sm:p-4">
              <h3 class="font-semibold text-lg sm:text-xl mb-3 flex items-center gap-2">
                <IconFileText :size="20" />
                {{
                  isNew
                    ? "Create New Employment Tracer Form"
                    : "Edit Employment Tracer Form"
                }}
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
                      v-model="form.title"
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
                      v-model.number="form.year"
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
                      v-model="form.deadline"
                      type="date"
                      class="input input-bordered w-full"
                    />
                  </div>

                  <div class="flex items-center gap-2 pt-8">
                    <input
                      type="checkbox"
                      class="checkbox checkbox-primary"
                      v-model="form.is_active"
                    />
                    <label class="label-text flex items-center gap-2">
                      <IconCheck :size="16" />
                      <span class="text-sm">{{
                        form.is_active ? "Active" : "Inactive"
                      }}</span>
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
                    v-model="form.description"
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
                          v-model="form.selectedEmploymentQuestions"
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
                    v-model="form.questions"
                    item-key="id"
                    class="space-y-3"
                    :disabled="form.questions.length === 0"
                  >
                    <template #item="{ element: q, index }">
                      <div
                        class="card card-compact app-surface-alt p-4 app-border border"
                      >
                        <div class="flex justify-between items-start gap-3">
                          <div class="w-full space-y-3">
                            <div class="flex gap-3 items-center">
                              <span
                                class="badge badge-outline font-semibold text-medium-blue border-medium-blue"
                                >Q{{ index + 1 }}</span
                              >
                              <input
                                v-model="q.label"
                                class="input input-sm input-bordered app-surface app-border flex-1 text-text"
                                placeholder="Enter your question here..."
                              />
                            </div>

                            <div
                              class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3"
                            >
                              <select
                                v-model="q.type"
                                class="select select-sm select-bordered app-surface app-border text-text"
                                @change="handleQuestionTypeChange(q)"
                              >
                                <option value="text">Text Input</option>
                                <option value="textarea">
                                  Long Text (Textarea)
                                </option>
                                <option value="number">Number Input</option>
                                <option value="radio">
                                  Single Choice (Radio)
                                </option>
                                <option value="checkbox">
                                  Multiple Choice (Checkbox)
                                </option>
                                <option value="select">Dropdown Menu</option>
                              </select>

                              <input
                                v-if="
                                  ['text', 'textarea', 'number'].includes(
                                    q.type
                                  )
                                "
                                v-model="q.placeholder"
                                class="input input-sm input-bordered app-surface app-border text-text"
                                placeholder="Placeholder text (optional)"
                              />

                              <div
                                v-if="
                                  ['radio', 'checkbox', 'select'].includes(
                                    q.type
                                  )
                                "
                                class="flex gap-2"
                              >
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
                            <div
                              v-if="q.options && q.options.length"
                              class="mt-2"
                            >
                              <div class="text-sm text-text-muted mb-2">
                                Options:
                              </div>
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
                        <div class="text-sm">
                          Click "Add Question" to get started
                        </div>
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
                      {{
                        isNew
                          ? "Create Employment Tracer"
                          : "Update Employment Tracer"
                      }}
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

          <!-- Preview (rendered form) -->
          <transition name="fade">
            <div
              v-if="preview && editing"
              class="card app-surface shadow p-4 mt-4"
            >
              <h3 class="font-semibold text-lg mb-4 flex items-center">
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
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                  ></path>
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                  ></path>
                </svg>
                Form Preview: {{ form.title || "Untitled Form" }}
              </h3>

              <div class="space-y-6">
                <!-- Core Employment Section Preview -->
                <div
                  class="card app-secondary-bg border border-medium-blue p-4"
                >
                  <h4 class="font-semibold text-medium-blue mb-3">
                    Employment Information (Required)
                  </h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div
                      v-for="q in coreEmploymentQuestions.slice(0, 4)"
                      :key="q.id"
                      class="form-control"
                    >
                      <label class="label text-text">{{ q.label }}</label>
                      <input
                        class="input input-sm input-bordered app-input text-text"
                        :placeholder="q.placeholder || 'Preview only'"
                        disabled
                      />
                    </div>
                  </div>
                </div>

                <!-- Additional Questions Preview -->
                <div
                  v-if="form.questions.length"
                  class="card app-surface-hover border border-gray-300 p-4"
                >
                  <h4 class="font-semibold text-text mb-3">
                    Additional Survey Questions
                  </h4>
                  <div class="space-y-4">
                    <div
                      v-for="(q, i) in form.questions"
                      :key="q.id"
                      class="form-control"
                    >
                      <label class="label">
                        <span class="label-text"
                          >{{ i + 1 }}.
                          {{ q.label || "Untitled Question" }}</span
                        >
                      </label>
                      <div>
                        <input
                          v-if="q.type === 'text'"
                          class="input input-sm input-bordered w-full"
                          :placeholder="q.placeholder || 'Text input preview'"
                          disabled
                        />
                        <textarea
                          v-else-if="q.type === 'textarea'"
                          class="textarea textarea-sm textarea-bordered w-full"
                          :placeholder="q.placeholder || 'Textarea preview'"
                          rows="2"
                          disabled
                        ></textarea>
                        <input
                          v-else-if="q.type === 'number'"
                          type="number"
                          class="input input-sm input-bordered w-full"
                          :placeholder="q.placeholder || 'Number input'"
                          disabled
                        />
                        <div v-else-if="q.type === 'radio'" class="space-y-2">
                          <label
                            v-for="opt in q.options"
                            :key="opt"
                            class="flex items-center gap-2"
                          >
                            <input
                              type="radio"
                              disabled
                              class="radio radio-sm radio-primary"
                            />
                            <span>{{ opt }}</span>
                          </label>
                        </div>
                        <div
                          v-else-if="q.type === 'checkbox'"
                          class="space-y-2"
                        >
                          <label
                            v-for="opt in q.options"
                            :key="opt"
                            class="flex items-center gap-2"
                          >
                            <input
                              type="checkbox"
                              disabled
                              class="checkbox checkbox-sm checkbox-primary"
                            />
                            <span>{{ opt }}</span>
                          </label>
                        </div>
                        <select
                          v-else-if="q.type === 'select'"
                          class="select select-sm select-bordered w-full"
                          disabled
                        >
                          <option selected>
                            {{ q.options?.[0] || "Select an option" }}
                          </option>
                          <option v-for="opt in q.options?.slice(1)" :key="opt">
                            {{ opt }}
                          </option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </transition>
        </div>

        <!-- Employment Tracer Forms Table -->
        <div>
          <div class="card app-surface shadow p-3 sm:p-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-3">
              <h3 class="font-semibold text-lg flex items-center gap-2">
                <IconFileText :size="20" />
                Employment Tracer Forms
              </h3>
              <div class="text-sm text-gray-500 flex items-center gap-2">
                <IconBarChart3 :size="16" />
                Total: {{ forms.length }} forms
              </div>
            </div>

            <!-- Responsive Table with Horizontal Scroll -->
            <div class="overflow-x-auto">
              <table class="table table-zebra w-full min-w-[800px]">
                <thead>
                  <tr>
                    <th class="w-20">Year</th>
                    <th class="min-w-[200px]">Form Title</th>
                    <th class="w-24">Status</th>
                    <th class="w-32">Questions</th>
                    <th class="w-24">Responses</th>
                    <th class="w-20">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in forms" :key="item.form_id" class="hover">
                    <td>
                      <div class="flex items-center gap-2">
                        <span class="font-semibold">{{ item.form_year }}</span>
                        <span
                          v-if="item.is_active"
                          class="badge badge-primary badge-xs"
                          >Active</span
                        >
                      </div>
                    </td>
                    <td>
                      <div class="flex flex-col">
                        <span class="font-medium text-text">{{
                          item.form_title
                        }}</span>
                        <span
                          v-if="item.form_description"
                          class="text-xs text-text-muted truncate max-w-xs"
                        >
                          {{ item.form_description }}
                        </span>
                      </div>
                    </td>
                    <td>
                      <span
                        v-if="item.is_active"
                        class="badge badge-success gap-2"
                      >
                        <IconCheck :size="12" />
                        Active
                      </span>
                      <span v-else class="badge badge-ghost text-text"
                        >Inactive</span
                      >
                    </td>
                    <td>
                      <div class="flex flex-col text-sm">
                        <div class="flex items-center gap-1 mb-1">
                          <IconUsers :size="12" />
                          <span class="badge badge-outline badge-xs">
                            {{ getAdditionalQuestionsCount(item) }}
                          </span>
                          <span class="text-xs opacity-60">custom</span>
                        </div>
                        <div class="text-xs opacity-60">
                          + {{ getEmploymentQuestionsCount(item) }} employment
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="flex items-center gap-2">
                        <IconBarChart3 :size="14" />
                        <span class="badge badge-info badge-sm">
                          {{ responseCounts[item.form_id] || 0 }}
                        </span>
                        <button
                          v-if="responseCounts[item.form_id] > 0"
                          @click="viewResponses(item)"
                          class="btn btn-xs btn-ghost text-blue-500 hover:bg-blue-500 hover:text-white"
                        >
                          View
                        </button>
                      </div>
                    </td>
                    <td>
                      <div class="dropdown dropdown-end dropdown-hover">
                        <div
                          tabindex="0"
                          role="button"
                          class="btn btn-ghost btn-sm btn-circle"
                        >
                          <IconEllipsisV :size="16" />
                        </div>
                        <ul
                          tabindex="0"
                          class="dropdown-content menu p-1 shadow-lg bg-base-100 rounded-box w-36 border border-base-300 z-50"
                        >
                          <li>
                            <a
                              @click="viewForm(item)"
                              class="flex items-center gap-2 text-xs py-1"
                            >
                              <IconEye :size="14" />
                              Preview
                            </a>
                          </li>
                          <li>
                            <a
                              @click="loadForm(item)"
                              class="flex items-center gap-2 text-xs py-1"
                            >
                              <IconEdit :size="14" />
                              Edit
                            </a>
                          </li>
                          <li>
                            <a
                              @click="viewResponses(item)"
                              class="flex items-center gap-2 text-xs py-1"
                            >
                              <IconBarChart3 :size="14" />
                              Responses
                            </a>
                          </li>
                          <li>
                            <a
                              @click="duplicateForm(item)"
                              class="flex items-center gap-2 text-xs py-1"
                            >
                              <IconFileText :size="14" />
                              Duplicate
                            </a>
                          </li>
                          <li v-if="!item.is_active">
                            <a
                              @click="activateForm(item)"
                              class="flex items-center gap-2 text-success text-xs py-1"
                            >
                              <IconCheck :size="14" />
                              Activate
                            </a>
                          </li>
                          <li>
                            <a
                              @click="deleteForm(item)"
                              class="flex items-center gap-2 text-error text-xs py-1"
                            >
                              <IconTrash2 :size="14" />
                              Delete
                            </a>
                          </li>
                        </ul>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>

              <div v-if="forms.length === 0" class="text-center py-12">
                <IconFileText :size="48" class="mx-auto text-gray-400 mb-4" />
                <div class="text-lg font-medium text-text mb-2">
                  No Employment Tracer Forms Yet
                </div>
                <div class="text-sm text-text-muted mb-4">
                  Create your first employment tracer form to start collecting
                  alumni employment data.
                </div>
                <button
                  class="btn app-primary text-white flex items-center gap-2 mx-auto"
                  @click="openCreate()"
                >
                  <IconPlus :size="16" />
                  Create Your First Employment Tracer
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Preview Modal -->
        <dialog ref="viewFormModal" class="modal">
          <form method="dialog" class="modal-box w-11/12 max-w-5xl max-h-[90vh] overflow-y-auto">
            <h3 class="font-bold text-lg sm:text-xl mb-4 flex items-center gap-2">
              <IconFileText :size="24" class="text-primary" />
              <span class="truncate">{{ viewFormData.title }}</span>
            </h3>

            <!-- Form Details -->
            <div class="mb-6 p-3 sm:p-4 app-surface-hover rounded-lg">
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                <div class="flex items-center gap-2">
                  <IconBarChart3 :size="16" class="text-text-muted" />
                  <span class="font-semibold text-text">Year:</span>
                  <span class="text-text">{{ viewFormData.year }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <IconCheck :size="16" class="text-text-muted" />
                  <span class="font-semibold">Status:</span>
                  <span
                    v-if="viewFormData.is_active"
                    class="badge badge-success badge-sm ml-1"
                    >Active</span
                  >
                  <span v-else class="badge badge-ghost badge-sm ml-1 text-text"
                    >Inactive</span
                  >
                </div>
                <div
                  v-if="viewFormData.deadline"
                  class="flex items-center gap-2"
                >
                  <IconBarChart3 :size="16" class="text-text-muted" />
                  <span class="font-semibold text-text">Deadline:</span>
                  <span class="text-text text-xs sm:text-sm">{{
                    new Date(viewFormData.deadline).toLocaleDateString()
                  }}</span>
                </div>
              </div>
              <div v-if="viewFormData.description" class="mt-4">
                <span class="font-semibold text-text">Description:</span>
                <p class="mt-1 text-text">{{ viewFormData.description }}</p>
              </div>
            </div>

            <!-- Form Preview -->
            <div class="space-y-6">
              <!-- Core Employment Questions -->
              <div class="card app-secondary-bg border border-medium-blue p-4">
                <h4
                  class="font-semibold text-medium-blue mb-3 flex items-center"
                >
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
                  Core Employment Questions ({{
                    selectedEmploymentQuestionsForView.length
                  }})
                </h4>
                <div class="text-sm text-text mb-3">
                  These employment questions are included in this form:
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                  <div
                    v-for="(q, i) in selectedEmploymentQuestionsForView.slice(0, 6)"
                    :key="q.id"
                    class="p-3 app-surface rounded border-l-4 border-medium-blue"
                  >
                    <label
                      class="label label-text font-medium text-sm text-text"
                    >
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
                          <input
                            type="radio"
                            disabled
                            class="radio radio-xs radio-primary"
                          />
                          <span>{{ opt }}</span>
                        </label>
                        <div
                          v-if="q.options?.length > 3"
                          class="text-xs text-gray-400"
                        >
                          ... and {{ q.options.length - 3 }} more
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div
                  v-if="selectedEmploymentQuestionsForView.length > 6"
                  class="mt-3 text-sm text-gray-500"
                >
                  ... and {{ selectedEmploymentQuestionsForView.length - 6 }} more employment questions
                </div>
              </div>

              <!-- Additional Custom Questions -->
              <div
                v-if="viewFormData.questions?.length"
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
                  Additional Custom Questions ({{
                    viewFormData.questions.length
                  }})
                </h4>
                <div class="space-y-3">
                  <div
                    v-for="(q, i) in viewFormData.questions"
                    :key="q.id"
                    class="p-3 bg-white rounded"
                  >
                    <label class="label label-text font-medium text-sm">
                      {{ selectedEmploymentQuestionsForView.length + i + 1 }}.
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
                          <input
                            type="radio"
                            disabled
                            class="radio radio-xs radio-primary"
                          />
                          <span>{{ opt }}</span>
                        </label>
                      </div>
                      <div v-else-if="q.type === 'checkbox'" class="space-y-1">
                        <label
                          v-for="opt in q.options"
                          :key="opt"
                          class="flex items-center gap-2 text-xs"
                        >
                          <input
                            type="checkbox"
                            disabled
                            class="checkbox checkbox-xs checkbox-primary"
                          />
                          <span>{{ opt }}</span>
                        </label>
                      </div>
                      <select
                        v-else-if="q.type === 'select'"
                        class="select select-xs select-bordered w-full"
                        disabled
                      >
                        <option disabled selected>Select an option</option>
                        <option v-for="opt in q.options" :key="opt">
                          {{ opt }}
                        </option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else class="text-center py-8 text-gray-500">
                <svg
                  class="w-12 h-12 mx-auto mb-3 text-gray-300"
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
                <div class="text-sm">
                  No additional custom questions defined
                </div>
                <div class="text-xs text-gray-400">
                  This form only includes the core employment questions
                </div>
              </div>
            </div>

            <div class="modal-action flex flex-col sm:flex-row gap-2">
              <button class="btn btn-outline flex items-center gap-2" @click="loadForm(viewFormData)">
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
      </div>
    </div>

    <!-- Form Responses Component -->
    <FormResponses ref="formResponsesComponent" />
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import Layout from "@/components/Layout.vue";
import FormResponses from "@/components/FormResponses.vue";
import axios from "axios";
import draggable from "vuedraggable";
import { messageService } from "@/services/messageService.js";

// Configure axios (ensure backend is properly accessible)
axios.defaults.baseURL = "http://localhost/skillslink/backend";
axios.defaults.withCredentials = true;

// Router instance
const router = useRouter();

// State
const forms = ref([]);
const editing = ref(false);
const isNew = ref(true);
const preview = ref(false);
const responseCounts = ref({});

// Core employment questions - Employment Status is always included, others are configurable
const coreEmploymentQuestions = ref([
  {
    id: "employment_status",
    label: "Employment Status",
    type: "radio",
    maps_to: "employment_status",
    required: true,
    always_include: true,
    options: [
      "Employed",
      "Unemployed", 
      "Further Studies",
      "Not Looking",
    ],
  },
  {
    id: "date_employed",
    label: "Date Employed",
    type: "date",
    maps_to: "date_employed",
    conditional: 'employment_status == "employed"',
    optional: true,
  },
  {
    id: "name_of_employer",
    label: "Name of Employer",
    type: "text",
    maps_to: "company_name",
    conditional: 'employment_status == "employed"',
    placeholder: "Enter employer/company name",
    optional: true,
  },
  {
    id: "work_location",
    label: "Work Location",
    type: "text",
    maps_to: "work_location",
    conditional: 'employment_status == "employed"',
    placeholder: "City, Province/State, Country",
    optional: true,
  },
  {
    id: "work_classification",
    label: "Work Classification",
    type: "radio",
    maps_to: "work_classification",
    conditional: 'employment_status == "employed"',
    options: [
      "Wage and Salary Workers",
      "Self-employed without any paid employee",
      "Employer in Own Family-Operated Farm or Business",
      "Work Without Pay in Own-Family-Operated Farm or Business"
    ],
    optional: true,
  },
  {
    id: "is_local",
    label: "Local",
    type: "radio",
    maps_to: "is_local",
    conditional: 'employment_status == "employed"',
    options: ["Yes", "No"],
    optional: true,
  },
  {
    id: "is_abroad",
    label: "Abroad",
    type: "radio",
    maps_to: "is_abroad",
    conditional: 'employment_status == "employed"',
    options: ["Yes", "No"],
    optional: true,
  },
  {
    id: "company_name",
    label: "Company/Organization Name",
    type: "text",
    maps_to: "company_name",
    conditional: 'employment_status == "employed"',
    placeholder: "Enter company or organization name",
    optional: true,
  },
  {
    id: "job_title",
    label: "Job Title/Position",
    type: "text",
    maps_to: "job_title",
    conditional: 'employment_status == "employed"',
    placeholder: "Enter your job title",
    optional: true,
  },
  {
    id: "job_description",
    label: "Job Description",
    type: "textarea",
    maps_to: "job_description",
    conditional: 'employment_status == "employed"',
    placeholder: "Describe your job responsibilities",
    optional: true,
  },
  {
    id: "salary_range",
    label: "Salary Range",
    type: "select",
    maps_to: "salary_range",
    conditional: 'employment_status == "employed"',
    options: [
      "Below 20,000",
      "20,000-40,000",
      "40,000-60,000",
      "60,000-80,000",
      "80,000-100,000",
      "Above 100,000",
    ],
    optional: true,
  },
  {
    id: "employment_type",
    label: "Employment Type",
    type: "radio",
    maps_to: "employment_type",
    conditional: 'employment_status == "employed"',
    options: ["Full-time", "Part-time", "Contract", "Freelance", "Temporary"],
    optional: true,
  },
  {
    id: "industry",
    label: "Industry",
    type: "text",
    maps_to: "industry",
    conditional: 'employment_status == "employed"',
    placeholder: "e.g., Information Technology, Healthcare, Education",
    optional: true,
  },
  {
    id: "work_setup",
    label: "Work Setup",
    type: "radio",
    maps_to: "work_setup",
    conditional: 'employment_status == "employed"',
    options: ["On-site", "Remote", "Hybrid"],
    optional: true,
  },
  {
    id: "months_to_find_job",
    label: "Months it took to find this job after graduation",
    type: "number",
    maps_to: "months_to_find_job",
    conditional: 'employment_status == "employed"',
    min: 0,
    max: 60,
    optional: true,
  },
]);

const form = reactive({
  form_id: null,
  title: "",
  year: new Date().getFullYear(),
  description: "",
  deadline: null,
  is_active: true,
  questions: [], // These are additional questions beyond core employment
  selectedEmploymentQuestions: [], // IDs of employment questions to include
});

// Form Responses Component Reference
const formResponsesComponent = ref(null);

// Form view modal
const viewFormModal = ref(null);
const viewFormData = ref({});

// Computed properties
const isFormValid = computed(() => {
  return form.title.trim() !== "" && form.year >= 2020 && form.year <= 2030;
});

const enabledEmploymentQuestions = computed(() => {
  return coreEmploymentQuestions.value.filter(q => 
    q.always_include || form.selectedEmploymentQuestions.includes(q.id)
  );
});

const selectedEmploymentQuestionsForView = computed(() => {
  if (!viewFormData.value.selectedEmploymentQuestions) return [];
  return coreEmploymentQuestions.value.filter(q => 
    viewFormData.value.selectedEmploymentQuestions.includes(q.id)
  );
});

// Helper functions for parsing form questions in the table
const getDefaultSelectedEmploymentQuestions = () => {
  const alwaysInclude = coreEmploymentQuestions.value
    .filter(q => q.always_include)
    .map(q => q.id);
  
  // Add the requested fields that should be automatically checked by default
  const autoChecked = [
    "date_employed",
    "name_of_employer", 
    "work_location",
    "work_classification",
    "is_local",
    "is_abroad"
  ];
  
  // Combine and remove duplicates
  return [...new Set([...alwaysInclude, ...autoChecked])];
};

const parseFormQuestions = (formQuestionsData) => {
  const defaultSelected = getDefaultSelectedEmploymentQuestions();
  
  if (!formQuestionsData) return { additional_questions: [], selected_employment_questions: defaultSelected };
  
  if (typeof formQuestionsData === 'string') {
    try {
      const parsed = JSON.parse(formQuestionsData);
      if (Array.isArray(parsed)) {
        // Old format
        return { additional_questions: parsed, selected_employment_questions: defaultSelected };
      } else {
        // New format - ensure always_include questions are included
        const selectedQuestions = parsed.selected_employment_questions || defaultSelected;
        const uniqueSelected = [...new Set([...defaultSelected, ...selectedQuestions])];
        return { 
          additional_questions: parsed.additional_questions || [], 
          selected_employment_questions: uniqueSelected 
        };
      }
    } catch (e) {
      return { additional_questions: [], selected_employment_questions: defaultSelected };
    }
  } else if (Array.isArray(formQuestionsData)) {
    // Old format
    return { additional_questions: formQuestionsData, selected_employment_questions: defaultSelected };
  } else {
    // New format - ensure always_include questions are included
    const selectedQuestions = formQuestionsData.selected_employment_questions || defaultSelected;
    const uniqueSelected = [...new Set([...defaultSelected, ...selectedQuestions])];
    return { 
      additional_questions: formQuestionsData.additional_questions || [], 
      selected_employment_questions: uniqueSelected 
    };
  }
};

const getAdditionalQuestionsCount = (item) => {
  const parsed = parseFormQuestions(item.form_questions);
  return (parsed.additional_questions || []).length;
};

const getEmploymentQuestionsCount = (item) => {
  const parsed = parseFormQuestions(item.form_questions);
  const selectedIds = parsed.selected_employment_questions || ["employment_status"];
  return selectedIds.length;
};

// Form management helpers
const resetForm = () => {
  form.form_id = null;
  form.title = "";
  form.year = new Date().getFullYear();
  form.description = "";
  form.deadline = null;
  form.is_active = true;
  form.questions = [];
  // Initialize with all always_include questions and some commonly used optional questions by default
  const alwaysIncludeQuestions = coreEmploymentQuestions.value
    .filter(q => q.always_include)
    .map(q => q.id);
  
  form.selectedEmploymentQuestions = [
    ...alwaysIncludeQuestions,
    // Add the requested fields that should be automatically checked by default
    "date_employed",
    "name_of_employer", 
    "work_location",
    "work_classification",
    "is_local",
    "is_abroad",
    // Add some other commonly used optional questions by default
    "company_name",
    "job_title", 
    "salary_range",
    "employment_type"
  ];
};

const addQuestion = () => {
  form.questions.push({
    id: Date.now() + Math.random(),
    label: "",
    type: "text",
    placeholder: "",
    options: [],
    optionInput: "",
  });
};

const removeQuestion = (index) => {
  form.questions.splice(index, 1);
};

const duplicateQuestion = (index) => {
  const q = JSON.parse(JSON.stringify(form.questions[index]));
  q.id = Date.now() + Math.random();
  form.questions.splice(index + 1, 0, q);
};

const handleQuestionTypeChange = (question) => {
  // Reset options when changing question type
  if (!["radio", "checkbox", "select"].includes(question.type)) {
    question.options = [];
    question.optionInput = "";
  } else if (!question.options) {
    question.options = [];
  }

  // Reset placeholder for option-based questions
  if (["radio", "checkbox", "select"].includes(question.type)) {
    question.placeholder = "";
  }
};

const addOption = (q) => {
  if (!q.optionInput || !q.optionInput.trim()) return;
  q.options = q.options || [];
  q.options.push(q.optionInput.trim());
  q.optionInput = "";
};

const removeOption = (q, oi) => {
  q.options.splice(oi, 1);
};

const togglePreview = () => {
  preview.value = !preview.value;
};

// API calls
const fetchResponseCounts = async () => {
  try {
    const counts = {};
    for (const form of forms.value) {
      try {
        // Use admin tracer forms endpoint for counting
        const res = await axios.get(
          `/admin/tracer_forms.php?action=count&form_id=${form.form_id}`
        );
        counts[form.form_id] = res.data.count || 0;
      } catch (err) {
        console.error(`Error fetching count for form ${form.form_id}:`, err);
        counts[form.form_id] = 0;
      }
    }
    responseCounts.value = counts;
  } catch (err) {
    console.error("Error fetching response counts:", err);
  }
};

const fetchForms = async () => {
  try {
    console.log("Fetching employment tracer forms...");
    const res = await axios.get("/admin/tracer_forms.php?action=list");
    console.log("Forms list response:", res.data);

    const data = res.data;
    if (Array.isArray(data)) {
      // Filter out empty/invalid forms
      forms.value = data.filter((f) => f && f.form_id && f.form_title);
      console.log("Filtered forms:", forms.value);

      // Fetch response counts after loading forms
      await fetchResponseCounts();
    } else {
      console.error("Expected array but got:", data);
      forms.value = [];
    }
  } catch (err) {
    console.error("Error in fetchForms:", err);
    messageService.showMessage(
      "Failed to load employment tracer forms",
      "error"
    );
    forms.value = [];
  }
};

const openCreate = () => {
  resetForm();
  editing.value = true;
  isNew.value = true;
  preview.value = false;
};

const loadForm = async (item) => {
  try {
    console.log("Loading form for editing:", item);
    const res = await axios.get(
      `/admin/tracer_forms.php?action=get&form_id=${item.form_id}`
    );
    console.log("Load form response:", res.data);

    const d = res.data;

    if (d.success === false) {
      throw new Error(d.message || "Failed to load form");
    }

    // Populate form data
    form.form_id = d.form_id;
    form.title = d.form_title || "";
    form.year = d.form_year || new Date().getFullYear();
    form.description = d.form_description || "";
    form.deadline = d.deadline_date || null;
    form.is_active = !!d.is_active;

    // Handle form questions - now contains both additional questions and selected employment questions
    const formQuestionsData = parseFormQuestions(d.form_questions);

    // Set the form data
    form.questions = formQuestionsData.additional_questions || [];
    form.selectedEmploymentQuestions = formQuestionsData.selected_employment_questions;

    editing.value = true;
    isNew.value = false;
    console.log("Form loaded successfully for editing");
  } catch (err) {
    console.error("Error loading form:", err);
    messageService.showMessage(
      `Failed to load form for editing: ${
        err.message || err.response?.data?.message || "Unknown error"
      }`,
      "error"
    );
  }
};

const saveForm = async () => {
  if (!isFormValid.value) {
    messageService.showMessage(
      "Please provide a valid title and year",
      "error"
    );
    return;
  }

  // Prepare payload - store both additional questions and selected employment questions in form_questions JSON
  const formData = {
    additional_questions: form.questions, // Custom questions added by admin
    selected_employment_questions: form.selectedEmploymentQuestions // IDs of employment questions to show
  };
  
  const payload = {
    form_id: form.form_id,
    form_title: form.title,
    form_year: form.year,
    form_description: form.description,
    form_questions: JSON.stringify(formData), // Combined data
    deadline_date: form.deadline,
    is_active: form.is_active,
  };

  console.log("Sending payload:", payload);

  try {
    const res = await axios.post("/admin/tracer_forms.php", payload);
    console.log("Response:", res.data);

    if (res.data.success) {
      const message = isNew.value
        ? "Employment tracer form created successfully!"
        : "Employment tracer form updated successfully!";
      messageService.showMessage(message, "success");
      editing.value = false;

      try {
        await fetchForms();
      } catch (fetchError) {
        console.error("Error refreshing forms list:", fetchError);
      }
    } else {
      messageService.showMessage(
        res.data.message || "Failed to save form",
        "error"
      );
    }
  } catch (err) {
    console.error("Error details:", err.response || err);
    messageService.showMessage(
      `Error saving form: ${err.response?.data?.message || err.message}`,
      "error"
    );
  }
};

const cancelEdit = () => {
  editing.value = false;
  preview.value = false;
};

const duplicateForm = async (item) => {
  try {
    console.log("Attempting to duplicate form:", item);
    const res = await axios.post("/admin/tracer_forms.php?action=duplicate", {
      form_id: item.form_id,
    });
    console.log("Duplicate response:", res.data);

    if (res.data.success) {
      await fetchForms();
      messageService.showMessage(
        "Employment tracer form duplicated successfully",
        "success"
      );
    } else {
      messageService.showMessage(
        `Failed to duplicate: ${res.data.message || "Unknown error"}`,
        "error"
      );
    }
  } catch (err) {
    console.error("Error duplicating form:", err);

    let errorMessage = "Failed to duplicate form";
    if (err.response?.status === 401) {
      errorMessage = "Authentication failed. Please log in again.";
    } else if (err.response?.status === 403) {
      errorMessage = "Access denied. Admin permissions required.";
    } else if (err.response?.data?.message) {
      errorMessage = err.response.data.message;
    }

    messageService.showMessage(errorMessage, "error");
  }
};

const deleteForm = async (item) => {
  if (
    !confirm(
      `Delete "${item.form_title}" and all responses? This cannot be undone.`
    )
  )
    return;

  try {
    const res = await axios.post("/admin/tracer_forms.php?action=delete", {
      form_id: item.form_id,
    });

    if (res.data.success) {
      await fetchForms();
      messageService.showMessage(
        "Employment tracer form deleted successfully",
        "success"
      );
    } else {
      messageService.showMessage("Failed to delete form", "error");
    }
  } catch (err) {
    console.error(err);
    messageService.showMessage("Failed to delete form", "error");
  }
};

const activateForm = async (item) => {
  if (
    !confirm(
      `Make "${item.form_title}" the active employment tracer form? This will deactivate all other forms.`
    )
  ) {
    return;
  }

  try {
    const res = await axios.post("/admin/tracer_forms.php?action=activate", {
      form_id: item.form_id,
    });

    if (res.data.success) {
      await fetchForms();
      messageService.showMessage(
        `"${item.form_title}" is now the active employment tracer form`,
        "success"
      );
    } else {
      messageService.showMessage("Failed to activate form", "error");
    }
  } catch (err) {
    console.error(err);
    messageService.showMessage("Failed to activate form", "error");
  }
};

const viewResponses = async (item) => {
  // Use the FormResponses component method
  if (formResponsesComponent.value) {
    formResponsesComponent.value.viewResponses(item);
  }
};

const viewForm = async (item) => {
  try {
    console.log("Attempting to view form:", item);

    const url = `/admin/tracer_forms.php?action=get&form_id=${item.form_id}`;
    const res = await axios.get(url);
    console.log("Response received:", res.data);

    const d = res.data;

    if (d.success === false) {
      console.error("API returned error:", d.message);
      messageService.showMessage(`Error: ${d.message}`, "error");
      return;
    }

    if (!d.form_id || !d.form_title) {
      console.error("Invalid form data received:", d);
      messageService.showMessage(
        "Invalid form data received from server",
        "error"
      );
      return;
    }

    // Prepare view data with both core and additional questions
    const parsedQuestions = parseFormQuestions(d.form_questions);
    viewFormData.value = {
      form_id: d.form_id,
      title: d.form_title,
      year: d.form_year,
      description: d.form_description || "",
      deadline: d.deadline_date || null,
      is_active: !!d.is_active,
      coreQuestions: coreEmploymentQuestions.value,
      questions: parsedQuestions.additional_questions,
      selectedEmploymentQuestions: parsedQuestions.selected_employment_questions,
    };

    console.log("View form data set:", viewFormData.value);
    viewFormModal.value.showModal();
  } catch (err) {
    console.error("Error loading form for view:", err);

    let errorMessage = "Failed to load form details";
    if (err.response?.status === 401) {
      errorMessage = "Authentication failed. Please log in again.";
    } else if (err.response?.status === 403) {
      errorMessage = "Access denied. Admin permissions required.";
    } else if (err.response?.data?.message) {
      errorMessage = err.response.data.message;
    }

    messageService.showMessage(errorMessage, "error");
  }
};

onMounted(() => {
  console.log("TracerFormsAdmin mounted, fetching forms...");
  fetchForms();
});

const handleLogout = () => {
  router.push("/home");
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Dropdown styling - clean and simple */
.dropdown {
  position: relative;
}

.dropdown-content {
  position: absolute !important;
  right: 0 !important;
  top: 100% !important;
  margin-top: 0.25rem;
  z-index: 50 !important;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.dropdown-end .dropdown-content {
  right: 0;
  left: auto;
}

/* Table styling improvements */
.table {
  table-layout: auto;
}

.table th,
.table td {
  padding: 0.75rem 0.5rem;
  vertical-align: middle;
}

.table td:last-child {
  width: 60px;
  text-align: center;
}

/* Container styling */
.card {
  overflow: visible !important;
}

.overflow-x-auto {
  overflow-x: auto;
  overflow-y: visible;
}

/* Mobile responsive styling */
@media (max-width: 640px) {
  .table th,
  .table td {
    padding: 0.5rem 0.25rem;
    font-size: 0.875rem;
  }
  
  .btn-xs {
    font-size: 0.75rem;
    padding: 0.125rem 0.5rem;
  }
  
  .badge-xs {
    font-size: 0.625rem;
  }
  
  .dropdown-content li a {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
  }
  
  /* Form input responsive styling */
  .input,
  .textarea,
  .select {
    font-size: 1rem; /* Prevents zoom on iOS */
  }
  
  .grid {
    gap: 1rem;
  }
}
</style>
