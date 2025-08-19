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
  },

  async updateProfile(profileData) {
    try {
      const response = await fetch('http://localhost/skillslink/backend/alumni/update_profile.php', {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(profileData)
      })
      
      const data = await response.json()
      
      if (data.success) {
        return { success: true, message: data.message }
      } else {
        return { success: false, error: data.error || 'Failed to update profile' }
      }
    } catch (error) {
      console.error('Error updating profile:', error)
      return { success: false, error: 'Network error occurred' }
    }
  },

  async updateSocialLinks(socialLinksData) {
    try {
      const response = await fetch('http://localhost/skillslink/backend/alumni/update_social_links.php', {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(socialLinksData)
      })
      
      const data = await response.json()
      
      if (data.success) {
        return { success: true, message: data.message }
      } else {
        return { success: false, error: data.error || 'Failed to update social links' }
      }
    } catch (error) {
      console.error('Error updating social links:', error)
      return { success: false, error: 'Network error occurred' }
    }
  }
}

export default alumniService