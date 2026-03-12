<template>
  <header class="topbar">
    <h1>RA Jiraf</h1>
    <nav>
      <RouterLink to="/">Главная</RouterLink>
      <RouterLink to="/services">Услуги</RouterLink>
      <RouterLink to="/admin">Админка</RouterLink>
    </nav>
  </header>
  <main class="container">
    <RouterView />
  </main>
</template>
