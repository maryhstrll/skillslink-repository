// 1. MessageComponent.vue - The main message component
<template>
  <div>
    <!-- Modal for confirmations and dialogs -->
    <div class="modal" :class="{ 'modal-open': isVisible && (type === 'confirm' || type === 'dialog') }">
      <div class="modal-box">
        <h3 class="font-bold text-lg" :class="getHeaderClass()">
          {{ title }}
        </h3>
        <p class="py-4">{{ message }}</p>
        <div class="modal-action">
          <button 
            v-if="type === 'confirm'" 
            class="btn btn-error" 
            @click="handleConfirm"
          >
            {{ confirmText }}
          </button>
          <button 
            class="btn" 
            @click="handleCancel"
          >
            {{ cancelText }}
          </button>
        </div>
      </div>
    </div>

    <!-- Toast notifications -->
    <div class="toast toast-top toast-end" v-if="isVisible && type === 'toast'">
      <div class="alert" :class="getAlertClass()">
        <div>
          <span>{{ message }}</span>
        </div>
        <div class="flex-none">
          <button class="btn btn-sm btn-ghost" @click="close">✕</button>
        </div>
      </div>
    </div>

    <!-- Alert messages - now displayed as modal-like overlays -->
    <div v-if="isVisible && type === 'alert'" class="modal modal-open">
      <div class="modal-box">
        <h3 class="font-bold text-lg" :class="getHeaderClass()">
          {{ title }}
        </h3>
        <div class="py-4 flex items-start gap-3">
          <svg v-if="variant === 'success'" xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else-if="variant === 'error'" xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else-if="variant === 'warning'" xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>{{ message }}</span>
        </div>
        <div class="modal-action">
          <button class="btn" @click="close">OK</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MessageComponent',
  props: {
    isVisible: {
      type: Boolean,
      default: false
    },
    type: {
      type: String,
      default: 'alert',
      validator: (value) => ['alert', 'toast', 'confirm', 'dialog'].includes(value)
    },
    variant: {
      type: String,
      default: 'info',
      validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
    },
    title: {
      type: String,
      default: 'Notification'
    },
    message: {
      type: String,
      required: true
    },
    confirmText: {
      type: String,
      default: 'Confirm'
    },
    cancelText: {
      type: String,
      default: 'Cancel'
    },
    duration: {
      type: Number,
      default: 3000
    }
  },
  mounted() {
    if (this.type === 'toast' && this.isVisible) {
      setTimeout(() => {
        this.close();
      }, this.duration);
    }
  },
  methods: {
    getAlertClass() {
      const classes = {
        success: 'alert-success',
        error: 'alert-error',
        warning: 'alert-warning',
        info: 'alert-info'
      };
      return classes[this.variant] || classes.info;
    },
    getHeaderClass() {
      const classes = {
        success: 'text-success',
        error: 'text-error',
        warning: 'text-warning',
        info: 'text-info'
      };
      return classes[this.variant] || classes.info;
    },
    handleConfirm() {
      this.$emit('confirm');
      this.close();
    },
    handleCancel() {
      this.$emit('cancel');
      this.close();
    },
    close() {
      this.$emit('close');
    }
  }
};
</script>