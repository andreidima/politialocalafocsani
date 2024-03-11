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

                    <div class="row mb-0 py-2 justify-content-center">
                        <div class="col-lg-12 py-2 justify-content-center">
                            Puteți verifica oricând accesul unui autovehicul vizitând pagina
                                <a class="navbar-brand me-5" href="{{ url('/plati/verificare') }}">
                                    Acces autovehicule de transport greu în Focșani</a>.
                            <br>
                            Linkul către această pagina se află postat și pe site-ul Poliției Focșani.
                            <br><br>
                            <a class="btn btn-lg btn-secondary text-white rounded-3" href="https://politialocalafocsani.ro/">Închide pagina si revino la site-ul principal</a>
                        </div>
                    </div>



                </div>
            </div>
        </div>
    </div>
</div>
@endsection
