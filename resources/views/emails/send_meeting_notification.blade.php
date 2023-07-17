<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>send meeting mail</title>
</head>
<body>
	<h1>Hi </h1>
	<p>You are invited to a meeting .</p>
	<a>meeting link : {{ $data['meeting_link'] }}</a>
	<p>meeting time : {{ $data['meeting_time'] }}</p>
</body>
</html>