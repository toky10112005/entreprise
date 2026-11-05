<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <header>
    <nav>
      <RouterLink to="/manager/besoins">Mes besoins</RouterLink>
      |
      <RouterLink to="/manager/formulaire">Nouveau besoin</RouterLink>
    </nav>
  </header>

  <main>
    <RouterView />
  </main>
</template>
