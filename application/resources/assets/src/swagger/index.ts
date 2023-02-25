const SwaggerUI = require('swagger-ui');

import 'swagger-ui/dist/swagger-ui.css';
import 'swagger-ui/dist/swagger-ui-standalone-preset';

declare global {
  export interface Window {
    ui: any;
    SWAGGER_JSON: Record<string, any>;
  }
}

window.onload = function () {
  const BASE_URL = document.querySelector('link[rel="base"]')!.getAttribute('href');

  window.ui = SwaggerUI({
    spec: window.SWAGGER_JSON,
    dom_id: '#swagger-ui',
    deepLinking: true,
    presets: [SwaggerUI.presets.apis, SwaggerUI.SwaggerUIStandalonePreset],
    plugins: [SwaggerUI.plugins.DownloadUrl],
    validatorUrl: 'https://validator.swagger.io/validator',
    url: `${BASE_URL}/api/docs`,
  });
};
