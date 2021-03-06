<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ServiceRequest;
use App\Service;
use App\Patient;
use App\AskAQuestion;
use App\User;
use App\Admin;
use App\VideoCall;
use Auth;
use Carbon\Carbon;
use App\Jobs\SendEmail;

class ServiceRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // 
    }

    public function query($query){
        if($query == "paid"){
            $result = ServiceRequest::where('paymentStatus', 1)->get();
            return $result;
        }
        if($query == "unpaid"){
            $result = ServiceRequest::whereNull('paymentStatus')->get();
            return $result;
        }
        if($query == "AAQ" || $query == "VED" || $query == "VTD" || $query == "CLI"){
            $service = Service::where('srvcShortName', $query)->first(); 
            $result = ServiceRequest::where('service_id', $service->id)->get();
            return $result;
        }
    }

    public function response($id, Request $request){
        $aaq = AskAQuestion::find($id);
        $aaq->aaqDocResponse = $request['response'];
        $aaq->update();

        $srvcReq = ServiceRequest::find($aaq->service_req_id);
        $srvcReq->srResponseDateTime = Carbon::now();
        $srvcReq->srStatus = 'CLOSED';
        $srvcReq->update();

        // $patient = $srvcReq->patient();
        $patient = Patient::find($srvcReq->patient_id);

        $user = User::find($srvcReq->user_id);
        
        //service request responded
        SendEmail::dispatch($patient, $srvcReq, $aaq, $srvcReq->payment, $user, 2)->delay(now()->addMinutes(1));
        // for service request closed
        SendEmail::dispatch($srvcReq->patient, $srvcReq, null, null, $srvcReq->user, 5); 

        return redirect()->route('admin.dashboard')->with('success', 'Added Response to Service Request ID :'.$srvcReq->srId.'!');
    }





      // Store doctor internal notes
    public function internalNotes($id, Request $request){
        $internalNotes = VideoCall::where('id', $id)->first();
        if($request){
            $internalNotes->vcDocInternalNotesText = $request['vcDocInternalNotesText'];
            $internalNotes->update();

            return redirect()->back()->with('success', 'Internal Notes Saved');
            // return $internalNotes;
        }
    } 

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response 
     */
    public function show($id)
    {
        $srvcReq = ServiceRequest::find($id);
        if(!empty($srvcReq)){
            // $service = Service::find($srvcReq->service_id);
            $patient = Patient::find($srvcReq->patient_id);
            // return strpos($srvcReq->srId, "AAQ");
            // if(strpos($srvcReq->srId, "AAQ") == true){
            //     $asaq = AskAQuestion::find($srvcReq->servSpecificId);
            return view('admin.service-request-details')->with('srvcReq', $srvcReq)
                ->with('patient', $patient);
                // ->with('aaq', $asaq);
            // }
            // $asaq = AskAQuestion::find($srvcReq->)
        }
        else{
            return redirect('/admin/dashboard')->with('error', 'Service Request not found!');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function closeServiceRequest($id){
        // return $id;
        $servcReq = ServiceRequest::find($id);
        if(isset($servcReq)){
            if($servcReq->srStatus == "CLOSED")
                return redirect()->back()->with('error', 'Service Request '.$servcReq->srId.' has been closed already!');
            $servcReq->srStatus = "CLOSED";
            $servcReq->update();
            SendEmail::dispatch($servcReq->patient, $srvcReq, null, null, $servcReq->user, 5);
            return redirect()->back()->with('success', 'Service Request '.$servcReq->srId.' closed successfully.');
        }else{
            return redirect()->back()->with('error', 'Something went wrong! Please try agian later.');
        }
    }
}
