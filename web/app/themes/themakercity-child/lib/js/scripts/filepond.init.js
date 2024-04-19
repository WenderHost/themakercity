/* Compiled via `npm run build:js` */
import * as FilePond from 'filepond';
import 'filepond/dist/filepond.min.css';

// Import the Image Preview plugin
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';

// Import the File Type Validation plugin
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';

// Register the plugin with FilePond
FilePond.registerPlugin(FilePondPluginImagePreview,FilePondPluginFileValidateType);

// Get a file input reference
const input = document.querySelector('input[type="file"]');

// Create a FilePond instance
const imageFields = FilePond.create(input, {
    // Only accept images
    acceptedFileTypes: ['image/*'],
    labelIdle: 'Drag & Drop your image or <span class="filepond--label-action">Browse</span>',
    storeAsFile: true
});

// Custom event listener for 'resetFilePond'
document.addEventListener('resetFilePond', () => {
    console.info( '🔔 `resetFilePond` has been triggered. Resetting all FilePond fields.' );
    imageFields.removeFiles();
});