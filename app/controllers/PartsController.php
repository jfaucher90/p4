<?php

class PartsController extends BaseController {

	public function addForm()
	{
		return View::make('add_form');
	}

	public function addPart()
	{
		$part = new Part;
		$part->type = Input::get('type');
		$part->part_name = Input::get('part_name');
		$part->user_id = Auth::user()->id;

 		# Try to add the part 
    	try {
        	$part->save();
    	}
    	# Fail
    	catch (Exception $e) {
        	return Redirect::to('/add')->with('error', 'Add failed, please try again.')->withInput();
    	}

		return Redirect::to('/profile/{user}')->with('user', Auth::user()->username);
	}

	public function deletePart()
	{
		# DELETE THE PART
		$id = Input::get('id');
		$part = Part::find($id);
		try {
        	$part->delete();
    	}
    	# Fail
    	catch (Exception $e) {
        	return Redirect::to('/profile/{user}')->with('error', 'Add failed; please try again.')
        										  ->with('user', Auth::user()->username)
        										  ->withInput();
    	}

		return Redirect::to('/profile/{user}')->with('user', Auth::user()->username);
	}

}