import axios from 'axios'

class AuthService {
  // Check if user is logged in
  async checkAuth() {
    try {
      const response = await axios.get('/api/session')
      return response.data
    } catch (error) {
      console.error('Auth check failed:', error)
      return { loggedIn: false }
    }
  }

  // Login user
  async login(credentials) {
    try {
      const response = await axios.post('/api/login', credentials)
      if (response.data.message === 'Login successfully') {
        // Store user data in localStorage as backup
        localStorage.setItem('user', JSON.stringify(response.data.user))
        return { success: true, data: response.data }
      }
      return { success: false, error: 'Login failed' }
    } catch (error) {
      return { 
        success: false, 
        error: error.response?.data?.error || 'Login failed' 
      }
    }
  }

  // Logout user
  async logout() {
    try {
      const response = await axios.post('/api/logout')
      if (response.data.success) {
        // Clear localStorage
        localStorage.removeItem('user')
        return { success: true, message: response.data.message }
      }
      return { success: false, error: 'Logout failed' }
    } catch (error) {
      // Even if API fails, clear local storage
      localStorage.removeItem('user')
      return { 
        success: false, 
        error: error.response?.data?.error || 'Logout failed' 
      }
    }
  }

  // Get current user from localStorage
  getCurrentUser() {
    try {
      const user = localStorage.getItem('user')
      return user ? JSON.parse(user) : null
    } catch (error) {
      return null
    }
  }

  // Check if user is authenticated (client-side)
  isAuthenticated() {
    return this.getCurrentUser() !== null
  }
}

export default new AuthService()
