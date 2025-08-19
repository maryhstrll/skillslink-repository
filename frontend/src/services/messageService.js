import { reactive } from 'vue';

const messageState = reactive({
  messages: []
});

let messageId = 0;

export const messageService = {
  // Show toast notification
  toast(message, variant = 'info', duration = 3000) {
    const id = messageId++;
    const messageObj = {
      id,
      type: 'toast',
      message,
      variant,
      duration,
      isVisible: true
    };
    
    messageState.messages.push(messageObj);
    
    setTimeout(() => {
      this.remove(id);
    }, duration);
    
    return id;
  },

  // Show alert
  alert(message, variant = 'info', title = 'Alert') {
    const id = messageId++;
    const messageObj = {
      id,
      type: 'alert',
      message,
      variant,
      title,
      isVisible: true
    };
    
    messageState.messages.push(messageObj);
    return id;
  },

  // Show confirmation dialog
  confirm(message, title = 'Confirmation') {
    return new Promise((resolve) => {
      const id = messageId++;
      const messageObj = {
        id,
        type: 'confirm',
        message,
        title,
        variant: 'warning',
        isVisible: true,
        resolve
      };
      
      messageState.messages.push(messageObj);
    });
  },

  // Show custom dialog
  dialog(message, title = 'Dialog', variant = 'info') {
    return new Promise((resolve) => {
      const id = messageId++;
      const messageObj = {
        id,
        type: 'dialog',
        message,
        title,
        variant,
        isVisible: true,
        resolve
      };
      
      messageState.messages.push(messageObj);
    });
  },

  // Remove message by id
  remove(id) {
    const index = messageState.messages.findIndex(msg => msg.id === id);
    if (index > -1) {
      messageState.messages.splice(index, 1);
    }
  },

  // Clear all messages
  clear() {
    messageState.messages.splice(0);
  },

  // Get current messages
  get messages() {
    return messageState.messages;
  },

  // Convenience methods
  showSuccess(message, title = 'Success') {
    return this.alert(message, 'success', title);
  },

  showError(message, title = 'Error') {
    return this.alert(message, 'error', title);
  },

  showWarning(message, title = 'Warning') {
    return this.alert(message, 'warning', title);
  },

  showInfo(message, title = 'Information') {
    return this.alert(message, 'info', title);
  }
};