<template>
  <!-- Responses Modal -->
  <dialog ref="responsesModal" class="modal">
    <form method="dialog" class="modal-box w-11/12 max-w-5xl">
      <h3 class="font-bold">Responses for {{ modalFormTitle }}</h3>
      <div class="mt-3">
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
                  {{
                    r.alumni_email ||
                    r.alumni_student_id ||
                    "No contact info"
                  }}
                </div>
                <div class="text-xs text-gray-400">
                  Submitted: {{ r.submitted_at || "No date" }}
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
                    Question ID {{ questionId }}:
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
      <div class="modal-action">
        <button class="btn">Close</button>
      </div>
    </form>
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

// Methods
const viewResponses = async (item) => {
  modalFormTitle.value = item.form_title;
  responses.value = [];
  try {
    const res = await axios.get(
      `/admin/form_responses.php?action=list&form_id=${item.form_id}`
    );
    console.log("Responses data:", res.data); // Debug log
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