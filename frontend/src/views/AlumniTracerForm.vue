<template>
  <Layout @logout="handleLogout">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Alumni Tracer Form</h1>

      <div v-if="loading">Loading form...</div>

      <div v-else>
        <!-- If already responded -->
        <div v-if="alreadyResponded" class="space-y-4">
          <div class="message p-4 bg-green-100 text-green-800 rounded-lg">
            {{ alreadyMessage }}
          </div>
          
          <!-- Option to view/edit existing responses -->
          <div class="flex gap-4">
            <button 
              @click="showExistingResponses = !showExistingResponses"
              class="btn btn-outline"
            >
              {{ showExistingResponses ? 'Hide' : 'View' }} My Responses
            </button>
            
            <button 
              @click="enableEditing"
              class="btn edit-btn"
              v-if="!editMode"
            >
              Edit My Responses
            </button>
            
            <button 
              @click="cancelEditing"
              class="btn btn-outline"
              v-if="editMode"
            >
              Cancel Editing
            </button>
          </div>
          
          <!-- Show existing responses -->
          <div v-if="showExistingResponses && !editMode" class="field space-y-4 p-4 bg-gray-50 rounded-lg">
            <h3 class="text-lg font-semibold">Your Previous Responses:</h3>
            <div 
              v-for="(question, idx) in questions"
              :key="idx"
              class="border-b pb-3"
            >
              <p class="font-medium mb-2">{{ question.label }}</p>
              <p class="text-gray-700">
                <span v-if="Array.isArray(existingResponses[question.id])">
                  {{ existingResponses[question.id].join(', ') }}
                </span>
                <span v-else>
                  {{ existingResponses[question.id] || 'No response' }}
                </span>
              </p>
            </div>
          </div>
        </div>

        <!-- Form to fill out or edit (show if not responded yet OR in edit mode) -->
        <div v-if="!alreadyResponded || editMode">
          <!-- Form Description -->
          <div v-if="form_description" class="mb-6 p-4 bg-base-200 rounded-lg">
            <p class="text-gray-700">{{ form_description }}</p>
          </div>

          <form @submit.prevent="submitForm" class="space-y-6">
            <div
              v-for="(question, idx) in questions"
              :key="idx"
              class="form-control"
            >
              <label class="label font-medium">{{ question.label }}</label>

              <!-- Render different input types -->
              <input
                v-if="question.type === 'text'"
                type="text"
                v-model="answers[question.id]"
                :placeholder="question.placeholder"
                class="input input-bordered"
              />

              <textarea
                v-if="question.type === 'textarea'"
                v-model="answers[question.id]"
                :placeholder="question.placeholder"
                class="textarea textarea-bordered"
              ></textarea>

              <div v-if="question.type === 'radio'" class="space-y-2">
                <label
                  v-for="opt in question.options"
                  :key="opt"
                  class="flex items-center gap-2 cursor-pointer"
                >
                  <input
                    type="radio"
                    :name="`question_${question.id}`"
                    :value="opt"
                    v-model="answers[question.id]"
                    class="radio"
                  />
                  <span>{{ opt }}</span>
                </label>
              </div>

              <div v-if="question.type === 'checkbox'" class="space-y-2">
                <label
                  v-for="opt in question.options"
                  :key="opt"
                  class="flex items-center gap-2 cursor-pointer"
                >
                  <input
                    type="checkbox"
                    :value="opt"
                    @change="handleCheckboxChange(question.id, opt, $event)"
                    :checked="Array.isArray(answers[question.id]) && answers[question.id].includes(opt)"
                    class="checkbox"
                  />
                  <span>{{ opt }}</span>
                </label>
              </div>

              <select
                v-if="question.type === 'select'"
                v-model="answers[question.id]"
                class="select select-bordered"
              >
                <option disabled value="">Select an option</option>
                <option v-for="opt in question.options" :key="opt" :value="opt">
                  {{ opt }}
                </option>
              </select>
            </div>

            <button type="submit" class="btn btn-primary">
              {{ editMode ? 'Update Response' : 'Submit' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, onMounted } from "vue";
import Layout from "@/components/Layout.vue";
import { useRouter } from "vue-router";
import axios from "axios";

// Configure axios
axios.defaults.baseURL = "/backend";
axios.defaults.withCredentials = true;

const router = useRouter();
const loading = ref(true);
const form_description = ref();
const questions = ref([]);
const answers = ref({});
const alreadyResponded = ref(false);
const alreadyMessage = ref("");
const showExistingResponses = ref(false);
const editMode = ref(false);
const existingResponses = ref({});

const loadForm = async () => {
  try {
    const { data } = await axios.get("/alumni/get_tracer_form.php");

    if (data.already_responded) {
      alreadyResponded.value = true;
      alreadyMessage.value = data.message;
      existingResponses.value = data.existing_responses || {};
      
      // Load the form structure for viewing/editing
      form_description.value = data.form_description || "";
      questions.value = JSON.parse(data.questions_json || "[]");
    } else {
      form_description.value = data.form_description || "";
      questions.value = JSON.parse(data.questions_json || "[]");

      // Initialize answers for new form
      questions.value.forEach((q) => {
        answers.value[q.id] = q.type === 'checkbox' ? [] : "";
      });
    }
  } catch (err) {
    console.error("Error loading form:", err);
    alert("Error loading tracer form");
  } finally {
    loading.value = false;
  }
};

const handleCheckboxChange = (questionId, option, event) => {
  if (!answers.value[questionId]) {
    answers.value[questionId] = [];
  }
  if (event.target.checked) {
    if (!answers.value[questionId].includes(option)) {
      answers.value[questionId].push(option);
    }
  } else {
    const index = answers.value[questionId].indexOf(option);
    if (index > -1) {
      answers.value[questionId].splice(index, 1);
    }
  }
};

const submitForm = async () => {
  try {
    await axios.post("/alumni/submit_tracer_form.php", {
      answers: answers.value,
    });
    
    const message = editMode.value ? "Form updated successfully!" : "Form submitted successfully!";
    alert(message);
    
    // Update state after successful submission
    alreadyResponded.value = true;
    alreadyMessage.value = "You have already responded to this form.";
    existingResponses.value = { ...answers.value };
    editMode.value = false;
    showExistingResponses.value = false;
  } catch (err) {
    console.error("Error submitting form:", err);
    alert("Error submitting form.");
  }
};

const enableEditing = () => {
  editMode.value = true;
  // Populate answers with existing responses for editing
  answers.value = { ...existingResponses.value };
  
  // Ensure checkbox answers are arrays
  questions.value.forEach((q) => {
    if (q.type === 'checkbox' && !Array.isArray(answers.value[q.id])) {
      answers.value[q.id] = answers.value[q.id] ? [answers.value[q.id]] : [];
    }
  });
};

const cancelEditing = () => {
  editMode.value = false;
  answers.value = {};
};

onMounted(loadForm);

const handleLogout = () => {
  router.push("/home");
};
</script>

<style scoped>
/* Demonstrating use of the CSS variable color system for this view */

.edit-btn {
  background: var(--color-accent);
  color: var(--color-text-invert);
  border: 1px solid rgb(var(--color-accent-rgb) / 0.4);
  transition: background .18s, box-shadow .18s, transform .12s;
}
.edit-btn:hover {
  background: var(--color-accent-hover);
  box-shadow: 0 4px 10px -2px rgb(var(--color-accent-rgb) / 0.45);
}
.edit-btn:active {
  transform: translateY(1px);
}

/* Re-style the already-responded message using semantic tokens */
.message {
  background: rgb(var(--color-light-blue-rgb) / 0.18);
  border: 1px solid rgb(var(--color-light-blue-rgb) / 0.45);
  color: var(--color-text);
}
.field {
  background: var(--color-surface);
}
</style>
