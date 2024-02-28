@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Swap Requests</div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Current User</th>
                                    <th>Selected User</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($swapRequests as $swapRequest)
                                    <tr>
                                        <td>{{ $swapRequest->datum }}</td>
                                        <td>{{ optional($swapRequest->currentUser)->name ?? 'N/A' }}</td>
                                        <td>{{ optional($swapRequest->selectedUser)->name ?? 'N/A' }}</td>
                                        <td>
                                            @if ($swapRequest->approval_status == 'pending')
                                                <a href="{{ route('admin.roosters.approveSwapRequest', ['id' => $swapRequest->id]) }}"
                                                    onclick="event.preventDefault(); document.getElementById('approve-form-{{ $swapRequest->id }}').submit();">
                                                    Approve
                                                </a>
                                                <form id="approve-form-{{ $swapRequest->id }}"
                                                    action="{{ route('admin.roosters.approveSwapRequest', ['id' => $swapRequest->id]) }}"
                                                    method="POST" style="display: none;">
                                                    @csrf
                                                </form>
                                                |
                                                <a href="{{ route('admin.roosters.rejectSwapRequest', ['id' => $swapRequest->id]) }}"
                                                    onclick="event.preventDefault(); document.getElementById('reject-form-{{ $swapRequest->id }}').submit();">
                                                    Reject
                                                </a>
                                                <form id="reject-form-{{ $swapRequest->id }}"
                                                    action="{{ route('admin.roosters.rejectSwapRequest', ['id' => $swapRequest->id]) }}"
                                                    method="POST" style="display: none;">
                                                    @csrf
                                                </form>
                                            @else
                                                Approved <!-- of een andere melding afhankelijk van je vereisten -->
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
