@extends('layouts.baseContent')

@section('main')

<main class="w-100 px-5">
    @include('partials._breadcrumbs', [
        'crumbs' => [
            ['página inicial', route('inicio.view')],
            ['gerir dados', route('admin.gerir.view')],
            ['adicionar docente', route('docente.adicionar.view')]
        ]
    ])

    @include('partials._pageTitle', ['title' => 'Adicionar Docente'])


    <section class="mt-3">
        <form id="add-docente-form" action="{{route('docentes.store')}}" method="POST" class="title-separator pt-3"> <!--FLAG-->
            @csrf
            <div class="d-flex align-items-center border border-dark p-2 mb-2">
                <label for="docente-numero" class="col-md-2 p-3">Número</label>
                <input type="number" name="docente-numero" id="docente-numero-input" class="col-md-2 p-1" 
                    value="" required min="0">
            </div>
            <div class="d-flex align-items-center border border-dark p-2 mb-2">
                <label for="docente-nome" class="col-md-2 p-3">Nome</label>
                <input type="text" name="docente-nome" id="docente-nome-input" class="col-md-4 p-1"
                    value="" required>
            </div>
            <div class="d-flex align-items-center border border-dark p-2 mb-2">
                <label for="docente-email" class="col-md-2 p-3">Email</label>
                <input type="email" name="docente-email" id="docente-email-input" class="col-md-4 p-1"
                    value="" required>
            </div>
            <div class="d-flex align-items-center border border-dark p-2 mb-2">
                <label for="docente-telemovel" class="col-md-2 p-3">Telemóvel</label>
                <input type="tel" name="docente-telemovel" id="docente-telemovel" class="col-md-4 p-1"
                    value="" required>
            </div>
            <div class="d-flex align-items-center border border-dark p-2 mb-2">
                <label class="col-md-2 p-3" for="uc-acn">Área Científica</label>
                <select class="col-md-2 p-1" name="acn" id="ucn-acn-select" required>
                    <option value="" selected>Selecione</option>
                    @foreach ($acns as $acn)
                        <option value="{{$acn->id}}">{{$acn->sigla}}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex align-items-center gap-2">
                <input class="btn" type="submit" value="Submeter">
                <a class="btn" href="{{route('admin.gerir.view')}}" value="Cancelar">Cancelar</a>
            </div>
        </form>
    </section>

</main>

@auth
    <script>
        const authUser = @json(auth()->user());
    </script>
@endauth
<script src="{{asset('js/addDocente.js')}}" defer></script>
@endsection