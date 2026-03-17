<template>
   <div class="loader">
      <div class="p-4 text-center">
        <div class="custom-loader"></div>
        <h2 class="my-3 f-w-400">Loading..</h2>
        <p class="mb-0">Please wait while we get your information from the web</p>
      </div>
    </div>
  </template>

  <script>
  // import { ref, watch } from 'vue';
  // import { useStore } from 'vuex';
  export default {
      name: 'Loading',
      setup() {

      }
  }
  </script>
  <style scoped>


  .relative {
    position: relative;
  }

  .absolute-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }

  .spinner-grow {
    width: 3rem;
    height: 3rem;
  }

  .text-red {
    color: red;
    margin-left: 10px;
  }
  @keyframes rotate {
      from {
          transform: rotate(0deg);
      }
      to {
          transform: rotate(360deg);
      }
  }


   @-webkit-keyframes rotate {
      from {
          -webkit-transform: rotate(0deg);
      }
      to {
          -webkit-transform: rotate(360deg);
      }
  }

  </style>
