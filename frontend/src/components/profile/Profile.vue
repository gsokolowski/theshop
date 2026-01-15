<template>
  <div class="row">
      <ProfileSidebar/>
      <ProfileUpdate :updateProfile="true" />
  </div>
</template>

<script setup>
  import ProfileSidebar from './ProfileSidebar.vue'
  import ProfileUpdate from './ProfileUpdate.vue'
</script>

<style scoped>
</style>