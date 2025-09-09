
<template>
  <Layout @logout="handleLogout">
      <div class="max-w-7xl mx-auto space-y-6">
        <!-- Page Header -->
        <PageHeader
          title="Alumni Directory"
          description="Manage and view all alumni records, including their personal information, academic background, and contact details."
          :title-icon="IconUsers"
          badge="Directory"
          badge-type="primary"
        >
          <template #actions>
            <button 
              class="btn btn-primary-custom shadow-lg hover:shadow-xl transition-all duration-200"
              @click="openAddModal"
            >
              <IconPlus class="w-4 h-4" />
              Add Alumni
            </button>
            <button 
              class="btn btn-neutral shadow-lg hover:shadow-xl transition-all duration-200"
              @click="fetchAlumni"
              :disabled="loading"
            >
              <IconRefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
              Refresh
            </button>
          </template>

          <template #subtitle>
            <div class="flex flex-wrap gap-4 items-center text-sm text-gray-600">
              <div class="flex items-center gap-2">
                <span class="font-medium">Total Alumni:</span>
                <span class="badge badge-ghost">{{ alumniList.length }}</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="font-medium">Filtered Results:</span>
                <span class="badge badge-accent">{{ filteredAlumni.length }}</span>
              </div>
              <div v-if="hasActiveFilters" class="flex items-center gap-2">
                <span class="text-orange-600 font-medium">Filters Active</span>
                <button 
                  class="btn btn-xs btn-outline"
                  @click="clearFilters"
                >
                  Clear All
                </button>
              </div>
            </div>
          </template>
        </PageHeader>

        <!-- Filters Section -->
        <div class="card app-surface shadow-lg">
          <div class="p-4 app-surface-alt">
            <h3 class="text-lg font-semibold mb-4" style="color: var(--color-text-primary);">Filter Alumni</h3>
            <div class="flex flex-col sm:flex-row gap-4">
              <!-- Search Filter -->
              <div class="form-control flex-1">
                <div class="input-group">
                  <input 
                    v-model="searchQuery" 
                    type="text" 
                    placeholder="Search by name, student ID..." 
                    class="input input-bordered flex-1 app-surface app-border"
                  />
                  <button class="btn btn-primary-custom">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                  </button>
                </div>
              </div>
              
              <!-- Program Filter -->
              <select 
                v-model="selectedProgram" 
                class="select select-bordered app-surface app-border min-w-[200px]"
              >
                <option value="">All Programs</option>
                <option v-for="program in programs" 
                        :key="program.id || program.program_id" 
                        :value="program.id || program.program_id">
                  {{ program.name || program.program_name }}
                </option>
              </select>
              
              <!-- Year Graduated Filter -->
              <select 
                v-model="selectedYear" 
                class="select select-bordered app-surface app-border min-w-[160px]"
              >
                <option value="">All Years</option>
                <option v-for="year in availableYears" 
                        :key="year" 
                        :value="year">
                  {{ year }}
                </option>
              </select>
              
              <!-- Clear Filters Button -->
              <button 
                class="btn btn-ghost-custom"
                @click="clearFilters"
                :disabled="!hasActiveFilters"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Clear
              </button>
            </div>
            
            <!-- Filter Results Summary -->
            <div class="mt-3 text-sm" style="color: var(--color-text-secondary);">
              Showing {{ filteredAlumni.length }} of {{ alumniList.length }} alumni
            </div>
          </div>
        </div>

        <!-- Alumni DataTable -->
        <DataTable
          title="Alumni Directory"
          :title-icon="IconUsers"
          :count-icon="IconUserCheck"
          :data="filteredAlumni"
          :columns="tableColumns"
          :loading="loading"
          item-label="alumni"
          empty-title="No alumni found"
          :empty-description="alumniList.length === 0 ? 'No alumni records have been added yet' : 'Try adjusting your filters to see more results'"
          :empty-icon="IconUsers"
          key-field="alumni_id"
          loading-text="Loading alumni data..."
        >
          <!-- Custom cell for student ID -->
          <template #cell-student_id="{ value }">
            <span class="font-mono text-sm" style="color: var(--color-primary);">{{ value }}</span>
          </template>

          <!-- Custom cell for full name -->
          <template #cell-full_name="{ item }">
            <div class="font-semibold" style="color: var(--color-text-primary);">
              {{ item.first_name }} {{ item.last_name }}
              <div v-if="item.middle_name" class="text-xs font-normal opacity-70">
                {{ item.middle_name }}
              </div>
            </div>
          </template>

          <!-- Custom cell for program -->
          <template #cell-program_name="{ item }">
            <span style="color: var(--color-primary);">
              {{ item.program_name || getProgramName(item.program_id) }}
            </span>
          </template>

          <!-- Custom cell for year graduated -->
          <template #cell-year_graduated="{ value }">
            <div class="badge" style="background: var(--color-accent); color: white; border: none;">
              {{ value }}
            </div>
          </template>

          <!-- Custom cell for phone -->
          <template #cell-phone_number="{ value }">
            <span style="color: var(--color-text-primary);">{{ value || '-' }}</span>
          </template>

          <!-- Custom cell for city -->
          <template #cell-city="{ value }">
            <span style="color: var(--color-text-primary);">{{ value || '-' }}</span>
          </template>

          <!-- Custom cell for actions -->
          <template #cell-actions="{ item }">
            <div class="flex gap-2">
              <button 
                class="btn btn-sm btn-primary-custom shadow-lg hover:shadow-xl transition-all duration-200" 
                @click="openEditModal(item)"
                :disabled="loading"
                title="Edit Alumni"
              >
                <IconEdit class="w-4 h-4" />
              </button>
              <button 
                class="btn btn-sm btn-accent-custom shadow-lg hover:shadow-xl transition-all duration-200" 
                @click="viewProfile(item)"
                :disabled="loading"
                title="View Profile"
              >
                <IconEye class="w-4 h-4" />
              </button>
            </div>
          </template>
        </DataTable>

        <!-- Add/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
          <div class="card app-surface shadow-2xl w-full max-w-lg relative">
            <!-- Modal Header -->
            <div class="p-6 app-surface-alt">
              <h3 class="text-xl md:text-2xl font-bold" style="color: var(--color-primary);">
                {{ isEditMode ? 'Edit Alumni' : 'Add Alumni' }}
              </h3>
              <p class="mt-1" style="color: var(--color-text-secondary);">
                {{ isEditMode ? 'Update alumni information' : 'Add new alumni to the directory' }}
              </p>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
              <!-- Error message in modal -->
              <div v-if="error" class="alert alert-error mb-4 rounded-xl" style="background: rgba(var(--color-danger-rgb), 0.1); border: 1px solid var(--color-danger); color: var(--color-danger);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <span>{{ error }}</span>
              </div>

              <form @submit.prevent="isEditMode ? updateAlumni() : addAlumni()" class="space-y-4">
                <!-- Personal Information -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  <div class="form-control">
                    <label class="label">
                      <span class="label-text font-medium" style="color: var(--color-text-primary);">First Name *</span>
                    </label>
                    <input v-model="form.first_name" 
                           class="input input-bordered w-full app-surface app-border" 
                           placeholder="Enter first name" 
                           required />
                  </div>
                  <div class="form-control">
                    <label class="label">
                      <span class="label-text text-[#081F37] font-medium">Middle Name</span>
                    </label>
                    <input v-model="form.middle_name" 
                           class="input input-bordered w-full focus:border-[#2E79BA] focus:ring-2 focus:ring-[#5FC9F3]/20" 
                           placeholder="Enter middle name" />
                  </div>
                  <div class="form-control">
                    <label class="label">
                      <span class="label-text text-[#081F37] font-medium">Last Name *</span>
                    </label>
                    <input v-model="form.last_name" 
                           class="input input-bordered w-full focus:border-[#2E79BA] focus:ring-2 focus:ring-[#5FC9F3]/20" 
                           placeholder="Enter last name" 
                           required />
                  </div>
                </div>
                
                <!-- Academic Information -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="form-control">
                    <label class="label">
                      <span class="label-text text-[#081F37] font-medium">Student ID *</span>
                    </label>
                    <input v-model="form.student_id" 
                           class="input input-bordered w-full focus:border-[#2E79BA] focus:ring-2 focus:ring-[#5FC9F3]/20" 
                           placeholder="Enter student ID" 
                           required />
                  </div>
                  <div class="form-control">
                    <label class="label">
                      <span class="label-text text-[#081F37] font-medium">Program *</span>
                    </label>
                    <select v-model="form.program_id" 
                            class="select select-bordered w-full focus:border-[#2E79BA] focus:ring-2 focus:ring-[#5FC9F3]/20"
                            :disabled="loadingPrograms" 
                            required>
                      <option value="" disabled>{{ loadingPrograms ? 'Loading programs...' : 'Select program' }}</option>
                      <option v-for="program in programs" 
                              :key="program.id || program.program_id" 
                              :value="program.id || program.program_id">
                        {{ program.name || program.program_name }}
                      </option>
                    </select>
                  </div>
                </div>
                
                <div class="form-control">
                  <label class="label">
                    <span class="label-text text-[#081F37] font-medium">Year Graduated *</span>
                  </label>
                  <input v-model="form.year_graduated" 
                         class="input input-bordered w-full focus:border-[#2E79BA] focus:ring-2 focus:ring-[#5FC9F3]/20" 
                         placeholder="Enter graduation year" 
                         required 
                         type="number" 
                         min="1900" 
                         :max="new Date().getFullYear()" />
                </div>

                <!-- Contact Information -->
                <div class="form-control">
                  <label class="label">
                    <span class="label-text text-[#081F37] font-medium">Phone Number</span>
                  </label>
                  <input v-model="form.phone_number" 
                         class="input input-bordered w-full focus:border-[#2E79BA] focus:ring-2 focus:ring-[#5FC9F3]/20" 
                         placeholder="Enter phone number" 
                         type="tel" />
                </div>

                <!-- Barangay Information -->
                <div class="form-control">
                  <label class="label">
                    <span class="label-text text-[#081F37] font-medium">Barangay</span>
                  </label>
                  <textarea v-model="form.barangay" 
                           class="textarea textarea-bordered w-full focus:border-[#2E79BA] focus:ring-2 focus:ring-[#5FC9F3]/20" 
                           placeholder="Enter barangay" 
                           rows="2"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="form-control">
                    <label class="label">
                      <span class="label-text text-[#081F37] font-medium">City</span>
                    </label>
                    <input v-model="form.city" 
                           class="input input-bordered w-full focus:border-[#2E79BA] focus:ring-2 focus:ring-[#5FC9F3]/20" 
                           placeholder="Enter city" />
                  </div>
                  <div class="form-control">
                    <label class="label">
                      <span class="label-text text-[#081F37] font-medium">Province</span>
                    </label>
                    <input v-model="form.province" 
                           class="input input-bordered w-full focus:border-[#2E79BA] focus:ring-2 focus:ring-[#5FC9F3]/20" 
                           placeholder="Enter province" />
                  </div>
                </div>

                <!-- Social Media Links -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="form-control">
                    <label class="label">
                      <span class="label-text text-[#081F37] font-medium">LinkedIn Profile</span>
                    </label>
                    <input v-model="form.linkedin_profile" 
                           class="input input-bordered w-full focus:border-[#2E79BA] focus:ring-2 focus:ring-[#5FC9F3]/20" 
                           placeholder="https://linkedin.com/in/..." 
                           type="url" />
                  </div>
                  <div class="form-control">
                    <label class="label">
                      <span class="label-text text-[#081F37] font-medium">Facebook Profile</span>
                    </label>
                    <input v-model="form.facebook_profile" 
                           class="input input-bordered w-full focus:border-[#2E79BA] focus:ring-2 focus:ring-[#5FC9F3]/20" 
                           placeholder="https://facebook.com/..." 
                           type="url" />
                  </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4">
                  <button type="button" 
                          class="btn btn-ghost-custom w-full sm:w-auto" 
                          @click="closeModal"
                          :disabled="loading">
                    Cancel
                  </button>
                  <button type="submit" 
                          class="btn btn-primary-custom w-full sm:w-auto"
                          :disabled="loading">
                    <span v-if="loading" class="loading loading-spinner loading-sm mr-2"></span>
                    <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                    </svg>
                    {{ loading ? 'Saving...' : (isEditMode ? 'Update Alumni' : 'Add Alumni') }}
                  </button>
                </div>
              </form>
            </div>

            <!-- Close Button -->
            <button class="absolute top-4 right-4 btn btn-sm btn-circle btn-ghost-custom" 
                    @click="closeModal">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>

    <!-- Admin Alumni Profile Modal -->
    <AdminAlumniProfileModal 
      :show="showProfileModal"
      :alumni="selectedAlumni"
      @close="closeProfileModal"
      @edit="editFromProfile"
    />
  </Layout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import Layout from '@/components/layout/Layout.vue'
