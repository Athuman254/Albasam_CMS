import DatePicker from "@/Components/global/_baseDatePicker.vue"; // Adjust path as needed

export default {
    install(app) {
        app.component("DatePicker", DatePicker);
    },
};
