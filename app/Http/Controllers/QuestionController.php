<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Assertion;
use App\Models\Phase;
use App\Models\QuestionPhase;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\SimpleExcel\SimpleExcelReader;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $questions = Question::orderBy('id', 'desc')->paginate(25);
        return view('questions.index', compact('questions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $phase = Phase::orderBy('id', 'desc')->get();
        return view('questions.create', compact('phase'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuestionRequest $request)
    {
        // dd($request->all());
        $verifQuestion = Question::orderBy('id', 'desc')->where("question", $request->question)->count();
        if ($verifQuestion > 0) {
            return back()->with("echec", "Cette question existe déjà");
        } else {
            $question = Question::firstOrCreate([
                'question' => $request->question,
            ]);
            $dernier = Question::orderBy('id', 'desc')->take(1)->get();
            foreach ($dernier as $key => $value) {
                $question_id = $value->id;
            }
            $enregistrment = ['question_id' => $question_id, 'phase_id' => $request->phase_id, 'ponderation' => $request->ponderation];
            $bonneAssertion = 0;
            // $autreAssertion = [];
            // dd($request->all());
            foreach ($request->assertions as $key => $value) {
                // dd($key, $request->bonneReponse);
                if ($key == $request->bonneReponse) {
                    $bonneAssertion = $value['ponderation'] = 1;
                    // $nameAsse = $key;
                    // dd("ponderation bonne assertion ".$bonneAssertion, "clé ".$key,"value ".$value["name"]);
                }
                // else{
                //     array_push($autreAssertion,$bonneAssertion);
                // }
                $assertion = Assertion::firstOrCreate([
                    'question_id' => $enregistrment['question_id'],
                    'assertion' => $value["name"],
                    'ponderation' => $bonneAssertion,
                    'statut' => "ok"
                ]);
                $bonneAssertion = 0;
            }
            // dd($bonneAssertion, $nameAsse, "autres assertion",$autreAssertion);
            $verif = QuestionPhase::all()->where("phase_id", $request->phase_id); //on recupere tout dans question phase et on verifie si l'enregistrement existe deja
            $tabQuestion = array();
            foreach ($verif as $key => $value) {
                $verif2 = $value->question_id;
                array_push($tabQuestion, $verif2);
            }
            if (in_array($request->question_id, $tabQuestion)) {
                return back()->with("echec", "Question existe dja dans cette phase");
            } else {
                $questionPhase = QuestionPhase::firstOrCreate([
                    "phase_id" => $enregistrment['phase_id'],
                    "question_id" => $enregistrment['question_id'],
                    "ponderation" => $enregistrment['ponderation']
                ]);
                return back()->with("success", "Question enregistrée avec succes");
            }
        }
    }

    public function importQuestion(StoreQuestionRequest $request)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:csv,txt'
        ]);

        $csvFile = $request->file('fichier');
        $csvPath = $csvFile->getRealPath();
        $phaseId = (int) $request->phase;

        $data = [];

        if (($handle = fopen($csvPath, 'r')) !== false) {
            fgetcsv($handle, 1000, ';');

            while (($row = fgetcsv($handle, 1000, ';')) !== false) {
                
                if (count($row) < 4) {
                    continue; 
                }

                $questionText = $row[0]; 
                $ponderation = $row[1]; 
                $assertions = explode(',', $row[2]); 
                $reponse = $row[3]; 

                $question = Question::create([
                    'question' => $questionText,
                ]);

                $questionId = $question->id;

                $data[] = [
                    'question_id' => $questionId,
                    'phase_id' => $phaseId,
                    'ponderation' => $ponderation,
                ];

                foreach ($assertions as $assertion) {
                    Assertion::create([
                        'assertion' => trim($assertion),
                        'ponderation' => (trim($assertion) == $reponse) ? 1 : 0,
                        'question_id' => $questionId,
                        'statut' => 'ok',
                    ]);
                }
            }
            fclose($handle);
        }

        $status = QuestionPhase::insert($data);

        if ($status) {
            return back()->with('successCand', 'Insertion effectuée avec succès.');
        } else {
            return back()->with('errorCand', 'L\'insertion a échoué.');
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Question $question)
    {
        $phaseAssoc = Phase::latest()->where('id', $question->phase_id)->get();
        $questionShow = $question;
        return view('questions.show', compact('questionShow', 'phaseAssoc'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Question $question)
    {

        $phases = Phase::orderBy('id', 'desc')->get();
        $questionEdit = $question;
        $phaseAssoc = Phase::latest()->where('id', $question->phase_id)->get();
        return view('questions.edit', compact('questionEdit', 'phases', 'phaseAssoc'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuestionRequest $request, Question $question)
    {
        $question->update([
            'question' => $request->question,
            'ponderation' => $request->ponderation,
            'statut' => $request->statut
        ]);
        return redirect()->route('questions.index')->with('success', 'Question modifiée avec succes');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $questionPhase = QuestionPhase::where('question_id', $id)->first();//->delete();
        if($questionPhase){
            $dejautilise = $questionPhase->reponse;

            if($dejautilise->count() > 0){
                return back()->with('success', 'Une question déjà utilisée ne peut être éffacée');
            }else{
                try{
                $question = Question::where('id',$id)->delete();
                $assertion = Assertion::where('question_id',$id)->delete();
                $questionPhase->delete();
                return back()->with('success', 'Question supprimée avec succès');
                }catch(Exception $e){
                    return back()->with('success', 'Erreur de suppression de question');
                }
            }
            
            


        }else{
            return back()->with('success', 'Erreur de suppression de question');
        }
        
    }

    public function getQuestionAuto1(Request $request)
    {
        $data = Question::select("question")
            ->where("question", "LIKE", "%{$request->get('query')}%")
            ->get();
        return response()->json($data);
    }

    public function getQuestionAuto(Request $request)
    {
        $search = $request->search;

        if ($search == '') {
            $employees = Question::orderby('question', 'asc')->select('id', 'question')->limit(5)->get();
        } else {
            $employees = Question::orderby('question', 'asc')->select('id', 'question')->where('question', 'like', '%' . $search . '%')->limit(5)->get();
        }

        $response = array();
        foreach ($employees as $employee) {
            $response[] = array("value" => $employee->id, "label" => $employee->question);
        }

        return response()->json($response);
    }
}