import DataTable from '@/components/tables/DataTable.vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import AdminAlumniProfileModal from '@/components/modals/AdminAlumniProfileModal.vue'
import { useRouter } from 'vue-router'
import alumniService from '@/services/alumni.js'
import programsService from '@/services/programs.js'
import { messageService } from '@/services/messageService.js'
import { 
  Users as IconUsers, 
  UserCheck as IconUserCheck, 
  Eye as IconEye, 
  Edit as IconEdit,
  Plus as IconPlus,
  RefreshCw as IconRefreshCw
} from 'lucide-vue-next'

const router = useRouter()

// State for alumni list and modal
const alumniList = ref([])
const programs = ref([])
const showModal = ref(false)
const showProfileModal = ref(false)
const selectedAlumni = ref(null)
const isEditMode = ref(false)
const loading = ref(false)
const loadingPrograms = ref(false)

// Filter states
const searchQuery = ref('')
const selectedProgram = ref('')
const selectedYear = ref('')

const form = reactive({
  alumni_id: null,
  first_name: '',
  last_name: '',
  middle_name: '',
  student_id: '',
  program_id: '',
  year_graduated: '',
  phone_number: '',
  barangay: '',
  city: '',
  province: '',
  linkedin_profile: '',
  facebook_profile: ''
})

