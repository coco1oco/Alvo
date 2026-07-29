import { defineStore } from 'pinia';
import axios from 'axios';

export const useAccountStore = defineStore('account', {
  state: () => ({
    accounts: [],
    loading: false,
    error: null,
  }),

  getters: {
    totalNetWorth: (state) => {
      return state.accounts.reduce((total, account) => {
        const val = parseFloat(account.balance) || 0;
        return account.type === 'credit_card' ? total - val : total + val;
      }, 0);
    },

    bankAccounts: (state) => state.accounts.filter((a) => a.type === 'bank'),
    creditCards: (state) => state.accounts.filter((a) => a.type === 'credit_card'),
    cashAccounts: (state) => state.accounts.filter((a) => a.type === 'cash'),
  },

  actions: {
    async fetchAccounts() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/api/accounts');
        this.accounts = response.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to load accounts';
      } finally {
        this.loading = false;
      }
    },
  },
});
