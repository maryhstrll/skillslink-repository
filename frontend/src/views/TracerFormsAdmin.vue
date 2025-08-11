<template>
  <Layout @logout="handleLogout">
    <div class="space-y-6 p-4">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold">Tracer Forms Management</h2>
        <div>
          <button class="btn btn-primary mr-2" @click="openCreate()">
            Create New Form
          </button>
          <button class="btn btn-outline" @click="fetchForms">Refresh</button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Left: Form Builder / Editor (collapses when not editing) -->
        <div class="col-span-2">
          <transition name="fade">
            <div v-if="editing" class="card bg-base-100 shadow p-4">
              <h3 class="font-semibold text-lg mb-3">
                {{ isNew ? "Create New Form" : "Edit Form" }}
              </h3>

              <div class="space-y-3">
                <label class="label">Title</label>
                <input
                  v-model="form.title"
                  class="input input-bordered w-full"
                  placeholder="e.g. Alumni Tracer Form 2025"
                />

                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                  <div>
                    <label class="label">Year</label>
                    <input
                      v-model.number="form.year"
                      type="number"
                      class="input input-bordered w-full"
                    />
                  </div>

                  <div>
                    <label class="label">Deadline</label>
                    <input
                      v-model="form.deadline"
                      type="date"
                      class="input input-bordered w-full"
                    />
                  </div>

                  <div>
                    <label class="label">Active</label>
                    <input
                      type="checkbox"
                      class="checkbox"
                      v-model="form.is_active"
                    />
                  </div>
                </div>

                <label class="label">Description</label>
                <textarea
                  v-model="form.description"
                  class="textarea textarea-bordered w-full"
                  rows="3"
                ></textarea>

                <!-- Questions builder -->
                <div>
                  <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium">Questions</h4>
                    <div>
                      <button
                        class="btn btn-sm btn-success"
                        @click="addQuestion"
                      >
                        + Add Question
                      </button>
                      <button
                        class="btn btn-sm btn-ghost ml-2"
                        @click="togglePreview"
                      >
                        Preview
                      </button>
                    </div>
                  </div>

                  <draggable
                    v-model="form.questions"
                    item-key="id"
                    class="space-y-2"
                  >
                    <template #item="{ element: q, index }">
                      <div class="card card-compact bg-base-200 p-3">
                        <div class="flex justify-between items-start gap-2">
                          <div class="w-full">
                            <div class="flex gap-2 items-center">
                              <span class="font-semibold"
                                >Q{{ index + 1 }}.</span
                              >
                              <input
                                v-model="q.label"
                                class="input input-sm input-bordered w-full"
                                placeholder="Question text"
                              />
                            </div>

                            <div
                              class="grid grid-cols-1 md:grid-cols-3 gap-2 mt-2"
                            >
                              <select
                                v-model="q.type"
                                class="select select-sm select-bordered"
                              >
                                <option value="text">Text</option>
                                <option value="textarea">Textarea</option>
                                <option value="radio">
                                  Single choice (radio)
                                </option>
                                <option value="checkbox">
                                  Multiple choice (checkbox)
                                </option>
                                <option value="select">Dropdown</option>
                              </select>

                              <input
                                v-if="
                                  q.type === 'text' || q.type === 'textarea'
                                "
                                v-model="q.placeholder"
                                class="input input-sm input-bordered"
                                placeholder="Placeholder (optional)"
                              />

                              <input
                                v-if="
                                  q.type === 'select' ||
                                  q.type === 'radio' ||
                                  q.type === 'checkbox'
                                "
                                v-model="q.optionInput"
                                @keyup.enter.prevent="addOption(q)"
                                class="input input-sm input-bordered"
                                placeholder="Type option then press Enter"
                              />
                            </div>

                            <div
                              v-if="q.options && q.options.length"
                              class="mt-2"
                            >
                              <div class="flex flex-wrap gap-2">
                                <span
                                  v-for="(opt, oi) in q.options"
                                  :key="oi"
                                  class="badge badge-outline"
                                >
                                  {{ opt }}
                                  <button
                                    class="btn btn-xs btn-ghost ml-2"
                                    @click="removeOption(q, oi)"
                                  >
                                    ×
                                  </button>
                                </span>
                              </div>
                            </div>
                          </div>

                          <div class="flex flex-col gap-2 ml-2">
                            <button
                              class="btn btn-xs btn-warning"
                              @click="duplicateQuestion(index)"
                            >
                              Dup
                            </button>
                            <button
                              class="btn btn-xs btn-error"
                              @click="removeQuestion(index)"
                            >
                              Del
                            </button>
                          </div>
                        </div>
                      </div>
                    </template>
                  </draggable>

                  <div class="mt-3 flex gap-2">
                    <button class="btn btn-primary" @click="saveForm">
                      Save
                    </button>
                    <button class="btn btn-outline" @click="cancelEdit">
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
              class="card bg-base-100 shadow p-4 mt-3"
            >
              <h3 class="font-semibold">Form Preview</h3>
              <div class="mt-3 space-y-3">
                <h4 class="font-medium">
                  {{ form.title }}
                  <span class="text-sm text-muted">({{ form.year }})</span>
                </h4>
                <p class="text-sm text-muted">{{ form.description }}</p>

                <div v-for="(q, i) in form.questions" :key="q.id" class="mt-2">
                  <label class="label"
                    ><span class="label-text"
                      >{{ i + 1 }}. {{ q.label }}</span
                    ></label
                  >
                  <div>
                    <input
                      v-if="q.type === 'text'"
                      class="input input-bordered w-full"
                      :placeholder="q.placeholder"
                      disabled
                    />
                    <textarea
                      v-if="q.type === 'textarea'"
                      class="textarea textarea-bordered w-full"
                      :placeholder="q.placeholder"
                      disabled
                    ></textarea>
                    <div v-if="q.type === 'radio'">
                      <label
                        v-for="opt in q.options"
                        :key="opt"
                        class="flex items-center gap-2"
                        ><input type="radio" disabled />
                        <span>{{ opt }}</span></label
                      >
                    </div>
                    <div v-if="q.type === 'checkbox'">
                      <label
                        v-for="opt in q.options"
                        :key="opt"
                        class="flex items-center gap-2"
                        ><input type="checkbox" disabled />
                        <span>{{ opt }}</span></label
                      >
                    </div>
                    <select
                      v-if="q.type === 'select'"
                      class="select select-bordered w-full"
                      disabled
                    >
                      <option v-for="opt in q.options" :key="opt">
                        {{ opt }}
                      </option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </transition>
        </div>

        <!-- Right: Past Forms Table -->
        <div class="col-span-1">
          <div class="card bg-base-100 shadow p-3">
            <h3 class="font-semibold mb-2">Past & Active Forms</h3>

            <div class="overflow-x-auto">
              <table class="table table-zebra w-full">
                <thead>
                  <tr>
                    <th>Year</th>
                    <th>Title</th>
                    <th>Active</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in forms" :key="item.form_id">
                    <td>{{ item.form_year }}</td>
                    <td>{{ item.form_title }}</td>
                    <td>
                      <span v-if="item.is_active" class="badge badge-success"
                        >Active</span
                      >
                      <span v-else class="badge badge-ghost">Inactive</span>
                    </td>
                    <td class="flex gap-1">
                      <button class="btn btn-xs" @click="loadForm(item)">
                        Edit
                      </button>
                      <button
                        class="btn btn-xs btn-ghost"
                        @click="viewResponses(item)"
                      >
                        Responses
                      </button>
                      <button
                        class="btn btn-xs btn-accent"
                        @click="duplicateForm(item)"
                      >
                        Duplicate
                      </button>
                      <button
                        class="btn btn-xs btn-error"
                        @click="deleteForm(item)"
                      >
                        Delete
                      </button>
                      <button
                        v-if="!item.is_active"
                        class="btn btn-xs btn-primary"
                        @click="activateForm(item)"
                      >
                        Make Active
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="mt-3 flex justify-between items-center">
              <div class="text-sm text-muted">
                Showing {{ forms.length }} forms
              </div>
              <div>
                <!-- pagination placeholder -->
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Responses Modal (simple) -->
      <dialog ref="responsesModal" class="modal">
        <form method="dialog" class="modal-box w-11/12 max-w-5xl">
          <h3 class="font-bold">Responses for {{ modalFormTitle }}</h3>
          <div class="mt-3">
            <div v-if="responses.length">
              <div
                v-for="r in responses"
                :key="r.response_id"
                class="card p-3 mb-2"
              >
                <div class="flex justify-between">
                  <div>
                    <div class="text-sm font-semibold">
                      {{ r.alumni_name || "Alumni #" + r.alumni_id }}
                    </div>
                    <div class="text-xs text-muted">
                      Submitted: {{ r.submitted_at }}
                    </div>
                  </div>
                  <div class="text-sm">
                    Completion: {{ r.completion_percentage }}%
                  </div>
                </div>
                <pre class="mt-2 text-xs bg-base-200 p-2 rounded">{{
                  r.responses
                }}</pre>
              </div>
            </div>
            <p v-else>No responses yet.</p>
          </div>
          <div class="modal-action">
            <button class="btn">Close</button>
          </div>
        </form>
      </dialog>
    </div>
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useRouter } from "vue-router";
import Layout from "@/components/Layout.vue";
import axios from "axios";
import draggable from "vuedraggable";

