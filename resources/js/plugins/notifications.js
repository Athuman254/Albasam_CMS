import iziToast from 'izitoast/dist/js/iziToast.min'

let position = 'topRight';
let displayMode = 'replace';
let timeout = 9000;
let layout = 2;

const toast = {
    error: (message, title = 'Error') => {
        return iziToast.error({
            title: title,
            message: message,
            position: position,
            transitionIn: 'fadeIn',
            icon: 'icon-x',
            displayMode: displayMode,
            layout: 2,
            timeout: timeout
        })
    },

    success: (message, title = 'Success') => {
        return iziToast.success({
            title: title,
            message: message,
            position: position,
            transitionIn: 'fadeIn',
            icon: 'uil uil-check',
            displayMode: displayMode,
            layout: layout,
            timeout: timeout
            // image: '/logo_35.jpg',
        })
    },

    question: (message = 'Deleting the selected item', title = 'Caution') => {
        return new Promise(resolve => {
            iziToast.question({
                title: title,
                message: message,
                timeout: 20000,
                progressBar: false,
                close: false,
                overlay: true,
                displayMode: displayMode,
                id: 'question',
                position: 'center',
                transitionIn: 'fadeIn',
                transitionOut: 'fadeOut',
                layout: layout,
                buttons: [
                    ['<button class="btn btn-danger"><b>Confirm</b></button>', function (instance, toast, button, e, inputs) {
                        instance.hide({transitionOut: 'fadeOut'}, toast, 'button')
                        resolve()
                    }, false], // true to focus

                    ['<button class="btn btn-light">Cancel</button>', function (instance, toast, button, e) {
                        instance.hide({transitionOut: 'fadeOut'}, toast, 'button')
                    }]
                ],
                onClosing(_instance, _toast, closedBy) {
                    // console.info('closedBy: ' + closedBy);
                }
            })
        })
    },

    info: (message, title = 'Info') => {
        return iziToast.info({
            title: title,
            message: message,
            displayMode: displayMode,
            position: position,
            transitionIn: 'fadeIn',
            transitionOut: 'fadeOut',
            layout: layout,
            timeout: timeout
        })
    },

    warning: (message, title = 'Warning') => {
        return iziToast.warning({
            title: title,
            message: message,
            displayMode: displayMode,
            id: 'question',
            position: position,
            transitionIn: 'fadeIn',
            transitionOut: 'fadeOut',
            layout: layout,
            timeout: timeout
        })
    }

}

// Object.defineProperty(Vue.prototype, '$toast', {value: toast});

export default {
    install(app) {
        app.config.globalProperties.$toast = toast;
    },
};
