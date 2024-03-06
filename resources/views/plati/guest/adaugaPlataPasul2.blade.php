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
                            {{-- <h4 class="mb-0 px-4" style="color:#ffffff">
                                Accesul autovehiculelor de transport greu
                            </h4>
                            <br> --}}
                            <h4 class="mb-0 px-4 text-center" style="color:#ffffff">
                                Accesul autovehiculelor de transport greu în Focșani
                                <br><br>
                                Plată online
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

                    <div class="row" id="inregistrarePlataForm">
                        <div class="col-lg-12 mx-auto">
                            <form  class="mb-0 needs-validation" novalidate method="POST" action="/plati/adauga-plata-pasul-2">
                            @csrf

                                <div class="row">
                                    <div class="col-lg-12">
                                        Plată online a accesului autovehiculelor de transport greu așa cum este prevăzut în H.C.L. 445/2023 cu modificările și completările ulterioare (H.C.L. 26/2024).
                                        <br>
                                        <br>
                                        {{-- Art.3(3)-Taxele pentru eliberarea avizului de acces transport greu/autotizație unică de transport greu în funcție de grupa de clasificare a autovehicului de transport sunt următoarele: --}}
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-lg-10 rounded-3 mx-auto" style="border:1px solid #e9ecef; border-left:0.25rem #e66800 solid; background-color:#fff9f5">
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

                                        {{-- Art.3(3)-Taxele pentru eliberarea avizului de acces transport greu/autotizație unică de transport greu în funcție de grupa de clasificare a autovehicului de transport sunt următoarele: --}}
                                    </div>
                                </div>

                                <div class="row mb-0 py-2 justify-content-center">
                                    <div class="col-lg-12 py-2 d-flex justify-content-center">
                                        <a class="me-4 px-5 btn btn-lg btn-secondary text-white rounded-3" href="/plati/adauga-plata-pasul-1">Modifică</a>
                                        <button type="submit" class="px-5 btn btn-lg btn-primary text-white rounded-3">
                                            Plătește
                                        </button>
                                        {{-- <a class="btn btn-lg btn-secondary text-white rounded-3" href="https://politialocalafocsani.ro/">Renunță</a> --}}
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>



                </div>
            </div>
        </div>
    </div>
</div>
@endsection
