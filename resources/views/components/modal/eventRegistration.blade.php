  <div id="eventregistrationmodle" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="log-in-pop  event_register">            
            <div class="log-in-pop-right log-in-pop-event-right">
                <a href="{{ url('#') }}" class="pop-close" data-dismiss="modal">
                    <img src="{{ asset("/frontend/images/cancel.png") }}" alt="" />
                </a>
                <h4>Event Registration Form</h4>
                <form id="eventparticipantform">
                    @csrf

                    <div class="alert alert-danger alert-dismissable" id="error" style="display: none"> 
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a> 
                        <p id="message"></p> 
                    </div>

                    <div>
                        <div class="input-field s12">
						<input type="hidden"  name="event_id" id="eventid" class="eventid">
						<input type="hidden"  name="user_id" id="userid" class="userid">
                            <input type="text"  name="participant" value="{{ Auth::user()->name}}" readonly />
                            <label>Participant Name</label>
                        </div>
                    </div>
                    <div>
                        <div class="input-field s12">
                            <input type="email" name="email" value="{{ Auth::user()->email}}" readonly />
                            <label>Email id</label>
                        </div>
                    </div>
                    <div>
                        <div class="input-field s12">
                            <input type="tel" name="mobile_no" value="{{ Auth::user()->mobile_no}}" readonly />
                            <label>Mobile Number</label>
                        </div>
                    </div>
                    <div>
                        <div class="input-field s4">
                            <input type="submit" value="Event Register" class="waves-effect waves-light log-in-btn"> </div>
                    </div>
                </form>
            </div>
        </div>
    </div>