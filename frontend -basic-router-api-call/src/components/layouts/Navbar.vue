<!-- Navbar component -->
<template>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
      <div class="container-fluid">
        <router-link class="navbar-brand" to="/">
          <img
            src="https://cdn.pixabay.com/photo/2014/04/02/11/16/shopping-305728_1280.png"
            alt="Logo"
            width="60"
            height="60"
          />
        </router-link>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <router-link class="nav-link" aria-current="page" to="/">
                <i class="bi bi-house-door-fill"></i> Home
              </router-link>
            </li>
            <ul class="navbar-nav">
              <li class="nav-item">
                <router-link class="nav-link" aria-current="page" to="/register">
                  <i class="bi bi-person-add"></i> Register
                </router-link>
              </li>
              <li class="nav-item">
                <router-link class="nav-link" aria-current="page" to="/login">
                  <i class="bi bi-box-arrow-right"></i> Login
                </router-link>
              </li>
            </ul>
          </ul>
        </div>
      </div>
    </nav>
  </template>
  
  <script setup>
  </script>
  
  <style scoped>
    .navbar a {
      font-size: 1.1rem;
      font-weight: 700;
    }
  </style>