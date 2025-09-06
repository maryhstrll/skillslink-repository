<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-3xl font-bold text-base-content">Employment Dashboard</h1>
        <p class="text-base-content/70 mt-1">
          Employment insights and program performance analytics based on 
          <span class="font-semibold">{{ dashboardData?.form_info?.form_title || 'Active Employment Tracer' }}</span>
          {{ dashboardData?.form_info?.form_year ? `(${dashboardData.form_info.form_year})` : '' }}
        </p>
      </div>
      <div class="flex gap-2">
        <button class="btn-primary-custom btn" @click="refreshData">
          <IconRefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
          Refresh
        </button>
        <button class="btn-secondary-custom btn" @click="exportReport">
          <IconDownload class="w-4 h-4" />
          Export Report
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-20">
      <div class="loading loading-spinner loading-lg"></div>
      <span class="ml-4 text-lg">Loading reports data...</span>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="alert alert-error">
      <IconX class="w-6 h-6" />
      <div>
        <h3 class="font-bold">Error Loading Reports</h3>
        <div class="text-sm">{{ error }}</div>
      </div>
      <button class="btn btn-sm btn-danger-custom" @click="refreshData">Retry</button>
    </div>

    <!-- Reports Content -->
    <div v-else class="space-y-6">
      <!-- Academic Excellence Summary -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card stat-card-primary">
          <div class="stat-figure text-white">
            <IconUsers class="w-8 h-8" />
          </div>
          <div class="stat-title text-white/80">Programs Analyzed</div>
          <div class="stat-value text-white">{{ academicData?.employment_by_program?.programs?.length || 0 }}</div>
          <div class="stat-desc text-white/70">Active academic programs</div>
        </div>
        
        <div class="stat-card stat-card-accent">
          <div class="stat-figure text-white">
            <IconUserCheck class="w-8 h-8" />
          </div>
          <div class="stat-title text-white/80">Best Employment Rate</div>
          <div class="stat-value text-white">
            {{ academicData?.employment_by_program?.employment_rates ? Math.max(...academicData.employment_by_program.employment_rates).toFixed(1) : 0 }}%
          </div>
          <div class="stat-desc text-white/70">Top performing program</div>
        </div>
        
        <div class="stat-card stat-card-ghost">
          <div class="stat-figure">
            <IconBarChart3 class="w-8 h-8" />
          </div>
          <div class="stat-title">Job-Course Alignment</div>
          <div class="stat-value">{{ academicData?.job_alignment_rate?.overall_rate || 0 }}%</div>
          <div class="stat-desc">Overall alignment rate</div>
        </div>
        
        <div class="stat-card stat-card-secondary">
          <div class="stat-figure text-white">
            <IconTrendingUp class="w-8 h-8" />
          </div>
          <div class="stat-title text-white/80">Avg. Time to Employment</div>
          <div class="stat-value text-white">
            {{ academicData?.time_to_employment_by_program?.average_months && academicData.time_to_employment_by_program.average_months.length > 0 ? 
                (academicData.time_to_employment_by_program.average_months.reduce((a, b) => a + b, 0) / 
                 academicData.time_to_employment_by_program.average_months.length).toFixed(1) : 0 }}
          </div>
          <div class="stat-desc text-white/70">months across programs</div>
        </div>
      </div>

      <!-- Academic Excellence Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Employment Rate by Academic Program (Bar Chart) -->
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title flex items-center">
              <IconBarChart3 class="w-5 h-5 text-primary" />
              Employment Rate by Academic Program
            </h2>
            <div class="h-80">
              <canvas ref="employmentByProgramChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Average Salary by Program (Horizontal Bar) -->
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title flex items-center">
              <IconBarChart3 class="w-5 h-5 text-success" />
              Average Salary by Program
            </h2>
            <div class="h-80">
              <canvas ref="salaryByProgramChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Job-Course Alignment Rate (Gauge Chart) -->
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title flex items-center">
              <IconPieChart class="w-5 h-5 text-info" />
              Job-Course Alignment Rate
            </h2>
            <div class="h-80">
              <canvas ref="jobAlignmentChart"></canvas>
            </div>
            <div class="mt-4 text-center">
              <div class="text-2xl font-bold text-info">
                {{ academicData?.job_alignment_rate?.overall_rate || 0 }}%
              </div>
              <div class="text-sm text-base-content/70">Overall Alignment Rate</div>
            </div>
          </div>
        </div>

        <!-- Time to Employment by Program (Line Chart) -->
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title flex items-center">
              <IconTrendingUp class="w-5 h-5 text-warning" />
              Time to Employment by Program
            </h2>
            <div class="h-80">
              <canvas ref="timeToEmploymentChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Program Performance Tables -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Employment Rate by Program Table -->
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h3 class="card-title">Employment Rates by Program</h3>
            <div class="overflow-x-auto">
              <table class="table table-zebra">
                <thead>
                  <tr>
                    <th>Program</th>
                    <th>Employment Rate</th>
                    <th>Employed</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(program, index) in academicData?.employment_by_program?.programs" :key="program">
                    <td class="font-medium">{{ program }}</td>
                    <td>
                      <div class="flex items-center gap-2">
                        <div class="badge" :class="getBadgeClass(academicData.employment_by_program.employment_rates[index])">
                          {{ academicData.employment_by_program.employment_rates[index] }}%
                        </div>
                      </div>
                    </td>
                    <td>{{ academicData.employment_by_program.employed_counts[index] }}</td>
                    <td>{{ academicData.employment_by_program.total_responses[index] }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Job-Course Alignment by Program Table -->
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h3 class="card-title">Job-Course Alignment by Program</h3>
            <div class="overflow-x-auto">
              <table class="table table-zebra">
                <thead>
                  <tr>
                    <th>Program</th>
                    <th>Alignment Rate</th>
                    <th>Aligned</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(program, index) in academicData?.job_alignment_rate?.programs" :key="program">
                    <td class="font-medium">{{ program }}</td>
                    <td>
                      <div class="flex items-center gap-2">
                        <div class="badge" :class="getBadgeClass(academicData.job_alignment_rate.alignment_rates[index])">
                          {{ academicData.job_alignment_rate.alignment_rates[index] }}%
                        </div>
                      </div>
                    </td>
                    <td>{{ academicData.job_alignment_rate.aligned_counts[index] }}</td>
                    <td>{{ academicData.job_alignment_rate.total_employed[index] }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { Chart, registerables } from 'chart.js'
// Icons are globally registered in main.js - no need to import

// Register Chart.js components
Chart.register(...registerables)

const router = useRouter()

// Configure axios
axios.defaults.baseURL = "http://localhost/skillslink/backend"
axios.defaults.withCredentials = true

// Reactive state
const loading = ref(true)
const error = ref(null)
const dashboardData = ref(null)
const academicData = ref(null)

// Chart refs
const employmentByProgramChart = ref(null)
const salaryByProgramChart = ref(null)
const jobAlignmentChart = ref(null)
const timeToEmploymentChart = ref(null)

// Chart instances
let charts = {}

// Chart colors
const chartColors = {
  primary: '#3B82F6',
  success: '#10B981', 
  warning: '#F59E0B',
  error: '#EF4444',
  info: '#06B6D4',
  secondary: '#8B5CF6'
}

const backgroundColors = [
  'rgba(59, 130, 246, 0.8)',   // blue
  'rgba(16, 185, 129, 0.8)',   // green  
  'rgba(245, 158, 11, 0.8)',   // yellow
  'rgba(239, 68, 68, 0.8)',    // red
  'rgba(6, 182, 212, 0.8)',    // cyan
  'rgba(139, 92, 246, 0.8)',   // purple
  'rgba(236, 72, 153, 0.8)',   // pink
  'rgba(34, 197, 94, 0.8)'     // emerald
]

const borderColors = [
  'rgb(59, 130, 246)',   // blue
  'rgb(16, 185, 129)',   // green  
  'rgb(245, 158, 11)',   // yellow
  'rgb(239, 68, 68)',    // red
  'rgb(6, 182, 212)',    // cyan
  'rgb(139, 92, 246)',   // purple
  'rgb(236, 72, 153)',   // pink
  'rgb(34, 197, 94)'     // emerald
]

// Utility functions
const getPercentage = (value, total) => {
  if (!total || total === 0) return 0
  return Math.round((value / total) * 100)
}

const getBadgeClass = (rate) => {
  if (rate >= 80) return 'badge-success'
  if (rate >= 60) return 'badge-warning'
  return 'badge-error'
}

// API functions
const fetchDashboardData = async () => {
  try {
    const response = await axios.get('/admin/reports.php?action=summary_dashboard')
    dashboardData.value = response.data.data
  } catch (err) {
    console.error('Error fetching dashboard data:', err)
    throw err
  }
}

const fetchAcademicExcellenceData = async () => {
  try {
    const response = await axios.get('/admin/reports.php?action=academic_excellence')
    academicData.value = response.data.data
    console.log('Academic data fetched and stored:', academicData.value)
  } catch (err) {
    console.error('Error fetching academic excellence data:', err)
    throw err
  }
}

// Chart creation functions
const createEmploymentByProgramChart = () => {
  console.log('Creating Employment chart - Debug info:', {
    canvas: !!employmentByProgramChart.value,
    canvasElement: employmentByProgramChart.value,
    academicData: !!academicData.value,
    employmentData: academicData.value?.employment_by_program,
    fullAcademicData: academicData.value
  })
  
  if (!employmentByProgramChart.value || !academicData.value?.employment_by_program) {
    console.log('Employment chart: Canvas or data not available')
    return
  }
  
  const data = academicData.value.employment_by_program
  console.log('Employment chart data:', data)
  
  if (charts.employmentByProgram) {
    charts.employmentByProgram.destroy()
  }
  
  charts.employmentByProgram = new Chart(employmentByProgramChart.value, {
    type: 'bar',
    data: {
      labels: data.programs,
      datasets: [{
        label: 'Employment Rate (%)',
        data: data.employment_rates,
        backgroundColor: chartColors.primary + '80',
        borderColor: chartColors.primary,
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            afterLabel: function(context) {
              const index = context.dataIndex
              return `Employed: ${data.employed_counts[index]}/${data.total_responses[index]}`
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          max: 100,
          ticks: {
            callback: function(value) {
              return value + '%'
            }
          }
        },
        x: {
          ticks: {
            maxRotation: 45
          }
        }
      }
    }
  })
  console.log('Employment chart created successfully')
}

const createSalaryByProgramChart = () => {
  if (!salaryByProgramChart.value || !academicData.value?.salary_by_program) {
    console.log('Salary chart: Canvas or data not available', {
      canvas: !!salaryByProgramChart.value,
      data: !!academicData.value?.salary_by_program,
      programs: academicData.value?.salary_by_program?.programs?.length || 0
    })
    return
  }
  
  const data = academicData.value.salary_by_program
  console.log('Salary chart data:', data)
  
  // Check if we have salary data
  if (!data.programs || data.programs.length === 0) {
    console.log('No salary data available')
    return
  }
  
  if (charts.salaryByProgram) {
    charts.salaryByProgram.destroy()
  }
  
  charts.salaryByProgram = new Chart(salaryByProgramChart.value, {
    type: 'bar',
    data: {
      labels: data.programs,
      datasets: [{
        label: 'Average Salary (₱)',
        data: data.average_salaries,
        backgroundColor: chartColors.success + '80',
        borderColor: chartColors.success,
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      indexAxis: 'y',
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              return `₱${context.parsed.x.toLocaleString()}`
            },
            afterLabel: function(context) {
              const index = context.dataIndex
              return `Based on ${data.total_employed[index]} employed alumni`
            }
          }
        }
      },
      scales: {
        x: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return '₱' + value.toLocaleString()
            }
          }
        }
      }
    }
  })
  console.log('Salary chart created successfully')
}

