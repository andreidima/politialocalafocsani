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

                    @isset ($plata)
                        <div class="row">
                            <div class="col-lg-12 mx-auto text-center">
                                @switch($plata->order_status)
                                    @case(0)
                                    @case(6)
                                        <h3 class="text-danger">{{ $plata->order_status_description }}</h3>
                                        @break
                                    @case(2)
                                        <h3 class="text-success">{{ $plata->order_status_description }}</h3>
                                        @break
                                    @default
                                @endswitch
                                @if ($plata->action_code != '0')
                                    <h3 class="text-danger">
                                        {{ $plata->action_code_description }}
                                    </h3>
                                @endif
                            </div>
                            <div class="col-lg-12 mx-auto">
                                <div class="row mb-4">
                                    <div class="col-lg-10 py-2 rounded-3 mx-auto" style="border:1px solid #e9ecef; border-left:0.25rem #e66800 solid; background-color:#fff9f5">
                                        Nume prenume: <b>{{ $plata->nume_prenume }}</b>
                                        <br>
                                        Telefon: <b>{{ $plata->telefon }}</b>
                                        <br>
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="row">
                            <div class="col-lg-12 mx-auto text-center">
                                <b>
                                    Nu am găsit în sistem această comandă.
                                    <br>
                                    Dacă plata ta a fost procesată, și banii ți-au fost luați din cont, te rugăm să ne comunici, pentru a corecta comanda.
                                    <br>
                                    Mulțumim!
                                </b>
                                <br><br>
                            </div>
                        </div>
                    @endisset

                    <div class="row mb-0 justify-content-center">
                        <div class="col-lg-12 text-center">
                            Puteți verifica oricând accesul unui autovehicul vizitând pagina
                                <a href="{{ url('/plati/verificare-plata-numar-inmatriculare') }}" target="_blank">
                                    Acces autovehicule de transport greu în Focșani</a>.
                            <br>
                            Linkul către pagina de verificare se află postat și pe site-ul principal al Poliției Focșani.
                            <br><br>
                            <a href="https://politialocalafocsani.ro/">Închide pagina si revino la site-ul principal</a>
                        </div>
                    </div>



                </div>
            </div>
        </div>
    </div>
</div>
@endsection
