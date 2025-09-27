<template>
  <div class="login-form">
    <h2>Вход</h2>
    <form @submit.prevent="login">
      <div>
        <label>Email:</label>
        <input v-model="email" type="email" required />
      </div>
      <div>
        <label>Пароль:</label>
        <input v-model="password" type="password" required />
      </div>
      <button type="submit">Войти</button>
    </form>
    <p v-if="error" style="color:red">{{ error }}</p>
  </div>
</template>

<script>
import api from "../api";

export default {
  data() {
    return {
      email: "",
      password: "",
      error: null,
    };
  },
  methods: {
    async login() {
      try {
        const res = await api.post("/login", {
          email: this.email,
          password: this.password,
        });

        localStorage.setItem("auth_token", res.data.token);

        this.error = null;
        this.$emit("logged-in");
      } catch (err) {
        this.error = err.response?.data?.message || "Ошибка входа";
      }
    },
  },
};
</script>
