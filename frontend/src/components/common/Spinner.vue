<template>
    <!-- eslint-disable vue/no-mutating-props -- v-model syncs overlay to Pinia store passed as prop -->
    <!-- v-model:active is used to bind the active property to the store.isLoading property -->
    <Loading
        v-model:active="store.isLoading"
        :is-full-page="data.fullPage"
    />
 </template>
 
 <script setup>
    import { reactive } from 'vue'
    import Loading from 'vue-loading-overlay'
 
     defineProps({
         store: {
             type: Object,
             required: true
         }
     })
 
     //define the data object
     const data = reactive({
         fullPage: true
     })
 </script>
 
 <style scoped>
 </style>