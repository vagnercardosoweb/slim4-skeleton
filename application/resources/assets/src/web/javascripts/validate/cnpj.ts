class Cnpj {
  public isValid(value: string): boolean {
    value = this.unmask(value);

    if (value.length !== 14 || value.charAt(0).repeat(14) === value) {
      return false;
    }

    const validTwelveDigit = this.calculate(value, 12) !== value.charAt(12);
    const validThirteenDigit = this.calculate(value, 13) !== value.charAt(13);

    return !validTwelveDigit || !validThirteenDigit;
  }

  private restOfDivision(value: number): string {
    const rest = value % 11;

    return `${rest < 2 ? 0 : 11 - rest}`;
  }

  private unmask(value: string) {
    return value.replace(/\.|-|\/|\s/gi, '');
  }

  private calculate(value: string, length: number): string {
    let sum = 0;
    let position = length - 7;

    for (let i = length; i >= 1; i -= 1) {
      sum += Number(value.charAt(length - i)) * position;
      position -= 1;

      if (position < 2) {
        position = 9;
      }
    }

    return this.restOfDivision(sum);
  }
}

export default new Cnpj();