// Router instance
const router = useRouter();

// state
const forms = ref([]);
const editing = ref(false);
const isNew = ref(true);
const preview = ref(false);

const form = reactive({
  form_id: null,
  title: "",
  year: new Date().getFullYear(),
  description: "",
  deadline: null,
  is_active: true,
  questions: [],
});

const responsesModal = ref(null);
const responses = ref([]);
const modalFormTitle = ref("");

// helpers
const resetForm = () => {
  form.form_id = null;
  form.title = "";
  form.year = new Date().getFullYear();
  form.description = "";
  form.deadline = null;
  form.is_active = true;
  form.questions = [];
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

const addOption = (q) => {
  if (!q.optionInput) return;
  q.options = q.options || [];
  q.options.push(q.optionInput);
  q.optionInput = "";
};

const removeOption = (q, oi) => {
  q.options.splice(oi, 1);
};

const togglePreview = () => {
  preview.value = !preview.value;
};

// API calls
const fetchForms = async () => {
  try {
    const res = await axios.get("/admin/tracer_forms.php?action=list");
    forms.value = res.data || [];
  } catch (err) {
    console.error(err);
    alert("Failed to load forms");
  }
};

const openCreate = () => {
  resetForm();
  editing.value = true;
  isNew.value = true;
  preview.value = false;
};

const loadForm = async (item) => {
  // load into editor
  try {
    const res = await axios.get(
      `/admin/tracer_forms.php?action=get&form_id=${item.form_id}`
    );
    const d = res.data;
    form.form_id = d.form_id;
    form.title = d.form_title;
    form.year = d.form_year;
    form.description = d.form_description;
    form.deadline = d.deadline_date || null;
    form.is_active = !!d.is_active;
    form.questions = JSON.parse(d.form_questions || "[]");

    editing.value = true;
    isNew.value = false;
  } catch (err) {
    console.error(err);
    alert("Failed to load form for editing");
  }
};

const saveForm = async () => {
  // validate
  if (!form.title || !form.year) {
    alert("Please provide title and year");
    return;
  }
  // prepare payload
  const payload = {
    form_id: form.form_id,
    form_title: form.title,
    form_year: form.year,
    form_description: form.description,
    form_questions: JSON.stringify(form.questions),
    deadline_date: form.deadline,
    is_active: form.is_active,
  };

  try {
    const res = await axios.post("/admin/tracer_forms.php", payload);
    if (res.data.success) {
      alert("Form saved");
      editing.value = false;
      fetchForms();
    } else {
      alert(res.data.message || "Failed to save");
    }
  } catch (err) {
    console.error(err);
    alert("Error saving form");
  }
};

const cancelEdit = () => {
  editing.value = false;
};

const duplicateForm = async (item) => {
  try {
    const res = await axios.post("/admin/tracer_forms.php?action=duplicate", {
      form_id: item.form_id,
    });
    if (res.data.success) {
      fetchForms();
      alert("Form duplicated");
    }
  } catch (err) {
    console.error(err);
    alert("Failed");
  }
};

const deleteForm = async (item) => {
  if (!confirm("Delete this form and all responses?")) return;
  try {
    const res = await axios.post("/admin/tracer_forms.php?action=delete", {
      form_id: item.form_id,
    });
    if (res.data.success) fetchForms();
    else alert("Delete failed");
  } catch (err) {
    console.error(err);
    alert("Delete failed");
  }
};

const activateForm = async (item) => {
  if (!confirm("Make this form active? This will deactivate other forms."))
    return;
  try {
    const res = await axios.post("/admin/tracer_forms.php?action=activate", {
      form_id: item.form_id,
    });
    if (res.data.success) fetchForms();
    else alert("Activate failed");
  } catch (err) {
    console.error(err);
    alert("Activate failed");
  }
};

const viewResponses = async (item) => {
  modalFormTitle.value = item.form_title;
  responses.value = [];
  try {
    const res = await axios.get(
      `/admin/form_responses.php?action=list&form_id=${item.form_id}`
    );
    responses.value = res.data || [];
    responsesModal.value.showModal();
  } catch (err) {
    console.error(err);
    alert("Failed to fetch responses");
  }
};

onMounted(() => {
  fetchForms();
});

const handleLogout = () => {
  router.push('/home')
}
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
</style>
