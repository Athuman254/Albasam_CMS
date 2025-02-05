<template>
    <div class="dropzone" :class="{ dragging: isDragging }" @dragover.prevent="onDragOver"
      @dragleave.prevent="onDragLeave" @drop.prevent="onDrop" @click="openFileInput">

      <div v-if="!files.length">
        <div><svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M32 32L24 24L16 32" stroke="#ADB2BA" stroke-width="3" stroke-linecap="round"
              stroke-linejoin="round" />
            <path d="M24 24V42" stroke="#ADB2BA" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
            <path
              d="M40.7828 36.78C42.7335 35.7165 44.2745 34.0337 45.1626 31.9972C46.0507 29.9607 46.2353 27.6864 45.6873 25.5333C45.1392 23.3803 43.8898 21.471 42.1362 20.1069C40.3826 18.7427 38.2246 18.0014 36.0028 18H33.4829C32.8775 15.6585 31.7492 13.4846 30.1827 11.642C28.6163 9.79927 26.6525 8.33567 24.439 7.36118C22.2256 6.3867 19.82 5.92669 17.4031 6.01573C14.9862 6.10478 12.621 6.74057 10.4852 7.8753C8.34942 9.01003 6.49867 10.6142 5.07209 12.5671C3.64552 14.5201 2.68023 16.771 2.24881 19.1508C1.81739 21.5305 1.93106 23.977 2.58128 26.3065C3.23149 28.6359 4.40133 30.7877 6.00285 32.6"
              stroke="#ADB2BA" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M32 32L24 24L16 32" stroke="#ADB2BA" stroke-width="3" stroke-linecap="round"
              stroke-linejoin="round" />
          </svg>
        </div>
        <span class="fw-bold">
          Browse photo </span>or drop here
        <p>A photo larger than 400 pixels work best. Max photo size 5 MB.</p>
      </div>
      <ul v-else>
        <li>
          <div class="d-flex">
          <img  :src="profileImagePreview" alt="">
        </div>
        </li>

        <!-- <li v-for="(file, index) in files" :key="index" :class="{ invalid: file.error }">
          {{ file.name }}
          <span v-if="file.error"> - {{ file.error }}</span>
          <span v-else> ({{ formatSize(file.size) }})</span>
        </li> -->
      </ul>
      <input type="file" :accept="accept" :multiple="multiple" ref="fileInput" @change="onFileSelect"
        style="display: none" />
    </div>
  </template>

  <script setup>
  import { ref, watch, computed } from "vue";

  // Props
  const props = defineProps({
    accept: {
      type: String,
      default: "",
    },
    multiple: {
      type: Boolean,
      default: true,
    },
    maxSize: {
      type: Number,
      default: 5 * 1024 * 1024,
    },
    placeHolder: {
      type: Text,
      default: ' Drag and drop your files here or click to upload',
    },
  });

  // Emits
  const emit = defineEmits(["update:files"]);


  const files = [];
  let profileImagePreview = null;

  const imageLoaded = ref(false);
  const isDragging = ref(false);
  const fileInput = ref(null);

  // Watch files and emit updates
  // watch(files, (newFiles) => {
  //   console.log('File list updated:', newFiles);
  //   emit("update:files", newFiles); // Emit the updated files
  // });
  watch(files, ()=>console.log('sdds'),{ deep: true });
  // Handlers for drag-and-drop
  const onDragOver = () => {
    isDragging.value = true;
  };

  const onDragLeave = () => {
    isDragging.value = false;
  };

  const onDrop = (event) => {
    isDragging.value = false;
    const droppedFiles = Array.from(event.dataTransfer.files);
    addFiles(droppedFiles);
  };

  // Handler for file selection via input
  const onFileSelect = (event) => {
    const selectedFiles = Array.from(event.target.files);
    addFiles(selectedFiles);
  };

  // Open file input dialog
  const openFileInput = () => {
    fileInput.value.click();
  };

  // Add files with validation
  const addFiles = (newFiles) => {

    // files.value = Array.from(event.target.files);
    files.push(...newFiles);
    emit("update:files", files);
    profileImagePreview = URL.createObjectURL(newFiles[0]);
    // const validatedFiles = newFiles.map((file) => validateFile(file));
    // if (!props.multiple) files.value = validatedFiles.slice(0, 1);
    // else files.value.push(...validatedFiles);
  };

  // Validate file
  const validateFile = (file) => {
    const isValidType = !props.accept || file.type.match(props.accept);
    const isValidSize = file.size <= props.maxSize;

    if (!isValidType) {
      return { ...file, error: "Invalid file type" };
    } else if (!isValidSize) {
      return { ...file, error: "File exceeds maximum size" };
    }
    return { ...file, error: null };
  };

  // Format file size
  const formatSize = (size) => {
    if (size < 1024) return `${size} B`;
    if (size < 1024 * 1024) return `${(size / 1024).toFixed(1)} KB`;
    return `${(size / (1024 * 1024)).toFixed(1)} MB`;
  };
  </script>

  <style scoped>
  .dropzone {
    border: 2px dashed #ccc;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transition: border-color 0.3s ease, background-color 0.3s ease;
    cursor: pointer;
    width: 100%;
    min-height: 200px;
  }
  .dropzone img{
    width: 100%;
    height: 205px;
  }
  .dropzone.dragging {
    border-color: #007bff;
    background-color: #f0f8ff;
  }

  ul {
    list-style: none;
    padding: 0;
  }

  li {
    margin: 5px 0;
  }

  li.invalid {
    color: red;
  }

  li span {
    margin-left: 10px;
  }
  </style>
