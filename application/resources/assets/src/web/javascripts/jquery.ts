import $ from 'jquery';
import 'jquery-mask-plugin';

declare global {
  export interface Window {
    jQuery: typeof $;
    $: typeof $;
  }

  namespace NodeJS {
    export interface Global extends Window {
      jQuery: typeof $;
      $: typeof $;
    }
  }
}

if (typeof window !== 'undefined') {
  window.jQuery = global.jQuery = $;
  window.$ = global.$ = $;
}
