<template>
  <div class="card app-surface shadow p-4 mt-4">
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
      <div class="card app-secondary-bg border border-medium-blue p-4">
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
              <span class="label-text">{{ i + 1 }}. {{ q.label || "Untitled Question" }}</span>
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
</template>

<script setup>
defineProps({
  form: {
    type: Object,
    required: true
  },
  coreEmploymentQuestions: {
    type: Array,
    required: true
  }
})
</script>
