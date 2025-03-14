import 'bootstrap';
import '@fortawesome/fontawesome-free/js/all'
import './scss/index.scss';
import '@contributte/datagrid/assets/datagrid'
import autocomplete from "./js/autocomplete";
require('@contributte/datagrid/assets/datagrid.css');
require('@contributte/datagrid/assets/datagrid-spinners.css');

document.addEventListener("DOMContentLoaded", function (event) {


    autocomplete();

});
