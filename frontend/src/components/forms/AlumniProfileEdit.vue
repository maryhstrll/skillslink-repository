<template>
  <div class="card bg-base-100 shadow-md rounded-xl">
    <div class="card-body">
      <h3 class="card-title text-lg mb-4">Edit Profile</h3>
      <form @submit.prevent="handleSubmit">
        <!-- Non-editable fields -->
        <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label">Student ID</label>
            <input type="text" class="input input-bordered w-full" :value="profile.user_info.student_id" disabled />
          </div>
          <div>
            <label class="label">Full Name</label>
            <input type="text" class="input input-bordered w-full" :value="profile.user_info.full_name" disabled />
          </div>
          <div>
            <label class="label">Date of Birth</label>
            <input type="date" class="input input-bordered w-full" :value="profile.personal_info.date_of_birth" disabled />
          </div>
          <div>
            <label class="label">Gender</label>
            <input type="text" class="input input-bordered w-full" :value="profile.personal_info.gender" disabled />
          </div>
        </div>
        <!-- Editable fields -->
        <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label">Phone</label>
            <input v-model="form.phone_number" type="text" class="input input-bordered w-full" />
          </div>
          <div>
            <label class="label">Alternative Phone</label>
            <input v-model="form.alternative_phone" type="text" class="input input-bordered w-full" />
          </div>
          <div>
            <label class="label">Barangay</label>
            <input v-model="form.barangay" type="text" class="input input-bordered w-full" />
          </div>
          <div>
            <label class="label">City</label>
            <input v-model="form.city" type="text" class="input input-bordered w-full" />
          </div>
          <div>
            <label class="label">Province</label>
            <input v-model="form.province" type="text" class="input input-bordered w-full" />
          </div>
          <div>
            <label class="label">Country</label>
            <input v-model="form.country" type="text" class="input input-bordered w-full" />
          </div>
          <div>
            <label class="label">Postal Code</label>
            <input v-model="form.postal_code" type="text" class="input input-bordered w-full" />
          </div>
        </div>
        <div class="flex justify-end">
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, toRefs } from 'vue';
import alumniService from '@/services/alumni.js';
import { messageService } from '@/services/messageService.js';

const props = defineProps({
  profile: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['updated']);

const form = ref({
  phone_number: '',
  alternative_phone: '',
  barangay: '',
  city: '',
  province: '',
  country: '',
  postal_code: ''
});

watch(() => props.profile, (newProfile) => {
  if (newProfile) {
    form.value.phone_number = newProfile.personal_info?.phone_number || '';
    form.value.alternative_phone = newProfile.personal_info?.alternative_phone || '';
    form.value.barangay = newProfile.address?.barangay || '';
    form.value.city = newProfile.address?.city || '';
    form.value.province = newProfile.address?.province || '';
    form.value.country = newProfile.address?.country || '';
    form.value.postal_code = newProfile.address?.postal_code || '';
  }
}, { immediate: true });

const handleSubmit = async () => {
  // Call API to update profile
  const payload = { ...form.value };
  try {
    const result = await alumniService.updateProfile(payload);
    console.log('Update result:', result); // Debug log
    if (result.success) {
      messageService.showSuccess('Profile updated successfully!');
      emit('updated');
    } else {
      console.error('Update failed:', result.error);
      messageService.showError(result.error || 'Failed to update profile');
    }
  } catch (err) {
    console.error('Error updating profile:', err);
    messageService.showError('Error updating profile: ' + err.message);
  }
};
</script>
