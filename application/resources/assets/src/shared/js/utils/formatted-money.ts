type Options = {
  locales?: string | string[];
} & Intl.NumberFormatOptions;

export const formattedMoney = (value: number, options?: Options) => {
  const parseOptions = {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
    locales: 'pt-BR',
    ...(options ?? {}),
  } as Options;

  const { locales } = parseOptions;

  return Intl.NumberFormat(locales, parseOptions).format(value);
};
