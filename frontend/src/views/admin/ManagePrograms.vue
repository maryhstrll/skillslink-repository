<template>
  <Layout @logout="handleLogout">
    <div class="space-y-4 sm:space-y-6 p-3 sm:p-4">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0">
          <div>
            <h1 class="text-3xl font-bold text-base-content">Programs Management</h1>
            <p class="text-base-content/70 mt-1">
              Manage programs and it's information
            </p>
          </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
          <button
            class="btn btn-primary flex items-center gap-2 text-sm"
            @click="openAddModal"
          >
            <IconPlus :size="16" />
            <span class="hidden sm:inline">Add New Program</span>
            <span class="sm:hidden">Add Program</span>
          </button>
        </div>
      </div>

      <!-- Programs DataTable with height restriction -->
      <div class="programs-table-container">
        <DataTable
          title="List of Programs"
          :data="programs"
          :columns="columns"
          :loading="loading"
          :title-icon="IconSquareLibrary"
          :count-icon="IconBarChart3"
          item-label="programs"
          loading-text="Loading programs..."
          empty-title="No Programs Found"
          empty-description="No programs have been added yet. Click 'Add New Program' to get started."
          :empty-icon="IconSquareLibrary"
        >
        <!-- Program Type Badge -->
        <template #cell-program_type="{ value }">
          <span 
            class="badge text-xs font-medium px-2 py-1"
            :class="{
              'badge-success': value === 'certificate',
              'badge-info': value === 'diploma', 
              'badge-warning': value === 'degree'
            }"
          >
            {{ formatProgramType(value) }}
          </span>
        </template>

        <!-- Status Badge -->
        <template #cell-is_active="{ value }">
          <span 
            class="badge text-xs font-medium px-2 py-1"
            :class="value ? 'badge-success' : 'badge-error'"
          >
            {{ value ? 'Active' : 'Inactive' }}
          </span>
        </template>

        <!-- Description with truncation -->
        <template #cell-description="{ value }">
          <div class="max-w-xs">
            <p class="text-sm truncate" :title="value">
              {{ value || 'No description' }}
            </p>
          </div>
        </template>

        <!-- Actions Column -->
        <template #cell-actions="{ item }">
          <div class="flex items-center gap-2">
            <button
              @click="openEditModal(item)"
              class="btn btn-circle btn-sm btn-ghost text-blue-600 hover:bg-blue-100"
              title="Edit Program"
            >
              <IconEdit :size="16" />
            </button>
            <button
              @click="confirmDelete(item)"
              class="btn btn-circle btn-sm btn-ghost text-red-600 hover:bg-red-100"
              title="Delete Program"
              :disabled="item.is_active === false"
            >
              <IconTrash :size="16" />
            </button>
          </div>
        </template>

        <!-- Empty state actions -->
        <template #empty-actions>
          <button
            @click="openAddModal"
            class="btn btn-primary"
          >
            <IconPlus :size="16" class="mr-2" />
            Add Your First Program
          </button>
        </template>
      </DataTable>
      </div>
    </div>

    <!-- Program Modal -->
    <ProgramModal
      :is-open="modalOpen"
      :program="selectedProgram"
      @close="closeModal"
      @submit="handleProgramSubmit"
    />

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="fixed inset-0 bg-black bg-opacity-50" @click="cancelDelete"></div>
      <div class="relative bg-white rounded-lg shadow-lg p-6 max-w-md mx-4">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
            <IconAlertTriangle class="w-6 h-6 text-red-600" />
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Delete Program</h3>
            <p class="text-sm text-gray-600">This action cannot be undone.</p>
          </div>
        </div>
        
        <p class="text-gray-700 mb-6">
          Are you sure you want to delete <strong>{{ programToDelete?.program_name }}</strong>?
        </p>
        
        <div class="flex justify-end gap-3">
          <button @click="cancelDelete" class="btn btn-ghost">
            Cancel
          </button>
          <button @click="deleteProgram" class="btn btn-error" :disabled="deleteLoading">
            <span v-if="deleteLoading" class="loading loading-spinner loading-sm"></span>
            Delete Program
          </button>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import Layout from '@/components/layout/Layout.vue'
