<template>
  <div class="bet-form">
    <h2>Сделать ставку</h2>

    <form @submit.prevent="submitBet">
      <div>
        <label for="event">Событие:</label>
        <select v-model="eventId" required>
          <option disabled value="">-- выберите событие --</option>
          <option v-for="event in events" :key="event.id" :value="event.id">
            {{ event.title }}
          </option>
        </select>
      </div>

      <div>
        <label for="outcome">Исход:</label>
        <select v-model="outcome" required>
          <option disabled value="">-- выберите исход --</option>
          <option
              v-for="o in selectedEventOutcomes"
              :key="o"
              :value="o"
          >
            {{ o }}
          </option>
        </select>
      </div>


      <div>
        <label for="amount">Сумма:</label>
        <input
            v-model.number="amount"
            type="number"
            min="1"
            :max="maxAmount"
            required
        />
      </div>

      <p v-if="error" class="error">{{ error }}</p>
      <p v-if="success" class="success">{{ success }}</p>

      <button type="submit">Поставить</button>
    </form>

    <div v-if="bets.length" class="bets-list">
      <h3>Мои ставки</h3>
      <ul>
        <li v-for="bet in bets" :key="bet.id">
          {{ bet.event.title }} — {{ bet.outcome }} — {{ bet.amount }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import api from "../api";

const events = ref([]);
const bets = ref([]);
const eventId = ref("");
const outcome = ref("");
const amount = ref(0);
const balance = ref(100);

const error = ref("");
const success = ref("");

const selectedEventOutcomes = computed(() => {
  const event = events.value.find(e => e.id === eventId.value);
  return event ? JSON.parse(event.outcomes) : [];
});
const maxAmount = computed(() => balance.value > 0 ? balance.value : null);


onMounted(async () => {
  try {
    const res = await api.get("/events");
    events.value = res.data.events;
    console.log("EVENTS", res.data)
  } catch (e) {
    error.value = "Не удалось загрузить события";
  }

  try {
    const res = await api.get("/bets");
    bets.value = res.data.bets;
    balance.value = res.data.balance;
  } catch (e) {
    console.log(e)
  }
});

async function submitBet() {
  error.value = "";
  success.value = "";

  if (amount.value <= 0) {
    error.value = "Сумма должна быть больше 0";
    return;
  }
  if (amount.value > balance.value) {
    error.value = "Недостаточно средств";
    return;
  }

  try {
    const resPost = await api.post("/bets", {
      event_id: eventId.value,
      outcome: outcome.value,
      amount: amount.value,
    },
        {
          headers: {
            "Idempotency-Key": crypto.randomUUID(),
          },
        });

    success.value = "Ставка успешно создана!";
    balance.value = resPost.data.balance;

    const res = await api.get("/bets");
    bets.value = res.data.bets;

    amount.value = 0;
    outcome.value = "";
    eventId.value = "";
  } catch (e) {
    if (e.response?.status === 429) {
      error.value = "Слишком частые запросы. Попробуйте позже.";
    } else if (e.response?.status === 400) {
      error.value = "Ошибка: " + e.response.data.message;
    } else {
      error.value = "Не удалось создать ставку";
    }
  }
}
</script>

<style scoped>
.bet-form {
  max-width: 400px;
  margin: 2rem auto;
  padding: 1rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  background: #fafafa;
}
.bet-form form {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}
.error {
  color: red;
}
.success {
  color: green;
}
button {
  padding: 0.6rem;
  background: #42b983;
  color: #fff;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
button:hover {
  background: #369f6b;
}
.bets-list {
  margin-top: 2rem;
}
</style>
