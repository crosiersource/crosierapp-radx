'use strict';


import Numeral from 'numeral';
import 'numeral/locales/pt-br.js';


import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

Numeral.locale('pt-br');

Routing.setRoutingData(routes)

