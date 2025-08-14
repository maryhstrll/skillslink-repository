import axios from 'axios'

// Base URL already set globally in auth.js, but ensure here if used standalone
axios.defaults.baseURL = axios.defaults.baseURL || 'http://localhost/skillslink/backend'
axios.defaults.withCredentials = true

const programsService = {
  async list() {
    try {
      const res = await axios.get('/api/programs.php')
      if (Array.isArray(res.data)) return res.data
      if (res.data.success && Array.isArray(res.data.data)) return res.data.data
      if (Array.isArray(res.data.programs)) return res.data.programs
      throw new Error('Unexpected response')
    } catch (e) {
      console.error('Programs fetch failed', e)
      throw e
    }
  }
}

export default programsService