const createJobAlignmentChart = () => {
  if (!jobAlignmentChart.value || !academicData.value?.job_alignment_rate) {
    console.log('Job alignment chart: Canvas or data not available', {
      canvas: !!jobAlignmentChart.value,
      data: !!academicData.value?.job_alignment_rate
    })
    return
  }
  
  const alignmentRate = academicData.value.job_alignment_rate.overall_rate || 0
  console.log('Job alignment rate:', alignmentRate)
  
  if (charts.jobAlignment) {
    charts.jobAlignment.destroy()
  }
  
  charts.jobAlignment = new Chart(jobAlignmentChart.value, {
    type: 'doughnut',
    data: {
      labels: ['Job-Course Aligned', 'Not Aligned'],
      datasets: [{
        data: [alignmentRate, 100 - alignmentRate],
        backgroundColor: [chartColors.info + '80', '#E5E7EB'],
        borderColor: [chartColors.info, '#D1D5DB'],
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '70%',
      plugins: {
        legend: {
          position: 'bottom'
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              return context.label + ': ' + context.parsed + '%'
            }
          }
        }
      }
    }
  })
  console.log('Job alignment chart created successfully')
}

const createTimeToEmploymentChart = () => {
  if (!timeToEmploymentChart.value || !academicData.value?.time_to_employment_by_program) {
    console.log('Time to employment chart: Canvas or data not available', {
      canvas: !!timeToEmploymentChart.value,
      data: !!academicData.value?.time_to_employment_by_program,
      programs: academicData.value?.time_to_employment_by_program?.programs?.length || 0
    })
    return
  }
  
  const data = academicData.value.time_to_employment_by_program
  console.log('Time to employment data:', data)
  
  // Check if we have time to employment data
  if (!data.programs || data.programs.length === 0) {
    console.log('No time to employment data available')
    return
  }
  
  if (charts.timeToEmployment) {
    charts.timeToEmployment.destroy()
  }
  
  charts.timeToEmployment = new Chart(timeToEmploymentChart.value, {
    type: 'line',
    data: {
      labels: data.programs,
      datasets: [
        {
          label: 'Average Months',
          data: data.average_months,
          borderColor: chartColors.warning,
          backgroundColor: chartColors.warning + '20',
          borderWidth: 3,
          fill: true,
          tension: 0.4
        },
        {
          label: 'Min Months',
          data: data.min_months,
          borderColor: chartColors.success,
          backgroundColor: chartColors.success + '20',
          borderWidth: 2,
          borderDash: [5, 5]
        },
        {
          label: 'Max Months',
          data: data.max_months,
          borderColor: chartColors.error,
          backgroundColor: chartColors.error + '20',
          borderWidth: 2,
          borderDash: [5, 5]
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top'
        },
        tooltip: {
          callbacks: {
            afterLabel: function(context) {
              const index = context.dataIndex
              return `Based on ${data.total_employed[index]} employed alumni`
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return value + ' months'
            }
          }
        },
        x: {
          ticks: {
            maxRotation: 45
          }
        }
      }
    }
  })
  console.log('Time to employment chart created successfully')
}

