const API_BASE_URL = 'http://localhost/skillslink/backend/api'

const alumniService = {
  async getAll() {
    try {
      const response = await fetch(`${API_BASE_URL}/alumni.php`)
      const data = await response.json()
      return data.success ? data.data : []
    } catch (error) {
      console.error('Error fetching alumni:', error)
      return []
    }
  },

  async create(alumni) {
    try {
      const response = await fetch(`${API_BASE_URL}/alumni.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(alumni)
      })
      return await response.json()
    } catch (error) {
      console.error('Error creating alumni:', error)
      return { success: false, message: 'Error creating alumni' }
    }
  },

  async update(id, alumni) {
    try {
      const response = await fetch(`${API_BASE_URL}/alumni.php`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ ...alumni, alumni_id: id })
      })
      return await response.json()
    } catch (error) {
      console.error('Error updating alumni:', error)
      return { success: false, message: 'Error updating alumni' }
    }
  },

  async remove(id) {
    try {
      const response = await fetch(`${API_BASE_URL}/alumni.php`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ alumni_id: id })
      })
      return await response.json()
    } catch (error) {
      console.error('Error deleting alumni:', error)
      return { success: false, message: 'Error deleting alumni' }
    }
  }
}

export default alumniService