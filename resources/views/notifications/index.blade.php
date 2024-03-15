@extends('layouts.app')

@section('content')
    <div class="container dark:bg-gray-800 p-4 rounded-lg">
        <h2 class="text-black dark:text-white text-2xl font-bold mb-4">Meldingen</h2>
        <ul class="bg-white rounded-lg shadow-md p-4">
            @forelse ($notifications as $notification)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-4">
                    <div class="p-4 flex justify-between items-center">
                        <div class="text-gray-800">
                            {!! $notification->title !!} - {!! $notification->message !!}
                        </div>
                        <form method="POST" action="{{ route('notifications.destroy', $notification) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                Verwijder
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow-lg p-4">
                    Geen nieuwe meldingen.
                </div>
            @endforelse
        </ul>
    </div>
@endsection