// Watch for academicData changes and create charts when data is available
watch(academicData, (newData) => {
  if (newData) {
    console.log('Academic data changed, creating charts in next tick...')
    nextTick(() => {
      // Small delay to ensure canvas elements are ready
      setTimeout(() => {
        createAllCharts()
      }, 100)
    })
  }
}, { deep: true })

const createAllCharts = () => {
  console.log('Creating all charts...')
  
  console.log('Canvas refs check:', {
    employment: !!employmentByProgramChart.value,
    salary: !!salaryByProgramChart.value,
    alignment: !!jobAlignmentChart.value,
    timeToEmployment: !!timeToEmploymentChart.value
  })
  
  createEmploymentByProgramChart()
  createSalaryByProgramChart()
  createJobAlignmentChart()
  createTimeToEmploymentChart()
  
  console.log('Chart creation completed')
}

const loadAllData = async () => {
  try {
    loading.value = true
    error.value = null
    
    console.log('Loading academic excellence data...')
    
    await Promise.all([
      fetchDashboardData(),
      fetchAcademicExcellenceData()
    ])
    
    console.log('Data loaded:', {
      dashboardData: dashboardData.value,
      academicData: academicData.value
    })
    
    // Charts will be created by the watcher when academicData changes
    
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load reports data'
    console.error('Error loading data:', err)
  } finally {
    loading.value = false
  }
}

const refreshData = async () => {
  await loadAllData()
}

const exportReport = () => {
  // Create report data
  const reportData = {
    form_info: dashboardData.value?.form_info,
    academic_excellence: academicData.value,
    generated_at: new Date().toISOString()
  }
  
  // Convert to JSON and download
  const dataStr = JSON.stringify(reportData, null, 2)
  const dataBlob = new Blob([dataStr], { type: 'application/json' })
  const url = URL.createObjectURL(dataBlob)
  const link = document.createElement('a')
  link.href = url
  link.download = `academic-excellence-report-${dashboardData.value?.form_info?.form_year || 'current'}.json`
  link.click()
  URL.revokeObjectURL(url)
}

// Lifecycle
onMounted(() => {
  loadAllData()
})
</script>