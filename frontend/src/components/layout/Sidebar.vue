<template>
  <div class="drawer-side">
    <label for="my-drawer" aria-label="close sidebar" class="drawer-overlay z-30"></label>
    <aside
      class="fixed lg:relative left-0 z-40 lg:z-auto flex flex-col w-70 text-base-content mobile-sidebar"
      aria-label="main sidebar"
    >
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
      
      <!-- Divider (styled) -->
      <div class="sidebar-divider"></div>
      
      <!-- User Section pinned to bottom -->
      <div class="p-4 user-footer">
        <div class="dropdown dropdown-top dropdown-end w-full">
          <div tabindex="0" role="button" class="btn btn-ghost border-0 w-full justify-start gap-3 px-2 py-2">
            <div class="avatar placeholder">
              <div class="bg-[color:var(--color-surface-alt)] text-[color:var(--color-text-primary)] rounded-full w-9 h-9 flex items-center justify-center">
                <span class="text-sm font-medium">{{ userInitials }}</span>
              </div>
            </div>
            <div class="flex-1 text-left">
              <div class="text-sm font-semibold">{{ userName }}</div>
              <div class="text-xs text-[color:var(--color-text-secondary)]">System Administrator</div>
            </div>
            <IconChevronUp class="text-xs" />
          </div>
          <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-full mb-2">
            <li>
              <router-link to="/alumni_profile">
                <IconUser class="w-4 h-4 mr-2" /> Profile
              </router-link>
            </li>
            <li>
              <a @click="$emit('logout')" class="text-error">
                <IconLogOut class="w-4 h-4 mr-2" /> Logout
              </a>
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

/* apply the top offset only on small screens so desktop layout isn't affected */
.mobile-sidebar {
  background: var(--color-primary-dark);
  top: 4rem;
  height: calc(100vh - 3.5rem);
  /* mobile uses fixed positioning (class already includes 'fixed') */
}

/* remove mobile rules on large screens (lg and up) */
@media (min-width: 1024px) {
  .mobile-sidebar {
    top: auto;
    height: auto;
    background: var(--color-primary-dark); /* keep same bg on desktop */
    /* position is controlled by utility classes (lg:relative), so no override needed */
  }
}

/* nicer divider and footer spacing */
.sidebar-divider {
  height: 1px;
  margin: 0.75rem 1rem;
  background: rgba(255,255,255,0.06);
  border-radius: 1px;
}

/* user footer tweaks so it sits compactly */
.user-footer .btn {
  background: transparent;
  width: 100%;
  justify-content: flex-start;
}

.user-footer .avatar > div {
  border-radius: 9999px;
}

/* ensure sidebar fills full height on desktop */
@media (min-width: 1024px) {
  aside.mobile-sidebar {
    min-height: 100vh;
  }
}

/* MENU HOVER: use a color darker than primary-dark for hover/selection */
aside.mobile-sidebar .flex.items-center:hover,
aside.mobile-sidebar a:hover,
aside.mobile-sidebar .btn-ghost:hover,
aside.mobile-sidebar .router-link-active:hover {
  background: var(--color-primary-darker) !important;
  color: var(--color-text-invert) !important;
}
</style>