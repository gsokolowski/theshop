<!-- Register component -->
<template>
      <div class="row justify-content-center">

        <div class="col-md-4 p-4">
              <div class="text-center mb-4">
                <h2>Register</h2>
              </div>
              <div class="row">
                <p>Register your account to start shopping</p>
              </div>
              <div class="row">
                <form>
                    <div class="form-group mb-3">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" placeholder="Enter your email">
                    </div>
                    <div class="form-group mb-3">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" placeholder="Enter your password">
                    </div>
                    <div class="form-group mb-3">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" placeholder="Confirm your password">
                    </div>
                    <div class="form-group d-grid mb-3">
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>
                </form>
              </div>
        </div>
    </div>
</template>

<script setup>
</script>

<style scoped>
</style>