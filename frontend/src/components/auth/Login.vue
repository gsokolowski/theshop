<!-- Register component -->
<template>
  <div class="row justify-content-center">
      <!-- Main content - 25% width (col-md-3) centered -->
      <div class="col-md-4 p-4">
        <div class="text-center mb-4">
          <h2>Login</h2>
        </div>
        <form>
          <div class="form-group mb-3">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" placeholder="Enter email">
          </div>
          <div class="form-group mb-3">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" placeholder="Enter password">
          </div>
          <div class="form-group d-grid mb-3">
            <button type="submit" class="btn btn-primary">Login</button>
          </div>
        </form>
      </div>
  </div>
</template>

<script setup>
</script>

<style scoped>
</style>