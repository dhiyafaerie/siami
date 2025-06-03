@extends('layouts.app')
@section('content')

    <!-- MAIN SECTION -->
    <article class="container mt-28">
      <h1
        class="text-xneutral-400 font-montserrat text-xl sm:text-2xl font-semibold"
      >
        Data Dosen
      </h1>
      <p
        class="text-xneutral-200 font-montserrat text-sm sm:text-base font-semibold"
      >
        Dosen berkompeten dibidangnya
      </p>
        @if ($lectures->isEmpty())
            <div class="text-center text-xneutral-300 font-montserrat font-medium text-sm sm:text-base mt-10">
                No Data Available
            </div>
        @else
        <div class="mt-10 space-y-11">
            @foreach ($lectures as $lecture)
                <div class="flex flex-col items-center sm:flex-row gap-8 sm:gap-16">
                    <img
                        class="sm:col-span-2"
                        src="{{'storage/' .  $lecture->image }}"
                        alt="dosen"
                    />
                    <div class="sm:col-span-10 font-montserrat">
                        <h3 class="text-base sm:text-lg font-semibold text-primary-200">
                            {{ $lecture->nama }}
                        </h3>
                        <table
                        class="table-auto sm:table-fixed font-medium text-xs sm:text-sm text-xneutral-300 mt-5"
                        >
                            <tbody>
                                <tr>
                                <td class="w-24 sm:w-48 py-1">NIDN</td>
                                <td class="w-4 sm:w-12">:</td>
                                <td>{{ $lecture->nidn }}</td>
                                </tr>
                                <tr>
                                <td class="w-24 sm:w-48 py-1">Pendidikan</td>
                                <td class="w-4 sm:w-12">:</td>
                                <td>
                                {{ $lecture->pendidikan }}
                                </td>
                                </tr>
                                <tr>
                                <td class="w-24 sm:w-48 py-1">Jabatan</td>
                                <td class="w-4 sm:w-12">:</td>
                                <td>{{ $lecture->jabatan }}</td>
                                </tr>
                                <tr>
                                <td class="w-24 sm:w-48 py-1">Email</td>
                                <td class="w-4 sm:w-12">:</td>
                                <td>{{ $lecture->email }}</td>
                                </tr>
                                <tr>
                                <td class="w-24 sm:w-48 py-1">Topik</td>
                                <td class="w-4 sm:w-12">:</td>
                                <td>{{ $lecture->topik }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </article>

    <article class="container mt-[150px]">
      <h1
        class="text-xneutral-400 font-montserrat text-xl sm:text-2xl font-semibold"
      >
        Tenaga Kependidikan
      </h1>
      <p
        class="text-xneutral-200 font-montserrat text-sm sm:text-base font-semibold"
      >
        Tendik B-University
      </p>

        @if ($admins->isEmpty())
            <div class="text-center text-xneutral-300 font-montserrat font-medium text-sm sm:text-base mt-10">
                No Data Available
            </div>
        @else
            <div class="mt-10 space-y-11">
                @foreach ($admins as $admin)
                    <div class="flex flex-col items-center sm:flex-row gap-8 sm:gap-16">
                        <img
                            class="sm:col-span-2"
                            src="{{ 'storage/' . $admin->image }}"
                            alt="Marta Ulina"
                        />
                        <div class="sm:col-span-10 font-montserrat">
                            <h3 class="text-base sm:text-lg font-semibold text-primary-200">
                                {{ $admin->nama }}
                            </h3>
                            <table
                            class="table-fixed w-full sm:table-fixed font-medium text-xs sm:text-sm text-xneutral-300 mt-5"
                            >
                            <tbody>
                                <tr>
                                <td class="w-24 sm:w-48 py-1">NIP</td>
                                <td class="w-4 sm:w-12">:</td>
                                <td>{{ $admin->nip }}</td>
                                </tr>
                                <tr>
                                <td class="w-24 sm:w-48 py-1">Jabatan</td>
                                <td class="w-4 sm:w-12">:</td>
                                <td>{{ $admin->jabatan }}</td>
                                </tr>
                            </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </article>
@endsection

