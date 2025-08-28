import axios from 'axios'

// Configure axios for reports
const reportsApi = axios.create({
  baseURL: 'http://localhost/skillslink/backend/admin',
  withCredentials: true,
})

export const reportsService = {
  // Get dashboard summary
  getDashboardSummary: () => reportsApi.get('/reports.php?action=summary_dashboard'),
  
  // Get employment statistics
  getEmploymentStatistics: () => reportsApi.get('/reports.php?action=employment_statistics'),
  
  // Get salary analysis
  getSalaryAnalysis: () => reportsApi.get('/reports.php?action=salary_analysis'),
  
  // Get geographic distribution
  getGeographicDistribution: () => reportsApi.get('/reports.php?action=geographic_distribution'),
  
  // Get job search analysis
  getJobSearchAnalysis: () => reportsApi.get('/reports.php?action=job_search_analysis'),
  
  // Get graduation trends
  getGraduationTrends: () => reportsApi.get('/reports.php?action=graduation_trends'),
  
  // Export report data
  exportReport: async (format = 'json') => {
    try {
      const [
        dashboard,
        employment,
        salary,
        location,
        jobSearch,
        trends
      ] = await Promise.all([
        reportsApi.get('/reports.php?action=summary_dashboard'),
        reportsApi.get('/reports.php?action=employment_statistics'),
        reportsApi.get('/reports.php?action=salary_analysis'),
        reportsApi.get('/reports.php?action=geographic_distribution'),
        reportsApi.get('/reports.php?action=job_search_analysis'),
        reportsApi.get('/reports.php?action=graduation_trends')
      ])
      
      return {
        dashboard: dashboard.data,
        employment: employment.data,
        salary: salary.data,
        location: location.data,
        jobSearch: jobSearch.data,
        trends: trends.data,
        generated_at: new Date().toISOString()
      }
    } catch (error) {
      throw new Error(`Failed to export report: ${error.message}`)
    }
  }
}
