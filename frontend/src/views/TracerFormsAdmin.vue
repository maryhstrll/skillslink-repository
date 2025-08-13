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

      <div class="space-y-6">
        <!-- Form Builder / Editor (collapses when not editing) -->
        <div>
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

        <!-- Past Forms Table -->
        <div>
          <div class="card bg-base-100 shadow p-3">
            <h3 class="font-semibold mb-2">Past & Active Forms</h3>

            <div class="overflow-x-auto">
              <table class="table table-zebra w-full">
                <thead>
                  <tr>
                    <th>Year</th>
                    <th>Title</th>
                    <th>Active</th>
                    <th>Responses</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in forms" :key="item.form_id">
                    <td>{{ item.form_year }}</td>
                    <td>
                      <a
                        @click="viewForm(item)"
                        class="text-primary hover:text-primary-focus cursor-pointer underline"
                      >
                        {{ item.form_title }}
                      </a>
                    </td>
                    <td>
                      <span v-if="item.is_active" class="badge badge-success"
                        >Active</span
                      >
                      <span v-else class="badge badge-ghost">Inactive</span>
                    </td>
                    <td>
                      <span class="badge badge-outline">
                        {{ responseCounts[item.form_id] || 0 }}
                      </span>
                    </td>
                    <td>
                      <div class="dropdown dropdown-end">
                        <div
                          tabindex="0"
                          role="button"
                          class="btn btn-ghost btn-sm"
                        >
                          <IconEllipsisV class="w-4 h-4" />
                        </div>
                        <ul
                          tabindex="0"
                          class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52"
                        >
                          <li>
                            <a @click="loadForm(item)">
                              <IconEdit class="w-4 h-4" />
                              Edit
                            </a>
                          </li>
                          <li>
                            <a @click="viewResponses(item)">
                              <IconEye class="w-4 h-4" />
                              View Responses
                            </a>
                          </li>
                          <li>
                            <a @click="duplicateForm(item)">
                              <IconFileText class="w-4 h-4" />
                              Duplicate
                            </a>
                          </li>
                          <li v-if="!item.is_active">
                            <a @click="activateForm(item)">
                              <IconCheck class="w-4 h-4" />
                              Make Active
                            </a>
                          </li>
                          <li>
                            <a @click="deleteForm(item)" class="text-error">
                              <IconTrash2 class="w-4 h-4" />
                              Delete
                            </a>
                          </li>
                        </ul>
                      </div>
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

      <!-- View Form Modal -->
      <dialog ref="viewFormModal" class="modal">
        <form method="dialog" class="modal-box w-11/12 max-w-4xl">
          <h3 class="font-bold text-lg mb-4">{{ viewFormData.title }}</h3>

          <!-- Form Details -->
          <div class="mb-4 p-4 bg-base-200 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
              <div>
                <span class="font-semibold">Year:</span> {{ viewFormData.year }}
              </div>
              <div>
                <span class="font-semibold">Status:</span>
                <span
                  v-if="viewFormData.is_active"
                  class="badge badge-success badge-sm ml-2"
                  >Active</span
                >
                <span v-else class="badge badge-ghost badge-sm ml-2"
                  >Inactive</span
                >
              </div>
              <div v-if="viewFormData.deadline">
                <span class="font-semibold">Deadline:</span>
                {{ viewFormData.deadline }}
              </div>
            </div>
            <div v-if="viewFormData.description" class="mt-3">
              <span class="font-semibold">Description:</span>
              <p class="mt-1 text-gray-600">{{ viewFormData.description }}</p>
            </div>
          </div>

          <!-- Form Preview -->
          <div class="space-y-4">
            <h4 class="font-semibold">Form Questions:</h4>
            <div
              v-for="(q, i) in viewFormData.questions"
              :key="q.id"
              class="p-3 bg-base-100 rounded-lg"
            >
              <label class="label">
                <span class="label-text font-medium"
                  >{{ i + 1 }}. {{ q.label }}</span
                >
              </label>
              <div>
                <input
                  v-if="q.type === 'text'"
                  class="input input-bordered w-full"
                  :placeholder="q.placeholder || 'Text input'"
                  disabled
                />
                <textarea
                  v-if="q.type === 'textarea'"
                  class="textarea textarea-bordered w-full"
                  :placeholder="q.placeholder || 'Textarea input'"
                  rows="3"
                  disabled
                ></textarea>
                <div v-if="q.type === 'radio'" class="space-y-2">
                  <label
                    v-for="opt in q.options"
                    :key="opt"
                    class="flex items-center gap-2"
                  >
                    <input type="radio" disabled class="radio radio-sm" />
                    <span>{{ opt }}</span>
                  </label>
                </div>
                <div v-if="q.type === 'checkbox'" class="space-y-2">
                  <label
                    v-for="opt in q.options"
                    :key="opt"
                    class="flex items-center gap-2"
                  >
                    <input
                      type="checkbox"
                      disabled
                      class="checkbox checkbox-sm"
                    />
                    <span>{{ opt }}</span>
                  </label>
                </div>
                <select
                  v-if="q.type === 'select'"
                  class="select select-bordered w-full"
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

          <div class="modal-action">
            <button class="btn">Close</button>
          </div>
        </form>
      </dialog>
    </div>

    <!-- Form Responses Component -->
    <FormResponses ref="formResponsesComponent" />
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useRouter } from "vue-router";
import Layout from "@/components/Layout.vue";
import FormResponses from "@/components/FormReponses.vue";
import axios from "axios";
import draggable from "vuedraggable";
import { messageService } from "@/services/messageService.js";