// Computed properties for filtering
const availableYears = computed(() => {
  const years = [...new Set(alumniList.value.map(alumni => alumni.year_graduated))].filter(Boolean)
  return years.sort((a, b) => b - a) // Sort descending (newest first)
})

const filteredAlumni = computed(() => {
  let filtered = alumniList.value

  // Search filter
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase().trim()
    filtered = filtered.filter(alumni => {
      const fullName = `${alumni.first_name} ${alumni.middle_name || ''} ${alumni.last_name}`.toLowerCase()
      return fullName.includes(query) || 
             alumni.student_id?.toLowerCase().includes(query) ||
             alumni.program_name?.toLowerCase().includes(query)
    })
  }

  // Program filter
  if (selectedProgram.value) {
    filtered = filtered.filter(alumni => alumni.program_id == selectedProgram.value)
  }

  // Year graduated filter
  if (selectedYear.value) {
    filtered = filtered.filter(alumni => alumni.year_graduated == selectedYear.value)
  }

  return filtered
})

const hasActiveFilters = computed(() => {
  return searchQuery.value.trim() !== '' || selectedProgram.value !== '' || selectedYear.value !== ''
})

// Table columns configuration for DataTable
const tableColumns = computed(() => [
  {
    key: 'student_id',
    title: 'Student ID',
    cellClass: 'font-mono text-sm'
  },
  {
    key: 'full_name',
    title: 'Name',
    cellClass: 'font-semibold'
  },
  {
    key: 'program_name',
    title: 'Program'
  },
  {
    key: 'year_graduated',
    title: 'Year Graduated'
  },
  {
    key: 'phone_number',
    title: 'Phone'
  },
  {
    key: 'city',
    title: 'City'
  },
  {
    key: 'actions',
    title: 'Actions',
    cellClass: 'w-32'
  }
])

