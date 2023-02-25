import React from 'react';
import ReactDOM from 'react-dom';
import { parseDatasetToObject } from '../../../web/javascripts/utils';

const Example: React.FC = () => {
  return <h1>React Example Component</h1>;
};

const element = document.getElementById('react-example');

if (element !== null) {
  const props = parseDatasetToObject(element.dataset);
  ReactDOM.render(<Example {...props} />, element);
}
