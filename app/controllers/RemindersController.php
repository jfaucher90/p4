<?php

class RemindersController extends Controller {

	/**
	 * Display the password reminder view.
	 *
	 * @return Response
	 */
	public function getRemind()
	{
		return View::make('remind');
	}

	/**
	 * Handle a POST request to remind a user of their password.
	 *
	 * @return Response
	 */
	public function postRemind()
	{
		switch ($response = Password::remind(Input::only('email')))
		{
			case Password::INVALID_USER:
				return Redirect::back()->with('flash_message', Lang::get($response));

			case Password::REMINDER_SENT:
				return Redirect::back()->with('flash_message', Lang::get($response));
		}
	}

	/**
	 * Display the password reset view for the given token.
	 *
	 * @param  string  $token
	 * @return Response
	 */
	public function getReset($token)
	{
		if (is_null($token)) App::abort(404);

		return View::make('reset')->with('token', $token);
	}

	/**
	 * Handle a POST request to reset a user's password.
	 *
	 * @return Response
	 */
	public function postReset()
	{
		$rules = array(
                'email' => 'required|email',
                'password' => 'required|min:6',
                'password_confirmation' => 'required|min:6'
            );
        
        $validator = Validator::make(Input::only('email', 'password', 'password_confirmation'), $rules);
        
        if ($validator->fails())
        {
            return Redirect::back()
                ->with('flash_message', 'Reset failed, please fix errors and try again')
                ->withInput()
                ->withErrors($validator);
        }


		$credentials = Input::only(
			'email', 'password', 'password_confirmation', 'token'
		);

		$response = Password::reset($credentials, function($user, $password)
		{
			$user->password = Hash::make($password);

			$user->save();
		});

		switch ($response)
		{
			case Password::INVALID_PASSWORD:
			case Password::INVALID_TOKEN:
			case Password::INVALID_USER:
				return Redirect::back()->with('error', Lang::get($response));

			case Password::PASSWORD_RESET:
				return Redirect::to('/login')->with('flash_message', 'Your password has been reset');
		}
	}

}