const clearFilters = () => {
  searchQuery.value = ''
  selectedProgram.value = ''
  selectedYear.value = ''
}

// Fetch alumni records from the database
const fetchAlumni = async () => {
  try {
    loading.value = true
    const data = await alumniService.getAll()
    alumniList.value = data
    messageService.toast('Alumni data loaded successfully', 'success', 2000)
  } catch (err) {
    console.error('Error fetching alumni:', err)
    messageService.toast('Failed to load alumni data. Please try again.', 'error')
    // Fallback to empty array on error
    alumniList.value = []
  } finally {
    loading.value = false
  }
}

// Fetch programs for the dropdown
const fetchPrograms = async () => {
  try {
    loadingPrograms.value = true
    const data = await programsService.list()
    programs.value = data
  } catch (err) {
    console.error('Error fetching programs:', err)
    messageService.toast('Failed to load programs', 'error')
    programs.value = []
  } finally {
    loadingPrograms.value = false
  }
}

onMounted(() => {
  fetchAlumni()
  fetchPrograms()
})

// Modal controls
const openAddModal = () => {
  isEditMode.value = false
  Object.assign(form, { 
    alumni_id: null, 
    first_name: '', 
    last_name: '', 
    middle_name: '',
    student_id: '', 
    program_id: '', 
    year_graduated: '',
    phone_number: '',
    barangay: '',
    city: '',
    province: '',
    linkedin_profile: '',
    facebook_profile: ''
  })
  showModal.value = true
}

