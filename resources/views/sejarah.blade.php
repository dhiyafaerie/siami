@extends('layouts.app')
@section('content')

    <!-- MAIN SECTION -->
    <article class="container mt-28">
      <h1
        class="text-xneutral-400 font-montserrat text-xl sm:text-2xl font-semibold"
      >
        Sejarah B-University
      </h1>
      <p
        class="text-xneutral-200 font-montserrat text-sm sm:text-base font-semibold"
      >
        Bersama mengembangkan pendidikan Negeri
      </p>

        @if ($historys->isEmpty())
            <div class="text-center text-xneutral-300 font-montserrat font-medium text-sm sm:text-base">
                No Data Available
            </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-8 mt-8">
            @foreach ($historys as $history)
            <div class="w-full h-[600px] sm:col-span-5 relative overflow-hidden rounded-[30px]">
                <img
                src="{{ asset('storage/' . $history->image) }}"
                alt="Sejarah B-University"
                class="absolute inset-0 w-full h-full object-contain object-center rounded-[30px]"
                />
            </div>
            <div
            class="font-montserrat font-medium text-sm sm:text-base text-justify sm:col-span-7 text-xneutral-300"
            >
                {!! $history->content !!}
            </div>
            @endforeach
        </div>
        @endif
    </article>

@endsection