// Configure axios (ensure backend is properly accessible)
axios.defaults.baseURL = "http://localhost/skillslink/backend";
axios.defaults.withCredentials = true;

// Router instance
const router = useRouter();

// state
const forms = ref([]);
const editing = ref(false);
const isNew = ref(true);
const preview = ref(false);
const responseCounts = ref({});

const form = reactive({
  form_id: null,
  title: "",
  year: new Date().getFullYear(),
  description: "",
  deadline: null,
  is_active: true,
  questions: [],
});

// Form Responses Component Reference
const formResponsesComponent = ref(null);

// Form view modal
const viewFormModal = ref(null);
const viewFormData = ref({});

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
const fetchResponseCounts = async () => {
  try {
    const counts = {};
    for (const form of forms.value) {
      try {
        const res = await axios.get(
          `/admin/form_responses.php?action=count&form_id=${form.form_id}`
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
    console.log("Fetching forms list..."); // Debug log
    const res = await axios.get("/admin/tracer_forms.php?action=list");
    console.log("Forms list response:", res.data); // Debug log

    // Ensure res.data is an array before filtering
    const data = res.data;
    if (Array.isArray(data)) {
      // Filter out empty/invalid forms (e.g., missing form_id or form_title)
      forms.value = data.filter((f) => f && f.form_id && f.form_title);
      console.log("Filtered forms:", forms.value); // Debug log

      // Fetch response counts after loading forms
      await fetchResponseCounts();
    } else {
      console.error("Expected array but got:", data);
      forms.value = [];
    }
  } catch (err) {
    console.error("Error in fetchForms:", err);
    alert("Failed to load forms");
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
  // load into editor
  try {
    console.log("Loading form for editing:", item); // Debug log
    const res = await axios.get(
      `/admin/tracer_forms.php?action=get&form_id=${item.form_id}`
    );
    console.log("Load form response:", res.data); // Debug log
    
    const d = res.data;
    
    // Check if response has success property and handle accordingly
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
    
    // Handle questions - ensure it's an array
    if (typeof d.form_questions === 'string') {
      form.questions = JSON.parse(d.form_questions || "[]");
    } else if (Array.isArray(d.form_questions)) {
      form.questions = d.form_questions;
    } else {
      form.questions = [];
    }

    editing.value = true;
    isNew.value = false;
    console.log("Form loaded successfully for editing"); // Debug log
  } catch (err) {
    console.error("Error loading form:", err);
    console.error("Response data:", err.response?.data);
    alert(`Failed to load form for editing: ${err.message || err.response?.data?.message || "Unknown error"}`);
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

  console.log("Sending payload:", payload); // Debug log

  try {
    const res = await axios.post("/admin/tracer_forms.php", payload);
    console.log("Response:", res.data); // Debug log
    if (res.data.success) {
      alert("Form saved");
      editing.value = false;
      // Safely refresh the forms list
      try {
        await fetchForms();
      } catch (fetchError) {
        console.error("Error refreshing forms list:", fetchError);
        // Don't show error to user, form was saved successfully
      }
    } else {
      alert(res.data.message || "Failed to save");
    }
  } catch (err) {
    console.error("Error details:", err.response || err);
    alert(`Error saving form: ${err.response?.data?.message || err.message}`);
  }
};

const cancelEdit = () => {
  editing.value = false;
};

const duplicateForm = async (item) => {
  try {
    console.log("Attempting to duplicate form:", item); // Debug log
    const res = await axios.post("/admin/tracer_forms.php?action=duplicate", {
      form_id: item.form_id,
    });
    console.log("Duplicate response:", res.data); // Debug log
    
    if (res.data.success) {
      await fetchForms();
      alert("Form duplicated successfully");
    } else {
      alert(`Failed to duplicate: ${res.data.message || "Unknown error"}`);
    }
  } catch (err) {
    console.error("Error duplicating form:", err);
    console.error("Response data:", err.response?.data);
    
    let errorMessage = "Failed to duplicate form";
    if (err.response?.status === 401) {
      errorMessage = "Authentication failed. Please log in again.";
    } else if (err.response?.status === 403) {
      errorMessage = "Access denied. Admin permissions required.";
    } else if (err.response?.data?.message) {
      errorMessage = err.response.data.message;
    } else if (err.message) {
      errorMessage = err.message;
    }
    
    alert(errorMessage);
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
  // Use the FormResponses component method
  if (formResponsesComponent.value) {
    formResponsesComponent.value.viewResponses(item);
  }
};

const viewForm = async (item) => {
  try {
    console.log("Attempting to view form:", item); // Debug log

    // First check if we can reach the backend
    const url = `/admin/tracer_forms.php?action=get&form_id=${item.form_id}`;
    console.log("Request URL:", url); // Debug log
    console.log("Full URL:", axios.defaults.baseURL + url); // Debug log

    const res = await axios.get(url);
    console.log("Response status:", res.status); // Debug log
    console.log("Response received:", res.data); // Debug log

    const d = res.data;

    // Check if we got an error response
    if (d.success === false) {
      console.error("API returned error:", d.message);
      alert(`Error: ${d.message}`);
      return;
    }

    // Check if we have the required fields
    if (!d.form_id || !d.form_title) {
      console.error("Invalid form data received:", d);
      alert("Invalid form data received from server");
      return;
    }

    viewFormData.value = {
      form_id: d.form_id,
      title: d.form_title,
      year: d.form_year,
      description: d.form_description || "",
      deadline: d.deadline_date || null,
      is_active: !!d.is_active,
      questions: Array.isArray(d.form_questions)
        ? d.form_questions
        : JSON.parse(d.form_questions || "[]"),
    };
    console.log("View form data set:", viewFormData.value); // Debug log
    viewFormModal.value.showModal();
  } catch (err) {
    console.error("Error loading form for view:", err);
    console.error("Error status:", err.response?.status); // Additional debug info
    console.error("Error response data:", err.response?.data); // Additional debug info
    console.error("Error message:", err.message); // Additional debug info

    let errorMessage = "Failed to load form details";
    if (err.response?.status === 401) {
      errorMessage = "Authentication failed. Please log in again.";
    } else if (err.response?.status === 403) {
      errorMessage = "Access denied. Admin permissions required.";
    } else if (err.response?.data?.message) {
      errorMessage = err.response.data.message;
    } else if (err.message) {
      errorMessage = err.message;
    }

    alert(errorMessage);
  }
};

onMounted(() => {
  console.log("TracerFormsAdmin mounted, fetching forms..."); // Debug log
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
</style>
