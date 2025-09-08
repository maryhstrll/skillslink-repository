<template>
  <div class="card card-chart-primary shadow-xl">
    <div class="card-body">
      <h2 class="card-title flex items-center" :class="titleClass">
        <component :is="icon" v-if="icon" class="w-5 h-5" :class="iconClass" />
        {{ title }}
      </h2>
      <div class="h-80">
        <canvas :ref="setCanvasRef"></canvas>
      </div>
      <div v-if="showStats" class="mt-4 text-center">
        <div class="text-2xl font-bold" :class="statsValueClass">
          {{ statsValue }}
        </div>
        <div class="text-sm text-base-content/70">{{ statsLabel }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  icon: {
    type: [String, Object],
    default: null
  },
  iconClass: {
    type: String,
    default: ''
  },
  titleClass: {
    type: String,
    default: 'text-primary'
  },
  chartType: {
    type: String,
    required: true,
    validator: (value) => ['bar', 'line', 'doughnut', 'pie'].includes(value)
  },
  chartData: {
    type: Object,
    required: true
  },
  chartOptions: {
    type: Object,
    default: () => ({})
  },
  showStats: {
    type: Boolean,
    default: false
  },
  statsValue: {
    type: [String, Number],
    default: ''
  },
  statsLabel: {
    type: String,
    default: ''
  },
  statsValueClass: {
    type: String,
    default: 'text-info'
  }
})

const canvasRef = ref(null)
let chartInstance = null

const setCanvasRef = (el) => {
  canvasRef.value = el
}

const defaultOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true
    }
  }
}

const createChart = () => {
  if (!canvasRef.value || !props.chartData) {
    console.log('Chart creation skipped - canvas or data not available:', {
      canvas: !!canvasRef.value,
      data: !!props.chartData
    })
    return
  }

  // Destroy existing chart
  if (chartInstance) {
    chartInstance.destroy()
  }

  try {
    // Merge default options with provided options
    const options = {
      ...defaultOptions,
      ...props.chartOptions
    }

    chartInstance = new Chart(canvasRef.value, {
      type: props.chartType,
      data: props.chartData,
      options: options
    })
    
    console.log('Chart created successfully:', props.title)
  } catch (error) {
    console.error('Error creating chart:', error)
  }
}

// Watch for data changes
watch(() => props.chartData, (newData) => {
  if (newData) {
    nextTick(() => {
      setTimeout(createChart, 100)
    })
  }
}, { deep: true })

watch(() => props.chartOptions, () => {
  if (props.chartData) {
    nextTick(() => {
      setTimeout(createChart, 100)
    })
  }
}, { deep: true })

onMounted(() => {
  // Small delay to ensure canvas is ready
  nextTick(() => {
    setTimeout(createChart, 200)
  })
})

onUnmounted(() => {
  if (chartInstance) {
    chartInstance.destroy()
  }
})

defineExpose({
  chartInstance,
  createChart
})
</script>
