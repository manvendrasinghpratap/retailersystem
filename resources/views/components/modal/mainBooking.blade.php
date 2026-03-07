<div class="col-md-4">
    <div class="book-form inn-com-form">
        <form class="col s12" id="booking-form">
             @csrf
            <div class="row">
                <div class="input-field col s6">
                    <input type="text" name="first_name" placeholder="First Name" required class="validate">
                    
                </div>
                <div class="input-field col s6">
                    <input type="text" name="last_name" placeholder="Last Name" required class="validate">
                    
                </div>
            </div>
            <div class="row">
                <div class="input-field col s6">
                    <input type="text" name="email" placeholder="Email" required class="validate">
                    
                </div>
                <div class="input-field col s6">
                    <input type="text" name="mobile" placeholder="Mobile No" required class="validate">
                    
                </div>
            </div>
            <div class="row">
                <div class="input-field col s6">
                    <select name="room_type" required placeholder="Room Type">
                        <option  value="" disabled selected>Room Type</option>
                        @foreach($roomListing as $room)
                            <option  value="{{ $room->id }}">{{ $room->room_type_name }} (NGN {{ $room->discount_price }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-field col s6">
                    <input type="text" name="country" placeholder="Country" required class="validate">
                    
                </div>
            </div>
            <div class="row">
                <div class="input-field col s6">
                    <select name="no_of_adults" placeholder="No of Adults" required>
                        <option  value="" disabled selected>No of adults</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="1">4</option>
                    </select>
                </div>
                <div class="input-field col s6">
                    <select name="no_of_childrens" placeholder="No of Childrens" required>
                        <option value="" disabled selected>No of childrens</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="1">4</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s6">
                    <input type="text" name="arrival_date" placeholder="Check In" autocomplete="off" required id="from" >
                    
                </div>
                <div class="input-field col s6">
                    <input type="text" name="departure_date" placeholder="Check Out"  autocomplete="off" required id="to" >
                    
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                    <textarea name="message" placeholder="Message" autocomplete="off" id="textarea1" class="materialize-textarea" data-length="120"></textarea>
                    
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                    <input type="submit" value="submit" class="form-btn"> </div>
            </div>
        </form>
    </div>
</div>

