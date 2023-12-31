@extends('layouts.baseContent')

@section('main')

<main class="w-100 px-5">
    @include('partials._breadcrumbs', [
        'crumbs' => [
            ['página inicial', route('inicio.view')],
            ['gerir dados', route('admin.gerir.view')],
            ['adicionar uc', route('ucs.adicionar.view')]
        ]
    ])

    @include('partials._pageTitle', ['title' => 'Adicionar Unidade Curricular'])

    <section class="mt-3">
        <form id="add-uc-form" action="{{route('ucs.store')}}" method="post" class="title-separator pt-3">
            @csrf
            <div class="d-flex align-items-center border border-dark p-2 mb-2">
                <label class="col-md-2 p-3" for="uc-codigo">Código</label>
                <input class="col-md-2 p-1" type="number" name="codigo" id="uc-codigo-input" required min="0">
            </div>

            <div class="d-flex align-items-center border border-dark p-2 mb-2">
                <label class="col-md-2 p-3" for="uc-nome">Nome</label>
                <input class="col-md-6 p-1" type="text" name="nome" id="uc-nome-input" required>
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
            
            <div class="d-flex align-items-center border border-dark p-2 mb-2">
                <label class="col-md-2 p-3" for="uc-ects" class="col-md-2 p-3">ECTS</label>
                <input class="col-md-1 p-1" type="number" name="ects" id="uc-ects-input" required min="0">
            </div>

            <div class="d-flex align-items-center border border-dark p-2 mb-2">
                <label class="col-md-2 p-3" for="uc-horas">Horas Semanais</label>
                <input class="col-md-1 p-1" type="number" name="horas" id="uc-horas-input" required min="0">
            </div>

            <div class="d-flex align-items-center border border-dark p-2 mb-2">
                <label class="col-md-2 p-3" for="uc-main-teacher">Docente Responsável</label>
                <select class="col-md-2 p-1" name="docente_responsavel_id" id="uc-main-teacher-select" required>
                    <option value="" selected>Selecione</option>
                    @foreach ($docentes as $docente)
                        <option value="{{$docente->id}}">{{$docente->numero_funcionario . ' - ' . $docente->user->nome}}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="d-flex align-items-center border border-dark p-2 mb-2">
                <label class="col-md-2 p-3" for="uc-teachers">Restantes Docentes</label>
                <div class="d-flex flex-column gap-2 col-md-4 py-1">
                    <select class="p-1" name="docentes_id[]" id="uc-teachers-select-1">
                        <option value="" selected>Selecione</option>
                        @foreach ($docentes as $docente)
                        <option value="{{$docente->id}}">{{$docente->numero_funcionario . ' - ' . $docente->user->nome}}</option>
                        @endforeach
                    </select>
                    <select class="p-1" name="docentes_id[]" id="uc-teachers-select-2">
                        <option value="" selected>Selecione</option>
                        @foreach ($docentes as $docente)
                        <option value="{{$docente->id}}">{{$docente->numero_funcionario . ' - ' . $docente->user->nome}}</option>
                        @endforeach
                    </select>
                    <select class="p-1" name="docentes_id[]" id="uc-teachers-select-3">
                        <option value="" selected>Selecione</option>
                        @foreach ($docentes as $docente)
                        <option value="{{$docente->id}}">{{$docente->numero_funcionario . ' - ' . $docente->user->nome}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex gap-3 mt-3 mb-5" id="form-btns">
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
<script src="{{asset('js/addUC.js')}}" defer></script>
@endsection