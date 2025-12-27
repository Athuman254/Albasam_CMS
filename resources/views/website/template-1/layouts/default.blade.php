<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <meta name="google-site-verification" content="u7fyCOjASVh_qUiSHCOEotYDc6D7BvcnbV_S7Eoxg6c" />
   @stack('meta_tags')

   <link rel="shortcut icon" type="image/x-icon" href="{{ $favicon ?? '' }}" />
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   <link href="https://fonts.googleapis.com/css2?family=Dosis:wght@200;300;400;500;600;700;800&family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="{{ asset('website/css/custom.css') }}">
   <link rel="stylesheet" href="{{ asset('website/css/bootstrap.min.css') }}">
   <link rel="stylesheet" href="{{ asset('/website/css/style.css') }}">
   <link rel="stylesheet" href="{{ asset('/website/css/icofont.min.css') }}">
   <link rel="stylesheet" href="{{ asset('/website/css/meanmenu.min.css') }}">
   <link rel="stylesheet" href="{{ asset('/website/css/owl.carousel.min.css')}}">
   <link rel="stylesheet" href="{{ asset('/website/css/owl.theme.default.min.css') }}">
   <link rel="stylesheet" href="{{ asset('/website/css/animate.css') }}">
   <link rel="stylesheet" href="{{ asset('/website/css/venobox.min.css')}}" />
   <link rel="stylesheet" href="{{ asset('/website/css/responsive.css') }}">
   <link rel="stylesheet" href="{{ asset('/website/css/custom.css') }}">

   <style>
      :root {
         font-family: 'Dosis', 'Roboto', sans-serif !important;
         font-feature-settings: 'liga' 1, 'calt' 1;

         /* fix for Chrome */
         --ecbz-primary: #25615a !important;
         --ecbz-secondary: #333 !important;
      }

      body {
         font-family: 'Dosis', 'Roboto', sans-serif !important;
      }

      h1,
      h2,
      h3,
      h4,
      h5,
      h6 {
         color: #333;
         font-family: 'Dosis', 'Roboto', sans-serif !important;
         font-weight: 700;
      }

      p {
         font-family: 'Roboto', 'Dosis', sans-serif !important;
      }
   </style>
   @stack('styles')
</head>

