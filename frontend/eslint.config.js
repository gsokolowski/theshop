import js from '@eslint/js'
import pluginVue from 'eslint-plugin-vue'
import vitest from 'eslint-plugin-vitest'
import globals from 'globals'

export default [
  {
    ignores: ['dist/**', 'node_modules/**', 'coverage/**'],
  },
  js.configs.recommended,
  ...pluginVue.configs['flat/essential'],
  {
    languageOptions: {
      globals: {
        ...globals.browser,
      },
    },
    rules: {
      // Route-level views often use single-word names (Home, About, Login).
      'vue/multi-word-component-names': 'off',
    },
  },
  {
    files: ['vite.config.js'],
    languageOptions: {
      globals: {
        ...globals.node,
      },
    },
  },
  {
    files: ['**/*.spec.js', '**/*.test.js'],
    ...vitest.configs.recommended,
    languageOptions: {
      globals: {
        ...globals.browser,
        ...vitest.configs.env.languageOptions.globals,
      },
    },
  },
]