const openEditModal = (alumni) => {
  isEditMode.value = true
  Object.assign(form, {
    alumni_id: alumni.alumni_id,
    first_name: alumni.first_name,
    last_name: alumni.last_name,
    middle_name: alumni.middle_name || '',
    student_id: alumni.student_id,
    program_id: alumni.program_id,
    year_graduated: alumni.year_graduated,
    phone_number: alumni.phone_number || '',
    barangay: alumni.barangay || '',
    city: alumni.city || '',
    province: alumni.province || '',
    linkedin_profile: alumni.linkedin_profile || '',
    facebook_profile: alumni.facebook_profile || ''
  })
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

// CRUD operations using the real API
const addAlumni = async () => {
  try {
    loading.value = true
    
    const response = await alumniService.create(form)
    
    if (response.success) {
      await fetchAlumni() // Refresh the list
      closeModal()
      messageService.toast('Alumni added successfully!', 'success')
    } else {
      messageService.alert(response.message || 'Failed to add alumni', 'error')
    }
  } catch (err) {
    console.error('Error adding alumni:', err)
    messageService.alert('Failed to add alumni. Please try again.', 'error')
  } finally {
    loading.value = false
  }
}

const updateAlumni = async () => {
  try {
    loading.value = true
    
    const response = await alumniService.update(form.alumni_id, form)
    
    if (response.success) {
      await fetchAlumni() // Refresh the list
      closeModal()
      messageService.toast('Alumni updated successfully!', 'success')
    } else {
      messageService.alert(response.message || 'Failed to update alumni', 'error')
    }
  } catch (err) {
    console.error('Error updating alumni:', err)
    messageService.alert('Failed to update alumni. Please try again.', 'error')
  } finally {
    loading.value = false
  }
}



const viewProfile = (alumni) => {
  selectedAlumni.value = alumni
  showProfileModal.value = true
}

const closeProfileModal = () => {
  showProfileModal.value = false
  selectedAlumni.value = null
}

const editFromProfile = (alumni) => {
  // Open the edit modal from the profile modal
  openEditModal(alumni)
}

const getProgramName = (programId) => {
  if (!programId || !programs.value.length) return `Program ${programId}`
  const program = programs.value.find(p => (p.id || p.program_id) == programId)
  return program ? (program.name || program.program_name) : `Program ${programId}`
}

const handleLogout = () => {
  router.push('/home')
}

</script>

<style scoped>
/* Custom table styling to match TracerFormTable */
h1, p {
  color: var(--color-text-primary);
}
.table th {
  background: var(--color-neutral);
  border-bottom: 2px solid var(--color-border);
  padding: 1rem 0.75rem;
  font-weight: 600;
}

.table td {
  border-bottom: 1px solid var(--color-border-light);
  padding: 1rem 0.75rem;
}

.table tr:hover {
  background: rgba(var(--color-primary-rgb), 0.05);
}

.table tr:last-child td {
  border-bottom: none;
}

/* Form inputs consistent with app design */
.input, .select, .textarea {
  background: var(--color-text-invert);
  border-color: var(--color-border);
  color: var(--color-text-primary);
}

.input:focus, .select:focus, .textarea:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(var(--color-primary-rgb), 0.1);
}

/* Custom scrollbar for mobile card view */
.max-h-96::-webkit-scrollbar {
  width: 6px;
}

.max-h-96::-webkit-scrollbar-track {
  background: var(--color-surface-alt);
  border-radius: 3px;
}

.max-h-96::-webkit-scrollbar-thumb {
  background: rgb(var(--color-primary-rgb) / 0.6);
  border-radius: 3px;
}

.max-h-96::-webkit-scrollbar-thumb:hover {
  background: rgb(var(--color-primary-rgb) / 0.8);
}

/* Button hover effects */
.btn:hover {
  transform: translateY(-1px);
}
/* Modal styling */
.modal-box {
  background: var(--color-surface-main);
  color: var(--color-text-primary);
}

/* Badge styling */
.badge {
  font-weight: 500;
  font-size: 0.75rem;
}

/* Loading states */
.loading {
  color: var(--color-primary);
}

/* Alert styling */
.alert {
  border-radius: 0.75rem;
}
</style>