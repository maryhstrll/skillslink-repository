const API_BASE_URL = 'http://localhost/skillslink/backend/admin'

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
  },

  async getProfile() {
    try {
      const response = await fetch('http://localhost/skillslink/backend/alumni/get_profile.php', {
        method: 'GET',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
        }
      })
      
      // Check if response is ok
      if (!response.ok) {
        console.error('HTTP error:', response.status, response.statusText);
        return null;
      }
      
      const data = await response.json()
      
      // Log the response for debugging
      console.log('Profile API response:', data)
      
      if (data.success) {
        return data.data
      } else {
        console.error('Profile API error:', data.message, data.debug || '')
        
        // Return error information so the frontend can handle it
        return { error: data.message, debug: data.debug }
      }
    } catch (error) {
      console.error('Error fetching profile:', error)
      return null
    }
  }
}

export default alumniService