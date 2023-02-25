import Cpf from './validate/cpf';
import Cnpj from './validate/cnpj';

$(() => {
  $('.maskCep').mask('00000-000', { placeholder: '_____-___' });
  $('.maskPhone').mask('+55 (00) 00000-0000', { placeholder: '(__) _____-____' });
  $('.maskCardCvc').mask('0009', { placeholder: '____' });
  $('.maskCardExpiration').mask('00/00', { placeholder: '__/__' });
  $('.maskCreditCard').mask('0000 0000 0000 0000', { placeholder: '____ ____ ____ ____' });

  $('.maskCpf').mask('000.000.000-00', {
    reverse: true,
    placeholder: '___.___.___-__',
    onChange(value: string, _e, $element) {
      if (!Cpf.isValid(value)) {
        $element.addClass('is-invalid');
      } else {
        $element.removeClass('is-invalid');
      }
    },
  });

  $('.maskCnpj').mask('00.000.000/0000-00', {
    reverse: true,
    placeholder: '__.___.___/____-__',
    onChange(value: string, _e, $element) {
      if (!Cnpj.isValid(value)) {
        $element.addClass('is-invalid');
      } else {
        $element.removeClass('is-invalid');
      }
    },
  });
});
