<template>
  <div class="drawer-side">
    <label for="my-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
    <aside class="flex flex-col h-screen w-70 bg-base-100 text-base-content">
      <!-- Menu Items -->
      <div class="flex-1 p-4">
        <ul class="space-y-2">
          <li v-for="(item, index) in menuItems" :key="index">
            <!-- Menu with submenu (dropdown) -->
            <div v-if="item.hasSubmenu" class="flex flex-col">
              <div class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-base-200 transition-colors cursor-pointer"
                   @click="toggleSubmenu(index)">
                <component :is="item.icon" class="text-lg" />
                <span class="flex-1">{{ item.label }}</span>
                <IconChevronDown v-if="!openSubmenuIndex || openSubmenuIndex !== index" class="text-xs" />
                <IconChevronUp v-else class="text-xs" />
              </div>
              
              <!-- Submenu items -->
              <ul v-if="openSubmenuIndex === index" class="pl-7 mt-1 space-y-1">
                <li v-for="submenuItem in item.submenu" :key="submenuItem.path">
                  <router-link 
                    :to="submenuItem.path" 
                    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-base-200 transition-colors"
                    :class="{ 'bg-primary text-primary-content': isActive(submenuItem.path) }"
                  >
                    <span>{{ submenuItem.label }}</span>
                  </router-link>
                </li>
              </ul>
            </div>
            
            <!-- Regular menu item without submenu -->
            <router-link 
              v-else
              :to="item.path" 
              class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-base-200 transition-colors"
              :class="{ 'bg-primary text-primary-content': isActive(item.path) }"
            >
              <component :is="item.icon" class="text-lg" />
              <span>{{ item.label }}</span>
            </router-link>
          </li>
        </ul>
      </div>
      
      <!-- Divider -->
      <div class="divider mx-4"></div>
      
      <!-- User Section -->
      <div class="p-4">
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
            <li>
              <router-link to="/alumni_profile"  >
              <IconUser class="w-4 h-4" /> Profile                
              </router-link>
            </li>
            <li>
              <a @click="$emit('logout')" class="text-error">
                <IconLogOut class="w-4 h-4" /> Logout</a>
            </li>
          </ul>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import { 
  ChevronUp as IconChevronUp, 
  ChevronDown as IconChevronDown,
  User as IconUser, 
  LogOut as IconLogOut 
} from 'lucide-vue-next'

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
const openSubmenuIndex = ref(null)

const toggleSubmenu = (index) => {
  if (openSubmenuIndex.value === index) {
    openSubmenuIndex.value = null
  } else {
    openSubmenuIndex.value = index
  }
}

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
ul {
  transition: all 0.3s ease;
}
</style>
