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
        <!-- Form Builder / Editor Component -->
        <FormEditor
          v-if="state.editing"
          :form="form"
          :is-new="state.isNew"
          :editing="state.editing"
          :core-employment-questions="coreEmploymentQuestions"
          @update:form="updateForm"
          @save="saveForm"
          @cancel="cancelEdit"
          @toggle-preview="togglePreview"
        />
        
        <!-- Form Preview Component -->
        <FormPreview
          v-if="state.preview && state.editing"
          :form="form"
          :enabled-employment-questions="enabledEmploymentQuestions"
        />

        <!-- Employment Tracer Forms Table -->
        <TracerFormTable 
          v-if="!state.editing"
          :forms="state.forms"
          :loading-forms="loadingForms"
          :response-counts="state.responseCounts"
          :open-dropdown-id="openDropdownId"
          :dropdown-pos="dropdownPos"
          @view-form="viewForm"
          @load-form="loadForm"
          @view-responses="viewResponses"
          @duplicate-form="duplicateForm"
          @activate-form="activateForm"
          @delete-form="deleteForm"
          @toggle-dropdown="toggleDropdown"
          @create-form="openCreate"
        />

        <!-- Form Preview Modal -->
        <FormViewModal
          ref="viewFormModal"
          :view-form-data="state.viewFormData"
          :selected-employment-questions-for-view="selectedEmploymentQuestionsForView"
          :core-employment-questions="coreEmploymentQuestions"
          :form-data="state.viewFormData"
          @edit-form="loadForm"
        />
      </div>
    </div>

    <!-- Form Responses Component -->
    <FormResponses ref="formResponsesComponent" />
  </Layout>
</template>


<script setup>
/**
 * TracerFormsAdmin.vue
 * 
 * Administrative interface for managing Employment Tracer Forms
 * 
 * Features:
 * - Create, edit, duplicate, and delete tracer forms
 * - Configure core employment questions and add custom questions
 * - Preview forms before publishing
 * - View form responses and statistics  
 * - Manage form activation status
 * 
 * Structure:
 * - Constants: Employment options and configurations
 * - State Management: Reactive state for forms, editing, and UI
 * - Utility Functions: Helper functions for form parsing and rendering
 * - Form Management: CRUD operations for forms
 * - API Functions: Backend communication
 * 
 * Component Structure:
 * - TracerFormTable: Table display of all tracer forms with actions
 * - FormEditor: Form creation and editing interface
 * - FormPreview: Real-time preview of form being edited
 * - FormViewModal: Modal for viewing form details
 */

import { ref, reactive, onMounted, onBeforeUnmount, computed } from "vue";
import { useRouter } from "vue-router";
import Layout from "@/components/Layout.vue";
import FormResponses from "@/components/FormResponses.vue";
import axios from "axios";
import { messageService } from "@/services/messageService.js";

// Import the modular components
import TracerFormTable from "@/components/tracer/TracerFormTable.vue";
import FormEditor from "@/components/tracer/FormEditor.vue";
import FormPreview from "@/components/tracer/FormPreview.vue";
import FormViewModal from "@/components/tracer/FormViewModal.vue";

// Note: Icons are globally registered in main.js as Lucide icons
// Available as: IconPlus, IconRefreshCw, IconFileText, IconEdit, IconBarChart3,
// IconCheck, IconUsers, IconSave, IconX, IconEye, IconEyeOff, IconEllipsisV, IconTrash2

// Configuration
axios.defaults.baseURL = "http://localhost/skillslink/backend";
axios.defaults.withCredentials = true;

// Router
const router = useRouter();

// Reactive state
const state = reactive({
  forms: [],
  editing: false,
  isNew: true,
  preview: false,
  responseCounts: {},
  viewFormData: {}
});

// Add loading state
const loadingForms = ref(false);

const form = reactive({
  form_id: null,
  title: "",
  year: new Date().getFullYear(),
  description: "",
  deadline: null,
  is_active: true,
  questions: [], // Additional custom questions
  selectedEmploymentQuestions: [], // IDs of employment questions to include
});

