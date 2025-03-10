<script>
import { defineComponent, ref } from "vue";
import { QuillEditor } from "@vueup/vue-quill";
import "@vueup/vue-quill/dist/vue-quill.snow.css";

export default defineComponent({
    name: "QuillEditor",
    components: { QuillEditor },
    props: {
        modelValue: String, // v-model support
    },
    emits: ["update:modelValue"],
});
</script>

<template>
    <quill-editor
        theme="snow"
        v-model:content="modelValue"
        content-type="html"
        @update:content="$emit('update:modelValue', $event)"
    />
</template>

<style>
/* Default Quill style */
</style>
