import 'bootstrap';
import '@fortawesome/fontawesome-free/js/all'
import './scss/index.scss';
import autocomplete from "./js/autocomplete";
import '@contributte/datagrid/dist/datagrid-full.css';
import '@contributte/datagrid/dist/datagrid-full.js';

document.addEventListener("DOMContentLoaded", function (event) {


    autocomplete();

});
