<?php

class SearchController extends BaseController {

	public function getSearch()
	{
		# Genarate search form if user is logged in
        if (Auth::user())
        {
            return View::make('search_form');
        }
        # Prompt user to log in if not logged in
        else
        {
            return Redirect::to('/login')
                ->with('flash_message', 'Please log in');
        }
	}
	public function postSearch()
	{
    	# Get input to determine which form was submitted
        $query = Input::get('query');
        $distanceAway = Input::get('distanceAway');
        $username = Input::get('username');
        # Redirect to search page if no input was entered
        if (empty($query) && empty($distanceAway) && empty($username))
        {
            return Redirect::to('/search')
                    ->with('flash_message', 'Please enter input!');  
        }
        # Execute if $distanceAway and $username are empty
        elseif (empty($distanceAway) && empty($username))
    	{
            # Adds wildcard to entered query and searches for parts with similar name           
            $query = '%'.$query.'%';
            # Gets type from input
            $type = Input::get('type');
        	# Searches for part with similar name.  
            # Searches all types if type was 'any'
            if ($type == 'any')
            {
                $parts = Part::where('part_name', 'LIKE', '%'.$query.'%')->get();
            }
            # Filters search results if type was specified
            else
            {
                $parts = Part::where('part_name', 'LIKE', '%'.$query.'%')
                    ->where('type', '=', $type)
                    ->get();
            }
            if ($parts->isEmpty())
            {
                return Redirect::to('/search')
                    ->with('flash_message', 'No parts found matching that name. Try again!');
            }
            $usernames = null;
            # Finds corresponding users for parts found
            foreach ($parts as $part)
            {
                $user = User::where('id', '=', $part->user_id)->first();

                $usernames[$part->id] = $user->username;
        	}
            # Returns search results with $parts and corresponding $usernames
            return View::make('search_results')
                ->with('parts', $parts)
                ->with('usernames', $usernames);

    	}
    	# Execute if $query and $username are empty
        elseif (empty($query) && empty($username))
    	{
            include 'search_helper.php';

            # Calculate logged in user's lat/long coordinates
            $coordinates1 = getCoordinates(Auth::user()->zip);
        	
            # Retrieves all users to calculate distance
            $users = User::all();
        	# Flag variable for $closeUsers array
            $i = 0;
            
            # Loops through all users in $users and calculates distance from logged in user
        	foreach ($users as $user)
        	{
            	# Skips logged in user
                if ($user->id == Auth::user()->id)
            	{
                    continue;
            	}
            	else
            	{
                    $coordinates2 = getCoordinates($user->zip);
                	$distanceBetween = calculateDistance($coordinates1['lat'], 
                                                         $coordinates1['long'], 
                                                         $coordinates2['lat'],
                                                         $coordinates2['long']);
                    # Adds user to $closerUsers array if they are close enough
                    # Increments $i
                    if ($distanceBetween <= $distanceAway)
                	{
                        $closeUsers[$i] = $user;
                    	$i++;
                	}
            	}
        	}
            # Returns Search Results view if $closerUsers contains users
        	if (!empty($closeUsers))
        	{
            	return View::make('search_results')
                    ->with('closeUsers', $closeUsers);
        	}
            # Returns search results view with $distanceAway to tell user there is no one within the chosen radius
        	else
        	{
        		return Redirect::to('/search')
                    ->with('flash_message', 'No users found in that radius. Try again!');

        	}
    	}

        # Execute if $distanceAway and $query are empty
        else
        {
            # Adds wildcard to entered username and searches for user with similar name           
            $username = '%'.$username.'%';
            $users = User::where('username', 'LIKE', '%'.$username.'%')
                        ->where('id', '<>', Auth::user()->id)
                        ->get();
            # Returns search results view with $users
            if ($users->isEmpty())
            {
                return Redirect::to('/search')
                    ->with('flash_message', 'No users found matching that name. Try again!');
            }
            else
            {
                return View::make('search_results')
                    ->with('users', $users);
            }
        }
	}
}