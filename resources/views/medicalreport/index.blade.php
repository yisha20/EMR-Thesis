@extends('layouts.app')

@section('content')
<div class="border">
    <div class="card-header">
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
                    <div class="col">
                        <table class="table table-hover">
                            <thead>
                              <tr>
                                <th scope="col">Date</th>
                                <th scope="col">Performed Service</th>
                                <th scope="col">Chief Complaints</th>
                                <th scope="col">Attending Physician</th>
                                <th scope="col">Attach File</th>
                                <th scope="col">Action</th>
                              </tr>
                            </thead>
                            <tbody>
                                @foreach($patient->medicalRecords as $row)
                                <tr>
                                    <td scope="row">{{ $row->date_of_consultation }}</td>
                                    <td data-toggle="tooltip" data-placement="right" title="Click to view more">
                                        {{ $row->performed_service }}    
                                    </td>
                                    <td>{{ $row->chief_complaint }}</td>
                                    <td>{{ $row->attending_physician }}</td>
                                    <td  data-toggle="tooltip" data-placement="right" title="Click to Open file">
                                        @if ($row->file)
                                        <a href="{{ $row->file }}" target="_blank" class="btn">File</a>
                                        @else
                                        No file
                                        @endif
                                    </td>
                                    <td>
                                        <form action="" id="deleteForm" onsubmit="return confirmDelete()" method="post">
                                          @csrf
                                          @method('DELETE')
                                          <a  href=""><i class="fa fa-eye" data-toggle="tooltip" data-placement="top" title="view" style="padding-right:20px"aria-hidden="true"></a></i>
                                          <a href=""><i class="fa fa-edit" data-toggle="tooltip" data-placement="top" title="edit" style="padding-right:20px"aria-hidden="true"></a></i>
                                          <button class="btn" type="submit">
                                            <i class="fa fa-archive" data-toggle="tooltip" data-placement="top" title="archive" style="padding-right:15px"aria-hidden="true"></i> 
                                          </button>{{--archive nalang daw instead of deleting the files of user--}}
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
                                    <h5 class="modal-title" id="exampleModalLabel">+Add Consultation</h5>
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
                                            <label for="chief_complaint" class="col-sm-2 col-form-label"><b>Chief Complaint:</b></label>
                                            <div class="col-sm-10">
                                            <input type="text" class="form-control" id="chief_complaint" name="chief_complaint" placeholder="">
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
                                            <div class="form-group row ">
                                                <label for="by_nurse_assigned" class="col-sm-2 col-form-label mt-4"><b>By : </b></label>
                                                <div class="col-sm-10 ">
                                                    <div class="form-inline ">
                                                        <input type="text" class="form-control col-sm-8 mt-4" name="nurse_assigned" id="by_nurse_assigned" value="">
                                                        <label class="col-form-label mt-4"><i>,RN</i></label>
                                                    </div>
                                                </div>
                                            </div>
                                        <hr>
                                            <div class="form-group row">
                                                    <label for="history_p_i" class="col-sm-2 col-form-label"><b>History of Present Illness:</b></label>
                                                <div class="col-sm-10">
                                                <input type="text" class="form-control mt-4" id="history_p_i" name="history_of_present_illness" placeholder="">
                                                </div>
                                            </div>
                                        <div class="form-group row">
                                            <label for="medication_taken" class="col-sm-2 col-form-label"><b>Medication Taken:</b></label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control mt-3" id="medication_taken" name="medication_taken" placeholder="">
                                             </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="medication_taken" class="col-sm-2 col-form-label"><b>Findings:</b></label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="medication_taken" name="findings" placeholder="">
                                             </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="medication_taken" class="col-sm-2 col-form-label"><b>Recommendations:</b></label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="medication_taken" name="recommendation" placeholder="">
                                             </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="medication_taken" class="col-sm-2 col-form-label"><b>Diagnosis:</b></label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="medication_taken" name="diagnosis" placeholder="">
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

