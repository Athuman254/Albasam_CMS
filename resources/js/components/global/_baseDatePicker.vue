<script>
import flatPickr from "vue-flatpickr-component"
import "flatpickr/dist/flatpickr.min.css"
import "flatpickr/dist/themes/airbnb.css"

export default {
    name: "DatePicker",

    components: {
        flatPickr,
    },

    props: {
        value: { required: true },
        placeholder: {
            type: String,
            default: '',
        },
        formclass: {
            type: String,
            default: 'form-control bg-white',
        },
        dateFormat: {
            // type: String,
            default: 'Y-m-d',
        },
        altFormat: {
            type: String,
            default: 'F j, Y',
        },
        wrap: {
            type: Boolean,
            default: false,
        },
        enableTime: {
            type: Boolean,
            default: false,
        },
        inline: {
            type: Boolean,
            default: false,
        },
        monthSelectorType: {
            type: String,
            default: 'dropdown', // static
        },
        mode: {
            type: String,
            default: 'single',
        }, // 'single', 'range', 'multiple'
        position: {
            type: String,
            default: 'auto center', //
        }, // 'auto', 'top', 'bottom'
        maxDate: { default: '' },
        minDate: { default: '' },
        defaultDate: {
            type: [String, Array],
            default: '',
        },
        // parseDate: {
        //   type: function() {},
        //   default: null,
        // },
        disabled: {
            type: Boolean,
            default: false,
        },
        noCalendar: {
            type: Boolean,
            default: false,
        },
    },

    data () {
        return {
            date: this.value,

            config: {
                wrap: false, // set wrap to true only when using 'input-group'
                altFormat: this.altFormat,
                altInputClass: this.formclass,
                placeholder: this.placeholder,
                altInput: true,
                dateFormat: this.dateFormat,
                enableTime: this.enableTime,
                inline: this.inline,
                mode: this.mode,
                position: this.position,
                maxDate: this.maxDate,
                minDate: this.minDate,
                defaultDate: this.defaultDate,
                monthSelectorType: this.monthSelectorType, // dropdow static
                // parseDate: function (dateString, format) {
                //   let timeZonedDate = new Date(dateString, format)
                // },
                noCalendar: this.noCalendar,
                // prevArrow: '<i class="uil uil-arrow-left"></i>',

                // https://chmln.github.io/flatpickr/plugins/
                // plugins: [new ConfirmDatePlugin(
                //   {
                //     confirmIcon: "<i class='uil uil-check-circle'></i>", // your icon's html, if you wish to override
                //     confirmText: "OK ",
                //     showAlways: false,
                //     theme: "dark" // or "dark"
                //   }
                // )]
            },
        }
    },

    watch: {
        defaultDate: function (newValue, oldValue) {
            // Notify flatpickr instance that there is a change in value
            this.$refs.flatpickr.fp.setDate(newValue, true)
            // this.onChange(newValue, newValue);
            // this.date = newValue;
        },
    },

    methods: {
        onChange (selectedDate, dateStr, instance) {
            this.$emit('on-change', selectedDate, dateStr, instance)
        },

        onClose (selectedDates, dateStr, instance) {
            this.$emit('on-close', selectedDates, dateStr, instance)
        },

        onOpen () {

        },
    },
}
</script>

<template>
    <flat-pickr
        ref="flatpickr"
        v-model="date"
        :placeholder="placeholder"
        :config="config"
        :disabled="disabled"
        @on-change="onChange"
        @on-close="onClose"
    />
</template>
