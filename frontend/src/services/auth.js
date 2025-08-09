import axios from 'axios'

// Configure axios base URL for your backend
axios.defaults.baseURL = 'http://localhost/skillslink/backend'
axios.defaults.withCredentials = true

class AuthService {
  // Check if user is logged in
  async checkAuth() {
    try {
      const response = await axios.get('/session.php')
      return response.data
    } catch (error) {
      console.error('Auth check failed:', error)
      return { loggedIn: false }
    }
  }

  // Login user
  async login(credentials) {
    try {
      const response = await axios.post('/auth/login.php', credentials)
      if (response.data.message === 'Login successfully') {
        // Store user data in localStorage as backup
        localStorage.setItem('user', JSON.stringify(response.data.user))
        // Also store role separately for easier access (optional)
        localStorage.setItem('userRole', response.data.user.role || 'alumni')
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
      const response = await axios.post('/auth/logout.php')
      // Always clear localStorage regardless of server response
      localStorage.removeItem('user')
      localStorage.removeItem('userRole')
      
      if (response.data.success) {
        return { success: true, message: response.data.message }
      }
      return { success: true, message: 'Logged out successfully' }
    } catch (error) {
      // Even if API fails, clear local storage and consider it successful
      localStorage.removeItem('user')
      localStorage.removeItem('userRole')
      return { 
        success: true, 
        message: 'Logged out successfully'
      }
    }
  }

  // Register new user (admin only)
  async register(userData) {
    try {
      const response = await axios.post('/auth/register.php', userData)
      if (response.data.message) {
        return { success: true, message: response.data.message }
      }
      return { success: false, error: 'Registration failed' }
    } catch (error) {
      return { 
        success: false, 
        error: error.response?.data?.error || 'Registration failed' 
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

  // Get current user role
  getCurrentUserRole() {
    const user = this.getCurrentUser()
    return user ? user.role : null
  }

  // Check if current user has specific role
  hasRole(role) {
    const userRole = this.getCurrentUserRole()
    return userRole === role
  }

  // Check if current user is admin
  isAdmin() {
    return this.hasRole('admin')
  }

  // Check if current user is alumni
  isAlumni() {
    return this.hasRole('alumni')
  }

  // Check if current user is staff
  isStaff() {
    return this.hasRole('staff')
  }
}

export default new AuthService()
