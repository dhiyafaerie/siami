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
        class="text-xneutral-200 text-sm sm:text-base font-montserrat font-semibold"
      >
        Bersama mengembangkan pendidikan Negeri
      </p>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-8">
        <div class="space-y-3">
          <h3
            class="font-montserrat text-base sm:text-lg font-semibold text-primary-200 text-center"
          >
            VISI
          </h3>
          <p
            class="text-lg sm:text-xl font-semibold font-montserrat text-xneutral-200 text-center uppercase"
          >
            {!! $visi ?? '"No Data Available"' !!}
          </p>
        </div>
        <div class="space-y-3">
          <h2
            class="font-montserrat text-base sm:text-lg font-semibold text-primary-200 text-center"
          >
            MISI
          </h2>
          <ol
            class="list-decimal pl-4 font-montserrat font-semibold text-xneutral-200 text-sm sm:text-base text-justify"
          >
            {!! $misi ?? '"No Data Available"' !!}
          </ol>
        </div>
      </div>

      <div
        class="grid grid-cols-1 sm:grid-cols-3 mt-10 border border-xneutral-100 rounded-2xl overflow-hidden"
      >
        <div class="p-[30px]">
          <h2
            class="text-base sm:text-lg font-semibold text-xneutral-400 font-montserrat"
          >
            Inovatif
          </h2>
          <p
            class="font-montserrat font-medium mt-4 text-xneutral-200 text-xs sm:text-sm"
          >
            Makna ”Inovatif” dalam visi universitas adalah Lulusan mempunyai
            kemampuan dalam berfikir untuk menciptakan pengetahuan dan teknologi
            baru yang tepat guna di bidang industri dan kesehatan untuk
            kemaslahatan ummat.
          </p>
        </div>
        <div>
            @if(isset($visimisiImg[0]))
                <img src="{{ asset('storage/'. $visimisiImg[0]) }}" alt="Inovatif" />
            @else
                <p>No Image Available</p>
            @endif
        </div>
        <div class="p-[30px]">
          <h2
            class="text-base sm:text-lg font-semibold text-xneutral-400 font-montserrat"
          >
            Profesional
          </h2>
          <p
            class="font-montserrat font-medium mt-4 text-xneutral-200 text-xs sm:text-sm"
          >
            Makna ”Profesional” dalam visi universitas adalah lulusan mempunyai
            kompetensi sesuai dengan profesinya masing-masing baik dalam aspek
            pengetahuan, sikap maupun keterampilan serta berpegang teguh pada
            nilai moral yang mengarahkan serta mendasari perbuatan.
          </p>
        </div>
        <div>
            @if(isset($visimisiImg[1]))
                <img src="{{ asset('storage/'. $visimisiImg[1]) }}" alt="Inovatif" />
            @else
                <p>No Image Available</p>
            @endif
        </div>
        <div class="p-[30px]">
          <h2
            class="text-base sm:text-lg font-semibold text-xneutral-400 font-montserrat"
          >
            Islami
          </h2>
          <p
            class="font-montserrat font-medium mt-4 text-xneutral-200 text-xs sm:text-sm"
          >
            Makna ”Islami” dalam visi universitas adalah lulusan mempunyai
            integritas menjunjung tinggi nilai-nilai keislaman (Islamic values)
            dalam setiap perilaku dan peduli terhadap kesejahteraan masyarakat
            serta perubahan dalam setiap aspek kehidupan di lingkungannya.
          </p>
        </div>
        <div>
            @if(isset($visimisiImg[2]))
                <img src="{{ asset('storage/'. $visimisiImg[2]) }}" alt="Inovatif" />
            @else
                <p>No Image Available</p>
            @endif
        </div>
      </div>
    </article>
@endsection
