  <div id="modal1" class="modal fade" role="dialog">

      <div class="log-in-pop">

          <div class="log-in-pop-left">
@lang('translation.havanainnsuitescom')
              <h1>Hello.......</h1>

              <p>Please log in to access your account or sign up if you're a new user. We are thrilled to have you on board.</p>

              <p>If you have any questions or encounter any issues during the login process, feel free to reach out to our support team at <i>{{ Config::get('constants.emailcontact') }} </i> or call us at <i>{{ Config::get('constants.hisphone') }}.</i>.</p>

          </div>

          <div class="log-in-pop-right">

              <a href="{{ url("#") }}" class="pop-close" data-dismiss="modal"><img src="{{ asset("/frontend/images/cancel.png") }}" alt="" />

              </a>

              <h4 class="formheading">Login</h4>

              <!-- <p>Don't have an account? Create your account. It's take less then a minutes</p> -->

              <form class="s12 loginform" id="loginForm" autocomplete="off">

                  @csrf



                  <div class="alert alert-danger alert-dismissable" id="error" style="display: none">

                      <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>

                      <p id="message"></p>

                  </div>

                  <div>

                      <div class="input-field s12">

                          <input name="username" type="text" class="validate" autocomplete="off">

                          <label>User name</label>

                      </div>

                  </div>

                  <div>

                      <div class="input-field s12">

                          <input name="password" type="password" class="validate" autocomplete="off">

                          <label>Password</label>

                      </div>

                  </div>

                  <div>

                      <div class="input-field s4">

                          <input type="submit" value="Login" class="waves-effect waves-light log-in-btn">

                          <a href="javascript:void(0);" class="forgotpasswordbutton forgotpasswordbuton">Forgot Password</a>

                      </div>

                  </div>

                  <div>

                      <div class="input-field s12"> <!--<a href="javascript:void(0);" data-dismiss="modal" data-toggle="modal" data-target="#modal3">Forgot password</a> | <a href="{{ url("#") }}" data-dismiss="modal" data-toggle="modal" data-target="#modal2">Create a new account</a>--> </div>

                  </div>

              </form>

              <form name="forgotpasswordform" class="mt-4 pt-2 forgotpasswordform" action="javascript::void(0)" method="POST" onSubmit="return validateforgotpassword();">

                  @csrf



                  <div>

                      <div class="input-field mb-4">

                         <p>Please contact the System Administrator for assistance with resetting the username and password.</p>

                          <!-- <label>Email Id</label> -->

                      </div>

                  </div>

                  <div>

                      <div class="input-field">

                          {{-- <input type="submit" value="Reset Password" class="ps-btn resetpassword" /> --}}

                          <a href="javascript:void(0);" class="loginbutton forgotpasswordbuton">Login</a>

                          

                      </div>

                  </div>

              </form>

          </div>

      </div>

  </div>