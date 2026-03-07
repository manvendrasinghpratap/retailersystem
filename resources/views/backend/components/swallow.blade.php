<div id="inputContainer">
	<div class="inputRow row">
	<div class="col-xl-3 col-md-3">
			<div class="form-group mb-3">
				<select name="itemtype[]"  class="form-control" >
					<option value="">--Select Type--</option>
					<option value="variation">Taste Blend</option>
					<option value="addon">Extra</option>
				</select>
			</div>
	</div>
	<div class="col-xl-5 col-md-5">
			<div class="form-group mb-3">
				<input name="itemname[]" placeholder="Item Name" type="text"  class="form-control" value="" />
			</div>
		</div>
		<div class="col-xl-2 col-md-2">
			<div class="form-group mb-3">
				<input type="number" name="itemprice[]" placeholder="Item Price" class="form-control onlyinteger setdefaultzero nocutcopypaste" value="" min="0" />
			</div>
		</div>

		<div class="col-xl-2 col-md-2 form-group">
			<button class="deleteBtn btn btn-danger">Delete</button>
		</div>
	</div>
</div>
<div class="form-group">
	<button type="button" id="addBtn" class="btn btn-primary right">Add More</button>
</div>