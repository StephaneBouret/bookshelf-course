/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/styles.css';
import 'bootstrap/dist/css/bootstrap.min.css';

require('bootstrap');
import noUiSlider from 'nouislider';
import 'nouislider/dist/nouislider.css';
import Filter from './js/modules/Filter.js';

new Filter(document.querySelector('.js-filter'))

const dateSlider = document.getElementById('date-slider');

if (dateSlider) {
    const min = document.getElementById('minPublicationAt');
    const max = document.getElementById('maxPublicationAt');
    const timestamp = (str) => {
        return new Date(str).getFullYear()
    }
    const minValue = parseInt(timestamp(dateSlider.dataset.min), 10);
    const maxValue = parseInt(timestamp(dateSlider.dataset.max), 10);
    const range = noUiSlider.create(dateSlider, {
        start: [min.value || minValue, max.value || maxValue],
        connect: true,
        step: 1,
        range: {
            'min': minValue,
            'max': maxValue
        }
    });
    range.on('slide', function (values, handle) {
        if (handle === 0) {
            min.value = Math.round(values[0])
        }
        if (handle === 1) {
            max.value = Math.round(values[1])
        }
    })
    range.on('end', function (values, handle) {
        if (handle === 0) {
            min.dispatchEvent(new Event('change'))
        } else {
            max.dispatchEvent(new Event('change'))
        }
    })
}

// Close alert message after 5 secondes
const alert = document.querySelector('.alert')
if (alert) {
    setTimeout(function () {
        alert.style.transition = "opacity 1s ease";
        alert.style.opacity = '0';
    
        setTimeout(function () {
            alert.style.display = 'none';
        }, 500); // After the fade-out animation (0.5 second)
    }, 5000); // After 5 seconds
}