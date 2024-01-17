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
use App\Mail\TestMail;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
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

    public function store(DocenteRequest $docenteRequest)
    {
        if (!$docenteRequest->authorize()) {
            return response()->json(['message' => 'nao autorizado'], 403);
        }

        $nome = $docenteRequest->input('nome');
        $numero = $docenteRequest->input('numero');
        $email = $docenteRequest->input('email');
        $telemovel = $docenteRequest->input('telemovel');
        $acn = $docenteRequest->input('acn');

        try {
            DB::beginTransaction();


            $docente = Docente::create([
                'numero_funcionario' => $numero,
                'numero_telefone' => $telemovel,
                'acn_id' => $acn
            ]);
            $docente->save();

            $user = User::create([
                'nome' => $nome,
                'email' => $email,
                'password' => bcrypt('password'),
                'admin' => false,
            ]);
            $docente->user()->save($user);
            DB::commit();
            Mail::to('miguelmvieira@ua.pt')->send(new TestMail());
            return response()->json([
                'message' => 'sucesso!',
                'redirect' => route('admin.gerir.view'),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->authorize('admin-access');

            $docente = Docente::findOrFail($id);

            $rules = [
                'nome' => ['required', 'string'],
                'acn' => ['required', 'integer', 'exists:acns,id'],
                'email' => ['required', 'email'],
                //todo @joao: o telefone vale a pena ser obrigatório?
                'telefone' => ['required'],
                'numero_funcionario' => ['required', 'integer', 'min:1'],
            ];

            $messages = [
                'nome.required' => 'Preencha o nome do docente!',
                'nome.string' => 'Nome do docente inválido!',
                'acn.required' => 'Selecione a Área Científica Nuclear do docente!',
                'acn.integer' => 'Área Científica Nuclear inválida!',
                'acn.exists' => 'Área Científica Nuclear inválida!',
                'email.required' => 'Preencha o email do docente!',
                'email.email' => 'Email inválido',
                'telefone.required' => 'Preencha o número de telefone do docente!',
                'numero_funcionario.required' => 'Preencha o número de funcionário do docente!',
                'numero_funcionario.integer' => 'Número de funcionário tem de ser número inteiro!',
                'numero_funcionario.min' => 'Número de funcionário tem de ser superior a 1!',
            ];

            $validatedData = Validator::make($request->all(), $rules, $messages)->validate();

            $docente->update([
                'numero_funcionario' => $validatedData['numero_funcionario'],
                'numero_telefone' => $validatedData['telefone'],
                'acn_id' => $validatedData['acn'],
            ]);

            $docente->user->update([
                'nome' => $validatedData['nome'],
                'email' => $validatedData['email']
            ]);

            DB::commit();
            return redirect()->back()->with('sucesso', 'Docente editado com sucesso!');
        } catch (AuthorizationException $e) {
            DB::rollBack();
            return redirect()->back()->with('alerta', 'Sem permissões para editar Docente!');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return redirect()->back()->with('alerta', 'Erro ao submeter edições!');
        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->with('alerta', $e->getMessage());
        }
    }

    public function delete(Docente $docente, $id)
    {
        //if (!$docenteRequest->authorize()) {
        //  return response()->json(['message' => 'nao autorizado'], 403);
        //}

        try {
            DB::beginTransaction();

            $docente = Docente::findOrFail($id);

            if ($docente->user == Auth::user()) {
                throw new Exception('A tentar eliminar docente associado ao utilizador atual');
            }

            $docente->user->delete();
            $docente->impedimentos()->delete();
            $docente->unidadesCurriculares()->detach();
            foreach ($docente->ucsResponsavel as $uc) {
                $uc->docente_responsavel_id = null;
                $uc->save();
            }


            $docente->delete();

            DB::commit();

            return response()->json([
                'message' => 'Docente removido com sucesso!',
                'redirect' => route('admin.gerir.view'),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()]);
        }
    }
}
