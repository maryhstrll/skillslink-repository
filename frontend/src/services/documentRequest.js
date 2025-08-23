const API_BASE_URL = 'http://localhost/skillslink/backend/alumni'

const documentRequestService = {
  async getAll() {
    try {
      const response = await fetch(`${API_BASE_URL}/document_requests.php`, {
        method: 'GET',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
        }
      })
      const data = await response.json()
      return data.success ? data.data : []
    } catch (error) {
      console.error('Error fetching document requests:', error)
      return []
    }
  },

  async create(request) {
    try {
      const response = await fetch(`${API_BASE_URL}/document_requests.php`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(request)
      })
      return await response.json()
    } catch (error) {
      console.error('Error creating document request:', error)
      return { success: false, message: 'Error creating document request' }
    }
  },

  async updateStatus(requestId, status) {
    try {
      const response = await fetch(`${API_BASE_URL}/document_requests.php`, {
        method: 'PUT',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
          request_id: requestId, 
          status: status 
        })
      })
      return await response.json()
    } catch (error) {
      console.error('Error updating document request status:', error)
      return { success: false, message: 'Error updating request status' }
    }
  },

  getStatusClass(status) {
    const statusClasses = {
      'Pending': 'badge-warning',
      'Processing': 'badge-info', 
      'Ready for Pickup': 'badge-success',
      'Completed': 'badge-neutral'
    }
    return statusClasses[status] || 'badge-ghost'
  },

  getStatusIcon(status) {
    const statusIcons = {
      'Pending': 'fas fa-clock',
      'Processing': 'fas fa-cog fa-spin',
      'Ready for Pickup': 'fas fa-check-circle',
      'Completed': 'fas fa-flag-checkered'
    }
    return statusIcons[status] || 'fas fa-question'
  },

  getDocumentTypeIcon(documentType) {
    const typeIcons = {
      'Transcript of Records': 'fas fa-file-alt',
      'Transcript of Competency': 'fas fa-certificate', 
      'Diploma': 'fas fa-graduation-cap',
      'Certificate of Training': 'fas fa-award'
    }
    return typeIcons[documentType] || 'fas fa-file'
  }
}

export default documentRequestService
