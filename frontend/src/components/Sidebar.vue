<template>
  <div class="drawer-side">
    <label for="my-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
    <aside class="menu p-4 w-80 bg-base-100 text-base-content min-h-full">
      <!-- Sidebar Header -->
      
      <!-- Menu Items -->
      <ul class="space-y-2">
        <li v-for="item in menuItems" :key="item.path">
          <router-link 
            :to="item.path" 
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-base-200 transition-colors"
            :class="{ 'bg-primary text-primary-content': isActive(item.path) }"
          >
            <component :is="item.icon" class="text-lg" />
            <span>{{ item.label }}</span>
          </router-link>
        </li>
      </ul>
      
      <!-- Divider -->
      <div class="divider"></div>
      
      <!-- User Section -->
      <div class="mt-auto">
        <div class="dropdown dropdown-top dropdown-end w-full">
          <div tabindex="0" role="button" class="btn btn-ghost w-full justify-start gap-3">
            <div class="avatar placeholder">
              <div class="bg-neutral text-neutral-content rounded-full w-8">
                <span class="text-xs">{{ userInitials }}</span>
              </div>
            </div>
            <span class="flex-1 text-left">{{ userName }}</span>
            <IconChevronUp class="text-xs" />
          </div>
          <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-full mb-2">
            <li><a @click="$emit('profile')"><IconUser class="w-4 h-4" /> Profile</a></li>
            <li><a @click="$emit('logout')" class="text-error"><IconLogOut class="w-4 h-4" /> Logout</a></li>
          </ul>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const props = defineProps({
  menuItems: {
    type: Array,
    required: true,
    default: () => []
  },
  userName: {
    type: String,
    default: 'User'
  }
})

const emit = defineEmits(['profile', 'logout'])

const route = useRoute()

const userInitials = computed(() => {
  if (!props.userName || typeof props.userName !== 'string') {
    return 'U';
  }
  const cleanName = props.userName.trim();
  if (cleanName === '') {
    return 'U';
  }
  return cleanName
    .split(' ')
    .filter(name => name.length > 0) // Filter out empty strings
    .map(name => name.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const isActive = (path) => {
  return route.path === path
}
</script>

<style scoped>

</style>
