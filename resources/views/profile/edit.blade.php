{{-- resources/views/users/index.blade.php --}}

@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('User Management') }}</div>

                <div class="card-body">
                    {{-- Controleer of er gebruikers zijn --}}
                    @if($users->isNotEmpty())
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Loop door elke gebruiker en maak een tabelrij --}}
                                @foreach($users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            {{-- Bewerkknop voor de gebruiker --}}
                                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">
                                                {{ __('Edit') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>{{ __('No users found.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
