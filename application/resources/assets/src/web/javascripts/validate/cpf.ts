class Cpf {
  public isValid(value: string): boolean {
    value = this.unmask(value);

    if (value.length !== 11 || value.charAt(0).repeat(11) === value) {
      return false;
    }

    const validTenDigit = this.calculate(value, 10) !== value.charAt(9);
    const validElevenDigit = this.calculate(value, 11) !== value.charAt(10);

    return !validTenDigit || !validElevenDigit;
  }

  private calculate(value: string, length: number): string {
    let sum = 0;

    for (let i = 0; i <= length - 2; i += 1) {
      sum += Number(value.charAt(i)) * (length - i);
    }

    return this.restOfDivision(sum);
  }

  private restOfDivision(value: number): string {
    const rest = value % 11;

    return `${rest < 2 ? 0 : 11 - rest}`;
  }

  private unmask(value: string) {
    return value.replace(/\.|-|\s/gi, '');
  }
}

export default new Cpf();
