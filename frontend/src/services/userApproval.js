import axios from 'axios'

class UserApprovalService {
  // Get pending users for approval
  async getPendingUsers() {
    try {
      const response = await axios.get('/admin/user_approvals.php')
      return { success: true, data: response.data }
    } catch (error) {
      return { 
        success: false, 
        error: error.response?.data?.error || 'Failed to fetch pending users' 
      }
    }
  }

  // Approve a user
  async approveUser(userId) {
    try {
      const response = await axios.post('/admin/user_approvals.php', {
        user_id: userId,
        action: 'approve'
      })
      return { success: true, data: response.data }
    } catch (error) {
      return { 
        success: false, 
        error: error.response?.data?.error || 'Failed to approve user' 
      }
    }
  }

  // Reject a user
  async rejectUser(userId) {
    try {
      const response = await axios.post('/admin/user_approvals.php', {
        user_id: userId,
        action: 'reject'
      })
      return { success: true, data: response.data }
    } catch (error) {
      return { 
        success: false, 
        error: error.response?.data?.error || 'Failed to reject user' 
      }
    }
  }
}

export default new UserApprovalService()
