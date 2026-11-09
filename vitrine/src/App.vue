<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <header>
    <nav>
      <RouterLink to="/offres">Offres d’emploi</RouterLink>
    </nav>
  </header>
  <RouterView />
</template>
