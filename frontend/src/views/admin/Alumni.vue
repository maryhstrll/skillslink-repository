
<template>
  <Layout @logout="handleLogout">
    <div class="min-h-screen bg-gradient-to-br from-[#081F37] to-[#1E549F] p-4 md:p-6 lg:p-8">
      <div class="max-w-7xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white/10 backdrop-blur-sm rounded p-6 shadow-xl border border-white/20">
          <div class="text-white">
            <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold mb-2 bg-gradient-to-r from-[#5FC9F3] to-[#2E79BA] bg-clip-text text-transparent">
              Alumni Management
            </h1>
            <p class="text-white/80 text-sm md:text-base">Manage and track alumni information</p>
          </div>
            <div class="text-right">
              <div class="stat-title text-[15px] text-white">Total Alumni</div>
              <div class="stat-value text-5xl text-[#5FC9F3]">
                {{ loading ? '...' : (hasActiveFilters ? `${filteredAlumni.length} / ${alumniList.length}` : alumniList.length) }}
              </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white/95 backdrop-blur-sm rounded shadow-xl border border-white/30 overflow-hidden">
          <div class="p-4 bg-gradient-to-r from-[#2E79BA]/10 to-[#1E549F]/10">
            <h3 class="text-lg font-semibold text-[#081F37] mb-4">Filter Alumni</h3>
            <div class="flex flex-col sm:flex-row gap-4">
              <!-- Search Filter -->
              <div class="form-control flex-1">
                <div class="input-group">
                  <input 
                    v-model="searchQuery" 
                    type="text" 
                    placeholder="Search by name, student ID..." 
                    class="input input-bordered flex-1 focus:border-[#2E79BA] focus:ring-2 focus:ring-[#5FC9F3]/20"
                  />
                  <button class="btn btn-square bg-[#2E79BA] text-white border-none hover:bg-[#1E549F]">
                    <i class="fas fa-search"></i>
                  </button>
                </div>
              </div>
              
              <!-- Program Filter -->
              <select 
                v-model="selectedProgram" 
                class="select select-bordered focus:border-[#2E79BA] focus:ring-2 focus:ring-[#5FC9F3]/20 min-w-[200px]"
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
                class="select select-bordered focus:border-[#2E79BA] focus:ring-2 focus:ring-[#5FC9F3]/20 min-w-[160px]"
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
                class="btn bg-gray-200 text-gray-700 border-none hover:bg-gray-300"
                @click="clearFilters"
                :disabled="!hasActiveFilters"
              >
                <i class="fas fa-times mr-2"></i>
                Clear
              </button>
            </div>
            
            <!-- Filter Results Summary -->
            <div class="mt-3 text-sm text-gray-600">
              Showing {{ filteredAlumni.length }} of {{ alumniList.length }} alumni
            </div>
          </div>
        </div>

        <!-- Alumni Table/Cards -->
        <div class="bg-white/95 backdrop-blur-sm rounded shadow-2xl border border-white/30 overflow-hidden">
          <div class="p-6 bg-gradient-to-r from-[#2E79BA] to-[#1E549F]">
            <h2 class="text-xl md:text-2xl font-bold text-white mb-2">Alumni Directory</h2>
            <p class="text-white/80">Complete list of registered alumni</p>
          </div>
          
          <!-- Mobile Card View (hidden on desktop) -->
          <div class="block lg:hidden p-4 space-y-4 max-h-96 overflow-y-auto">
            <div v-if="loading" class="text-center py-8">
              <div class="loading loading-spinner loading-lg text-[#2E79BA]"></div>
              <p class="text-gray-500 mt-4">Loading alumni data...</p>
            </div>
            <div v-else-if="filteredAlumni.length === 0" class="text-center py-8 text-gray-500">
              <IconUsers class="w-16 h-16 mb-4 text-gray-300 mx-auto" />
              <p>{{ alumniList.length === 0 ? 'No alumni records found.' : 'No alumni match your filters.' }}</p>
            </div>
            <div v-else v-for="alumni in filteredAlumni" :key="alumni.alumni_id" 
                 class="card bg-gradient-to-r from-white to-gray-50 shadow-md border border-gray-200 hover:shadow-lg transition-all duration-300">
              <div class="card-body p-4">
                <div class="flex justify-between items-start mb-3">
                  <div>
                    <h3 class="font-bold text-[#081F37] text-lg">{{ alumni.first_name }} {{ alumni.last_name }}</h3>
                  </div>
                  <div class="badge bg-[#5FC9F3] text-white border-none">{{ alumni.year_graduated }}</div>
                </div>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-600">Student ID:</span>
                    <span class="font-medium text-[#2E79BA]">{{ alumni.student_id }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Program:</span>
                    <span class="font-medium text-[#1E549F]">{{ alumni.program_name || getProgramName(alumni.program_id) }}</span>
                  </div>
                  <div v-if="alumni.phone_number" class="flex justify-between">
                    <span class="text-gray-600">Phone:</span>
                    <span class="font-medium text-[#2E79BA]">{{ alumni.phone_number }}</span>
                  </div>
                  <div v-if="alumni.city" class="flex justify-between">
                    <span class="text-gray-600">City:</span>
                    <span class="font-medium text-[#1E549F]">{{ alumni.city }}</span>
                  </div>
                </div>
                <div class="flex gap-2 mt-4">
                  <button class="btn btn-sm bg-[#2E79BA] text-white border-none hover:bg-[#1E549F] flex-1" 
                          @click="openEditModal(alumni)"
                          :disabled="loading">
                    <IconEdit class="w-3 h-3 mr-1" />Edit
                  </button>
                  <button class="btn btn-sm bg-[#5FC9F3] text-white border-none hover:bg-[#2E79BA] flex-1" 
                          @click="viewProfile(alumni)"
                          :disabled="loading">
                    <IconUserRound class="w-3 h-3 mr-1"/>View Profile
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Desktop Table View (hidden on mobile) -->
          <div class="hidden lg:block overflow-x-auto">
            <table class="table w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="text-[#081F37] font-bold">Student ID</th>
                  <th class="text-[#081F37] font-bold">Name</th>
                  <th class="text-[#081F37] font-bold">Program</th>
                  <th class="text-[#081F37] font-bold">Year Graduated</th>
                  <th class="text-[#081F37] font-bold">Phone</th>
                  <th class="text-[#081F37] font-bold">City</th>
                  <th class="text-[#081F37] font-bold">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="7" class="text-center py-8">
                    <div class="flex flex-col items-center">
                      <div class="loading loading-spinner loading-lg text-[#2E79BA]"></div>
                      <p class="text-gray-500 mt-4">Loading alumni data...</p>
                    </div>
                  </td>
                </tr>
                <tr v-else-if="filteredAlumni.length === 0">
                  <td colspan="7" class="text-center py-8 text-gray-500">
                    <div class="flex flex-col items-center">
                      <IconUsers class="w-16 h-16 mb-4 text-gray-300" />
                      <p>{{ alumniList.length === 0 ? 'No alumni records found.' : 'No alumni match your filters.' }}</p>
                    </div>
                  </td>
                </tr>
                <tr v-else v-for="alumni in filteredAlumni" :key="alumni.alumni_id" 
                    class="hover:bg-gradient-to-r hover:from-[#5FC9F3]/10 hover:to-[#2E79BA]/10 transition-all duration-300">
                  <td class="text-[#2E79BA]">{{ alumni.student_id }}</td>
                  <td class="font-semibold text-[#081F37]">
                    {{ alumni.first_name }} {{ alumni.last_name }}
                    <span v-if="alumni.middle_name" class="font-normal text-gray-600">{{ alumni.middle_name }}</span>
                  </td>
                  <td class="text-[#1E549F]">{{ alumni.program_name || getProgramName(alumni.program_id) }}</td>
                  <td>
                    <div class="badge bg-[#5FC9F3] text-white border-none">{{ alumni.year_graduated }}</div>
                  </td>
                  <td class="text-[#2E79BA]">{{ alumni.phone_number || '-' }}</td>
                  <td class="text-[#1E549F]">{{ alumni.city || '-' }}</td>
                  <td>
                    <div class="flex gap-2">
                      <button class="btn btn-m bg-[#2E79BA] text-white border-none hover:bg-[#1E549F] hover:scale-105 transition-all duration-300" 
                              @click="openEditModal(alumni)"
                              :disabled="loading">
                        <IconEdit class="w-4 h-4" />
                      </button>
                      <button class="btn btn-m bg-[#5FC9F3] text-white border-none hover:bg-[#2E79BA] hover:scale-105 transition-all duration-300" 
                              @click="viewProfile(alumni)"
                              :disabled="loading">
                        <IconUserRound class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Add/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
          <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative transform transition-all duration-300 scale-100">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-[#2E79BA] to-[#1E549F] p-6 rounded-t-2xl">
              <h3 class="text-xl md:text-2xl font-bold text-white">
                {{ isEditMode ? 'Edit Alumni' : 'Add Alumni' }}
              </h3>
              <p class="text-white/80 mt-1">
                {{ isEditMode ? 'Update alumni information' : 'Add new alumni to the directory' }}
              </p>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
              <!-- Error message in modal -->
              <div v-if="error" class="alert alert-error bg-red-500/20 border border-red-500/30 text-red-800 mb-4 rounded-xl">
                <i class="fas fa-exclamation-triangle"></i>
                <span>{{ error }}</span>
              </div>

              <form @submit.prevent="isEditMode ? updateAlumni() : addAlumni()" class="space-y-4">
                <!-- Personal Information -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  <div class="form-control">
                    <label class="label">
                      <span class="label-text text-[#081F37] font-medium">First Name *</span>
                    </label>
                    <input v-model="form.first_name" 
                           class="input input-bordered w-full focus:border-[#2E79BA] focus:ring-2 focus:ring-[#5FC9F3]/20" 
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
                          class="btn bg-gray-200 text-gray-700 border-none hover:bg-gray-300 w-full sm:w-auto" 
                          @click="closeModal"
                          :disabled="loading">
                    Cancel
                  </button>
                  <button type="submit" 
                          class="btn bg-gradient-to-r from-[#2E79BA] to-[#1E549F] text-white border-none hover:from-[#5FC9F3] hover:to-[#2E79BA] transform hover:scale-105 transition-all duration-300 w-full sm:w-auto"
                          :disabled="loading">
                    <span v-if="loading" class="loading loading-spinner loading-sm mr-2"></span>
                    <i v-else class="fas fa-save mr-2"></i>
                    {{ loading ? 'Saving...' : (isEditMode ? 'Update Alumni' : 'Add Alumni') }}
                  </button>
                </div>
              </form>
            </div>

            <!-- Close Button -->
            <button class="absolute top-4 right-4 btn btn-sm btn-circle bg-white/20 border-none text-white hover:bg-white/30 backdrop-blur-sm" 
                    @click="closeModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
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
import AdminAlumniProfileModal from '@/components/modals/AdminAlumniProfileModal.vue'
import { useRouter } from 'vue-router'
import alumniService from '@/services/alumni.js'
import programsService from '@/services/programs.js'
import { messageService } from '@/services/messageService.js'

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

const deleteAlumni = async (id) => {
  const confirmed = await messageService.confirm(
    'Are you sure you want to delete this alumni? This action cannot be undone.',
    'Delete Alumni'
  )
  
  if (confirmed) {
    try {
      loading.value = true
      
      const response = await alumniService.remove(id)
      
      if (response.success) {
        await fetchAlumni() // Refresh the list
        messageService.toast('Alumni deleted successfully', 'success')
      } else {
        messageService.alert(response.message || 'Failed to delete alumni', 'error')
      }
    } catch (err) {
      console.error('Error deleting alumni:', err)
      messageService.alert('Failed to delete alumni. Please try again.', 'error')
    } finally {
      loading.value = false
    }
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
/* Custom scrollbar for mobile card view */
.max-h-96::-webkit-scrollbar {
  width: 6px;
}

.max-h-96::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.max-h-96::-webkit-scrollbar-thumb {
  background: #2E79BA;
  border-radius: 3px;
}

.max-h-96::-webkit-scrollbar-thumb:hover {
  background: #1E549F;
}

/* Smooth transitions for all interactive elements */
.btn, .card, .input, .stat {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Glass morphism effect enhancements */
.bg-white\/10 {
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

/* Focus states for better accessibility */
.input:focus {
  transform: translateY(-1px);
  box-shadow: 0 10px 25px rgba(46, 121, 186, 0.1);
}

/* Hover animations */
.hover\:scale-105:hover {
  transform: scale(1.05);
}

/* Mobile-first responsive animations */
@media (max-width: 640px) {
  .transform.hover\:scale-105:hover {
    transform: scale(1.02);
  }
}

/* Enhanced gradient text effect */
.bg-clip-text {
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Loading skeleton animation (if needed) */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>