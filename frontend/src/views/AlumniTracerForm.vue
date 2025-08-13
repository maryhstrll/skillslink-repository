<template>
  <Layout @logout="handleLogout">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Alumni Tracer Form</h1>
      
      <!-- Form Description -->
      <div v-if="form_description" class="mb-6 p-4 bg-base-200 rounded-lg">
        <p class="text-gray-700">{{ form_description }}</p>
      </div>

      <div v-if="loading">Loading form...</div>
      <div v-else>
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

          <button type="submit" class="btn btn-primary">Submit</button>
        </form>
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
axios.defaults.baseURL = '/backend';
axios.defaults.withCredentials = true;

const router = useRouter();
const loading = ref(true);
const form_description = ref();
const questions = ref([]);
const answers = ref({});

const loadForm = async () => {
  try {
    const { data } = await axios.get("/alumni/get_tracer_form.php");
    console.log("Form data received:", data); // Debug log
    if (data.questions_json) {
      questions.value = JSON.parse(data.questions_json);
      questions.value.forEach((q) => {
        // Initialize answers based on question type
        if (q.type === 'checkbox') {
          answers.value[q.id] = [];
        } else {
          answers.value[q.id] = "";
        }
      });
    }
    // Load form description
    form_description.value = data.form_description || "";
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
    alert("Form submitted successfully!");
    router.push("/alumni_tracer_form");
  } catch (err) {
    console.error("Error submitting form:", err);
    alert("Error submitting form.");
  }
};

onMounted(() => {
  loadForm();
});

const handleLogout = () => {
  router.push("/home");
};
</script>
