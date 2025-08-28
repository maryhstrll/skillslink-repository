// Simple API service without authentication for testing CORS
const BASE_URL = 'http://localhost/skillslink/backend/api'

const apiService = {
  async getUsers(params = {}) {
    try {
      const queryString = new URLSearchParams(params).toString()
      const url = `${BASE_URL}/users-public.php${queryString ? `?${queryString}` : ''}`
      
      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        },
        credentials: 'include'
      })
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      
      const data = await response.json()
      return { success: true, data, status: response.status }
    } catch (error) {
      console.error('API Error:', error)
      return { success: false, error: error.message, status: 500 }
    }
  },

  async getPrograms() {
    try {
      const response = await fetch(`${BASE_URL}/programs.php`, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        }
      })
      
      const data = await response.json()
      return { success: true, data, status: response.status }
    } catch (error) {
      console.error('API Error:', error)
      return { success: false, error: error.message, status: 500 }
    }
  },

  async testCors() {
    try {
      const response = await fetch(`${BASE_URL}/test-cors.php`, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        },
        credentials: 'include'
      })
      
      const data = await response.json()
      return { success: true, data, status: response.status }
    } catch (error) {
      console.error('CORS Test Error:', error)
      return { success: false, error: error.message, status: 500 }
    }
  }
}

export default apiService
