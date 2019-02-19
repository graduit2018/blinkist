@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Goodreads Want to Read</div>

                <div class="card-body">
                    @if (Auth::user()->goodreads_access_token)
                        <table class="table">
                            <tbody>
                                @foreach ($books as $book)
                                    <tr>
                                        <td>
                                            <h5 class="font-weight-bold">{{ $book['isbn13'] }}</h5>
                                            <div>{{ $book['title'] }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        Please connect your GoodReads
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
