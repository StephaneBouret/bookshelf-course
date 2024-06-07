import {
    Flipper,
    spring
} from 'flip-toolkit'

/**
 * @property {HTMLElement} pagination
 * @property {HTMLElement} content
 * @property {HTMLElement} sorting
 * @property {HTMLFormElement} form
 * @property {HTMLElement} reset
 */
export default class Filter {
    /**
     * @param {HTMLElement|null} element 
     */
    constructor(element) {
        if (element === null) {
            return
        }
        console.log('Je me construis');
        this.pagination = element.querySelector('.js-filter-pagination')
        this.content = element.querySelector('.js-filter-content')
        this.sorting = element.querySelector('.js-filter-sorting')
        this.form = element.querySelector('.js-filter-form')
        this.reset = element.querySelector('#resetBtn')
        this.bindEvents()
    }

    /**
     * Ajoute les comportements aux différents éléments
     */
    bindEvents() {
        const aClickListener = e => {
            if (e.target.tagName === 'A') {
                e.preventDefault()
                this.loadUrl(e.target.getAttribute('href'))
            }
        }
        this.sorting.addEventListener('click', aClickListener)
        this.pagination.addEventListener('click', aClickListener)
        this.form.querySelectorAll('input').forEach(input => {
            input.addEventListener('change', this.loadForm.bind(this))
        })
        this.form.querySelector('#q').addEventListener('keyup', this.loadForm.bind(this))
        this.form.querySelector('#author').addEventListener('keyup', this.loadForm.bind(this))
        this.reset.addEventListener('click', this.resetForm.bind(this))
    }

    async loadForm() {
        const data = new FormData(this.form)
        const url = new URL(this.form.getAttribute('action') || window.location.href)
        const params = new URLSearchParams()
        data.forEach((value, key) => {
            params.append(key, value)
        })
        return this.loadUrl(url.pathname + '?' + params.toString())
    }

    async loadUrl(url) {
        this.showLoader()
        const params = new URLSearchParams(url.split('?')[1] || '')
        params.set('ajax', 1)
        const response = await fetch(url.split('?')[0] + '?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        if (response.status >= 200 && response.status < 300) {
            const data = await response.json()
            this.flipContent(data.content)
            // this.content.innerHTML = data.content
            this.sorting.innerHTML = data.sorting
            this.pagination.innerHTML = data.pagination
            // Update totalItems in display template
            document.getElementById('totalItems').innerText = data.totalItems + ' résultat(s)';
            // params contient l'url
            params.delete('ajax') // On ne veut pas que le paramètre "ajax" se retrouve dans l'URL
            history.replaceState({}, '', url.split('?')[0] + '?' + params.toString())
        } else {
            console.error(response)
        }
        this.hideLoader()
    }

    resetForm() {
        // Reset the form elements
        this.form.reset();
        // After resetting the form, trigger the form submission to update the content
        this.loadForm();
    }

    showLoader() {
        this.form.classList.add('is-loading')
        const loader = this.form.querySelector('.js-loading')
        if (loader === null) {
            return
        }
        loader.setAttribute('aria-hidden', 'false')
        loader.style.display = null
    }

    hideLoader() {
        this.form.classList.remove('is-loading')
        const loader = this.form.querySelector('.js-loading')
        if (loader === null) {
            return
        }
        loader.setAttribute('aria-hidden', 'true')
        loader.style.display = 'none'
    }

    /**
     * Remplace les éléments de la grille avec un effet d'animation flip
     * @param {string} content
     */
        flipContent(content) {
            const springConfig = 'gentle'
            const exitSpring = function (element, index, onComplete) {
                spring({
                    config: "stiff",
                    values: {
                        translateY: [0, -20],
                        opacity: [1, 0]
                    },
                    onUpdate: ({
                        translateY,
                        opacity
                    }) => {
                        element.style.opacity = opacity;
                        element.style.transform = `translateY(${translateY}px)`;
                    },
                    onComplete
                })
            }
            const appearSpring = function (element, index) {
                spring({
                    config: 'stiff',
                    values: {
                        translateY: [20, 0],
                        opacity: [0, 1]
                    },
                    onUpdate: ({
                        translateY,
                        opacity
                    }) => {
                        element.style.opacity = opacity;
                        element.style.transform = `translateY(${translateY}px)`;
                    },
                    delay: index * 10
                })
            }
            const flipper = new Flipper({
                element: this.content
            })
            Array.from(this.content.children).forEach(element => {
                flipper.addFlipped({
                    element,
                    spring: springConfig,
                    flipId: element.id,
                    shouldFlip: false,
                    onExit: exitSpring
                })
            })
            flipper.recordBeforeUpdate()
            this.content.innerHTML = content
            Array.from(this.content.children).forEach(element => {
                flipper.addFlipped({
                    element,
                    spring: springConfig,
                    flipId: element.id,
                    onAppear: appearSpring
                })
            })
            flipper.update()
        }
}