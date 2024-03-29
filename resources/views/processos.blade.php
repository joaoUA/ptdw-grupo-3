@extends('layouts.baseContent')

@section('main')

<main class="w-100">
    @include('partials._breadcrumbs', [
    'crumbs' => [
    ['página inicial', route('inicio.view')],
    ['recolha de restrições', route('restricoes.recolha.view')]
    ]
    ])

    @include('partials._pageTitle', ['title' => $page_title])

    @include('partials._alerts')

    <section class="mt-3">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle='tab' data-bs-target='#manage-forms'>
                    Gerir Formulários
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle='tab' data-bs-target='#history'>
                    Histórico
                </button>
            </li>
        </ul>
    </section>

    <div class="tab-content">
        <section id="manage-forms" class="tab-pane active p-3">
            @php
                $docSemForm = $docentes->filter( function ($docente) use ($periodo) {
                    return $docente->impedimentos()->where('periodo_id', $periodo->id)->doesntExist() && 
                    $docente->unidadesCurriculares()->where('periodo_id', $periodo->id)->exists();
                });
            @endphp

            <div class="d-flex justify-content-between mb-2">
                @if ($periodo->impedimentos->count() > 0)
                <div>
                    <h2>Formulário aberto: {{substr($periodo->ano, 2, 2)}}/{{substr($periodo->ano+1, 2, 2)}} {{$periodo->semestre}}º Semestre</h2>
                    <p>Data Limite: {{$periodo->data_final}}</p>
                </div>
                @else
                <div>
                    <p>Nenhum formulário ativo de momento.</p>
                </div>
                @endif

                <div class="d-flex gap-2">
                    @if ($docSemForm->count() > 0)
                    <button class="btn" data-bs-toggle="modal" data-bs-target="#modal-new-process">
                        Gerar Formulários {{$docSemForm->count()}}
                    </button>
                    @endif

                    @if ($periodo->impedimentos->count() > 0)
                        <form class="d-flex" action="{{route('mailMissingForms')}}" method="post" id="send-emails-form">
                            @csrf
                            <button type="submit" class="btn" id="send-emails-btn" disabled>
                                <i class="fa fa-envelope-o"></i>
                            </button>
                        </form>
                        <a href="{{route('download', ['periodo' => $periodo->id])}}" class="btn d-flex justify-content-center align-items-center" download="output_restricoes_{{$periodo->ano}}_{{$periodo->semestre}}.xlsx">
                            <i class="fa-solid fa-download"></i>
                        </a>
                    @endif
                </div>
            </div>
            @if ($periodo->impedimentos->count() > 0)
                <table class="table-ua w-100" id="table">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col"></th>
                            <th scope="col-1"></th>
                            <th scope="col-1">Nome</th>
                            <th scope="col-1" class="text-center">
                                Restrições de UCs 
                                ({{$periodo->unidadesCurriculares()->where('restricoes_submetidas',true)->count()}}/{{$periodo->unidadesCurriculares()->count()}})
                            </th>
                            <th scope="col-1" class="text-center">
                                Impedimento de Horário 
                                ({{$periodo->impedimentos()->where('submetido', true)->count()}}/{{$periodo->impedimentos->count()}})
                            </th>
                            <th scope="col-1" class="text-center">
                                Emails
                                <input type="checkbox">
                            </th>
                        </tr>
                    </thead>
                    <tbody class="title-separator">
                        @php
                            $impedimentosOrd = $periodo->impedimentos->sortByDesc(function ($imp){
                                return $imp->docente->user->numero_funcionario;
                            });
                        @endphp
    
                        @foreach ($impedimentosOrd as $impedimento)
                        @php
                            $ucsResponsavel = $impedimento->docente->ucsResponsavel()->where('periodo_id', $periodo->id)->get();
                        @endphp
                        <tr class="border border-light pe-none">
                            <th scope="row"></th>
                            <td colspan="1">{{$impedimento->docente->user->numero_funcionario}}</td>
                            <td colspan="1">{{$impedimento->docente->user->nome}}</td>
                            <td colspan="1" class="text-center">
                                @if ($ucsResponsavel->count() > 0)
                                    {{$ucsResponsavel->where('restricoes_submetidas', true)->count()}}/{{$ucsResponsavel->count()}}
                                @else
                                    ---
                                @endif
                            </td>
                            <td colspan="1" class="text-center">
                                @if ($impedimento->submetido)
                                    <i class="fa-solid fa-check"></i>
                                @else
                                    Pendente
                                @endif
                            </td>
                            <td class="text-center">
                                @if (!$impedimento->submetido || $ucsResponsavel->where('restricoes_submetidas', true)->count() < $ucsResponsavel->count())
                                    <input class="pe-auto" type="checkbox" data-id="{{$impedimento->id}}">
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <section id="history" class="tab-pane p-3">
            <div class="d-flex gap-2 align-items-center">
                <h3>Histórico</h3>
            </div>

            <table class="table-ua w-100" id="history-table">
                <thead class="bg-light">
                    <tr>
                        <th scope="col"></th>
                        <th scope="col-1"></th>
                        <th scope="col-4">Nome</th>
                        <th scope="col-1" class="text-center">Restrições UC</th>
                        <th scope="col-1" class="text-center">Impedimentos horários</th>
                        <th scope="col-1"></th>
                    </tr>
                </thead>
                <tbody class="title-separator">
                    @if ($periodosH->count() > 0)
                        @foreach ($periodosH as $index => $periodoH)
                        <tr data-bs-toggle="collapse" data-bs-target="{{'#rh'.$index}}">
                            <th scope="row"></th>
                            <td><i class="fa-solid fa-chevron-right"></i></td>
                            <td>Formulários {{substr($periodoH->ano, 2, 2)}}/{{substr($periodoH->ano+1, 2, 2)}} - {{$periodoH->semestre}}º Semestre</td>
                            <td class="text-center">
                                Submetidos: {{$periodoH->unidadesCurriculares()->where('restricoes_submetidas', true)->count()}}/{{$periodoH->unidadesCurriculares()->count()}}
                            </td>
                            <td class="text-center">
                                Submetidos: {{$periodoH->impedimentos->where('submetido', true)->count()}}/{{$periodoH->impedimentos->count()}}
                            </td>
                            <td>
                                <a href="{{route('download', ['periodo' => $periodoH->id])}}" download="output_restricoes_{{$periodoH->ano}}_{{$periodoH->semestre}}.xlsx">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                            </td>
                        </tr>
                            @foreach ($docentes as $docente)
                            @if ($docente->impedimentos()->where('periodo_id', $periodoH->id)->exists())
                            <tr class="collapse accordion-collapse bg-terciary pe-none" id="{{'rh'.$index}}">
                                <th scope="row"></th>
                                <td colspan="1">{{$docente->user->numero_funcionario}}</td>
                                <td colspan="1">{{$docente->user->nome}}</td>
                                <td colspan="1" class="text-center">
                                    @if ($docente->ucsResponsavel)
                                    {{$docente->ucsResponsavel()->where('periodo_id', $periodoH->id)->where('restricoes_submetidas', true)->count()}}/{{$docente->ucsResponsavel()->where('periodo_id', $periodoH->id)->count()}}
                                    @else ---
                                    @endif
                                </td>
                                <td colspan="1" class="text-center">
                                    @if ($docente->impedimentos()->where('periodo_id', $periodoH->id)->first()->submetido)
                                    <i class="fa fa-check"></i>
                                    @else
                                    Não submetido
                                    @endif
                                </td>
                                <td></td>
                            </tr>
                            @endif
                            @endforeach
                        @endforeach
                    @else
                    <tr class="border border-light pe-none">
                        <th scope="row"></th>
                        <td colspan="5">Sem correspondências</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </section>
    </div>

    <div class="modal fade" id="modal-new-process" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered ">
            <div class="modal-content rounded-0">
                <div class="modal-body">
                    <form id="gerar-formularios-form" class="d-flex flex-column gap-3 px-5 py-4"
                        action="{{route('impedimentos.periodo')}}" method="post">
                        @csrf
                        <div class="d-flex gap-3 justify-content-between align-items-center">
                            <label class="w-50" for="data-inicial-impedimentos-input">Abre a :</label>
                            <input class="w-50" type="date" name="data_inicial" id="data-inicial-impedimentos-input"
                                value="{{$periodo->data_inicial}}">
                        </div>
                        <div class="d-flex gap-3 justify-content-between align-items-center">
                            <label class="w-50" for="data-limite-impedimentos-input">Fecha a:</label>
                            <input class="w-50" type="date" name="data_final" id="data-limite-impedimentos-input"
                                value="{{$periodo->data_final}}">
                        </div>
                        <div class="d-flex gap-3 justify-content-center" id="form-btns">
                            <input class="btn" type="submit" value="Submeter">
                            <input class="btn cancelar" data-bs-dismiss="modal" type="button" value="Cancelar">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</main>
<script src="{{asset('js/processos.js')}}" defer></script>

@endsection