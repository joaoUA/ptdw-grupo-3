@extends('layouts.baseContent')

@section('main')

<main class="w-100">
    @include('partials._breadcrumbs', [
        'crumbs' => [
            ['página inicial', route('inicio.view')],
            ['gerir dados', route('admin.gerir.view')],
            [$docente->user->nome, route('docentes.editar.view', ['docente' => $docente->id])]
        ]
    ])

    @include('partials._pageTitle', ['title' => $docente->user->numero_funcionario . ' - ' . $docente->user->nome])

    <section class="mt-3 title-separator pt-2">
        @include('partials._alerts')
        
        <form id="edit-docente-form" action="{{route('docentes.update', ['id' => $docente->id])}}" method="POST">
            @csrf
            @method('PUT')
            <div class="d-flex align-items-center p-2">
                <label for="docente-numero" class="col-md-2">Número</label>
                <input type="number" name="numero" id="docente-numero-input" class="col-md-1 px-1" value="{{ $docente->user->numero_funcionario}}" required>
            </div>

            <hr class="m-0 bg-secondary">
            
            <div class="d-flex align-items-center p-2">
                <label for="docente-nome" class="col-md-2 ">Nome</label>
                <input type="text" name="nome" id="docente-nome-input" class="col-md-4 px-1" value="{{$docente->user->nome}}" required>
            </div>

            <hr class="m-0 bg-secondary">
            
            <div class="d-flex align-items-center p-2">
                <label for="docente-email" class="col-md-2 ">Email</label>
                <input type="email" name="email" id="docente-email-input" class="col-md-4 px-1" value="{{$docente->user->email}}">
            </div>

            <hr class="m-0 bg-secondary">

            <div class="d-flex align-items-center p-2">
                <label for="docente-telefone" class="col-md-2 ">Telefone</label>
                <input type="tel" name="telemovel" id="docente-telefone" class="col-md-2 px-1" value="{{$docente->user->numero_telefone}}">
            </div>

            <hr class="m-0 bg-secondary">

            <div class="d-flex align-items-center p-2">
                <label for="docente-acn" class="col-md-2">Área Científica</label>
                <select class="col-md-1 p-1" name="acn" id="uc-acn-select" required>
                    @foreach ($acns as $acn)
                        <option value="{{$acn->id}}" @selected($docente->acn->id == $acn->id)>{{$acn->nome}}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="d-flex gap-3 mt-3 mb-5">
                <input class="btn" type="submit" value="Submeter">
                <input type="button" class="btn remover" id="btn-delete" value="Remover">
                <a class="btn cancelar" href="{{route('admin.gerir.view')}}" value="Cancelar">Cancelar</a>
            </div>
        </form>

        <form id="delete-docente-form" action="{{route('docentes.delete', ['id' => $docente->id])}}" method="POST">
            @csrf
            @method('DELETE')
        </form>
    </section>
</main>

<script src="{{asset('js/editDocente.js')}}" defer></script>
@endsection