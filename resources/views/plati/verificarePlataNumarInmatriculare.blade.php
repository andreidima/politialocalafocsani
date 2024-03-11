@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row my-0 justify-content-center">
        <div class="col-md-9 p-0">
            <div class="shadow-lg bg-white" style="border-radius: 40px 40px 40px 40px;">
                <div class="p-2"
                    style="
                        border-radius: 40px 40px 0px 0px;
                        border:5px solid #B0413E;
                        color: #ffffff;
                        background-color:#B0413E;
                    "
                >
                    <div class="row">
                        <div class="col-lg-12">
                            <!-- <h4 class="mb-0 px-4" style="color:#ffffff">
                                Accesul autovehiculelor de transport greu
                            </h4>
                            <br> -->
                            <h4 class="mb-0 px-4 text-center" style="color:#ffffff">
                                Accesul autovehiculelor de transport greu în Focșani
                            </h4>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 text-start"
                    style="
                        color:rgb(0, 0, 0);
                        background-color:#ffffff;
                        border:5px solid #B0413E;
                        border-radius: 0px 0px 40px 40px
                    "
                >

                @include ('errors.errors')

                    <div class="row">
                        <div class="col-lg-12 mb-4 mx-auto">
                            <form class="needs-validation" novalidate method="GET" action="{{ url()->current() }}">
                                @csrf
                                <div class="row mb-1 custom-search-form justify-content-center">
                                    <div class="col-lg-3">
                                        <input type="text" class="form-control rounded-3" id="searchNumarInmatriculare" name="searchNumarInmatriculare" placeholder="Numă înmatriculare" value="{{ $searchNumarInmatriculare }}">
                                    </div>
                                </div>
                                <div class="row custom-search-form justify-content-center">
                                    <button class="btn btn-sm btn-primary text-white col-md-4 me-3 border border-dark rounded-3" type="submit">
                                        <i class="fas fa-search text-white me-1"></i>Caută
                                    </button>
                                    <a class="btn btn-sm btn-secondary text-white col-md-4 border border-dark rounded-3" href="{{ url()->current() }}" role="button">
                                        <i class="far fa-trash-alt text-white me-1"></i>Resetează căutarea
                                    </a>
                                </div>
                            </form>
                        </div>
                        <div class="col-lg-12 mx-auto">
                            @if ($plati)
                                @foreach ($plati as $plata)
                                    <div class="row mb-4">
                                        <div class="col-lg-10 py-2 rounded-3 mx-auto" style="border:1px solid #e9ecef; border-left:0.25rem #e66800 solid; background-color:#fff9f5">
                                            Categorie: <b>{{ $plata->tarif->categorie }}</b>
                                            <br>
                                            Durata: <b>{{ $plata->tarif->durata }}</b>
                                            <br>
                                            Valabilitate:
                                                <b>
                                                    {{ $plata->data_inceput ? \Carbon\Carbon::parse($plata->data_inceput)->isoFormat('DD.MM.YYYY') : '' }}
                                                    -
                                                    {{ $plata->data_sfarsit ? \Carbon\Carbon::parse($plata->data_sfarsit)->isoFormat('DD.MM.YYYY') : '' }}
                                                </b>
                                            <br>
                                            Număr înmatriculare: <b>{{ $plata->nr_inmatriculare }}</b>
                                            <br>
                                            Preț: <b>{{ $plata->pret }} lei</b>
                                            <br>
                                            Status plată:
                                            @switch($plata->order_status)
                                                @case(0)
                                                @case(6)
                                                    <span class="text-danger">{{ $plata->order_status_description }}</span>
                                                    @break
                                                @case(2)
                                                    <span class="text-success">{{ $plata->order_status_description }}</span>
                                                    @break
                                                @default
                                            @endswitch
                                            @if ($plata->action_code != '0')
                                                <span class="text-danger">
                                                    {{ $plata->action_code_description }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @elseif ($searchNumarInmatriculare)
                                Nu există înregistrări pentru numărul de înmatriculare <b>{{ $searchNumarInmatriculare }}</b>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-0 justify-content-center">
                        <div class="col-lg-12 text-center">
                            <a href="https://politialocalafocsani.ro/">Închide pagina si revino la site-ul principal</a>
                        </div>
                    </div>



                </div>
            </div>
        </div>
    </div>
</div>
@endsection
