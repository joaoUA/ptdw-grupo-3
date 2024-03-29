<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\Impedimento;
use App\Models\UnidadeCurricular;
use App\Models\Periodo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class RestricoesViewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function restricoes()
    {
        $user = Auth::user();

        if (!$user->docente) {
            return redirect()->route('inicio.view');
        }

        $hoje = Carbon::now();

        $periodo = Periodo::orderBy('ano', 'desc')
            ->orderBy('semestre', 'desc')
            ->get()
            ->filter(function ($p) use ($hoje) {
                return $hoje->lte($p->data_final);
            })
            ->first();

        $ucs = $periodo ?
            $user->docente->unidadesCurriculares()
            ->where('periodo_id', $periodo->id)
            ->get() :
            null;

        $impedimento = $periodo ?
            $user->docente->impedimentos()->where('periodo_id', $periodo->id)->first() :
            null;

        $historico_ucs = $periodo ?
            $user->docente->unidadesCurriculares()
            ->where('periodo_id', '!=', $periodo->id)
            ->get()
            ->sortByDesc(function ($item) {
                return $item->periodo->ano * 10 + $item->periodo->semestre;
            }) :
            $user->docente->unidadesCurriculares()->get();

        $historico_impedimentos = $periodo ? $user->docente->impedimentos()
            ->where('periodo_id', '!=', $periodo->id)
            ->get()
            ->sortByDesc(function ($item) {
                return $item->periodo->ano * 10 + $item->periodo->semestre;
            }) :
            $user->docente->impedimentos()->get();

        return view('restrições', [
            'page_title' => 'Restrições',
            'periodo' => $periodo,
            'ucs' => $ucs,
            'impedimento' => $impedimento,
            'historico_impedimentos' => $historico_impedimentos ?? null,
            'historico_ucs' => $historico_ucs,
            'user' => $user,
        ]);
    }

    public function restricoesUC(UnidadeCurricular $uc, $ano_inicial, $semestre)
    {
        if (Gate::denies('access-uc-restricoes', $uc)) {
            return redirect()->back();
        }

        $user = Auth::user();

        $periodo = Periodo::where('ano', $ano_inicial)
            ->where('semestre', $semestre)
            ->first();

        $hoje = Carbon::now();
        $data_inicial = Carbon::createFromFormat('Y-m-d', $periodo->data_inicial);
        $data_final = Carbon::createFromFormat('Y-m-d', $periodo->data_final);

        $aberto = ($hoje->lt($data_final) && $hoje->gt($data_inicial));

        return view('restrição', [
            'page_title' => 'Restrições de Sala de Aula',
            'uc' => $uc,
            'ano_inicial' => $ano_inicial,
            'semestre' => $semestre,
            'user' => $user,
            'aberto' => $aberto,
            'permissao_editar' => $uc->docenteResponsavel && $uc->docenteResponsavel->id === $user->docente->id,
        ]);
    }

    public function recolha()
    {
        $user = Auth::user();

        if (Gate::denies('admin-access')) {
            return redirect(route('inicio.view'));
        }

        $periodos = Periodo::orderBy('ano', 'desc')
            ->orderBy('semestre', 'desc')
            ->get();

        $docentes = Docente::all()->sortByDesc(function ($docente) {
            return $docente->user->numero_funcionario;
        });

        return view('processos', [
            'page_title' => 'Recolha de Restrições',
            'user' => $user,
            'docentes' => $docentes,
            'periodo' => $periodos[0],
            'periodosH' => $periodos->slice(1),
        ]);
    }
}
