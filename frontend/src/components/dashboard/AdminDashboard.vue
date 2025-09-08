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
        <StatCard 
          v-for="(card, index) in statCardsData" 
          :key="index"
          :title="card.title"
          :value="card.value"
          :description="card.description"
          :icon="card.icon"
          :variant="card.variant"
          :format="card.format"
        />
      </div>

      <!-- Academic Excellence Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Employment Rate by Academic Program -->
        <ChartCard
          title="Employment Rate by Academic Program"
          :icon="IconBarChart3"
          chart-type="bar"
          :chart-data="employmentChartData"
          :chart-options="employmentChartOptions"
        />

        <!-- Average Salary by Program -->
        <ChartCard
          title="Average Salary by Program"
          :icon="IconBarChart3"
          icon-class="text-success"
          chart-type="bar"
          :chart-data="salaryChartData"
          :chart-options="salaryChartOptions"
        />

        <!-- Job-Course Alignment Rate -->
        <ChartCard
          title="Job-Course Alignment Rate"
          :icon="IconPieChart"
          icon-class="text-info"
          chart-type="doughnut"
          :chart-data="alignmentChartData"
          :chart-options="alignmentChartOptions"
          :show-stats="true"
          :stats-value="`${academicData?.job_alignment_rate?.overall_rate || 0}%`"
          stats-label="Overall Alignment Rate"
          stats-value-class="text-info"
        />

        <!-- Time to Employment by Program -->
        <ChartCard
          title="Time to Employment by Program"
          :icon="IconTrendingUp"
          icon-class="text-warning"
          chart-type="line"
          :chart-data="timeToEmploymentChartData"
          :chart-options="timeToEmploymentChartOptions"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import ChartCard from '@/components/charts/ChartCard.vue'
import DataTable from '@/components/tables/DataTable.vue'
import StatCard from '@/components/cards/StatCard.vue'
import { 
  RefreshCw as IconRefreshCw,
  Download as IconDownload,
  X as IconX,
  Users as IconUsers,
  UserCheck as IconUserCheck,
  BarChart3 as IconBarChart3,
  TrendingUp as IconTrendingUp,
  PieChart as IconPieChart
} from 'lucide-vue-next'

// Configure axios
axios.defaults.baseURL = "http://localhost/skillslink/backend"
axios.defaults.withCredentials = true

// Reactive state
const loading = ref(true)
const error = ref(null)
const dashboardData = ref(null)
const academicData = ref(null)

// Chart colors
const chartColors = {
  primary: '#3B82F6',
  success: '#10B981', 
  warning: '#F59E0B',
  error: '#EF4444',
  info: '#06B6D4',
  secondary: '#8B5CF6'
}

// Computed properties for stat cards
const statCardsData = computed(() => [
  {
    title: 'Programs Analyzed',
    value: academicData.value?.employment_by_program?.programs?.length || 0,
    description: 'Active academic programs',
    icon: IconUsers,
    variant: 'primary'
  },
  {
    title: 'Best Employment Rate',
    value: academicData.value?.employment_by_program?.employment_rates ? 
      Math.max(...academicData.value.employment_by_program.employment_rates).toFixed(1) : 0,
    description: 'Top performing program',
    icon: IconUserCheck,
    variant: 'accent',
    format: 'percentage'
  },
  {
    title: 'Job-Course Alignment',
    value: academicData.value?.job_alignment_rate?.overall_rate || 0,
    description: 'Overall alignment rate',
    icon: IconBarChart3,
    variant: 'ghost',
    format: 'percentage'
  },
  {
    title: 'Avg. Time to Employment',
    value: academicData.value?.time_to_employment_by_program?.average_months && 
      academicData.value.time_to_employment_by_program.average_months.length > 0 ? 
      (academicData.value.time_to_employment_by_program.average_months.reduce((a, b) => a + b, 0) / 
       academicData.value.time_to_employment_by_program.average_months.length).toFixed(1) : 0,
    description: 'months across programs',
    icon: IconTrendingUp,
    variant: 'secondary',
    format: 'decimal'
  }
])

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

// Computed chart data
const employmentChartData = computed(() => {
  if (!academicData.value?.employment_by_program?.programs) return null
  
  const data = academicData.value.employment_by_program
  return {
    labels: data.programs || [],
    datasets: [{
      label: 'Employment Rate (%)',
      data: data.employment_rates || [],
      backgroundColor: chartColors.primary + '80',
      borderColor: chartColors.primary,
      borderWidth: 2
    }]
  }
})

