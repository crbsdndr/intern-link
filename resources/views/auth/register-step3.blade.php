<!DOCTYPE html>
<html>
<head>
    <title>Register - Step 3</title>
</head>
<body>
<form method="POST" action="{{ route('register.step3.post') }}">
    @csrf
    <p>Confirm your registration.</p>
    <button type="submit">Finish</button>
</form>
<a href="{{ route('register.step2') }}">Back</a>
</body>
</html>