import DataTable from '@/components/tables/DataTable.vue'
import ProgramModal from '@/components/modals/ProgramModal.vue'
import programsService from '@/services/programs.js'
import { 
  Plus as IconPlus, 
  SquareLibrary as IconSquareLibrary, 
  BarChart3 as IconBarChart3,
  Edit as IconEdit,
  Trash as IconTrash,
  AlertTriangle as IconAlertTriangle
} from 'lucide-vue-next'

const router = useRouter()

// Data state
const programs = ref([])
const loading = ref(false)
const modalOpen = ref(false)
const selectedProgram = ref(null)
const showDeleteConfirm = ref(false)
const programToDelete = ref(null)
const deleteLoading = ref(false)

// Table columns configuration
const columns = [
  { 
    key: 'program_code', 
    title: 'Program Code',
    headerClass: 'text-left',
    cellClass: 'font-mono text-sm'
  },
  { 
    key: 'program_name', 
    title: 'Program Name',
    headerClass: 'text-left',
    cellClass: 'font-medium'
  },
  { 
    key: 'program_type', 
    title: 'Type',
    headerClass: 'text-center',
    cellClass: 'text-center'
  },
  { 
    key: 'department', 
    title: 'Department',
    headerClass: 'text-left'
  },
  { 
    key: 'duration', 
    title: 'Duration',
    headerClass: 'text-center',
    cellClass: 'text-center'
  },
  { 
    key: 'description', 
    title: 'Description',
    headerClass: 'text-left'
  },
  { 
    key: 'is_active', 
    title: 'Status',
    headerClass: 'text-center',
    cellClass: 'text-center'
  },
  { 
    key: 'actions', 
    title: 'Actions',
    headerClass: 'text-center',
    cellClass: 'text-center'
  }
]

// Lifecycle
onMounted(() => {
  fetchPrograms()
})

// Methods
const fetchPrograms = async () => {
  loading.value = true
  try {
    const data = await programsService.list()
    programs.value = data || []
  } catch (error) {
    console.error('Failed to fetch programs:', error)
    // You might want to show a toast notification here
  } finally {
    loading.value = false
  }
}

const openAddModal = () => {
  selectedProgram.value = null
  modalOpen.value = true
}

const openEditModal = (program) => {
  selectedProgram.value = { ...program }
  modalOpen.value = true
}

const closeModal = () => {
  modalOpen.value = false
  selectedProgram.value = null
}

const handleProgramSubmit = async (formData) => {
  try {
    if (selectedProgram.value) {
      // Edit mode
      await programsService.update(selectedProgram.value.program_id, formData)
    } else {
      // Add mode
      await programsService.create(formData)
    }
    
    // Refresh the programs list
    await fetchPrograms()
    
    // You might want to show a success toast notification here
  } catch (error) {
    console.error('Failed to save program:', error)
    // You might want to show an error toast notification here
    throw error // Re-throw to prevent modal from closing
  }
}

const confirmDelete = (program) => {
  programToDelete.value = program
  showDeleteConfirm.value = true
}

const cancelDelete = () => {
  showDeleteConfirm.value = false
  programToDelete.value = null
}

const deleteProgram = async () => {
  if (!programToDelete.value) return
  
  deleteLoading.value = true
  try {
    await programsService.delete(programToDelete.value.program_id)
    await fetchPrograms()
    showDeleteConfirm.value = false
    programToDelete.value = null
    // You might want to show a success toast notification here
  } catch (error) {
    console.error('Failed to delete program:', error)
    // You might want to show an error toast notification here
  } finally {
    deleteLoading.value = false
  }
}

const formatProgramType = (type) => {
  const types = {
    'certificate': 'Certificate',
    'diploma': 'Diploma', 
    'degree': 'Degree'
  }
  return types[type] || type
}

const handleLogout = () => {
  router.push('/home')
}
</script>

<style scoped>
h1, p {
  color: var(--color-text-primary);
}

/* Custom table height restriction */
.programs-table-container :deep(.card) {
  height: 70vh;
  display: flex;
  flex-direction: column;
}

.programs-table-container :deep(.card-body) {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.programs-table-container :deep(.overflow-x-auto) {
  flex: 1;
  max-height: calc(75vh - 120px); /* Subtract header space */
  overflow: auto !important;
}

.programs-table-container :deep(.table-container) {
  height: 100%;
  overflow: auto;
}
</style>
