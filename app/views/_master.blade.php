<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">		
		<title>
			@yield('title', 'Bike Swap')
		</title>
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
		{{ HTML::style('css/p4.css'); }}
	</head>
	<body>
		<div class="container">
			@if(Session::get('flash_message'))
				<div class='flash-message text-center'>{{ Session::get('flash_message') }}</div>
			@endif
			@yield('content')
		</div>
	</body>
</html>