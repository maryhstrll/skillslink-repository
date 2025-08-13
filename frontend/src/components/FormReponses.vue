<template>
  <!-- Responses Modal -->
  <dialog ref="responsesModal" class="modal">
    <div class="modal-box w-11/12 max-w-5xl h-5/6 flex flex-col">
      <!-- Fixed Header -->
      <div class="sticky top-0 bg-base-100 z-10 pb-3 border-b border-base-300">
        <h3 class="font-bold text-lg">Responses for {{ modalFormTitle }}</h3>
      </div>
      
      <!-- Scrollable Content -->
      <div class="flex-1 overflow-y-auto py-3">
        <div v-if="responses.length">
          <div
            v-for="r in responses"
            :key="r.response_id"
            class="card p-3 mb-2 bg-base-200"
          >
            <div class="flex justify-between items-start">
              <div>
                <div class="text-sm font-semibold">
                  {{ r.alumni_name || `Alumni ID: ${r.alumni_id}` }}
                </div>
                <div class="text-xs text-gray-500">
                  {{ r.alumni_email || 'No email available' }}
                </div>
                <div class="text-xs text-gray-500">
                  Student ID: {{ r.alumni_student_id || 'Not provided' }}
                </div>
                <div class="text-xs text-gray-400">
                  Submitted: {{ formatDate(r.submitted_at) }}
                </div>
              </div>
              <div class="text-sm">
                <span class="badge badge-primary">
                  {{ r.completion_percentage || 0 }}% Complete
                </span>
              </div>
            </div>

            <!-- Response Data -->
            <div class="mt-3">
              <h4 class="font-medium text-sm mb-2">Responses:</h4>
              <div
                v-if="r.responses && Object.keys(r.responses).length"
                class="space-y-2"
              >
                <div
                  v-for="(answer, questionId) in r.responses"
                  :key="questionId"
                  class="bg-base-100 p-2 rounded text-xs"
                >
                  <div class="font-medium">
                    {{ getQuestionText(questionId) }}
                  </div>
                  <div class="text-gray-600">
                    {{ Array.isArray(answer) ? answer.join(", ") : answer }}
                  </div>
                </div>
              </div>
              <div v-else class="text-xs text-gray-500 italic">
                No response data available
              </div>
            </div>
          </div>
        </div>
        <p v-else class="text-center text-gray-500 py-4">
          No responses yet.
        </p>
      </div>
      
      <!-- Fixed Footer -->
      <div class="sticky bottom-0 bg-base-100 z-10 pt-3 border-t border-base-300">
        <div class="modal-action mt-0">
          <form method="dialog">
            <button class="btn">Close</button>
          </form>
        </div>
      </div>
    </div>
  </dialog>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";

// Configure axios
axios.defaults.baseURL = "http://localhost/skillslink/backend";
axios.defaults.withCredentials = true;

// Props
const props = defineProps({
  // Optional: You can pass initial data if needed
});

// Refs
const responsesModal = ref(null);
const responses = ref([]);
const modalFormTitle = ref("");
const formQuestions = ref([]);

// Methods
const formatDate = (dateString) => {
  if (!dateString) return "No date";
  try {
    const date = new Date(dateString);
    return date.toLocaleString();
  } catch (error) {
    return dateString;
  }
};

const getQuestionText = (questionId) => {
  const question = formQuestions.value.find(q => q.id == questionId);
  return question ? question.label : `Question ID ${questionId}`;
};

const fetchFormQuestions = async (formId) => {
  try {
    const res = await axios.get(`/admin/tracer_forms.php?action=get&form_id=${formId}`);
    if (res.data && res.data.form_questions) {
      // Handle both string and array formats
      if (typeof res.data.form_questions === 'string') {
        formQuestions.value = JSON.parse(res.data.form_questions);
      } else {
        formQuestions.value = res.data.form_questions;
      }
    } else {
      formQuestions.value = [];
    }
  } catch (err) {
    console.error("Error fetching form questions:", err);
    formQuestions.value = [];
  }
};

const viewResponses = async (item) => {
  modalFormTitle.value = item.form_title;
  responses.value = [];
  formQuestions.value = [];
  
  try {
    // Fetch form questions first
    await fetchFormQuestions(item.form_id);
    
    // Then fetch responses
    const res = await axios.get(
      `/admin/form_responses.php?action=list&form_id=${item.form_id}`
    );
    console.log("Responses data:", res.data); // Debug log
    console.log("Form questions:", formQuestions.value); // Debug log
    responses.value = res.data || [];
    responsesModal.value.showModal();
  } catch (err) {
    console.error("Error fetching responses:", err);
    alert("Failed to fetch responses");
  }
};

// Expose methods to parent component
defineExpose({
  viewResponses
});
</script>

<style scoped>
.modal-box {
  height: 70%;
}
</style>