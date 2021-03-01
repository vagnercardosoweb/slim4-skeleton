import * as $ from 'jquery';

declare global {
  export interface Window {
    $: typeof $;
    jQuery: typeof $;
  }

  namespace NodeJS {
    export interface Global {
      $: typeof $;
      jQuery: typeof $;
    }
  }
}

if (typeof window !== 'undefined') {
  window.$ = global.$ = $;
  window.jQuery = global.jQuery = $;
}
