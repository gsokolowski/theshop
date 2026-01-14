<template>
    <div 
        v-if="errors && Object.keys(errors).length > 0" 
        class="alert alert-danger" 
        role="alert">
        <ul class="mb-0">
            <li v-for="(errorArray, field) in errors" :key="field">
                <span v-for="(error, index) in errorArray" :key="index">
                    {{ error }}<br v-if="index < errorArray.length - 1">
                </span>
            </li>
        </ul>
    </div>
</template>

<script setup>
defineProps({
    errors: {
        type: Object,
        default: () => ({})
    }
})
</script>

<style scoped>
</style>