<template>
    <div>
        <h5>Review List</h5>
        <div v-for="review in reviews" :key="review.id">
            <h6>{{ review.title }}</h6>
            <p>{{ review.body }}</p>
            <p>{{ review.rating }}</p>
        </div>
    </div>
</template>

<script setup>

</script>

<style scoped>

</style>