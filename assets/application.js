import 'bootstrap';
import '@fortawesome/fontawesome-free/js/all'
import './scss/index.scss';
import autocomplete from "./js/autocomplete";
import '@contributte/datagrid/dist/datagrid-full.css';
import '@contributte/datagrid/dist/datagrid-full.js';
import prepareForm from "./js/edit";
import Dropdown from 'bootstrap/js/dist/dropdown';

document.addEventListener("DOMContentLoaded", function (event) {


    autocomplete();
    prepareForm();

});
