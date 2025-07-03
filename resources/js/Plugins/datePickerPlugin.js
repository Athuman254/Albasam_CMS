import DatePicker from "@/Components/global/_baseDatePicker.vue";

export default {
    install(app) {
        app.component("DatePicker", DatePicker);
    },
};