// Refs
const formResponsesComponent = ref(null);
const viewFormModal = ref(null);
// Constants
const EMPLOYMENT_STATUS_OPTIONS = ["Employed", "Unemployed", "Further Studies", "Not Looking"];
const WORK_CLASSIFICATION_OPTIONS = [
  "Wage and Salary Workers",
  "Self-employed without any paid employee", 
  "Employer in Own Family-Operated Farm or Business",
  "Work Without Pay in Own-Family-Operated Farm or Business"
];
const SALARY_RANGES = [
  "Below 20,000", "20,000-40,000", "40,000-60,000",
  "60,000-80,000", "80,000-100,000", "Above 100,000"
];
const EMPLOYMENT_TYPES = ["Permanent", "Casual", "Contractual", "Seasonal or Short Term"];
const WORK_SETUPS = ["On-site", "Remote", "Hybrid"];
const YES_NO_OPTIONS = ["Yes", "No"];

// Core employment questions configuration
const coreEmploymentQuestions = ref([
  {
    id: "employment_status",
    label: "Employment Status", 
    type: "radio",
    maps_to: "employment_status",
    required: true,
    always_include: true,
    options: EMPLOYMENT_STATUS_OPTIONS,
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
    options: WORK_CLASSIFICATION_OPTIONS,
    optional: true,
  },
  {
    id: "is_local",
    label: "Local",
    type: "radio",
    maps_to: "is_local", 
    conditional: 'employment_status == "employed"',
    options: YES_NO_OPTIONS,
    optional: true,
  },
  {
    id: "is_abroad",
    label: "Abroad",
    type: "radio",
    maps_to: "is_abroad",
    conditional: 'employment_status == "employed"',
    options: YES_NO_OPTIONS,
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
    options: SALARY_RANGES,
    optional: true,
  },
  {
    id: "employment_type",
    label: "Employment Type",
    type: "radio",
    maps_to: "employment_type",
    conditional: 'employment_status == "employed"',
    options: EMPLOYMENT_TYPES,
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
    options: WORK_SETUPS,
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
  {
    id: "job_relevance_to_course",
    label: "How relevant is your current job to your course/program?",
    type: "radio",
    maps_to: "job_relevance_to_course",
    conditional: 'employment_status == "employed"',
    options: ["Highly Relevant", "Somewhat Relevant", "Not Relevant"],
    optional: true,
  },
]);

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
  if (!state.viewFormData.selectedEmploymentQuestions) return [];
  return coreEmploymentQuestions.value.filter(q => 
    state.viewFormData.selectedEmploymentQuestions.includes(q.id)
  );
});

// =============================================================================
// UTILITY FUNCTIONS
// =============================================================================

// Helper functions for rendering form fields
const renderFormField = (question, disabled = false, size = 'sm') => {
  const baseClasses = disabled ? 'disabled' : '';
  const sizeClasses = size === 'xs' ? 'input-xs textarea-xs select-xs radio-xs checkbox-xs' : 'input-sm textarea-sm select-sm radio-sm checkbox-sm';
  
  return question;
};

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
    "is_abroad",
    "salary_range",
    "months_to_find_job",
    "job_relevance_to_course"
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

// Handler for form updates from the FormEditor component
const updateForm = (updatedForm) => {
  // Update our local form object with values from the child component
  Object.assign(form, updatedForm);
};

// =============================================================================
// FORM MANAGEMENT FUNCTIONS  
// =============================================================================

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
    "employment_type",
    "months_to_find_job",
    "job_relevance_to_course"
  ];
};

// These functions have been moved to the FormEditor component
// Only keeping togglePreview as it's used to communicate between components

const togglePreview = () => {
  state.preview = !state.preview;
};

// =============================================================================
// API FUNCTIONS
// =============================================================================

// API calls
const fetchResponseCounts = async () => {
  try {
    const counts = {};
    for (const form of state.forms) {
      try {
        const res = await axios.get(
          `/admin/tracer_forms.php?action=count&form_id=${form.form_id}`
        );
        counts[form.form_id] = res.data.count || 0;
      } catch (err) {
        console.error(`Error fetching count for form ${form.form_id}:`, err);
        counts[form.form_id] = 0;
      }
    }
    state.responseCounts = counts;
  } catch (err) {
    console.error("Error fetching response counts:", err);
  }
};

