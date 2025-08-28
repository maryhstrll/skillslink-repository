import axios from 'axios'

// Configure axios base URL
axios.defaults.baseURL = 'http://localhost/skillslink/backend'
axios.defaults.withCredentials = true

class UsersService {
  // Get all users with optional filters and pagination
  async getUsers(params = {}) {
    try {
      const queryParams = new URLSearchParams(params)
      const response = await axios.get(`/api/users.php?${queryParams}`)
      return { success: true, data: response.data }
    } catch (error) {
      console.error('Error fetching users:', error)
      return { 
        success: false, 
        error: error.response?.data?.error || 'Failed to fetch users',
        status: error.response?.status 
      }
    }
  }

  // Create a new user
  async createUser(userData) {
    try {
      const response = await axios.post('/api/users.php', userData)
      return { success: true, data: response.data }
    } catch (error) {
      console.error('Error creating user:', error)
      return { 
        success: false, 
        error: error.response?.data?.error || 'Failed to create user',
        status: error.response?.status 
      }
    }
  }

  // Update an existing user
  async updateUser(userId, userData) {
    try {
      const response = await axios.put(`/api/users.php?id=${userId}`, userData)
      return { success: true, data: response.data }
    } catch (error) {
      console.error('Error updating user:', error)
      return { 
        success: false, 
        error: error.response?.data?.error || 'Failed to update user',
        status: error.response?.status 
      }
    }
  }

  // Delete/deactivate a user
  async deleteUser(userId) {
    try {
      const response = await axios.delete(`/api/users.php?id=${userId}`)
      return { success: true, data: response.data }
    } catch (error) {
      console.error('Error deleting user:', error)
      return { 
        success: false, 
        error: error.response?.data?.error || 'Failed to delete user',
        status: error.response?.status 
      }
    }
  }

  // Get user statistics
  async getUserStats() {
    try {
      const response = await axios.get('/api/users.php?stats=true')
      return { success: true, data: response.data }
    } catch (error) {
      console.error('Error fetching user stats:', error)
      return { 
        success: false, 
        error: error.response?.data?.error || 'Failed to fetch user statistics',
        status: error.response?.status 
      }
    }
  }

  // Reset user password
  async resetPassword(userId) {
    try {
      const response = await axios.post(`/api/users.php?id=${userId}&action=reset_password`)
      return { success: true, data: response.data }
    } catch (error) {
      console.error('Error resetting password:', error)
      return { 
        success: false, 
        error: error.response?.data?.error || 'Failed to reset password',
        status: error.response?.status 
      }
    }
  }

  // Toggle user status (active/inactive)
  async toggleUserStatus(userId, isActive) {
    try {
      const response = await axios.put(`/api/users.php?id=${userId}`, { is_active: isActive })
      return { success: true, data: response.data }
    } catch (error) {
      console.error('Error toggling user status:', error)
      return { 
        success: false, 
        error: error.response?.data?.error || 'Failed to update user status',
        status: error.response?.status 
      }
    }
  }

  // Get programs for user creation
  async getPrograms() {
    try {
      const response = await axios.get('/api/programs.php')
      return { success: true, data: response.data }
    } catch (error) {
      console.error('Error fetching programs:', error)
      return { 
        success: false, 
        error: error.response?.data?.error || 'Failed to fetch programs',
        status: error.response?.status 
      }
    }
  }
}

export default new UsersService()
