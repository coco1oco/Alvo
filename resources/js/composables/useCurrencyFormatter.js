/**
 * Composable for formatting financial currency values and date strings.
 */
export function useCurrencyFormatter() {
  /**
   * Format a numeric amount into a localized currency string.
   *
   * @param {number|string} amount
   * @param {string} currency - Default USD
   * @returns {string}
   */
  const formatCurrency = (amount, currency = 'USD') => {
    const num = parseFloat(amount) || 0;
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: currency,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(num);
  };

  /**
   * Format a date string into human-readable format (e.g. "Jul 29, 2026").
   *
   * @param {string} dateString
   * @returns {string}
   */
  const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    }).format(date);
  };

  return {
    formatCurrency,
    formatDate,
  };
}