{{----------------modal 2----}}    

                    <!-- Modal -->
            <div id="printThis">
                <div class="modal fade" id="myModal3" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">+Add/Edit Consultation </h5>
                        
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        </div>
                        <div class="modal-body">
                            <form>
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
                                    <label for="services" class="col-sm-2 col-form-label"><b>Service:</b></label>
                                    <div class="col-sm-10">
                                            
                                                <select class="form-control" id="services" name="performed_service" required>
                                                    <option class="hidden"  selected disabled>Name of Service</option>
                                                        <option value="1">Checkup</option>
                                                        <option value="2">Vaccine</option>
                                                        <option value="3">Blood Type Test</option>
                                                        <option value="4">Urinary Test</option>
                                                        <option value="5">Hematology Test</option>
                                                        <option value="6">First Aid</option>
                                                        <option value="7">Check Blood Pressure</option>
                                                        <option value="8">Follow Up Checkup</option>
                                                    </select>
                                    </div>
                                </div>
                                 <div class="form-group row">
                                    <label for="chief_complaint" class="col-sm-2 col-form-label"><b>Chief Complaint:</b></label>
                                    <div class="col-sm-10">
                                    <input type="text" class="form-control" id="chief_complaint" name="chief_complaint" placeholder="">
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
                                    <div class="form-group row ">
                                        <label for="by_nurse_assigned" class="col-sm-2 col-form-label mt-4"><b>By : </b></label>
                                        <div class="col-sm-10 ">
                                            <div class="form-inline ">
                                                <input type="text" class="form-control col-sm-8 mt-4" name="nurse_assigned" id="by_nurse_assigned" value="">
                                                <label class="col-form-label mt-4"><i>,RN</i></label>
                                            </div>
                                        </div>
                                    </div>
                                <hr>
                                    <div class="form-group row">
                                            <label for="history_p_i" class="col-sm-2 col-form-label"><b>History of Present Illness:</b></label>
                                        <div class="col-sm-10">
                                        <input type="text" class="form-control mt-4" id="history_p_i" name="history_of_present_illness" placeholder="">
                                        </div>
                                    </div>
                                <div class="form-group row">
                                    <label for="medication_taken" class="col-sm-2 col-form-label"><b>Medication Taken:</b></label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control mt-3" id="medication_taken" name="medication_taken" placeholder="">
                                     </div>
                                </div>
                                <div class="form-group row">
                                    <label for="medication_taken" class="col-sm-2 col-form-label"><b>Findings:</b></label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="medication_taken" name="findings" placeholder="">
                                     </div>
                                </div>
                                <div class="form-group row">
                                    <label for="medication_taken" class="col-sm-2 col-form-label"><b>Recommendations:</b></label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="medication_taken" name="recommendation" placeholder="">
                                     </div>
                                </div>
                                <div class="form-group row">
                                    <label for="medication_taken" class="col-sm-2 col-form-label"><b>Diagnosis:</b></label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="medication_taken" name="diagnosis" placeholder="">
                                     </div>
                                </div>
                                <div class="form-group row">
                                    <label for="attached_file" class="col-sm-2 col-form-label"><i><b>Attach File:</b></i></label>
                                        <div class="col-sm-10">
                                            <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="attached_file" name="attached_file">
                                            <label class="custom-file-label" for="customFile">Choose file</label>
                                            </div>
                                        </div>
                                </div>
                                <div class="form-group row">
                                    <label for="attending_physician" class="col-sm-2 col-form-label"><i><b>ATTENDING PHYSICIAN:</b></i></label>
                                    <div class="col-sm-10">
                                        <select class="form-control mt-3" id="attending_physician" name="attending_physician" required>
                                            <option class="hidden"  selected disabled>Name of Physician</option>
                                                <option value="1">Current Active User/ Doctor using this account</option>
                                            </select>
                                     </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button id="btnPrint" type="button" class="btn btn-outline-info">Print</button>
                            <button type="button" class="btn btn-primary">Save changes</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                    </div>
                </div>
            </div>

{{---------------------------end of modal 2---}}
    <!-- Modal -->
    <div class="modal fade" id="myModal4" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Attached File</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="modal-body">
                <form>
                    
                    
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary">Save changes</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
        </div>
    </div>

{{-----end of modal 3--------}}


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
      $("#myBtn3").click(function(){
        $("#myModal3").modal({backdrop: "static"});
      });
      $("#myBtn4").click(function(){
        $("#myModal4").modal({backdrop: "static"});
      });
    });
    </script>
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