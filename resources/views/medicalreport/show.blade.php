@extends('layouts.app')

@section('content')
<div class="border border-info">
    <div class="card-header border-info">
      <ul class="nav nav-tabs card-header-tabs mr-auto">
        <li class="nav-item">
          <a class="nav-link active">Medical Record</a>
        </li>
        <li class="nav nav-item-right ml-auto">
            <a href="{{route('patients.show', $patient->id)}}"><button type="button" class="close" >&times; </button> </a> {{--redirect to patient record ID--}}
        </li>
      </ul>
    </div>
    <div class="card-body">
                <div class="row">
                    <div class="col text-center">
                    <p><b><h5>MEDICAL AND HEALTH SERVICES</b></h5></p>
                    </div>
                </div>
                <div class="row">
                 
                    <div class="col mb-2 mt-3">
                        <label for="validation2"><b>OPD / Id Number :</b> {{$patient->id_number}}</label>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4">
                        <label for="validation2">Patient Name : {{"$patient->first_name $patient->middle_name $patient->last_name"}} </label>
                    </div>
                    <div class="col-2">
                        <label for="validation2">Age :{{$patient->age}}</label> 
                    </div>
                    <div class="col-2">
                        <label for="validation2">Gender : {{$patient->gender}}</label> 
                    </div>
                    <div class="col-4">
                        <label for="validation2">Contact No.: {{$patient->phone_number}}</label> 
                    </div>
                </div>
                <hr>
                <hr>

                <div class="row">
                    <div class="col">
                        <button type="button" class="btn btn-primary" id="myBtn">
                            +Add Consultation
                        </button>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                              <tr>
                                <th class="w-20">Date</th>
                                <th class="w-20">Performed Service</th>
                                <th class="w-25">Chief Complaints</th>
                                <th class="w-20">Attending Physician</th>
                                <th class="w-20">Attach File</th>
                                <th class="w-20">Action</th>
                              </tr>
                            </thead>
                            <tbody>
                                @foreach($patient->medicalRecords as $row)
                                <tr>
                                    <td scope="row">{{ $row->date_of_consultation }}</td>
                                    <td>{{ $row->performed_service }}</td>
                                    <td>{{ $row->chief_complaint }}</td>
                                    <td>{{ $row->attending_physician }}</td>
                                    <td  data-toggle="tooltip" data-placement="right" title="Click to Open file">
                                        @if ($row->file)
                                        <a href="{{ $row->file }}" target="_blank" class="btn">File <i class="fa fa-paperclip"></i></a>
                                        @else
                                        No file
                                        @endif
                                    </td>
                                    <td>
                                        <form action="" id="deleteForm" onsubmit="return confirmDelete()" method="post">
                                          @csrf
                                          @method('DELETE')
                                          <a  href="{{ route('medical-records.edit', $row->id) }}"><i class="fa fa-eye " data-toggle="tooltip" data-placement="top" title="view" style="padding-right:20px"aria-hidden="true"></a></i>
                                        </form>
                                      </td>
                                </tr>
                                @endforeach
                              
                            </tbody>
                          </table>
                    </div>
                </div>

