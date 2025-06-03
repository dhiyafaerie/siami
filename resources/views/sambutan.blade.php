@extends('layouts.app')

@section('content')

    <!-- MAIN SECTION -->
    <article class="container mt-28">
      <h1
        class="text-xneutral-400 font-montserrat text-xl sm:text-2xl font-semibold"
      >
        Sambutan Rektor B-University
      </h1>
      <p
        class="text-xneutral-200 text-sm sm:text-base font-montserrat font-semibold"
      >
        Meraih Masa Depan dengan Semangat Kebersamaan
      </p>
        @if ($greetings->isEmpty())
            <div class="text-center text-xneutral-300 font-montserrat font-medium text-sm sm:text-base">
                No Data Available
            </div>
        @else

        @foreach ($greetings as $greeting)
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-8 mt-8">
                <img
                src="{{ asset('storage/' . $greeting->image) }}"
                alt="Rektor B-University"
                class="w-full h-fit object-cover rounded-[30px] sm:col-span-4"
                />
                <div
                class="font-montserrat font-medium text-sm sm:text-base text-justify sm:col-span-8 text-xneutral-300"
                >
                    {!! $greeting->content !!}
                </div>
            </div>
        @endforeach

        @endif

    </article>

@endsection
