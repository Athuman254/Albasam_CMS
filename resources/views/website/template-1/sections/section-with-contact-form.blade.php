<section id="contcat" class="section-padding">
   <div class="auto-container">
      <div class="row">
         <div class="col-lg-5 col-md-5 col-12 mb-lg-0 mb-md-0 mb-5">
            <div class="address-box-wrap bg-gray shadow-sm p-lg-5 p-md-3 p-3">
               <div class="address-box-sin mb-4">
                  <div class="address-box-icon">
                     <i class="icofont-location-pin"></i>
                  </div>
                  <div class="address-box-des">
                     <h4>Main Address</h4>
                     <p>{{ $institution->physical_address }}</p>
                  </div>
               </div>
               <!-- end single address box -->
               <div class="address-box-sin mb-4">
                  <div class="address-box-icon">
                     <i class="icofont-envelope-open"></i>
                  </div>
                  <div class="address-box-des">
                     <h4>Send Email</h4>
                     <p>
                        {{ $institution->email }} <br>
                        {{ $institution->email }}
                     </p>
                  </div>
               </div>
               <!-- end single address box -->
               <div class="address-box-sin mb-4">
                  <div class="address-box-icon">
                     <i class="icofont-fax"></i>
                  </div>
                  <div class="address-box-des">
                     <h4>Phone</h4>
                     <p>
                        {{ $institution->phone }} <br>
                        {{ $institution->phone }}
                     </p>
                  </div>
               </div>
            </div>
         </div>
         <!-- end col -->
         <div class="col-lg-7 col-md-7 col-12 pl-lg-5 pl-md-3 pl-0 mb-3">
            <div class="contact-heading mb-5">
               <h2>{{ $section->title }}</h2>
            </div>
            <div class="contact-form-wrap">
               <form id="main-form" class="contact-form form" name="enq" method="POST" action="">
                  <div class="row">
                     <div class="col-md-6">
                        <div class="form-group">
                           <span class="form-icon"><i class="icofont-user"></i></span>
                           <input type="text" class="form-control" id="name" placeholder="John" required="">
                           <label for="name">First Name*</label>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <span class="form-icon"><i class="icofont-envelope"></i></span>
                           <input type="email" class="form-control" id="email" placeholder="example@xyz.com" required="">
                           <label for="email">Email*</label>
                        </div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-6">
                        <div class="form-group">
                           <span class="form-icon"><i class="icofont-ui-dial-phone"></i></span>
                           <input type="text" class="form-control" id="number" placeholder="xxx-xxx-xxxx" required="">
                           <label for="number">Contact Number*</label>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <span class="form-icon"><i class="icofont-at"></i></span>
                           <input type="text" class="form-control" id="subject" placeholder="Subject" required="">
                           <label for="subject">Subject*</label>
                        </div>
                     </div>
                  </div>
                  <div class="form-group form-message">
                     <textarea class="form-control" id="message" rows="6" placeholder="Message"></textarea>
                     <label for="message">Message</label>
                  </div>
                  <div class="text-center wow fadeInUp" style="visibility: visible; animation-name: fadeInUp;">
                     <div class="actions">
                        <input value="SUBMIT MESSAGE" name="submit" id="submitButton" class="btn con-btn" title="Click here to submit your message!" type="submit">
                        <img src="{{ asset('website-assets/template-1/assets/img/ajax-loader.gif') }}" id="loader" style="display:none" alt="loading" width="16" height="16">
                     </div>
                  </div>
               </form>
            </div>
         </div>
         <!-- end col -->
      </div>
   </div>
</section>
