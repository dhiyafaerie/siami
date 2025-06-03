@extends('layouts.app')
@section('content')
    <!-- HERO SECTION -->
    <section class="container min-h-[90vh] relative mt-28">
      <div
        id="hero-1"
        class="absolute animate-fade top-0 left-0 w-full h-full flex flex-col lg:flex-row lg:portrait:flex-col items-center justify-center rounded-[30px]"
      >
        <div class="px-4 md:pl-12 w-full lg:w-1/2 lg:portrait:w-full">
          <h1
            class="text-4xl font-semibold font-montserrat md:text-5xl/[60px] text-white mb-7"
          >
            Menyatukan Ilmu dan Iman untuk Masa Depan Cerah
          </h1>
          <span
            class="text-lg md:text-xl text-white font-montserrat font-medium"
            >Kami berkomitmen mendidik generasi unggul yang menjunjung tinggi
            nilai agama dan kecemerlangan akademik.</span
          >
        </div>
        <img
          src="../assets/images/hero-illustration-1.png"
          class="mr-12 sm:-mr-12 z-0 w-full lg:w-1/2 lg:portrait:w-4/5"
          alt="Hero Image"
        />
      </div>
      <div
        id="hero-2"
        class="absolute top-0 animate-fade left-0 w-full h-full flex flex-col lg:flex-row lg:portrait:flex-col items-center justify-center rounded-[30px]"
      >
        <div class="px-4 md:pl-12 w-full lg:w-1/2 lg:portrait:w-full">
          <h1
            class="text-4xl font-semibold font-montserrat md:text-5xl/[60px] text-white mb-7"
          >
            Menuju Pendidikan Berdaya Saing Global
          </h1>
          <span
            class="text-lg md:text-xl text-white font-montserrat font-medium"
            >Teknik pembelajaran yang memadukan tradisi keilmuan Agama dengan
            inovasi modern.</span
          >
        </div>
        <img
          src="../assets/images/hero-illustration-2.png"
          class="mr-12 sm:-mr-12 z-0 w-full lg:w-1/2 lg:portrait:w-4/5"
          alt="Hero Image"
        />
      </div>
      <div
        id="hero-3"
        class="absolute animate-fade top-0 left-0 w-full h-full flex flex-col lg:flex-row lg:portrait:flex-col items-center justify-center rounded-[30px]"
      >
        <div class="px-4 md:pl-12 w-full lg:w-1/2 lg:portrait:w-full">
          <h1
            class="text-4xl font-semibold font-montserrat md:text-5xl/[60px] text-white mb-7"
          >
            Menciptakan Generasi Berakhlak dan Berwawasan
          </h1>
          <span
            class="text-lg md:text-xl text-white font-montserrat font-medium"
            >Kami hadir untuk membimbing Anda dalam meraih prestasi akademik dan
            menjadi agen perubahan di dunia.</span
          >
        </div>
        <img
          src="../assets/images/hero-illustration-3.png"
          class="mr-12 sm:-mr-12 z-0 w-full lg:w-1/2 lg:portrait:w-4/5"
          alt="Hero Image"
        />
      </div>
    </section>

    <div
      class="container overflow-hidden w-fit md:px-16 py-5 rounded-3xl mx-4 sm:mx-auto -mt-32 bg-white z-10 relative shadow-2xl"
    >
      <h3
        class="text-xl sm:text-2xl text-center w-full font-semibold font-montserrat mb-4"
      >
        Bekerjasama Dengan
      </h3>

      <div class="flex overflow-x-auto gap-16">
        @if($cooperationImg->isEmpty())
            <p class="text-center text-xneutral-200 font-montserrat text-lg">
                No data available
            </p>
        @else
            @foreach ($cooperationImg as $image)
                <img
                    class="w-12 md:w-fit"
                    src="{{ asset('storage/' . $image->image) }}"
                    alt="Cooperation"
                />
            @endforeach
        @endif
        </div>
    </div>

    <!-- END OF HERO SECTION -->

    <!-- ABOUT SECTION -->
    <section class="w-full overflow-hidden py-28">
      <div
        class="container grid grid-cols-1 md:grid-cols-2 items-center gap-12"
      >
        <div class="space-y-4 lg:ml-20">
          <h3
            class="text-primary-200 font-bold text-xl sm:text-2xl font-montserrat"
          >
            TENTANG KAMI
          </h3>
          @if (empty($abouts->content) && empty($abouts->image)) <!-- Check if both content and images are empty -->
            <p class="font-montserrat text-lg sm:text-xl font-semibold text-xneutral-200">
                No data available
            </p>
          @else
            <p class="font-montserrat text-2xl sm:text-4xl font-semibold">
                Membangun generasi
                <span class="text-secondary-purple">unggul</span> dan
                <span class="text-secondary-pink">berakhlak</span>
            </p>
            <p
                class="text-base sm:text-lg font-semibold text-xneutral-200 font-montserrat"
            >
                {{ $abouts->content }}
            </p>
            <a
                href="{{ route('sejarah') }}"
                class="px-6 py-[14px] font-montserrat text-neutral-0 bg-white border w-fit text-lg font-semibold border-primary-200 text-primary-200 rounded-full flex gap-[10px]"
                ><span>Tentang kami</span>
                <i class="bi bi-arrow-right"></i>
            </a>
          @endif
        </div>
        <div class="relative">
          <div class="grid grid-cols-2 gap-6 w-fit">
            @if (isset($abouts->image[0]))
                <img src="{{ asset('storage/'. $abouts->image[0]) }}" alt="Illustration 1" />
            @endif
            @if (isset($abouts->image[1]))
                <img src="{{ asset('storage/'. $abouts->image[1]) }}" alt="Illustration 2" />
            @endif
            @if (isset($abouts->image[2]))
            <img
              class="col-span-2"
              src="{{ asset('storage/'. $abouts->image[2]) }}"
              alt="Illustration 3"
            />
            @endif
          </div>
          <img
            class="absolute -bottom-32 -left-36 -z-10"
            src="/assets/images/elipse-1.svg"
            alt=""
          />
          <img
            class="absolute -top-24 -right-16 -z-10"
            src="/assets/images/elipse-2.svg"
            alt=""
          />
        </div>
      </div>
    </section>
    <!-- END OF ABOUT SECTION -->

    <!-- NEWS SECTION -->
    <section class="container relative">
      <div class="mb-10 flex justify-between items-center">
        <div>
          <h3
            class="font-semibold text-xl sm:text-2xl font-montserrat text-xneutral-400"
          >
            Berita terkini untuk Anda
          </h3>
          <p
            class="text-sm sm:text-base font-montserrat text-xneutral-200 font-semibold"
          >
            Temukan berita terbaru hari ini
          </p>
        </div>
        <div>
          <button
            class="text-xneutral-400 hover:text-xneutral-0 bg-xneutral-0 transition-all hover:bg-xneutral-400 w-10 h-10 rounded-full"
          >
            <i class="bi bi-arrow-left-short text-4xl"></i>
          </button>
          <button
            class="text-xneutral-400 hover:text-xneutral-0 bg-xneutral-0 transition-all hover:bg-xneutral-400 w-10 h-10 rounded-full"
          >
            <i class="bi bi-arrow-right-short text-4xl"></i>
          </button>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @if($news->isEmpty())
            <div>
                No data available
            </div>
        @else
            @foreach ($news as $newslist)
                <div
                    class="p-[14px] rounded-[20px] border border-xneutral-100 bg-xneutral-0"
                >
                    <div class="max-h-[214px] rounded-2xl overflow-hidden mb-5">
                    <img src="{{ asset('storage/' . $newslist->image) }}" alt="{{ $newslist->title }}" alt="Berita 1" />
                    </div>
                    <a
                    href="{{ route('berita.show', $newslist->slug) }}"
                    class="text-base sm:text-lg font-montserrat font-semibold text-xneutral-400 line-clamp-2"
                    >
                    {{ $newslist->title }}
                    </a>
                    <p
                    class="font-montserrat text-xs sm:text-sm font-semibold text-xneutral-200"
                    >
                        {{ \Carbon\Carbon::parse($newslist->created_at)->format('d/m/y') }}
                    </p>
                </div>
            @endforeach
        @endif
      </div>
      <div class="absolute top-12 -left-24 -z-10">
        <img src="/assets/images/elipse-1.svg" alt="" />
      </div>
    </section>
    <!-- END OF NEWS SECTION -->

    <!-- RECTOR SECTION -->
    <section class="container mt-28">
      <div class="text-center space-y-2">
        <h3
          class="font-montserrat font-semibold text-xneutral-400 text-xl sm:text-2xl"
        >
          Rektorat B-Universitas
        </h3>
        <p
          class="font-montserrat text-sm sm:text-base font-semibold text-xneutral-200"
        >
          Berkomitmen untuk meningkatkan kualitas Pendidikan
        </p>
      </div>
      <div class="grid grid-cols-2 gap-12 lg:grid-cols-4 mt-11 text-center">
        @if ($rectors->isEmpty()) <!-- Check if the collection is empty -->
            <div class="col-span-2 lg:col-span-4">
                <p class="text-xneutral-200 font-montserrat text-lg">
                    No data available
                </p>
            </div>
        @else
            @foreach ($rectors as $rektor)
                <div class="flex flex-col items-center">
                    <div class="rounded-full overflow-hidden w-fit mb-6">
                    <img src="{{ asset('storage/' . $rektor->image) }}" alt="{{ $rektor->nama }}" />
                    </div>
                    <p
                    class="mb-[2px] text-sm sm:text-base text-xneutral-400 font-semibold font-montserrat"
                    >
                    {{ $rektor->nama }}
                    </p>
                    <p
                    class="mb-[2px] text-xs sm:text-sm text-xneutral-200 font-semibold font-montserrat"
                    >
                    {{ $rektor->jabatan }}
                    </p>
                </div>
            @endforeach
        @endif
      </div>
    </section>
    <!-- END OF RECTOR SECTION -->

    <!-- ANNOUNCEMENT SECTION -->
    <section class="mt-28 x-announcement w-full">
      <div class="container pt-9 pb-16">
        <div class="mb-10 flex justify-between items-center">
          <div>
            <h3
              class="font-semibold text-xl sm:text-2xl font-montserrat text-xneutral-0"
            >
              Pengumuman
            </h3>
            <p
              class="font-montserrat text-sm sm:text-base text-xneutral-0 font-semibold"
            >
              Dapatkan pengumuman terbaru
            </p>
          </div>
          <div>
            <button
              class="text-xneutral-400 hover:text-xneutral-0 bg-xneutral-0 transition-all hover:bg-xneutral-400 w-10 h-10 rounded-full"
            >
              <i class="bi bi-arrow-left-short text-4xl"></i>
            </button>
            <button
              class="text-xneutral-400 hover:text-xneutral-0 bg-xneutral-0 transition-all hover:bg-xneutral-400 w-10 h-10 rounded-full"
            >
              <i class="bi bi-arrow-right-short text-4xl"></i>
            </button>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          @forelse($announcements as $announcement)
          <div
            class="py-[26px] px-7 rounded-[20px] border border-xneutral-100 bg-white"
          >
            <a
              href="{{ route('pengumuman.show', $announcement->slug) }}"
              class="text-base sm:text-lg font-montserrat font-semibold text-xneutral-400 line-clamp-2 mb-4"
            >
                {{ $announcement->title }}
            </a>
            <p
              class="font-montserrat text-xs sm:text-sm font-semibold text-xneutral-200 mb-1.5"
            >
                {{ Str::limit(strip_tags(html_entity_decode($announcement->content)), 100, '...') }}
            </p>
            <p class="font-montserrat text-xs font-semibold text-xneutral-200">
                {{ \Carbon\Carbon::parse($announcement->created_at)->format('d/m/y') }}
            </p>
          </div>
          @empty
            <div class="py-[26px] px-7 rounded-[20px] border border-xneutral-100 bg-white">
                <p class="font-montserrat text-base font-semibold text-xneutral-400">
                    No data available
                </p>
            </div>
        @endforelse
        </div>
      </div>
    </section>
    <!-- END OF ANNOUNCEMENT SECTION -->
@endsection
