type Options = {
  locales?: string | string[];
} & Intl.DateTimeFormatOptions;

export const formattedDate = (
  date: number | Date | string,
  options?: Options,
): string => {
  const parseOptions = {
    timeZone: 'America/Sao_Paulo',
    locales: 'pt-BR',
    ...(options ?? {}),
  } as Options;

  const { locales } = parseOptions;

  if (typeof date === 'string') {
    date = new Date(date);
  }

  return Intl.DateTimeFormat(locales, parseOptions).format(date);
};
