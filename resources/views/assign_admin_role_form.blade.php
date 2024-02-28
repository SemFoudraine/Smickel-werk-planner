<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Admin Role</title>
    <!-- CSS hier inbegrepen -->
</head>
<body>
    <div class="container">
        <h1>Assign Admin Role</h1>
        <p>Gebruiker: {{ $user->name }}</p>

        <form action="{{ route('assign-admin-role', ['id' => $user->id]) }}" method="POST">
            @csrf
            <button type="submit">Geef adminrol</button>
        </form>
    </div>
    <!-- JavaScript hier inbegrepen -->
</body>
</html>
