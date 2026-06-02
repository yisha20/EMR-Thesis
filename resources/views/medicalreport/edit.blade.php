@extends('layouts.app')

@section('content')
<div class="border border-info">
    <div class="card-header border-info">
      <ul class="nav nav-tabs card-header-tabs mr-auto">
        <li class="nav-item">
          <a class="nav-link" href="{{ route('medical-records.show', $patient->id) }}">Medical Record</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('medical-records.show', $patient->id) }}"> View / Edit Consultation
            <button type="button" class="close">&times; </button> </a>
          </li>
        <li class="nav nav-item-right ml-auto">
            <a href="{{route('patients.show', $patient->id)}}"><button type="button" class="close" >&times; </button> </a> {{--redirect to patient record ID--}}
        </li>
      </ul>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-block">
            <button type="button" class="close" data-dismiss="alert">×</button>	
            <strong>{{ $message }}</strong>
        </div>
    @endif

    @if ($errors->any())
        <div class="container mt-5">
            <div class="alert alert-danger alert-block">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    
    <div class="card-body">
        <form action="{{ route('medical-records.update', $medicalRecord->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div id="printThis">
            <div class="row">
                <div class="col-auto">
                </div>
                <div class="col table-bordered mt-4">
                        <div class="row">
                            <div class="col">
                            <p><b><h5>Medical Record</b></h5></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label for="validation2"><b>OPD / Id Number :</b> {{$patient->id_number}}</label>
                                <br>
                                <label for="validation2"><b>Date / Time:</b> {{ $medicalRecord->getDateTimeConsultation() }} </label>
                                <input type="hidden" name="date_of_consultation" value="{{ $medicalRecord->date_of_consultation }}">
                                <input type="hidden" name="time_of_consultation" value="{{ $medicalRecord->time_of_consultation }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <br>
                                <label for="validation2"><b>Attached File :</b></label>
                                @if ($medicalRecord->file)
                                <a href="{{ $medicalRecord->file }}" target="_blank" class="btn">File <i class="fa fa-paperclip"></i></a>
                                @else
                                No file
                                @endif
                                <br>
                                
                            </div>
                        </div>
                        <hr>
                        
                        <div class="form-group row">
                            <label for="chief_complaint" class="col-sm-2 col-form-label"><b>Chief Complaint:</b></label>
                            <div class="col-sm-10">
                                <textarea class="form-control" id="chief_complaint" rows="2" name="chief_complaint" value="">{{ $medicalRecord->chief_complaint }}</textarea>
                        
                            </div>
                         </div>
                         <div class="form-group row">
                            <label for="services" class="col-sm-2 col-form-label"><b>Service:</b></label>
                            <div class="col-sm-10">
                                <select class="form-control" id="services" name="performed_service" required>
                                    <option class="hidden"  selected value="{{ $medicalRecord->performed_service }}">{{ $medicalRecord->performed_service }}</option>
                                    @foreach(\App\Service::get() as $service)
                                        <option value="{{ $service->name }}">{{ $service->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label"><b>Vital Signs :</b></label>
                            <div class="col-sm-2">
                                <div class="form-inline ">
                                    <label for="temp" class="col-form-label">T :</label>
                                    <input type="number" class="form-control-sm col-sm-5" name="vital_signs[temperature]" id="temp" value="{{ $medicalRecord->vital_signs['temperature'] }}">
                                    <label class="col-form-label"><i><small> °C</small></i></label>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-inline ">
                                    <label for="pulse_rate" class="col-form-label">PR:</label>
                                    <input type="number" class="form-control-sm col-sm-5" name="vital_signs[pulse_rate]" id="pulse_rate" value="{{ $medicalRecord->vital_signs['pulse_rate'] }}">
                                    <label class="col-form-label"><i><small> bpm</small></i></label>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-inline ">
                                            
                                    <label for="res_rate" class="col-form-label">RR :</label>
                                    <input type="number" class="form-control-sm col-sm-5" name="vital_signs[respiratory_rate]" id="res_rate" value="{{ $medicalRecord->vital_signs['respiratory_rate'] }}">
                                    <label class="col-form-label"><i><small> bpm</small></i></label>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-inline ">
                                    <label for="b_p" class="col-form-label">BP:</label>
                                    <input type="number" class="form-control-sm col-sm-5" name="vital_signs[blood_pressure]" id="b_p" value="{{ $medicalRecord->vital_signs['blood_pressure'] }}">
                                    <label class="col-form-label"><i><small>mmhg</small></i></label>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-inline ">
                                    <label for="weight" class="col-form-label">WT :</label>
                                    <input type="number" class="form-control-sm col-sm-5" name="vital_signs[weight]" id="weight" value="{{ $medicalRecord->vital_signs['weight'] }}">
                                    <label class="col-form-label"><i><small> kg</small></i></label>
                                </div>
                            </div>
                        </div>
                        {{--
                        <div class="form-group row ">
                            <label for="by_nurse_assigned" class="col-sm-2 col-form-label mt-4"><b>By : </b></label>
                            <div class="col-sm-10 ">
                                <div class="form-inline ">
                                    <input type="text" class="form-control col-sm-8 mt-4" name="nurse_assigned" id="by_nurse_assigned" value="{{ $medicalRecord->nurse_assigned }}">
                                    <label class="col-form-label mt-4"><i>,RN</i></label>
                                </div>
                            </div>
                        </div>
                        --}}

                        <div class="form-group row">
                            <label for="history_p_i" class="col-sm-2 col-form-label"><b>History of Present Illness:</b></label>
                        <div class="col-sm-10">
                            <textarea class="form-control" id="history_p_i" rows="3" name="history_of_present_illness" >{{ $medicalRecord->history_of_present_illness }}</textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="medication_taken" class="col-sm-2 col-form-label"><b>Medication Taken:</b></label>
                        <div class="col-sm-10">
                            <textarea class="form-control" id="medication_taken" rows="2" name="medication_taken" >{{ $medicalRecord->medication_taken }}</textarea>

                         </div>
                    </div>
                    <div class="form-group row">
                        <label for="findings" class="col-sm-2 col-form-label"><b>Findings:</b></label>
                        <div class="col-sm-10">
                            <textarea class="form-control" id="findings" rows="2" name="findings" value="">{{ $medicalRecord->findings }}</textarea>
                            
                         </div>
                    </div>
                    <div class="form-group row">
                        <label for="recommendation" class="col-sm-2 col-form-label"><b>Recommendations:</b></label>
                        <div class="col-sm-10">
                            <textarea class="form-control" id="recommendation" rows="3" name="recommendation" value="">{{ $medicalRecord->recommendation }}</textarea>
                            
                         </div>
                    </div>
                    <div class="form-group row">
                        <label for="diagnosis" class="col-sm-2 col-form-label"><b>Diagnosis:</b></label>
                        <div class="col-sm-10">
                            <textarea class="form-control" id="diagnosis" rows="3" name="diagnosis" value="">{{ $medicalRecord->diagnosis }}</textarea>
                            
                         </div>
                    </div>
                    <div class="form-group row">
                        <label for="file" class="col-sm-2 col-form-label"><i><b>Attach File:</b></i></label>
                            <div class="col-sm-10">
                                <div class="custom-file">
                                <input type="file" class="custom-file-input" id="file" name="file">
                                <label class="custom-file-label" for="customFile">Choose file</label>
                                </div>
                            </div>
                    </div>
                    <div class="form-group row">
                        <label for="attending_physician" class="col-sm-2 col-form-label"><i><b>ATTENDING PHYSICIAN:</b></i></label>
                        <div class="col-sm-10">
                            <input class="form-control mt-3" id="attending_physician"  value="{{ $medicalRecord->attending_physician }}" readonly />
                         </div>
                    </div>
                    <div class="form-group row">
                        <label for="attending_physician" class="col-sm-2 col-form-label"><i><b>To be Update By:</b></i></label>
                        <div class="col-sm-10">
                            <input class="form-control mt-3" id="attending_physician" name="attending_physician" value="{{ auth()->user()->fullName() }}" readonly />
                         </div>
                    </div>
                    <div class="modal-footer">
                        <button id="btnPrint" type="button" class="btn btn-outline-info">Print</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                        <a href="{{ route('medical-records.show', $patient->id) }}"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button></a>
                    </div>
                </div>
                <div class="col-auto">
                </div>
            </div>
            </div>
        </form>
    </div>
</div>


  <style>
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
    }
</style> 

    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();   
        });
    </script>
    <script>
        // Add the following code if you want the name of the file appear on select
        $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
        </script>
        <script>
        document.getElementById("btnPrint").onclick = function () {
            printElement(document.getElementById("printThis"));
        }

        function printElement(elem) {
            var domClone = elem.cloneNode(true);
            
            var $printSection = document.getElementById("printSection");
            
            if (!$printSection) {
                var $printSection = document.createElement("div");
                $printSection.id = "printSection";
                document.body.appendChild($printSection);
            }
            
            $printSection.innerHTML = "";
            $printSection.appendChild(domClone);
            window.print();
        }
    </script>
@stop   