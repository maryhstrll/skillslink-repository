<template>
  <div class="card bg-base-100 shadow-md settings-card">
    <div class="card-body">
      <h3 class="card-title text-lg mb-4">Edit Social Links</h3>
      <form @submit.prevent="handleSubmit">
        <div class="mb-4 space-y-4">
          <div>
            <label class="label">
              <span class="label-text">LinkedIn Profile</span>
            </label>
            <input 
              v-model="form.linkedin_profile" 
              type="url" 
              class="input input-bordered w-full" 
              placeholder="https://www.linkedin.com/in/your-profile"
            />
          </div>
          <div>
            <label class="label">
              <span class="label-text">Facebook Profile</span>
            </label>
            <input 
              v-model="form.facebook_profile" 
              type="url" 
              class="input input-bordered w-full" 
              placeholder="https://www.facebook.com/your-profile"
            />
          </div>
        </div>
        
        <!-- Current Links Display -->
        <div class="mb-4 p-4 bg-base-200 special-bg">
          <h4 class="font-semibold mb-2">Current Links:</h4>
          <div class="space-y-2 text-sm">
            <p>
              <strong>LinkedIn:</strong>
              <a v-if="profile?.social_links?.linkedin_profile" 
                 :href="profile.social_links.linkedin_profile" 
                 target="_blank" 
                 class="link link-primary ml-2">
                {{ profile.social_links.linkedin_profile }}
              </a>
              <span v-else class="text-gray-500 ml-2">Not provided</span>
            </p>
            <p>
              <strong>Facebook:</strong>
              <a v-if="profile?.social_links?.facebook_profile" 
                 :href="profile.social_links.facebook_profile" 
                 target="_blank" 
                 class="link link-primary ml-2">
                {{ profile.social_links.facebook_profile }}
              </a>
              <span v-else class="text-gray-500 ml-2">Not provided</span>
            </p>
          </div>
        </div>
        
        <div class="flex justify-end">
          <button type="submit" class="btn btn-primary" :disabled="isLoading">
            <span v-if="isLoading" class="loading loading-spinner loading-sm"></span>
            {{ isLoading ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
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
  linkedin_profile: '',
  facebook_profile: ''
});

const isLoading = ref(false);

watch(() => props.profile, (newProfile) => {
  if (newProfile?.social_links) {
    form.value.linkedin_profile = newProfile.social_links.linkedin_profile || '';
    form.value.facebook_profile = newProfile.social_links.facebook_profile || '';
  }
}, { immediate: true });

const handleSubmit = async () => {
  isLoading.value = true;
  
  try {
    const result = await alumniService.updateSocialLinks(form.value);
    
    if (result.success) {
      messageService.showSuccess('Social links updated successfully!');
      emit('updated');
    } else {
      messageService.showError(result.error || 'Failed to update social links');
    }
  } catch (err) {
    console.error('Error updating social links:', err);
    messageService.showError('Error updating social links: ' + err.message);
  } finally {
    isLoading.value = false;
  }
};
</script>