const employmentChartOptions = computed(() => ({
  plugins: {
    legend: { display: false },
    tooltip: {
      callbacks: {
        afterLabel: function(context) {
          const data = academicData.value?.employment_by_program
          if (!data) return ''
          const index = context.dataIndex
          return `Employed: ${data.employed_counts?.[index] || 0}/${data.total_responses?.[index] || 0}`
        }
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      max: 100,
      ticks: { callback: function(value) { return value + '%' } }
    },
    x: { ticks: { maxRotation: 45 } }
  }
}))

const salaryChartData = computed(() => {
  if (!academicData.value?.salary_by_program?.programs?.length) return null
  
  const data = academicData.value.salary_by_program
  return {
    labels: data.programs || [],
    datasets: [{
      label: 'Average Salary (₱)',
      data: data.average_salaries || [],
      backgroundColor: chartColors.success + '80',
      borderColor: chartColors.success,
      borderWidth: 2
    }]
  }
})

const salaryChartOptions = computed(() => ({
  indexAxis: 'y',
  plugins: {
    legend: { display: false },
    tooltip: {
      callbacks: {
        label: function(context) { return `₱${context.parsed.x.toLocaleString()}` },
        afterLabel: function(context) {
          const data = academicData.value?.salary_by_program
          if (!data) return ''
          const index = context.dataIndex
          return `Based on ${data.total_employed?.[index] || 0} employed alumni`
        }
      }
    }
  },
  scales: {
    x: {
      beginAtZero: true,
      ticks: { callback: function(value) { return '₱' + value.toLocaleString() } }
    }
  }
}))

const alignmentChartData = computed(() => {
  if (!academicData.value?.job_alignment_rate) return null
  
  const alignmentRate = academicData.value.job_alignment_rate.overall_rate || 0
  return {
    labels: ['Job-Course Aligned', 'Not Aligned'],
    datasets: [{
      data: [alignmentRate, 100 - alignmentRate],
      backgroundColor: [chartColors.info + '80', '#E5E7EB'],
      borderColor: [chartColors.info, '#D1D5DB'],
      borderWidth: 2
    }]
  }
})

const alignmentChartOptions = computed(() => ({
  cutout: '70%',
  plugins: {
    legend: { position: 'bottom' },
    tooltip: {
      callbacks: {
        label: function(context) { return context.label + ': ' + context.parsed + '%' }
      }
    }
  }
}))

const timeToEmploymentChartData = computed(() => {
  if (!academicData.value?.time_to_employment_by_program?.programs?.length) return null
  
  const data = academicData.value.time_to_employment_by_program
  return {
    labels: data.programs || [],
    datasets: [
      {
        label: 'Average Months',
        data: data.average_months || [],
        borderColor: chartColors.warning,
        backgroundColor: chartColors.warning + '20',
        borderWidth: 3,
        fill: true,
        tension: 0.4
      },
      {
        label: 'Min Months',
        data: data.min_months || [],
        borderColor: chartColors.success,
        backgroundColor: chartColors.success + '20',
        borderWidth: 2,
        borderDash: [5, 5]
      },
      {
        label: 'Max Months',
        data: data.max_months || [],
        borderColor: chartColors.error,
        backgroundColor: chartColors.error + '20',
        borderWidth: 2,
        borderDash: [5, 5]
      }
    ]
  }
})

const timeToEmploymentChartOptions = computed(() => ({
  plugins: {
    legend: { position: 'top' },
    tooltip: {
      callbacks: {
        afterLabel: function(context) {
          const data = academicData.value?.time_to_employment_by_program
          if (!data) return ''
          const index = context.dataIndex
          return `Based on ${data.total_employed?.[index] || 0} employed alumni`
        }
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: { callback: function(value) { return value + ' months' } }
    },
    x: { ticks: { maxRotation: 45 } }
  }
}))

const loadAllData = async () => {
  try {
    loading.value = true
    error.value = null
    
    await Promise.all([
      fetchDashboardData(),
      fetchAcademicExcellenceData()
    ])
    
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

<style scoped>
h1, p {
  color: var(--color-text-primary);
}

h2, h3 {
  color: var(--color-text-invert);
}

.font-medium {
  color: var(--color-text-light);
}
</style>