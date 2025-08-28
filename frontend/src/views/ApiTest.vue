<template>
  <div class="p-8">
    <h1 class="text-2xl font-bold mb-4">API Connection Test</h1>
    
    <div class="space-y-4">
      <button @click="testCors" class="btn btn-primary" :disabled="loading">
        <span v-if="loading" class="loading loading-spinner loading-sm"></span>
        Test CORS Connection
      </button>
      
      <button @click="testUsers" class="btn btn-secondary" :disabled="loading">
        <span v-if="loading" class="loading loading-spinner loading-sm"></span>
        Test Users API
      </button>
      
      <button @click="testPrograms" class="btn btn-accent" :disabled="loading">
        <span v-if="loading" class="loading loading-spinner loading-sm"></span>
        Test Programs API
      </button>
    </div>
    
    <div v-if="result" class="mt-6 p-4 border rounded">
      <h3 class="font-bold mb-2">Result:</h3>
      <pre class="text-sm overflow-x-auto">{{ JSON.stringify(result, null, 2) }}</pre>
    </div>
    
    <div v-if="error" class="mt-6 p-4 bg-red-100 text-red-700 border border-red-300 rounded">
      <h3 class="font-bold mb-2">Error:</h3>
      <p>{{ error }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import apiService from '@/services/users-test.js'

const loading = ref(false)
const result = ref(null)
const error = ref('')

const testCors = async () => {
  loading.value = true
  result.value = null
  error.value = ''
  
  try {
    const response = await apiService.testCors()
    result.value = response
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

const testUsers = async () => {
  loading.value = true
  result.value = null
  error.value = ''
  
  try {
    const response = await apiService.getUsers({ limit: 5 })
    result.value = response
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

const testPrograms = async () => {
  loading.value = true
  result.value = null
  error.value = ''
  
  try {
    const response = await apiService.getPrograms()
    result.value = response
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}
</script>
