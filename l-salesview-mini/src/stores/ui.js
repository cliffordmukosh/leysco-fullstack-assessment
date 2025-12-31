import { defineStore } from "pinia";

export const useUiStore = defineStore("ui", {
  state: () => ({
    sidebarCollapsed: false,
  }),
  actions: {
    toggleSidebar() {
      this.sidebarCollapsed = !this.sidebarCollapsed;
    },
    collapseSidebar() {
      this.sidebarCollapsed = true;
    },
    expandSidebar() {
      this.sidebarCollapsed = false;
    },
  },
});
