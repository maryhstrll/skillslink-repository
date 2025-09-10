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
  },

  async create(programData) {
    try {
      const res = await axios.post('/api/programs.php', programData)
      return res.data
    } catch (e) {
      console.error('Program creation failed', e)
      throw e
    }
  },

  async update(programId, programData) {
    try {
      const res = await axios.put('/api/programs.php', { ...programData, program_id: programId })
      return res.data
    } catch (e) {
      console.error('Program update failed', e)
      throw e
    }
  },

  async delete(programId) {
    try {
      const res = await axios.delete('/api/programs.php', { data: { program_id: programId } })
      return res.data
    } catch (e) {
      console.error('Program deletion failed', e)
      throw e
    }
  }
}

export default programsService
