export const normalizeMoney = (value: string): number =>
  Number(value.replace(/[^0-9-]/g, '')) / 100;
