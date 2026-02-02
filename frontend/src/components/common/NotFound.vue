<template>
    <div class="row my-5">
        <div class="col-md-8 mx-auto text-center">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    
                    <h1 class="display-1 fw-bold mt-3">404</h1>
                    <h2 class="mb-3">Page Not Found</h2>
                    <p class="text-muted mb-4">
                        The page you are looking for does not exist or has been moved.
                    </p>
                    <router-link to="/" class="btn btn-primary btn-lg">
                        <i class="bi bi-house-fill me-2"></i>
                        Go Home
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
// No script logic needed for this component
</script>

<style scoped>
.card {
    border: none;
}

.display-1 {
    font-size: 6rem;
    line-height: 1;
}

@media (max-width: 768px) {
    .display-1 {
        font-size: 4rem;
    }
}
</style>
