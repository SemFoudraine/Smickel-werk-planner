<h1>Gebruiker toewijzen als admin</h1>
<p>Gebruiker: {{ $user->name }}</p>

<form action="{{ route('assign.admin.role', ['userId' => $user->id]) }}" method="POST">
    @csrf
    <button type="submit">Toewijzen als Admin</button>
</form>