{{----------------------------------------}}
                    <!-- Modal 1 -->
                    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title" id="exampleModalLabel">+Add Consultation </h6><p class="font-italic ml-2"> ( Fill up the form completely. Don't leave it blank. N/a for no answer. )</p>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>

                                <div class="modal-body">
                                    <form method="POST" action="{{ route('medical-records.store') }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="text" name="patient_id" value="{{ $patient->id }}" hidden/>
                                        <div class="form-group row">
                                            <label for="date_added" class="col-sm-2 col-form-label"><b>Date:</b></label>
                                            <div class="col-sm-4">
                                            <input type="date" class="form-control" id="date_added" name="date_of_consultation" placeholder="">
                                            </div>
                                            <label for="time_added" class="col-sm-2 col-form-label"><b>Time:</b></label>
                                            <div class="col-sm-4">
                                            <input type="time" class="form-control" id="time_added" name="time_of_consultation" placeholder="">
                                            </div>
                                        </div>
                                        
                                         <div class="form-group row">
                                            <label for="chief_complaint" class="col-sm-2 col-form-label"><b>Chief Complaint:</b></label>
                                            <div class="col-sm-10">
                                                <textarea class="form-control" id="chief_complaint" rows="2" name="chief_complaint" value=""></textarea>
                                            </div>
                                         </div>
                                         <div class="form-group row">
                                            <label for="services" class="col-sm-2 col-form-label"><b>Service:</b></label>
                                            <div class="col-sm-10">
                                                <select class="form-control" id="services" name="performed_service" required>
                                                    <option class="hidden"  selected disabled>Name of Service</option>
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
                                                        <input type="number" class="form-control-sm col-sm-5" name="vital_signs[temperature]" id="temp" value="">
                                                        <label class="col-form-label"><i><small> °C</small></i></label>
                                                    </div>
                                                </div>
                                                <div class="col-sm-2">
                                                    <div class="form-inline ">
                                                        <label for="pulse_rate" class="col-form-label">PR:</label>
                                                        <input type="number" class="form-control-sm col-sm-5" name="vital_signs[pulse_rate]" id="pulse_rate" value="">
                                                        <label class="col-form-label"><i><small> bpm</small></i></label>
                                                    </div>
                                                </div>
                                                <div class="col-sm-2">
                                                    <div class="form-inline ">
                                                                
                                                        <label for="res_rate" class="col-form-label">RR :</label>
                                                        <input type="number" class="form-control-sm col-sm-5" name="vital_signs[respiratory_rate]" id="res_rate" value="">
                                                        <label class="col-form-label"><i><small> bpm</small></i></label>
                                                    </div>
                                                </div>
                                                <div class="col-sm-2">
                                                    <div class="form-inline ">
                                                        <label for="b_p" class="col-form-label">BP:</label>
                                                        <input type="number" class="form-control-sm col-sm-5" name="vital_signs[blood_pressure]" id="b_p" value="">
                                                        <label class="col-form-label"><i><small>mmhg</small></i></label>
                                                    </div>
                                                </div>
                                                <div class="col-sm-2">
                                                    <div class="form-inline ">
                                                                
                                                        <label for="weight" class="col-form-label">WT :</label>
                                                        <input type="number" class="form-control-sm col-sm-5" name="vital_signs[weight]" id="weight" value="">
                                                        <label class="col-form-label"><i><small> kg</small></i></label>
                                                    </div>
                                                </div>
                                             </div>
                                             {{--
                                            <div class="form-group row ">
                                                <label for="by_nurse_assigned" class="col-sm-2 col-form-label mt-4"><b>By : </b></label>
                                                <div class="col-sm-10 ">
                                                    <div class="form-inline ">
                                                        <input type="text" class="form-control col-sm-8 mt-4" name="nurse_assigned" id="by_nurse_assigned" value="">
                                                        <label class="col-form-label mt-4"><i>,RN</i></label>
                                                    </div>
                                                </div>
                                            </div>
                                            --}}
                                        <hr>
                                            <div class="form-group row">
                                                    <label for="history_p_i" class="col-sm-2 col-form-label"><b>History of Present Illness:</b></label>
                                                <div class="col-sm-10">
                                                    <textarea class="form-control" id="history_p_i" rows="3" name="history_of_present_illness" value=""></textarea>
                                                </div>
                                            </div>
                                        <div class="form-group row">
                                            <label for="medication_taken" class="col-sm-2 col-form-label"><b>Medication Taken:</b></label>
                                            <div class="col-sm-10">
                                                <textarea class="form-control" id="medication_taken" rows="2" name="medication_taken" value=""></textarea>
                                                
                                             </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="findings" class="col-sm-2 col-form-label"><b>Findings:</b></label>
                                            <div class="col-sm-10">
                                                <textarea class="form-control" id="findings" rows="2" name="findings" value=""></textarea>
                                             </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="recommendation" class="col-sm-2 col-form-label"><b>Recommendations:</b></label>
                                            <div class="col-sm-10">
                                                <textarea class="form-control" id="recommendation" rows="3" name="recommendation" value=""></textarea>
                                             </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="diagnosis" class="col-sm-2 col-form-label"><b>Diagnosis:</b></label>
                                            <div class="col-sm-10"> 
                                                <textarea class="form-control" id="diagnosis" rows="3" name="diagnosis" value=""></textarea>
                                               
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
                                                <input class="form-control mt-3" id="attending_physician" name="attending_physician" value="{{ auth()->user()->fullName() }}" readonly />
                                             </div>
                                        </div>
                                        <input type="submit" id="submit-btn" hidden />
                                    </form>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" onclick="document.getElementById('submit-btn').click()">Submit</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                             </div>
                        </div>
                    </div>
{{---------------end of modal1------------}}


        </div>
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
        $("#myBtn").click(function(){
            $("#myModal").modal({backdrop: "static"});
        });
        
        });

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
        
@stop   