const fetchForms = async () => {
  try {
    loadingForms.value = true;
    console.log("Fetching employment tracer forms...");
    const res = await axios.get("/admin/tracer_forms.php?action=list");
    console.log("Forms list response:", res.data);

    const data = res.data;
    if (Array.isArray(data)) {
      // Filter out empty/invalid forms
      state.forms = data.filter((f) => f && f.form_id && f.form_title);
      console.log("Filtered forms:", state.forms);

      // Fetch response counts after loading forms
      await fetchResponseCounts();
    } else {
      console.error("Expected array but got:", data);
      state.forms = [];
    }
  } catch (err) {
    console.error("Error in fetchForms:", err);
    messageService.showMessage(
      "Failed to load employment tracer forms",
      "error"
    );
    state.forms = [];
  } finally {
    loadingForms.value = false;
  }
};

const openCreate = () => {
  resetForm();
  state.editing = true;
  state.isNew = true;
  state.preview = false;
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

    state.editing = true;
    state.isNew = false;
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
      const message = state.isNew
        ? "Employment tracer form created successfully!"
        : "Employment tracer form updated successfully!";
      messageService.showMessage(message, "success");
      state.editing = false;

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
  state.editing = false;
  state.preview = false;
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
  )    return;

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
  // Navigate to the Form Responses view with the form ID
  router.push({
    path: '/form_responses',
    query: { form_id: item.form_id }
  });
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
    state.viewFormData = {
      form_id: d.form_id,
      title: d.form_title,
      year: d.form_year,
      description: d.form_description || "",
      deadline: d.deadline_date || null,
      is_active: !!d.is_active,
      // Add formData property for modal compatibility
      formData: d,
      coreEmploymentQuestions: coreEmploymentQuestions.value,
      questions: parsedQuestions.additional_questions,
      selectedEmploymentQuestions: parsedQuestions.selected_employment_questions,
    };

    console.log("View form data set:", state.viewFormData);
    
    // Make sure the modal exists before trying to show it
    if (viewFormModal.value && typeof viewFormModal.value.showModal === 'function') {
      viewFormModal.value.showModal();
    } else {
      console.error("Modal reference not found or showModal is not a function", viewFormModal.value);
      // Use native document querySelector as fallback
      const modalElement = document.querySelector('dialog[ref="viewFormModal"]');
      if (modalElement && typeof modalElement.showModal === 'function') {
        modalElement.showModal();
      } else {
        messageService.showMessage("Could not open form preview", "error");
      }
    }
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

// =============================================================================
// LIFECYCLE & EVENT HANDLERS
// =============================================================================

// For the Dropdown Menu
const openDropdownId = ref(null)
const dropdownPos = ref({ top: 0, left: 0 })
const triggerRefs = new Map()

function setTriggerRef(id) {
  return (el) => {
    if (el) triggerRefs.set(id, el)
    else triggerRefs.delete(id)
  }
}

function toggleDropdown(item, event) {
  if (openDropdownId.value === item.form_id) {
    closeDropdown()
    return
  }
  openDropdownId.value = item.form_id
  // position relative to trigger button
  const btn = triggerRefs.get(item.form_id) || event.currentTarget
  const rect = btn.getBoundingClientRect()
  // adjust left so menu aligns to right edge like dropdown-end
  const menuWidth = 144 // approximate menu width (w-36 = 9rem = 144px)
  dropdownPos.value.top = rect.bottom + window.scrollY
  dropdownPos.value.left = rect.right + window.scrollX - menuWidth
}

function closeDropdown() {
  openDropdownId.value = null
}

function onDocClick(e) {
  // close when clicking outside
  if (!e.target.closest('.menu')) closeDropdown()
}

function onEsc(e) {
  if (e.key === 'Escape') closeDropdown()
}

onMounted(() => {
  document.addEventListener('click', onDocClick)
  document.addEventListener('keydown', onEsc)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', onDocClick)
  document.removeEventListener('keydown', onEsc)
})

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
