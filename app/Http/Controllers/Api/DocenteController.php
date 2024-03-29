<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DocenteViewController;
use App\Http\Requests\DocenteRequest;
use App\Models\Docente;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DocenteController extends Controller
{
    public function index()
    {
        try {
            $docentes = Docente::all();
            return response()->json($docentes, 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    public function show(Docente $docente)
    {
        try {
            return response()->json($docente, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Docente não encontrado'], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->authorize('admin-access');

            $rules = [
                'nome' => ['required', 'string'],
                'email' => ['nullable', 'email', 'unique:users,email'],
                'numero' => ['required', 'integer', 'min:1', 'unique:users,numero_funcionario'],
                'telemovel' => ['nullable', 'string'],
                'acn' => ['required', 'integer', 'exists:acns,id'],
            ];

            $messages = [
                'nome.required' => 'Preencha o nome do Docente!',
                'nome.string' => 'Nome do docente inválido!',
                'email.email' => 'Email do docente inválido!',
                'email.unique' => 'Email já está atribuído a outro utilizador!',
                'numero.required' => 'Preencha o número de funcionário!',
                'numero.integer' => 'Número de funcionário inválido!',
                'numero.min' => 'Número de funcionário tem de ser superior a 1!',
                'numero.unique' => 'Número de funcionário já está em uso!',
                'telemovel.string' => 'Número de telefone do Docente inválido!',
                'acn.required' => 'Selecione a Área Científica Nuclear do Docente!',
                'acn.integer' => 'Área Científica Nuclear do Docente inválida!',
                'acn.exists' => 'Área Científica Nuclear do Docente inválida!',
            ];

            $validatedData = Validator::make($request->all(), $rules, $messages)->validate();

            DB::beginTransaction();

            $docente = Docente::create([
                'acn_id' => $validatedData['acn'],
            ]);
            $docente->save();

            $user = User::create([
                'nome' => $validatedData['nome'],
                'email' => $validatedData['email'],
                'password' => bcrypt('password'),
                'admin' => false,
                'numero_funcionario' => $validatedData['numero'],
                'numero_telefone' => $validatedData['telemovel'],
            ]);
            $docente->user()->save($user);

            DB::commit();
            return redirect(route('admin.gerir.view'))->with('sucesso', 'Docente adicionado com sucesso!');
        } catch (AuthorizationException $e) {
            DB::rollBack();
            return redirect()->back()->with('alerta', 'Não tem as permissões necessárias para adicionar docentes!');
        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->with('alerta', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->authorize('admin-access');

            $docente = Docente::findOrFail($id);

            $rules = [
                'nome' => ['required', 'string'],
                'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($docente->user->id)],
                'numero' => ['required', 'integer', 'min:1', Rule::unique('users', 'numero_funcionario')->ignore($docente->user->id)],
                'telemovel' => ['nullable', 'string'],
                'acn' => ['required', 'integer', 'exists:acns,id'],
            ];

            $messages = [
                'nome.required' => 'Preencha o nome do docente!',
                'nome.string' => 'Nome do docente inválido!',
                'email.email' => 'Email do docente inválido!',
                'email.unique' => 'Email já está atribuído a outro utilizador!',
                'numero.required' => 'Preencha o número de funcionário!',
                'numero.integer' => 'Número de funcionário inválido!',
                'numero.min' => 'Número de funcionário tem de ser superior a 1!',
                'numero.unique' => 'Número de funcionário já está em uso!',
                'telemovel.string' => 'Número de telefone do Docente inválido!',
                'acn.required' => 'Selecione a Área Científica Nuclear do docente!',
                'acn.integer' => 'Área Científica Nuclear inválida!',
                'acn.exists' => 'Área Científica Nuclear inválida!',
            ];

            $validatedData = Validator::make($request->all(), $rules, $messages)->validate();

            $docente->update([
                'acn_id' => $validatedData['acn'],
            ]);

            $docente->user->update([
                'nome' => $validatedData['nome'],
                'email' => $validatedData['email'],
                'numero_funcionario' => $validatedData['numero'],
                'numero_telefone' => $validatedData['telemovel'],
            ]);

            DB::commit();
            return redirect()->back()->with('sucesso', 'Docente editado com sucesso!');
        } catch (AuthorizationException $e) {
            DB::rollBack();
            return redirect()->back()->with('alerta', 'Não tem permissões para editar docentes!');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return redirect()->back()->with('alerta', 'Erro ao submeter edições!');
        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->with('alerta', $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $this->authorize('admin-access');

            $docente = Docente::findOrFail($id);

            if ($docente->user->id == Auth::user()->id) {
                throw new Exception('Não é possível remover o docente associado a esta conta!');
            }

            DB::beginTransaction();

            $docente->user->delete();
            $docente->impedimentos()->delete();
            $docente->unidadesCurriculares()->detach();
            foreach ($docente->ucsResponsavel as $uc) {
                $uc->docente_responsavel_id = null;
                $uc->save();
            }
            $docente->delete();

            DB::commit();
            return redirect(route('admin.gerir.view'))->with('sucesso', 'Docente removido com sucesso!');
        } catch (AuthorizationException $e) {
            DB::rollBack();
            return redirect()->back()->with('alerta', 'Sem permissões para remover Docente!');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return redirect()->back()->with('alerta', 'Erro ao tentar remover docente!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('alerta', $e->getMessage());
        }
    }
}
