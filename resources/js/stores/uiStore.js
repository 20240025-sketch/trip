import { defineStore } from 'pinia';

export const useUiStore = defineStore('ui', {
  state: () => ({
    isModalOpen: false,
    modalComponent: null,
    modalProps: {},
    alert: {
      show: false,
      type: 'success', // 'success', 'error', 'warning', 'info'
      message: '',
    },
  }),

  actions: {
    openModal(component, props = {}) {
      this.modalComponent = component;
      this.modalProps = props;
      this.isModalOpen = true;
    },

    closeModal() {
      this.isModalOpen = false;
      this.modalComponent = null;
      this.modalProps = {};
    },

    showAlert(type, message, duration = 3000) {
      this.alert = {
        show: true,
        type,
        message,
      };

      if (duration > 0) {
        setTimeout(() => {
          this.hideAlert();
        }, duration);
      }
    },

    hideAlert() {
      this.alert.show = false;
    },

    showSuccess(message, duration = 3000) {
      this.showAlert('success', message, duration);
    },

    showError(message, duration = 5000) {
      this.showAlert('error', message, duration);
    },

    showWarning(message, duration = 4000) {
      this.showAlert('warning', message, duration);
    },

    showInfo(message, duration = 3000) {
      this.showAlert('info', message, duration);
    },
  },
});
