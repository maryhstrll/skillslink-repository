<template>
  <Layout @logout="handleLogout">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Alumni Tracer Form</h1>

      <div v-if="loading">Loading form...</div>
      <div v-else>
        <form @submit.prevent="submitForm" class="space-y-6">
          <div
            v-for="(question, idx) in questions"
            :key="idx"
            class="form-control"
          >
            <label class="label font-medium">{{ question.text }}</label>

            <!-- Render different input types -->
            <input
              v-if="question.type === 'text'"
              type="text"
              v-model="answers[question.id]"
              class="input input-bordered"
            />

            <textarea
              v-if="question.type === 'textarea'"
              v-model="answers[question.id]"
              class="textarea textarea-bordered"
            ></textarea>

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

const router = useRouter();
const loading = ref(true);
const questions = ref([]);
const answers = ref({});

const loadForm = async () => {
  try {
    const { data } = await axios.get("/api/alumni/get_tracer_form");
    questions.value = JSON.parse(data.questions_json);
    questions.value.forEach((q) => {
      answers.value[q.id] = "";
    });
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const submitForm = async () => {
  try {
    await axios.post("/api/alumni/submit_tracer_form", {
      answers: answers.value,
    });
    alert("Form submitted successfully!");
    router.push("/dashboard");
  } catch (err) {
    console.error(err);
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
