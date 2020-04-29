<?php

namespace App\Http\Controllers\Pergunta;

use App\Http\Controllers\Controller;
use App\Models\Respostas\Respostas;
use App\Models\Om\Om;
use App\Models\Perguntas\Perguntas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request as FormRequest;
use Illuminate\Support\Facades\Auth;
use Request;

class PerguntaController extends Controller
{

    public function index()
    {

        return view('admin.cadastros.pergunta.pergunta');
    }

    public function cadastra(FormRequest $request)
    {

        //return response()->json($request->all());

        $pergunta = new Perguntas();
        $pergunta->descricao = $request['descricao'];
        $pergunta->user_id = auth()->user()->id;

        if ($request->hasFile('anexo') && $request->file('anexo')->isValid()) {

            $folderPath = 'arquivos/perguntas/anexo/';

            // Define um aleatório para o arquivo baseado no timestamps atual
            $name = uniqid(date('HisYmd'));

            // Recupera a extensão do arquivo
            $extension = $request->anexo->extension();

            // Define finalmente o nome
            $nameFile = "{$name}.{$extension}";

            // Faz o upload:
            $upload = $request->anexo->storeAs($folderPath, $nameFile);
            $pergunta->anexo = $folderPath . $nameFile;
        }

        $pergunta->save();


        if ($pergunta instanceof Model) {

            Request::session()->flash('sucesso', "Pergunta cadastrada com sucesso.");
            return back();

        } else {
            Request::session()->flash('erro', "Ocorreu um erro, tente novamente.");

            return back();
        }
    }



    public function edita(FormRequest $request,$id)
    {
        $pergunta = Perguntas::find($id);
        $pergunta->descricao = $request['descricao'];
        $pergunta->anexo = $request['anexo'];

        $pergunta->save();

        return response()->json($pergunta);
    }


    public function lista($id)
    {

        $levantamentos = Respostas::where('periodo_id',$id)->get();

        $periodos = Perguntas::find($id)->with(['levantamentos' => function($levantamentos){
            $levantamentos->with(['users' => function($users){
                $users->with('om');
            }]);
        }])->get();

        $retorno = [];
        foreach ($periodos as $periodo){
            if($periodo->id == $id){
                $retorno[] = $periodo;
            }
        }


        return response()->json($retorno);
    }


    public function getData()
    {
        $pergunta = Perguntas::all();

        return datatables()->of($pergunta)->addColumn('action', function ($query) {
            return '<div class="text-center"> 
                       
                        <a href="#" class="link-simples " id="edita_'.$query->id.'" onclick="editaPergunta('.$query->id.')"  data-descricao="' . $query->descricao . '" data-toggle="modal">
                            <i class="fa fa-edit separaicon " data-toggle="tooltip" data-placement="top" title="Editar Pergunta"></i>
                        </a>
             
                    </div>';
        })->make(true);
    }

}