<body id="main">
   <div id="page-preloader">
      <div class="loader"></div>
      <div class="loa-shadow"></div>
   </div>

   <script>
      // Force hide preloader if it stays too long
      (function() {
         var preloader = document.getElementById('page-preloader');
         if (preloader) {
            // Wait max 3 seconds, then force show the page
            setTimeout(function() {
               if (preloader.style.display !== 'none') {
                  console.log('Force hiding preloader...');
                  preloader.style.transition = 'opacity 0.5s ease';
                  preloader.style.opacity = '0';
                  setTimeout(function() {
                     preloader.style.display = 'none';
                  }, 500);
               }
            }, 3000);
         }
      })();
   </script>

   @include('website.template-1.layouts.shared.header')

   @yield('page-content')

   @include('website.template-1.layouts.shared.footer')

   <script src="{{ asset('/website/js/jquery-2.2.4.min.js') }}"></script>
   <script src="{{ asset('/website/js/popper.min.js') }}"></script>
   <script src="{{ asset('/website/js/bootstrap.min.js') }}"></script>
   <script src="{{ asset('/website/js/jquery.meanmenu.js') }}"></script>
   <script src="{{ asset('/website/js/jquery.sticky.js') }}"></script>
   <script src="{{ asset('/website/js/owl.carousel.min.js') }}"></script>
   <script src="{{ asset('/website/js/isotope.3.0.6.min.js') }}"></script>
   <script src="{{ asset('/website/js/venobox.min.js') }}"></script>
   <script src="{{ asset('/website/js/jquery.appear.js') }}"></script>
   <script src="{{ asset('/website/js/jquery.inview.min.js') }}"></script>
   <script src="{{ asset('/website/js/scrolltopcontrol.js') }}"></script>
   <script src="{{ asset('/website/js/wow.min.js') }}"></script>
   <!-- scripts js -->
   <script src="{{ asset('/website/js/scripts.js') }}"></script>
   <a href="https://api.whatsapp.com/send?phone=+254703887888&text=Hi,skaasSMS" class="whatsapp-float"
      aria-label="Chat with us on WhatsApp" target="_blank">
      <i class="fab fa-whatsapp"></i>
   </a>
   <style>
      .whatsapp-float {
         position: fixed;
         bottom: 24px;
         left: 24px;
         background-color: #25D366;
         color: white;
         padding: 14px;
         border-radius: 50%;
         box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
         z-index: 9999;
         text-align: center;
         transition: background-color 0.3s ease;
      }

      .whatsapp-float:hover {
         background-color: #1ebe5d;
      }

      .whatsapp-float i {
         font-size: 24px;
      }
   </style>
   <!-- Online Admission Modal -->
   <div class="modal fade" id="admissionModal" tabindex="-1" aria-labelledby="admissionModalLabel" aria-hidden="true" style="z-index: 99999;">
      <div class="modal-dialog modal-xl modal-dialog-centered">
         <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, #25615a 0%, #3b82f6 100%);">
               <h5 class="modal-title fw-bold" id="admissionModalLabel">
                  <i class="fas fa-graduation-cap me-2"></i> Online Admission Portal
               </h5>
               <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
               <div id="modalLoader" class="text-center py-5">
                  <div class="spinner-border text-primary" role="status">
                     <span class="visually-hidden">please Wait...</span>
                  </div>
                  <p class="mt-2 text-muted">Loading Admission Form...</p>
               </div>
               <iframe src="" id="admissionIframe" frameborder="0" style="width: 100%; height: 80vh; display: none;"></iframe>
            </div>
         </div>
      </div>
   </div>

   <script>
      $(document).ready(function() {
         console.log('Admission popup script loaded');

         // Intercept clicks on links pointing to /admission
         $(document).on('click', 'a', function(e) {
            var href = $(this).attr('href');
            var text = $(this).text().trim().toLowerCase();

            // Check if it's the public admission link
            if (href && (href.indexOf('/admission') !== -1 || text.indexOf('admission') !== -1) && !href.includes('/admin/')) {
               console.log('Admission link clicked:', href || text);
               e.preventDefault();

               var modalUrl = href;
               if (!modalUrl || modalUrl === '#') {
                  modalUrl = '/admission';
               }

               if (modalUrl.indexOf('modal=1') === -1) {
                  modalUrl += (modalUrl.indexOf('?') !== -1 ? '&' : '?') + 'modal=1';
               }

               console.log('Loading modal URL:', modalUrl);
               $('#modalLoader').show();
               $('#admissionIframe').hide().attr('src', modalUrl);

               try {
                  var admissionModalEl = document.getElementById('admissionModal');
                  if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                     var myModal = new bootstrap.Modal(admissionModalEl);
                     myModal.show();
                  } else {
                     $(admissionModalEl).modal('show');
                  }
               } catch (err) {
                  console.log('Modal error:', err);
                  $('#admissionModal').modal('show');
               }

               $('#admissionIframe').off('load').on('load', function() {
                  console.log('Admission iframe loaded successfully');
                  $('#modalLoader').hide();
                  $(this).fadeIn();
               });
            }
         });

         // Clear iframe src when modal is closed to avoid state issues
         $('#admissionModal').on('hidden.bs.modal', function() {
            $('#admissionIframe').attr('src', '').hide();
         });

         // Listen for messages from iframe to close modal
         window.addEventListener('message', function(event) {
            if (event.data === 'closeAdmissionModal') {
               console.log('Close message received from iframe');
               try {
                  if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                     var admissionModalEl = document.getElementById('admissionModal');
                     var modalInstance = bootstrap.Modal.getInstance(admissionModalEl);
                     if (modalInstance) {
                        modalInstance.hide();
                     } else {
                        $('#admissionModal').modal('hide');
                     }
                  } else {
                     $('#admissionModal').modal('hide');
                  }
               } catch (e) {
                  $('#admissionModal').modal('hide');
               }
            }
         });

         // Preloader Fallback: Hide it after 5 seconds if scripts.js fails to do so
         setTimeout(function() {
            var preloader = document.getElementById('page-preloader');
            if (preloader && preloader.style.display !== 'none') {
               console.log('Preloader fallback triggered');
               $(preloader).fadeOut(500);
            }
         }, 1000); // Reduced to 1 second for faster recovery
      });
   </script>
</body>

</html>