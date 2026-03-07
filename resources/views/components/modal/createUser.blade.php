  <div id="modal2" class="modal fade" role="dialog">
        <div class="log-in-pop">
            <div class="log-in-pop-left">
                <h1>Hello...  </h1>
                <p>We are thrilled that you've chosen to join our community. By creating an account, you're unlocking a world of possibilities and exclusive features tailored just for you. </p>
                <p>If you have any questions or encounter any issues during the login process, feel free to reach out to our support team at [support@email.com] or call us at [support phone number].</p>
            </div>
            <div class="log-in-pop-right">
                <a href="{{ url("#") }}" class="pop-close" data-dismiss="modal">
                    <img src="{{ asset("/frontend/images/cancel.png") }}" alt="" />
                </a>
                <h4>Create an Account</h4>
                <form id="createUser">
                    @csrf 
                    <div class="alert alert-danger alert-dismissable" id="error" style="display: none"> 
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a> 
                        <p id="message"></p> 
                    </div>

                    <div>
                        <div class="input-field s12">
                            <input type="text"  name="name" class="validate">
                            <label>User name</label>
                        </div>
                    </div>
                    <div>
                        <div class="input-field s12">
                            <input type="email" name="email" class="validate">
                            <label>Email id</label>
                        </div>
                    </div>
                    <div>
                        <div class="input-field s12">
                            <input type="tel" name="mobile_no" class="validate">
                            <label>Mobile Number</label>
                        </div>
                    </div>
                    <div>
                        <div class="input-field s12">
                            <input type="password" name="password" class="validate">
                            <label>Password</label>
                        </div>
                    </div>
                    <div>
                        <div class="input-field s12">
                            <input type="password" name="password_confirmation" class="validate">
                            <label>Confirm password</label>
                        </div>
                    </div>
                    <div>
                        <div class="input-field s4">
                            <input type="submit" value="Register" class="waves-effect waves-light log-in-btn"> </div>
                    </div>
                    <div>
                        <div class="input-field s12"> <a href="{{ url('#') }}" data-dismiss="modal" data-toggle="modal" data-target="#modal1">Are you a already member ? Login</a> </div>
                    </div>
                </form>
            </div>
        </div>
    </div>