@extends('layouts.app')


@section('content')

<div class="card border-info">
    <div class="card-header border">
      <ul class="nav nav-tabs card-header-tabs">
        <li class="nav-item">
          <a class="nav-link active" href="/patients">Patients</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/patients/create">Add New Patient</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('patients.archive') }}">Archive</a>
          </li>
      </ul>
    </div>
    <div class="card-header border-info">
      <ul class="nav nav-tabs card-header-tabs">
        
          <li class="nav-item">
            <a class="nav-link active" href="{{ route('patients.index') }}">View Patient... 
            <button type="button" class="close">&times; </button> </a>
          </li>
      </ul>
    </div>
    <div class="card-body">
         <div class="form-group text-center">
            <div class="col" style=" margin-top: 3%">
                <img src="{{ $patient->avatar ?? '/img/no_avatar.jpg' }}"  alt="create_avatar" class="create_avatar "><br>{{--PRofile pic upload (Restrict user thaht only img/png file can be uploaded--}}
            </div>
        </div>
        <div class="form-group text-center">
            <div class="col">
                <h4 >{{ "$patient->first_name $patient->middle_name $patient->last_name" }}</h4>
                <h6>ID No. {{ $patient->id_number }}</h6> 
                
                <a href="{{ route('medical-records.show', $patient->id) }}" class="btn btn-info">View Medical Record</a>
                <br><br>
            </div>
        </div>
        <div class="container">
                                {{--row1--}}
                                <div class="row ">
                                    <div class="col-2">
                                    </div>
                                    <div class="col">
                                        <h6 class><i>Home Address:</h6></i>
                                        <p>{{ $patient->home_address }}</p>
                                    <h6><i>Present Address:</h6></i>
                                    <p>{{ $patient->present_address }}</p>
                                        <h6><i>Civil Status:</i></h6>
                                        <p>{{ $patient->status }}</p>
                                        <h6><i>Gender:</i></h6>
                                        <p>{{ $patient->gender }}</p>
                                    </div>
                                    <div class="col">
                                        <h6><i>Age:</i></h6>
                                        <p>{{ $patient->age }}</p>
                                        <h6><i>Birth Date/Month/Year:</i></h6>
                                        <p>{{ $patient->birthdate }}</p>
                                        <h6><i>College/ Department:</i></h6>
                                        <p>{{ $patient->college_department }}</p>
                                        <h6><i>Phone Number:</i></h6>
                                        <p>{{ $patient->phone_number }}</p>   
                                    </div>
                                </div>

                            {{--row2--}}
                            <div class="row justify-content-center align-items-center">
                                <div class="border border-info mt-3 tab-card mr-10" >
                                    <div class=" border-info card-header tab-card-header">
                                        <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="one-tab" data-toggle="tab" href="#one" role="tab" aria-controls="One" aria-selected="true">Past Medical History</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="two-tab" data-toggle="tab" href="#two" role="tab" aria-controls="Two" aria-selected="false">Family History</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="three-tab" data-toggle="tab" href="#three" role="tab" aria-controls="Three" aria-selected="false">Social History</a>
                                            </li>
                                            <li class="nav-item">
                                            <a class="nav-link" id="three-tab" data-toggle="tab" href="#four" role="tab" aria-controls="Four" aria-selected="false">Physical Examination</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="three-tab" data-toggle="tab" href="#five" role="tab" aria-controls="Five" aria-selected="false">Vital Signs</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="three-tab" data-toggle="tab" href="#six" role="tab" aria-controls="Six" aria-selected="false">Nursing Intervention</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="three-tab" data-toggle="tab" href="#seven" role="tab" aria-controls="Seven" aria-selected="false">Assessment</a>
                                            </li>
                                        </ul>
                                    </div>

                                <fieldset disabled>
                                    <div class="tab-content" id="myTabContent">
                                        {{--tab1--}}
                                        <div class="tab-pane fade show active p-3" id="one" role="tabpanel" aria-labelledby="one-tab">
                                            <div class="row">
                                                     <div class="col text-left">
                                                        <div class="form-group">
                                                            <div class="form-check">
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Allergies') ? 'checked' : '' }} value="Allergies"> Allergies<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Amoebiasis') ? 'checked' : '' }} value="Amoebiasis"> Amoebiasis<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Anemia') ? 'checked' : '' }} value="Anemia"> Anemia<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Arthritis') ? 'checked' : '' }} value="Arthritis"> Arthritis<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Back and Joints Pains') ? 'checked' : '' }} value="Back and Joints Pains"> Back and Joints Pains<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Bone Fracture') ? 'checked' : '' }} value="Bone Fracture"> Bone Fracture<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Breast mass/lump') ? 'checked' : '' }} value="Breast mass/lump"> Breast mass/lump<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Chest Pains') ? 'checked' : '' }} value="Chest Pains"> Chest Pains<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Chicken Pox') ? 'checked' : '' }} value="Chicken Pox"> Chicken Pox<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Diabetes Mellitus') ? 'checked' : '' }} value="Diabetes Mellitus"> Diabetes Mellitus<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Epilepsy') ? 'checked' : '' }} value="Epilepsy"> Epilepsy<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Eye or Ear Problem') ? 'checked' : '' }} value="Eye or Ear Problem"> Eye or Ear Problem<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Gallbladder Stone') ? 'checked' : '' }} value="Gallbladder Stone"> Gallbladder Stone<br>
                                                            </div>   
                                                        </div>
                                                    </div>
                                                    <div class="col text-left">
                                                        <div class="form-group">
                                                            <div class="form-check">
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Goiter') ? 'checked' : '' }} value="Goiter"> Goiter<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Gout') ? 'checked' : '' }} value="Gout"> Gout<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Hemorrhoids') ? 'checked' : '' }} value="Hemorrhoids"> Hemorrhoids<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Hepatitis: A/B/C') ? 'checked' : '' }} value="Hepatitis: A/B/C"> Hepatitis: A/B/C<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Hyperacidity/Ulcer') ? 'checked' : '' }} value="Hyperacidity/Ulcer"> Hyperacidity/Ulcer<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Hypertension') ? 'checked' : '' }} value="Hypertension"> Hypertension<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Kidney/Bladder Stones') ? 'checked' : '' }} value="Kidney/Bladder Stones"> Kidney/Bladder Stones<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Loss of Conciousness') ? 'checked' : '' }} value="Loss of Conciousness"> Loss of Conciousness<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Measles') ? 'checked' : '' }} value="Measles"> Measles<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Mumps') ? 'checked' : '' }} value="Mumps"> Mumps<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Pneumonia') ? 'checked' : '' }} value="Pneumonia"> Pneumonia<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Prostate Problems') ? 'checked' : '' }} value="Prostate Problems"> Prostate Problems<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Seizure') ? 'checked' : '' }} value="Seizure"> Seizure<br>
                                                            </div>
                                                        </div>
                                                    </div>    
                                                <div class="col text-left">
                                                    <div class="form-group">
                                                        <div class="form-check">
                                                            <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Sinusitis/Allergic rhinitis') ? 'checked' : '' }} value="Sinusitis/Allergic rhinitis"> Sinusitis/Allergic rhinitis<br>
                                                            <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Skin Disorders') ? 'checked' : '' }} value="Skin Disorders"> Skin Disorders<br>
                                                            <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('STI/HIV') ? 'checked' : '' }} value="STI/HIV"> STI/HIV<br>
                                                            <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Stroke') ? 'checked' : '' }} value="Stroke"> Stroke<br>
                                                            <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Surgery/Injury') ? 'checked' : '' }} value="Surgery/Injury"> Surgery/Injury<br>
                                                            <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Thyroid Problems') ? 'checked' : '' }} value="Thyroid Problems"> Thyroid Problems<br>
                                                            <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Tonsillitis') ? 'checked' : '' }} value="Tonsillitis"> Tonsillitis<br>
                                                            <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Tuberculosis') ? 'checked' : '' }} value="Tuberculosis"> Tuberculosis<br>
                                                            <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('UTI') ? 'checked' : '' }} value="UTI"> UTI<br>
                                                            <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('others') ? 'checked' : '' }} value="others"> Others: 
                                                           
                                                            <input style= "margin-left: 2%" type="text" name="pastmedical_history[]" value="{{ $patient->healthExaminationRecord->past_medical_history_others }}" >
                                                            <br><br>    
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        
                                            <div class="row">
                                                <div class="col text-center">
                                                    <b><br><br>For Women:<br> Last Menstrual Period(LMP) </b><br><input class="ml-auto mr-auto mt-2 form-control mb-5 col-sm-5" value="{{ $patient->getLastMenstrualPeriod() }}" type="date" name="last_menstrual_period">
                                                </div>
                                                <div class="col text-center">
                                                    <h6><br><br><br><b>Menstrual Pattern:</b><h6>
                                                        <div class="custom-control custom-radio custom-control-inline">
                                                            <input type="radio" class="custom-control-input" id="mp1" name="menstrual_pattern" {{ $patient->getMenstrualPattern() == 'regular' ? 'checked' : '' }} value="regular">
                                                            <label class="custom-control-label mt-2" for="mp1">Regular</label>
                                                          </div>   
                                                          <div class="custom-control custom-radio custom-control-inline">
                                                            <input type="radio" class="custom-control-input" id="mp2" name="menstrual_pattern" {{ $patient->getMenstrualPattern() == 'irregular' ? 'checked' : '' }} value="irregular">
                                                            <label class="custom-control-label" for="mp2">Irregular</label>
                                                            <br>
                                                        </div> 
                                                </div>
                                            </div>         
                                        </div> {{--end /div tab1--}}

                                         {{--tab2--}}
                                         <div class="tab-pane fade p-3" id="two" role="tabpanel" aria-labelledby="two-tab">
                                            <div class="row">
                                                <div class="col text-left">
                                                    <div class="form-group">
                                                        <div class="form-check">
                                                            <p>Do you have a close relative (parent/grandparents/siblings) who have been diagnosed of:</p>
                                                    
                                                                    <input style= "margin-left: 2%" type="checkbox" name="family_history[]" {{ $patient->hasFamilyHistory('High Blood Pressure') ? 'checked' : '' }} value="High Blood Pressure"> High Blood Pressure<br>
                                                                    <input style= "margin-left: 2%" type="checkbox" name="family_history[]" {{ $patient->hasFamilyHistory('Tuberculosis') ? 'checked' : '' }} value="Tuberculosis"> Tuberculosis<br>
                                                                    <input style= "margin-left: 2%" type="checkbox" name="family_history[]" {{ $patient->hasFamilyHistory('Heart Disease') ? 'checked' : '' }} value="Heart Disease"> Heart Disease<br>
                                                                    <input style= "margin-left: 2%" type="checkbox" name="family_history[]" {{ $patient->hasFamilyHistory('Asthma') ? 'checked' : '' }} value="Asthma"> Asthma<br>
                                                                <br>
                                                        </div> 
                                                    </div>       
                                                </div>
                                                <div class="col text-left">
                                                    <div class="form-group">
                                                        <div class="form-check">
                                                            <br><br><br>
                                                            <input style= "margin-left: 2%" type="checkbox" name="family_history[]" {{ $patient->hasFamilyHistory('Diabetes') ? 'checked' : '' }} value="Diabetes"> Diabetes<br>
                                                            <input style= "margin-left: 2%" type="checkbox" name="family_history[]" {{ $patient->hasFamilyHistory('Allergies') ? 'checked' : '' }} value="Allergies"> Allergies<br>
                                                            <input style= "margin-left: 2%" type="checkbox" name="family_history[]" {{ $patient->hasFamilyHistory('Cancer') ? 'checked' : '' }} value="Cancer"> Cancer<br>
                                                            <input style= "margin-left: 2%" type="checkbox" name="family_history[]" {{ $patient->hasFamilyHistory('Others') ? 'checked' : '' }} value="Others"> Other:
                                                            <input style= "margin-left: 2%" type="text" name="family_history[]" value="{{ $patient->healthExaminationRecord->family_history_others }}">
                                                            <br>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>             
                                        </div> {{--end /div2--}}

                                         {{--tab3--}}
                                         <div class="tab-pane fade p-3" id="three" role="tabpanel" aria-labelledby="three-tab">
                                            <div class="row">
                                                <div class="col text-left" style= "margin-left: 14%">
                                                    <div class="form-group">
                                                        <h6><br>1. Do you Smoke? </h6>
                                                        <div class="form-check">
                                                            <div class="custom-control custom-radio ">
                                                                <input type="radio" class="custom-control-input" id="choice1" name="is_smoking" {{ $patient->getSocialHistoryAttr('is_smoking') == 'No' ? 'checked' : '' }} value="No">
                                                                <label class="custom-control-label" for="choice1">No</label>
                                                              </div>
                                                        </div>
                                                        <div class="form-check">   
                                                            <div class="custom-control custom-radio ">
                                                                <input style= "margin-left: 9%"type="radio" class="custom-control-input" id="choice2" name="is_smoking" {{ $patient->getSocialHistoryAttr('is_smoking') == 'Yes' ? 'checked' : '' }} value="Yes">
                                                                <label class="custom-control-label" for="choice2">Yes</label>
                                                                <br>
                                                             </div>
                                                        </div> 
                                                    
                                                        
                                                        <h6><br>If yes, how many packs?</h6>
                                                        <input class="form-control mb-3 col-sm-8" type="number" value="{{ $patient->getSocialHistoryAttr('packs_smoked') }}"  name="packs_smoked">
                                                        <h6> <br> 2. Do you drink alcohol (beer/liquer)? </h6>
                                                        <div class="form-check">
                                                            <div class="custom-control custom-radio ">
                                                                <input type="radio" class="custom-control-input" id="choice1a" name="is_drinking_beer" {{ $patient->getSocialHistoryAttr('is_drinking_beer') == 'No' ? 'checked' : '' }} value="No">
                                                                <label class="custom-control-label" for="choice1a">No</label>
                                                            </div> 
                                                        </div>
                                                        <div class="form-check">
                                                            <div class="custom-control custom-radio ">
                                                                <input type="radio" class="custom-control-input" id="choice2a" name="is_drinking_beer" {{ $patient->getSocialHistoryAttr('is_drinking_beer') == 'Yes' ? 'checked' : '' }} value="Yes">
                                                                <label class="custom-control-label" for="choice2a">Yes</label>
                                                            </div> 
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="sel1"><h6><br>If yes, how frequent?</h6></label>
                                                            <select class="form-control col-sm-8" id="sel1" name="howfrequent">
                                                            <option class="hidden"  selected disabled>Please Choose:</option>
                                                              <option {{ $patient->getSocialHistoryAttr('drinking_frequency') == 'Seldom' ? 'selected' : '' }}>Seldom</option>
                                                              <option {{ $patient->getSocialHistoryAttr('drinking_frequency') == 'Occasional' ? 'selected' : '' }}>Occasional</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>   

                                                <div class="col text-left">
                                                    <div class="form-group">
                                                         <h6> <br> 3. Do you take medication at present? </h6>
                                                        <div class="form-check">
                                                            <div class="custom-control custom-radio ">
                                                                <input type="radio" class="custom-control-input" id="choice3a" name="is_taking_medication" {{ $patient->getSocialHistoryAttr('is_taking_medication') == 'No' ? 'checked' : '' }} value="No">
                                                                <label class="custom-control-label" for="choice3a">No</label>
                                                            </div> 
                                                        </div>
                                                            <div class="form-check">
                                                                <div class="custom-control custom-radio ">
                                                                    <input type="radio" class="custom-control-input" id="choice3b" name="is_taking_medication" {{ $patient->getSocialHistoryAttr('is_taking_medication') == 'Yes' ? 'checked' : '' }} value="Yes">
                                                                    <label class="custom-control-label" for="choice3b">Yes</label>
                                                                </div>
                                                            </div>
                                                            <h6> <br> If yes, please indicate below</h6>
                                                            <ol>
                                                                @foreach($patient->getSocialHistoryAttr('medications') as $medication)
                                                                    <li>{{ $medication }}</li>
                                                                @endforeach
                                                            </ol>
                                                           
                                                        </div>
                                                    </div>     
                                                </div>             
                                        </div><br> {{--end /div3--}}

                                        
                                                {{--tab4--}}
                                                <div class="tab-pane fade p-3" id="four" role="tabpanel" aria-labelledby="three-tab">
                                                    <div class="table-responsive-md">
                                                        <div class="row">
                                                            <div class="col text-left table-responsive-md">
                                                                <p class="register-heading text-center">(To be accomplished by physician)</p>
                                                                <table class="table table-bordered ">
                                                                    <thead>
                                                                        <tr>
                                                                            <th></th>
                                                                            <th></th>
                                                                            <th>Normal</th>
                                                                            <th>Abnormal</th>
                                                                            <th>Remarks</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    <form>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Skin</td>
                                                                            <td><input type="radio" name="skin_status" {{ $patient->getPEAttr('skin_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="skin_status" {{ $patient->getPEAttr('skin_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" type="text" value="{{ $patient->getPEAttr('skin_remarks') }}" name="skin_remarks"></td>
                                                                            </td>
                                                                            
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Head / Neck / Scalp</td>
                                                                            <td><input  type="radio" name="head_status" {{ $patient->getPEAttr('head_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="head_status" {{ $patient->getPEAttr('head_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" type="text" value="{{ $patient->getPEAttr('head_remarks') }}" name="head_remarks"></td>
                                                                            </td>
                                                                            
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Eyes</td>
                                                                            <td><input  type="radio" name="eyes_status" {{ $patient->getPEAttr('eyes_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="eyes_status" {{ $patient->getPEAttr('eyes_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" type="text" value="{{ $patient->getPEAttr('eyes_remarks') }}" name="eyes_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Ears/Nose/Throat</td>
                                                                            <td><input  type="radio" name="ears_status" {{ $patient->getPEAttr('ears_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="ears_status" {{ $patient->getPEAttr('ears_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" type="text" value="{{ $patient->getPEAttr('ears_remarks') }}" name="ears_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Nose/Sinuses</td>
                                                                            <td><input  type="radio" name="nose_status" {{ $patient->getPEAttr('nose_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="nose_status" {{ $patient->getPEAttr('nose_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" type="text" value="{{ $patient->getPEAttr('nose_remarks') }}" name="nose_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Mouth/Throat</td>
                                                                            <td><input  type="radio" name="mouth_status" {{ $patient->getPEAttr('mouth_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="mouth_status" {{ $patient->getPEAttr('mouth_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" type="text" value="{{ $patient->getPEAttr('mouth_remarks') }}" name="mouth_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Neck, LN, Thyroid</td>
                                                                            <td><input  type="radio" name="neck_status" {{ $patient->getPEAttr('neck_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="neck_status" {{ $patient->getPEAttr('neck_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" type="text" value="{{ $patient->getPEAttr('neck_remarks') }}" name="neck_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Chest-Breast-Axilla</td>
                                                                            <td><input  type="radio" name="chest_status" {{ $patient->getPEAttr('chest_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="chest_status" {{ $patient->getPEAttr('chest_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" type="text" value="{{ $patient->getPEAttr('chest_remarks') }}" name="chest_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Lungs</td>
                                                                            <td><input  type="radio" name="lungs_status" {{ $patient->getPEAttr('lungs_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="lungs_status" {{ $patient->getPEAttr('lungs_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" type="text" value="{{ $patient->getPEAttr('lungs_remarks') }}" name="lungs_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Heart</td>
                                                                            <td><input  type="radio" name="heart_status" {{ $patient->getPEAttr('heart_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="heart_status" {{ $patient->getPEAttr('heart_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" type="text" value="{{ $patient->getPEAttr('heart_remarks') }}" name="heart_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Abdomen</td>
                                                                            <td><input  type="radio" name="abdomen_status" {{ $patient->getPEAttr('abdomen_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="abdomen_status" {{ $patient->getPEAttr('abdomen_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" type="text" value="{{ $patient->getPEAttr('abdomen_remarks') }}" name="abdomen_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Back, Flank</td>
                                                                            <td><input  type="radio" name="back_status" {{ $patient->getPEAttr('back_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="back_status" {{ $patient->getPEAttr('back_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" type="text" value="{{ $patient->getPEAttr('back_remarks') }}" name="back_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Anus-rectum</td>
                                                                            <td><input  type="radio" name="anus_status" {{ $patient->getPEAttr('anus_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="anus_status" {{ $patient->getPEAttr('anus_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" type="text" value="{{ $patient->getPEAttr('anus_remarks') }}" name="anus_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>GU system</td>
                                                                            <td><input  type="radio" name="gu_system_status" {{ $patient->getPEAttr('gu_system_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="gu_system_status" {{ $patient->getPEAttr('gu_system_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" type="text" value="{{ $patient->getPEAttr('gu_system_remarks') }}" name="gu_system_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Inguinals, Genitals</td>
                                                                            <td><input  type="radio" name="genitals_status" {{ $patient->getPEAttr('genitals_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="genitals_status" {{ $patient->getPEAttr('genitals_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" type="text" value="{{ $patient->getPEAttr('genitals_remarks') }}" name="genitals_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Reflexes</td>
                                                                            <td><input  type="radio" name="reflexes_status" {{ $patient->getPEAttr('reflexes_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="reflexes_status" {{ $patient->getPEAttr('reflexes_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" type="text" value="{{ $patient->getPEAttr('reflexes_remarks') }}" name="reflexes_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Extremities</td>
                                                                            <td><input type="radio" name="extremities_status" {{ $patient->getPEAttr('extremities_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="extremities_status" {{ $patient->getPEAttr('extremities_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" type="text" value="{{ $patient->getPEAttr('extremities_remarks') }}" name="extremities_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Neurologic</td>
                                                                            <td><input  type="radio" name="neurologic_status" {{ $patient->getPEAttr('neurologic_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="neurologic_status" {{ $patient->getPEAttr('neurologic_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" type="text" value="{{ $patient->getPEAttr('neurologic_remarks') }}" name="neurologic_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Endocrine</td>
                                                                            <td><input  type="radio" name="endocrine_status" {{ $patient->getPEAttr('endocrine_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="endocrine_status" {{ $patient->getPEAttr('endocrine_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" type="text" value="{{ $patient->getPEAttr('endocrine_remarks') }}" name="endocrine_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Others</td>
                                                                            <td><input  type="radio" name="others_status" {{ $patient->getPEAttr('others_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="others_status" {{ $patient->getPEAttr('others_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" type="text" value="{{ $patient->getPEAttr('others_remarks') }}" name="others_remarks" value=""></td>
                                                                            </td>
                                                                        </tr>
                                                                        </form>
                                                                        </tbody>
                                                                    
                                                                </table>       
                                                            </div>
                                                        </div>
                                                    </div>          
                                                </div> {{--end /div tab4--}}


                                                 {{--tab5--}}
                                                 <div class="tab-pane fade p-3" id="five" role="tabpanel" aria-labelledby="three-tab">
                                                    <div class="row justify-content-center ">
                                                        <div class="form-inline ">
                                                            
                                                                <label for="temp" class="mr-sm-2">Temperature :</label>
                                                                <input type="number" value="{{ $patient->getVitalSignAttr('temperature') }}" class="form-control mb-3 col-sm-3 mt-3" name="temperature" id="temp" value="">
                                                                <label class="form-control-label ml-1 mr-2"><i>°C</i></label>
                                                                <label for="pulse_rate" class="col-sm-2 ml-5 ">Pulse Rate :</label> 
                                                                <input type="text" value="{{ $patient->getVitalSignAttr('pulse_rate') }}" class="form-control mb-3 ml-2 col-sm-3 mt-3" name="pulse_rate" id="pulse_rate" value="">
                                                                <label class="form-control-label ml-1 mr-2"><i>bpm</i></label>
                                                        </div>
                                                    </div>
                                                    <div class="row justify-content-center ">
                                                        <div class="form-inline ">
                                                            
                                                                <label for="r_r" class="mr-sm-2">Respiratory Rate:</label>
                                                                <input type="text" value="{{ $patient->getVitalSignAttr('respiratory_rate') }}" class="form-control mb-3 col-sm-3 mt-3" name="respiratory_rate" id="r_r" value="">
                                                                <label class="form-control-label ml-1 mr-2"><i>bpm</i></label>
                                                                <label for="bp" class="ml-sm-5 ">Blood Pressure :</label> 
                                                                <input type="text" value="{{ $patient->getVitalSignAttr('blood_pressure') }}" class="form-control mb-3 ml-2 col-sm-3 mt-3" name="blood_pressure" id="bp" value="">
                                                                <label class="form-control-label ml-1 mr-2"><i>mmhg</i></label>
                                                        </div>
                                                    </div>

                                                    <div class="row justify-content-center ">
                                                        <div class="form-inline">
                                                            <label for="weight" class="mr-sm-2 mt-4 mb-4"> Weight : </label>
                                                            <input type="number" value="{{ $patient->getVitalSignAttr('weight') }}" class="form-control col-sm-5 mt-4 mb-4" name="weight" id="weight" value="">
                                                            <label class="form-control-label ml-1"><i>kg</i></label>
                                                        </div>
                                                     </div>               
                                                </div> {{--end /div tab5--}}



                                                {{--tab6--}}
                                                <div class="tab-pane fade p-3" id="six" role="tabpanel" aria-labelledby="three-tab">
                                                            
                                                    <div class="row justify-content-center">
                                                         <div class="form-group ">
                                                            <table>
                                                                <thead>
                                                                    <tr>
                                                                        <th>Nurse Intervention:</th>
                                                                        <th>Time: <small>(12:59 am/pm)</small></th>
                                                                        <th class="pl-3">By:</th>
                                                                        
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($patient->getNursingInterventions() as $row)
                                                                    <tr>
                                                                        <td><input class="form-control col-sm-15 mb-2 mt-2" type="text" name="nursing_intervention" value="{{ $row['intervention'] }}"></td>
                                                                        <td><input class="form-control col-sm-14 mb-2 ml-1 mt-2" type="time" name="time" value="{{ $row['time'] }}"></td>
                                                                        <td><input class="form-control col-sm-14 mb-2 ml-2 mt-2" type="text" name="by" value="{{ $row['by'] }}"></td>
                                                                    </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table><br>
                                                        </div>
                                                    </div>            
                                                 </div> {{--end /div tab6--}}


                                                  {{--tab7--}}
                                                <div class="tab-pane fade p-3" id="seven" role="tabpanel" aria-labelledby="three-tab">
                                                    <div class="row justify-content-center">
                                                        <div class="form-group">
                                                            <p class="register-heading text-center"><b>ASSESSMENT AND RECOMMENDATION</b></p>
                                                            
                                                            <div class="custom-control custom-radio custom-control-inline">
                                                                <input type="radio" class="custom-control-input mb-2" id="customRadio" name="physically_fit" {{ $patient->getAssessmentAttr('physically_fit') == 'Yes' ? 'checked' : '' }} value="Yes">
                                                                <label class="custom-control-label mb-3" for="customRadio">Physically Fit</label>
                                                              </div>   
                                                              <div class="custom-control custom-radio custom-control-inline">
                                                                <input type="radio" class="custom-control-input" id="customRadio1" name="physically_fit" {{ $patient->getAssessmentAttr('physically_fit') == 'No' ? 'checked' : '' }} value="No">
                                                                <label class="custom-control-label" for="customRadio1">Not Physically Fit</label>
                                                                <br>
                                                            </div> 
                                                                <div class="form-group">
                                                                    <label >Comment: </label>
                                                                    <textarea class="form-control" name="physically_fit_description">{{ $patient->getAssessmentAttr('physically_fit_description') }}</textarea><br>
                                                                </div>
                                                                <label >Date of Examination: </label>
                                                                <input class="form-control" type="date" name="date_examined" value="{{ $patient->getAssessmentAttr('date_examined') }}">
                                                                
                                                       
            
                                                                <br><br>
                                                                <div class="form-check-inline">
                                                                    <label class="form-control-label mr-2 mb-2">Added By: </label>
                                                                    <input class="form-control col-sm-12" type="text" name="by" value="{{ $patient->getAssessmentAttr('by') }}">
                                                                    
                                                                </div>
                                                                <div class="form-group">
                                                                    <div class="form-check-inline">
                                                                        <label class="form-control-label mr-2 "><br>License No.</label>
                                                                        <input class="form-control col-sm-8" type="number" name="license_no" value="{{ $patient->getAssessmentAttr('license_no') }}">
                                                                    </div>
                                                                </div> 
                                                                <b class="font-italic">MEDICAL EXAMINER</b> 
                                                        </div>
                                                    </div>
                                                </div> {{--end /div tab7--}}
                                </fieldset>
{{----------------------------------------------------------------------------}}
                                    </div>{{--end of all tab content--}}
  {{------------------------------------------------------------------------}}                                  
                                </div> {{--end of tabs--}}
                            </div>
 {{--------------------------------------------------------------------------}}               
            
        </div>

    </div>
                              
@stop