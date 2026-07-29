import { ref } from 'vue'

export const currentCurrency = ref(localStorage.getItem('alvo_pref_currency') || 'PHP')

export const currencySymbols = {
  PHP: '₱',
  USD: '$',
  EUR: '€',
  GBP: '£',
  JPY: '¥',
}

export function setGlobalCurrency(code) {
  currentCurrency.value = code || 'PHP'
  localStorage.setItem('alvo_pref_currency', currentCurrency.value)
}

export function getCurrencySymbol(code = null) {
  const c = code || currentCurrency.value || 'PHP'
  return currencySymbols[c] || '₱'
}

export function formatCurrency(val, code = null) {
  const c = code || currentCurrency.value || 'PHP'
  const localeMap = {
    PHP: 'en-PH',
    USD: 'en-US',
    EUR: 'en-IE',
    GBP: 'en-GB',
    JPY: 'ja-JP',
  }
  const locale = localeMap[c] || 'en-US'
  const minDigits = c === 'JPY' ? 0 : 2
  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency: c,
    minimumFractionDigits: minDigits,
    maximumFractionDigits: minDigits,
  }).format(val || 0)
}
