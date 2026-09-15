/**
 * Planning cost estimate for the 3D editor.
 *
 * Mirrors app/Support/CostEstimate.php so the total can refresh the moment a
 * part is placed, moved or deleted, with no round trip. The rate table is
 * injected from config/spatialsync.php — change a rate there, not here.
 *
 * This figure is an estimate for planning conversations. It is not a quote.
 */
(function () {
  'use strict';

  function number(value) {
    const n = Number(value);
    return Number.isFinite(n) ? n : 0;
  }

  class CostEstimator {
    /**
     * @param {Object} config  {rates: {type: {basis, rate, label}}, symbol}
     */
    constructor(config) {
      const cfg = config || {};
      this.rates = cfg.rates || {};
      this.symbol = typeof cfg.symbol === 'string' && cfg.symbol !== '' ? cfg.symbol : '$';
    }

    rateFor(type) {
      const rate = this.rates[type] || this.rates.generic;

      return rate && typeof rate === 'object' ? rate : { basis: 'unit', rate: 0, label: type };
    }

    quantityFor(part, basis) {
      if (basis !== 'area') {
        return 1;
      }

      const width = number(part.width);
      // Walls stand up (width x height); floors and roofs are flat (width x depth).
      const other = part.type === 'wall' ? number(part.height) : number(part.depth);

      return Math.max(width * other, 0);
    }

    /**
     * @param {Array<{data?: Object, mesh?: {userData?: Object}}|Object>} parts
     * @returns {{total: number, formatted: string, lines: Array<{label: string, quantity: number, amount: number, unit: string}>}}
     */
    estimate(parts) {
      const lines = new Map();
      let total = 0;

      (Array.isArray(parts) ? parts : []).forEach((entry) => {
        const part = (entry && (entry.data || (entry.mesh && entry.mesh.userData))) || entry || {};
        const type = String(part.type || 'generic');
        const rate = this.rateFor(type);
        const basis = String(rate.basis || 'unit');
        const quantity = this.quantityFor(part, basis);
        const amount = quantity * number(rate.rate);

        total += amount;

        const label = String(rate.label || type);
        const line = lines.get(label) || {
          label: label,
          quantity: 0,
          amount: 0,
          unit: basis === 'area' ? 'm²' : 'units',
        };

        line.quantity += quantity;
        line.amount += amount;
        lines.set(label, line);
      });

      return {
        total: total,
        formatted: this.format(total),
        lines: Array.from(lines.values()).sort((a, b) => b.amount - a.amount),
      };
    }

    format(value) {
      return this.symbol + number(value).toLocaleString(undefined, { maximumFractionDigits: 0 });
    }
  }

  window.CostEstimator = CostEstimator;
})();
