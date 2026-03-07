 <div id="modal3" class="modal fade" role="dialog">
        <div class="log-in-pop">
            <div class="log-in-pop-left">
                <h1>Hello... </h1>
                <p>We understand that it's easy to forget passwords sometimes. But fear not, we're here to help you regain access to your account.</p>
                <p>If you have any questions or encounter any issues during the login process, feel free to reach out to our support team at [support@email.com] or call us at [support phone number].</p>
            </div>
            <div class="log-in-pop-right">
                <a href="{{ url("#") }}" class="pop-close" data-dismiss="modal"><img src="{{ asset("/frontend/images/cancel.png") }}" alt="" />
                </a>
                <h4>Forgot password</h4>
                <form class="s12">
                    <div>
                        <div class="input-field s12">
                            <input type="text" data-ng-model="name3" class="validate">
                            <label>User name or email id</label>
                        </div>
                    </div>
                    <div>
                        <div class="input-field s4">
                            <input type="submit" value="Submit" class="waves-effect waves-light log-in-btn"> </div>
                    </div>
                    <div>
                        <div class="input-field s12"> <a href="{{ url("#") }}" data-dismiss="modal" data-toggle="modal" data-target="#modal1">Are you a already member ? Login</a> <!--| <a href="{{ url("#") }}" data-dismiss="modal" data-toggle="modal" data-target="#modal2">Create a new account</a>--> </div>
                    </div>
                </form>
            </div>
        </div>
    </div>