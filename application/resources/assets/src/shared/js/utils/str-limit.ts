export const strLimit = (
  value: string,
  limit?: number,
  endLine?: '...',
): string => {
  const strLength = value.replace(/\s/g, '').length;

  if (limit && strLength > limit) {
    return value.substring(0, limit) + endLine;
  }

  return value;
};
