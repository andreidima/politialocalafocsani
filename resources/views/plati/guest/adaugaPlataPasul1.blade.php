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

                    <div class="row" id="inregistrarePlataForm">
                        <div class="col-lg-12 mx-auto">
                            <form  class="mb-0 needs-validation" novalidate method="POST" action="/plati/adauga-plata-pasul-1">
                                @csrf

                                <div class="row">
                                    <div class="col-lg-12">
                                        Plată online a accesului autovehiculelor de transport greu așa cum este prevăzut în H.C.L. 445/2023 cu modificările și completările ulterioare (H.C.L. 26/2024).
                                        <br>
                                        <br>
                                        <!-- Art.3(3)-Taxele pentru eliberarea avizului de acces transport greu/autotizație unică de transport greu în funcție de grupa de clasificare a autovehicului de transport sunt următoarele: -->
                                    </div>
                                </div>

                                <!-- <div class="row">
                                    <div class="col-lg-12 mb-4">
                                        <div class="table-responsive rounded-3">
                                            <table class="mb-0 table table-bordered rounded-3">
                                                <tr>
                                                    <td></td>
                                                    <td>Taxă specială de acces <b>pentru o zi</b></td>
                                                    <td>Taxă specială de acces <b>pentru o lună</b></td>
                                                    <td>Taxă specială de acces <b>pentru un an</b></td>
                                                </tr>
                                                @foreach ($tarife as $tarif)
                                                    <tr>
                                                        <td>{!! $tarif->nume !!}</td>
                                                        <td class="text-primary" style="font-weight: bold">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" value="0" name="tarif" id="tarif_zi_{{ $tarif->id }}_{{ $loop->iteration }}"
                                                                    {{ old('tarif', $plata->tarif) == '0' ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="tarif_zi_{{ $tarif->id }}_{{ $loop->iteration }}">{{ $tarif->pret_1_zi }} lei</label>
                                                            </div>
                                                        </td>
                                                        <td class="text-primary" style="font-weight: bold">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" value="0" name="tarif" id="tarif_luna_{{ $tarif->id }}_{{ $loop->iteration }}"
                                                                    {{ old('tarif', $plata->tarif) == '0' ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="tarif_luna_{{ $tarif->id }}_{{ $loop->iteration }}">{{ $tarif->pret_1_luna }} lei</label>
                                                            </div>
                                                        </td>
                                                        <td class="text-primary" style="font-weight: bold">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" value="0" name="tarif" id="tarif_an_{{ $tarif->id }}_{{ $loop->iteration }}"
                                                                    {{ old('tarif', $plata->tarif) == '0' ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="tarif_an_{{ $tarif->id }}_{{ $loop->iteration }}">{{ $tarif->pret_1_an }} lei</label>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </div>
                                        <small class="ps-3">* Selectează din tabel taxa pe care dorești să o plătești</small>
                                    </div>
                                </div> -->

                                <!-- <div class="row mb-4 pt-2 rounded-3" style="border:1px solid #e9ecef; border-left:0.25rem darkcyan solid; background-color:rgb(241, 250, 250)"> -->
                                <div class="row mb-4 justify-content-center">
                                    <div class="col-lg-3 mb-4">
                                        <label for="nume_prenume" class="mb-0 ps-3">Nume prenume<span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('nume_prenume') ? 'is-invalid' : '' }}"
                                            name="nume_prenume"
                                            value="{{ old('nume_prenume', $plata->nume_prenume) }}">
                                    </div>
                                    <div class="col-lg-3 mb-4">
                                        <label for="telefon" class="mb-0 ps-3">Telefon<span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('telefon') ? 'is-invalid' : '' }}"
                                            name="telefon"
                                            value="{{ old('telefon', $plata->telefon) }}">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-lg-5 mb-4">
                                        <label for="tarif_id" class="mb-0 ps-3">Categorie/durata<span class="text-danger">*</span></label>
                                        <select name="tarif_id" class="form-select bg-white rounded-3 {{ $errors->has('tarif_id') ? 'is-invalid' : '' }}">
                                            <option selected></option>
                                            @foreach ($tarife as $tarif)
                                                <option value="{{ $tarif->id }}" {{ ($tarif->id === intval(old('tarif_id', $plata->tarif_id))) ? 'selected' : '' }}>{{ $tarif->nume }} - {{ $tarif->pret }} lei</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3 mb-4 text-center">
                                        <label for="data_inceput" class="mb-0 ps-0">Dată începere valabilitate<span class="text-danger">*</span></label>
                                        <vue-datepicker-next
                                            data-veche="{{ old('data_inceput', $plata->data_inceput) }}"
                                            nume-camp-db="data_inceput"
                                            tip="date"
                                            value-type="YYYY-MM-DD"
                                            format="DD.MM.YYYY"
                                            not-before-date="{{ \Carbon\Carbon::today() }}"
                                            :latime="{ width: '120px' }"
                                        ></vue-datepicker-next>
                                    </div>
                                    <div class="col-lg-3 mb-4">
                                        <label for="nr_inmatriculare" class="mb-0 ps-3">Număr înmatriculare<span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('nr_inmatriculare') ? 'is-invalid' : '' }}"
                                            name="nr_inmatriculare"
                                            value="{{ old('nr_inmatriculare', $plata->nr_inmatriculare) }}">
                                    </div>
                                </div>

                                <!-- <div class="row mb-4 pt-2 rounded-3" style="border:1px solid #e9ecef; border-left:0.25rem #e66800 solid; background-color:#fff9f5">
                                    <div class="col-lg-3 mb-0">
                                        <label for="email" class="mb-0 ps-3">Email</label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                            name="email"
                                            value="{{ old('email', $plata->email) }}">
                                    </div>
                                    <div class="col-lg-3 mb-0">
                                        <label for="telefon" class="mb-0 ps-3">Număr de telefon</label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('telefon') ? 'is-invalid' : '' }}"
                                            name="telefon"
                                            value="{{ old('telefon', $plata->telefon) }}">
                                    </div>
                                    <div class="col-lg-12 mb-4">
                                        * Factura o puteți descărca după ce faceți plata. Dacă vă adăugați adresa de email, vă vom trimite factura și prin email.
                                    </div>
                                </div> -->

                                <!-- <div class="row mb-4 pt-2 rounded-3" style="border:1px solid #e9ecef; border-left:0.25rem rgb(255, 225, 0) solid;">
                                    <div class="col-lg-12 mb-4">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input {{ $errors->has('gdpr') ? 'is-invalid' : '' }}" name="gdpr" id="gdpr" value="1" required
                                            {{ old('gdpr', ($plata->gdpr ?? "0")) === "1" ? 'checked' : '' }}>
                                            <label class="form-check-label" for="gdpr">
                                                <span class="text-danger">*</span> Sunt de acord cu prelucrarea datelor mele personale în conformitate cu Regulamentul (UE) 2016-679 - privind protecţia persoanelor fizice în ceea ce priveşte
                                                prelucrarea datelor cu caracter personal şi privind libera circulaţie a acestor date şi de abrogare a Directivei 95/46/CE ale cărei prevederi le-am citit şi le cunosc.
                                            </label>
                                        </div>
                                    </div>
                                </div> -->

                                <div class="row mb-0 py-2 justify-content-center">
                                    <div class="col-lg-12 py-2 d-flex justify-content-center">
                                        <button type="submit" class="px-5 btn btn-lg btn-primary text-white rounded-3">
                                            Continuă plata
                                        </button>
                                        <!-- <a class="btn btn-lg btn-secondary text-white rounded-3" href="https://politialocalafocsani.ro/">Renunță</a> -->
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
