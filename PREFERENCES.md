# Development Preferences

> **For AI Assistants:** Please read this file before suggesting any code changes. These preferences are mandatory and should guide all code implementations. Always reference these patterns when writing code.


## Vue.js / Frontend
- Use `reactive` instead of `ref` for objects/arrays wherever possible
- Use `ref` only for primitives (booleans, strings, numbers) when needed
- Always mark code changes with `✅ ADDED`, `✅ CHANGED`, `✅ REMOVED` indicators
- Use `computed` for reactive store access (e.g., `const user = computed(() => authStore.getUser)`)
- Use `ref` for DOM element references (e.g., `const fileInput = ref(null)`)

## API Calls & Error Handling
- Use `async/await` for API calls
- Always use `try/catch/finally` blocks for error handling
- Use `console.error()` for debugging errors
- Update store state after successful API calls
- Clear form data after successful submission
- Use `router.push()` for navigation after successful actions

## Loading States
- Use `setIsLoading(true/false)` in store for loading state
- Use `<Spinner :store="storeName" />` component for loading indicators
- Disable buttons during loading: `:disabled="store.isLoading"`

## Toast Notifications
- Use `toast.success()` for successful actions
- Use `toast.error()` for errors
- Use `toast.info()` for informational messages
- Show toast messages after API calls (success or error)

## File Uploads
- Use `FormData` for file uploads
- Set `enctype="multipart/form-data"` on form element
- Set `'Content-Type': 'multipart/form-data'` in axios headers
- Clear file input after successful upload

## Form Handling
- Use `@submit.prevent` for form submission
- Use `novalidate` attribute on forms to disable HTML5 validation
- Clear validation errors on component mount (`onMounted`)
- Use `v-model` for two-way data binding

## Component Structure
- Use `<Spinner :store="storeName" />` for loading states
- Use `<ValidationErrors :errors="errors" :visible="true/false" />` for error display
- Clear validation errors and messages in `onMounted` hook

## Validation
- Keep all validation logic on Laravel backend (single source of truth)
- Frontend should only display backend validation errors
- Use `novalidate` attribute on forms to disable HTML5 validation

## Code Style
- Prefer simple, minimal solutions
- Mark all changes clearly when working on files
- Use Bootstrap classes for styling (mb-3, mt-2, d-flex, etc.)
- Use Bootstrap Icons for icons (`<i class="bi bi-icon-name"></i>`)
- Use `object-fit: cover` for images
- Use `rounded-circle` class for profile images