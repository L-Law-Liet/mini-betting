<template>
  <div id="app">
    <h1>Мини-бет сервис</h1>

    <LoginForm v-if="!isAuthenticated" @logged-in="onLoginSuccess" />

    <BetForm v-else />
  </div>
</template>

<script>
import LoginForm from "./components/LoginForm.vue";
import BetForm from "./components/BetForm.vue";

export default {
  components: { LoginForm, BetForm },
  computed: {
    isAuthenticated() {
      return !!localStorage.getItem("auth_token");
    },
  },
  methods: {
    onLoginSuccess() {
      this.$forceUpdate();
    },
  },
};
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  max-width: 400px;
  margin: 40px auto;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 8px;
}
</style>
