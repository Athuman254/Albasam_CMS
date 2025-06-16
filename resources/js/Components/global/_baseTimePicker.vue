<script>
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.min.css";
import "flatpickr/dist/themes/airbnb.css";

export default {
   name: "TimePicker",
   
   components: {
      flatPickr,
   },
   
   props: {
      value: { required: true },
      placeholder: {
         type: String,
         default: 'Select Time',
      },
      formclass: {
         type: String,
         default: 'form-control bg-white',
      },
      wrap: {
         type: Boolean,
         default: false,
      },
      inline: {
         type: Boolean,
         default: false,
      },
      position: {
         type: String,
         default: 'auto center',
      },
      defaultTime: {
         type: String,
         default: '',
      },
      disabled: {
         type: Boolean,
         default: false,
      },
   },
   
   data() {
      return {
         time: this.value, // Make sure to use 'time' as v-model
         
         config: {
            wrap: this.wrap,
            altInput: true,
            altInputClass: this.formclass,
            placeholder: this.placeholder,
            enableTime: true,    // Ensure time selection is enabled
            noCalendar: true,    // Hides the date picker, only allows time
            dateFormat: "H:i K", // 12-hour format with AM/PM
            time_24hr: false,    // Ensure 12-hour format
            defaultDate: this.defaultTime,
            position: this.position,
            inline: this.inline,
            prevArrow: '<i class="bx bx-arrow-to-left"></i>',
         },
      };
   },
   
   watch: {
      value(newValue) {
         this.time = newValue;
      },
   },
   
   methods: {
      onChange(selectedTime, timeStr) {
         this.$emit("input", timeStr); // Emit selected time in "h:i K" format
      },
   },
};
</script>

<template>
   <flat-pickr
      ref="flatpickr"
      v-model="time"
      :placeholder="placeholder"
      :config="config"
      :disabled="disabled"
      @on-change="onChange"
   />
</template>
