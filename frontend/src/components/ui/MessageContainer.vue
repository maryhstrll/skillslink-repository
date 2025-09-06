<template>
  <div class="message-container">
    <MessageComponent
      v-for="message in messages"
      :key="message.id"
      :is-visible="message.isVisible"
      :type="message.type"
      :variant="message.variant"
      :title="message.title"
      :message="message.message"
      :duration="message.duration"
      @confirm="handleConfirm(message)"
      @cancel="handleCancel(message)"
      @close="handleClose(message)"
    />
  </div>
</template>

<script>
import MessageComponent from './MessageComponent.vue';
import { messageService } from '../../services/messageService.js';

export default {
  name: 'MessageContainer',
  components: {
    MessageComponent
  },
  computed: {
    messages() {
      return messageService.messages;
    }
  },
  methods: {
    handleConfirm(message) {
      if (message.resolve) {
        message.resolve(true);
      }
      messageService.remove(message.id);
    },
    handleCancel(message) {
      if (message.resolve) {
        message.resolve(false);
      }
      messageService.remove(message.id);
    },
    handleClose(message) {
      if (message.resolve) {
        message.resolve(false);
      }
      messageService.remove(message.id);
    }
  }
};
</script>