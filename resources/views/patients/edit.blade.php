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
                    <a class="nav-link" href="#">Archive</a>
                </li>
            </ul>
        </div>
        <div class="card-header border-info">
            <ul class="nav nav-tabs card-header-tabs">
              
                <li class="nav-item">
                  <a class="nav-link active" href="{{ route('patients.index') }}">Edit Patient... 
                  <button type="button" class="close">&times; </button> </a>
                </li>
            </ul>
          </div>

        <div class="card-body">
            <div class="container px-10">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        </ul>
                    </div>
                @endif
                @if ($message = Session::get('success'))
                    <div class="alert alert-success alert-block">
                        <button type="button" class="close" data-dismiss="alert">×</button>	
                        <strong>{{ $message }}</strong>
                    </div>
                @endif

                <form action="{{ route('patients.update', $patient->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{--Profile--}}
                    <div class="form-group text-center">
                        <div class="col" style=" margin-top: 3%">
                            <img src="{{ $patient->avatar ?? '/img/no_avatar.jpg' }}"  alt="create_avatar" class="create_avatar "><br>{{--PRofile pic upload (Restrict user thaht only img/png file can be uploaded--}}
                        </div>
                        </div>
                            <div class="form-group text-center">
                            <div class="col"><br>
                            <input name="avatar" type="file" style="width: 30%" class="form-control-file border ml-auto mr-auto" accept="image/*">
                        </div>
                    </div> 
                    {{--end of profile--}}

                        <div class="container"><br>
                            {{--row1--}}
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="idnum">ID Number:</label>
                                        <input type="text" autocomplete="off" name="id_number" class="form-control" id="idnum" placeholder="ID Number *" value="{{ $patient->id_number }}" />
                                    </div>
                                    <div class="form-group">
                                        <label for="fname">First Name:</label>
                                        <input id="first_name" autocomplete="off" name="first_name" type="text" class="form-control" placeholder="First Name *" value="{{ $patient->first_name }}" />
                                    </div>
                                    <div class="form-group">
                                        <label for="mname">Middle Name:</label>
                                        <input id="middle_name" autocomplete="off" name="middle_name" type="text" class="form-control"  placeholder="Middle Name *" value="{{ $patient->middle_name }}" />
                                    </div>
                                    <div class="form-group">
                                        <label for="lname">Last Name:</label>
                                        <input id="last_name" autocomplete="off" name="last_name" type="text" class="form-control" placeholder="Last Name *" value="{{ $patient->last_name }}" />
                                    </div>
                                    <div class="form-group">
                                        <label for="hadd">Home Address:</label>
                                        <input id="home_address" autocomplete="off" name="home_address" type="text" class="form-control"  placeholder="Home Address *" value="{{ $patient->home_address }}" />
                                    </div>
                                    <div class="form-group">
                                        <label for="padd">Present Address:</label>
                                        <input id="present_address" autocomplete="off" name="present_address" type="text" class="form-control"  placeholder="Present Address *" value="{{ $patient->present_address }}" />
                                    </div>
                                    
                                </div>

                                <div class="col">
                                    <div class="form-group">
                                        <label class="mt-3">Gender:</label>
                                        <div class="form-check">
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" class="custom-control-input"  id="malegender" name="gender" {{ $patient->gender == 'male' ? 'checked' : '' }} value="male">
                                                <label class="custom-control-label" for="malegender">Male</label>
                                            </div>   
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" class="custom-control-input " id="femalegender" name="gender" {{ $patient->gender == 'female' ? 'checked' : '' }} value="female">
                                                <label class="custom-control-label" for="femalegender">Female</label>
                                                <br>
                                            </div> 
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="age">Age:</label>
                                        <input id="age" type="number" name="age" class="form-control" placeholder="Age *" value="{{ $patient->age }}" />
                                    </div>
                                    <div class="form-group">
                                        <label for="bdate">Birth Date:</label>
                                        <input id="bdate" type="date" class="form-control" name="birthdate" placeholder="Birth Date *" value="{{ $patient->birthdate }}" />
                                    </div>
                                    <div class="form-group">
                                        <label for="status">Civil Status:</label>
                                        <select name="status" id="status" 
                                            value="{{ $patient->status ? $patient->status : old('status') }}"
                                            class="form-control @error('status') is-invalid @enderror">
                                            <option class="hidden" @if(!$patient->status) selected @endif disabled>Civil Status</option>
                                                <option value="Single" @if($patient->status == 'Single') selected @endif>Single</option>
                                                <option value="Married" @if($patient->status == 'Married') selected @endif>Married</option>
                                                <option value="Widowed" @if($patient->status == 'Widowed') selected @endif>Widowed</option>
                                                <option value="Separated" @if($patient->status == 'Separated') selected @endif>Separated</option>
                                                <option value="In certain cases" @if($patient->tatus == 'In certain cases') selected @endif>In certain cases</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="cd">College/Department:</label>
                                        <select id="cd" class="form-control" name="college_department">
                                        <option class="hidden"  selected disabled>College/Department</option>
                                        @if(isset($patient->college_department))
                                        <option selected>{{ $patient->college_department }}</option>
                                        @endif
                                        <option>OPD/DEPENDENT</option>
                                        <option>SCHOOL OF GRADUATE STUDIES</option>
                                        <option>COLLEGE OF EDUCATION</option>
                                        <option>COLLEGE OF NURSING</option>
                                        <option>CSM GRADUATE PROGRAMS</option>
                                        <option>COLLEGE OF BUSINESS ADMINISTRATION AND ACCOUNTANCY</option>
                                        <option>COLLEGE OF SCIENCE AND MATHEMATICS</option>
                                        <option>COLLEGE OF ARTS AND SOCIAL SCIENCE</option>
                                        <option>SCHOOL OF ENGINEERING TECHNOLOGY</option>
                                        <option>COLLEGE OF ENGINEERING AND TECHNOLOGY</option>
                                        <option>COLLEGE OF COMPUTER STUDIES</option>
                                        <option>ACCOUNTING SECTION</option>
                                        <option>ADMISSION OFFICE</option>
                                        <option>BUDGET OFFICE</option>
                                        <option>CASHIERING SECTION</option>
                                        <option>CULTURAL DEVELOPMENT OFFICE</option>
                                        <option>DEPARTMENT OF STUDENTS AFFAIRS</option>
                                        <option>DEPARTMENT OF TECHNOLOGY APPLICATION AND PROMOTION</option>
                                        <option>GUIDANCE OFFICE</option>
                                        <option>HUMAN RESOURCE MNGT. DIVISION</option>
                                        <option>ICTC-COMPUTER FACILITIES ANS SUPPORT SERVICES/option>
                                        <option>INSTITUTE LIBRARY</option>
                                        <option>INSTITUTE SECRETARY</option>
                                        <option>INTEGRATED DEVELOPMENTAL SCHOOL</option>
                                        <option>INTERNAL AUDIT SERVICES UNIT</option>
                                        <option>MANILA INFORMATION AND LIAISON OFFICE</option>
                                        <option>MEDICAL SERVICES</option>
                                        <option>MSU-IIT CENTER FOR eLEARNING</option>
                                        <option>NSTP UNIT</option>
                                        <option>OFFICE FOR ALUMNI RELATIONS AND REPLACEMENT</option>
                                        <option>OFFICE OF THE BAC SECRETARIAT</option>
                                        <option>OFFICE OF THE CHANCELLOR</option>
                                        <option>OFFICE OF THE VICE CHANCELLOR</option>
                                        <option>OFFICE OF THE VICE CHANCELLOR FOR ADMINISTRATION AND FINANCE</option>
                                        <option>OFFICE OF THE VICE CHANCELLOR FOR PLANNING AND DEVELOPMENT</option>
                                        <option>OFFICE OF THE VICE CHANCELLOR FOR RESEARCH AND EXTENSION</option>
                                        <option>PHYSICAL PLANT DIVISION</option>
                                        <option>PROPERTY AND SUPPLY OFFICE</option>
                                        <option>PURCHASING OFFICE</option>
                                        <option>REGISTRAR OFFICE</option>
                                        <option>SPORTS DEVELOPMENT OFFICE</option>
                                        <option>N/A</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="phonenum">Phone Number:</label>
                                        <input id="phonenum" autocomplete="off" type="text"  class="form-control" name="phone_number" value="{{ $patient->phone_number }}" />
                                    </div>
                                    <div class="form-group">
                                    {{-- <label for="role">Role:</label> --}}
                                    {{-- <select id="role" class="form-control">
                                    <option class="hidden" name="role_id"  selected disabled>Role</option>
                                    <option>Student</option>
                                    <option>Faculty</option>
                                    <option>Staff</option>
                                    <option>OPD/Dependent</option>
                                    </select> --}}
                                    </div>
                                </div>               
                            </div>{{--end row1--}}


                            {{--row2--}}
                            <div class="row justify-content-center align-items-center">
                                <div class="form-group">
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

                                        <div class="tab-content" id="myTabContent">
                                            {{--Tab1--}}
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
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Breast mass/lump') ? 'checked' : '' }}  value="Breast mass/lump"> Breast mass/lump<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Chest Pains') ? 'checked' : '' }} value="Chest Pains"> Chest Pains<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Chicken Pox') ? 'checked' : '' }} value="Chicken Pox"> Chicken Pox<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Diabetes Mellitus') ? 'checked' : '' }} value="Diabetes Mellitus"> Diabetes Mellitus<br>
                                                                <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" {{ $patient->hasPastMedicalHistory('Epilepsy') ? 'checked' : '' }}  value="Epilepsy"> Epilepsy<br>
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
                                                                <input style= "margin-left: 2%" autocomplete="off" type="text" id="others" name="past_medical_history_others" value="{{ $patient->healthExaminationRecord->past_medical_history_others }}">
                                                                <br><br>       
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                        <div class="col text-center">
                                                            <b><br><br>For Women:<br> Last Menstrual Period(LMP) </b><br>
                                                            <input class="ml-auto mr-auto mt-2 form-control mb-5 col-sm-5" value="{{ $patient->getLastMenstrualPeriod() }}" 
                                                            type="date" name="last_menstrual_period">
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
                                                                <input style= "margin-left: 2%" type="text" name="family_history_others" value="{{ $patient->healthExaminationRecord->family_history_others }}">
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
                                                                    @if(isset($patient->getSocialHistory()['is_smoking']))
                                                                    <input type="radio" class="custom-control-input" id="choice1" name="is_smoking" {{ $patient->getSocialHistory()['is_smoking'] == 'No' ? 'checked' : '' }} value="No">
                                                                    @else
                                                                    <input type="radio" class="custom-control-input" id="choice1" name="is_smoking" value="No">
                                                                    @endif
                                                                    <label class="custom-control-label" for="choice1">No</label>
                                                                </div>
                                                            </div>
                                                            <div class="form-check">   
                                                                <div class="custom-control custom-radio ">
                                                                    @if(isset($patient->getSocialHistory()['is_smoking']))
                                                                    <input type="radio" class="custom-control-input" id="choice2" name="is_smoking" {{ $patient->getSocialHistory()['is_smoking'] == 'Yes' ? 'checked' : '' }} value="Yes">
                                                                    @else
                                                                    <input type="radio" class="custom-control-input" id="choice2" name="is_smoking" value="Yes">
                                                                    @endif
                                                                    <label class="custom-control-label" for="choice2">Yes</label>
                                                                    <br>
                                                                </div>
                                                            </div> 


                                                            <h6><br>If yes, how many packs?</h6>
                                                            <input class="form-control mb-3 col-sm-8" autocomplete="off" type="number" value="{{ $patient->getSocialHistory()['packs_smoked'] }}"  name="packs_smoked">
                                                            <h6> <br> 2. Do you drink alcohol (beer/liquer)? </h6>
                                                            <div class="form-check">
                                                                <div class="custom-control custom-radio ">
                                                                    @if(isset($patient->getSocialHistory()['is_drinking_beer']))
                                                                    <input type="radio" class="custom-control-input" id="choice1a" name="is_drinking_beer" {{ $patient->getSocialHistory()['is_drinking_beer'] == 'No' ? 'checked' : '' }} value="No">
                                                                    @else
                                                                    <input type="radio" class="custom-control-input" id="choice1a" name="is_drinking_beer" value="No">
                                                                    @endif
                                                                    <label class="custom-control-label" for="choice1a">No</label>
                                                                </div> 
                                                            </div>
                                                            <div class="form-check">
                                                                <div class="custom-control custom-radio ">
                                                                    @if(isset($patient->getSocialHistory()['is_drinking_beer']))
                                                                    <input type="radio" class="custom-control-input" id="choice2a" name="is_drinking_beer" {{ $patient->getSocialHistory()['is_drinking_beer'] == 'Yes' ? 'checked' : '' }} value="Yes">
                                                                    @else
                                                                    <input type="radio" class="custom-control-input" id="choice2a" name="is_drinking_beer" value="Yes">
                                                                    @endif
                                                                    <label class="custom-control-label" for="choice2a">Yes</label>
                                                                </div> 
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="sel1"><h6><br>If yes, how frequent?</h6></label>
                                                                <select class="form-control col-sm-8" id="sel1" name="drinking_frequency">
                                                                <option class="hidden"  selected disabled>Please Choose:</option>
                                                                @if(isset($patient->getSocialHistory()['drinking_frequency'])) 
                                                                <option {{ $patient->getSocialHistory()['drinking_frequency'] == 'Seldom' ? 'selected' : '' }}>Seldom</option>
                                                                <option {{ $patient->getSocialHistory()['drinking_frequency'] == 'Occasional' ? 'selected' : '' }}>Occasional</option>
                                                                @else
                                                                <option>Seldom</option>
                                                                <option>Occasional</option>
                                                                @endif
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>   

                                                    <div class="col text-left">
                                                        <div class="form-group">
                                                            <h6> <br> 3. Do you take medication at present? </h6>
                                                            <div class="form-check">
                                                                <div class="custom-control custom-radio ">
                                                                    @if(isset($patient->getSocialHistory()['is_taking_medication']))
                                                                    <input type="radio" class="custom-control-input" id="choice3a" name="is_taking_medication" {{ $patient->getSocialHistory()['is_taking_medication'] == 'No' ? 'checked' : '' }} value="No">
                                                                    @else
                                                                    <input type="radio" class="custom-control-input" id="choice3a" name="is_taking_medication" value="No">
                                                                    @endif
                                                                    <label class="custom-control-label" for="choice3a">No</label>
                                                                </div> 
                                                            </div>
                                                            <div class="form-check">
                                                                <div class="custom-control custom-radio ">
                                                                    @if(isset($patient->getSocialHistory()['is_taking_medication']))
                                                                    <input type="radio" class="custom-control-input" id="choice3b" name="is_taking_medication" {{ $patient->getSocialHistory()['is_taking_medication'] == 'Yes' ? 'checked' : '' }} value="Yes">
                                                                    @else
                                                                    <input type="radio" class="custom-control-input" id="choice3b" name="is_taking_medication" value="Yes">
                                                                    @endif
                                                                    <label class="custom-control-label" for="choice3b">Yes</label>
                                                                </div>
                                                            </div>
                                                            <h6> <br> If yes, please indicate below</h6>

                                                            @foreach($patient->getSocialHistory()['medications'] as $key => $medication)
                                                            {{ $key+1 }}. <input type="text" class="form-control col-sm-8" name="medications[]" autocomplete="off" value="{{ $medication }}"><br>
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
                                                                                <td><input type="radio" name="skin_status" {{  isset($patient->getPhysicalExamination()['skin_status']) ? ($patient->getPhysicalExamination()['skin_status'] == 'normal' ? 'checked' : '') : '' }} value="normal"></td>
                                                                                <td><input type="radio" name="skin_status" {{  isset($patient->getPhysicalExamination()['skin_status']) ? ($patient->getPhysicalExamination()['skin_status'] == 'abnormal' ? 'checked' : '') : '' }} value="abnormal"></td>
                                                                                <td><input class="form-control  col-sm-10" autocomplete="off" type="text" value="{{  isset($patient->getPhysicalExamination()['skin_remarks']) ? ($patient->getPhysicalExamination()['skin_remarks']) : '' }}" name="skin_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                            <td>Head / Neck / Scalp</td>
                                                                            <td><input  type="radio" name="head_status" {{ isset($patient->getPhysicalExamination()['head_status']) ? ($patient->getPhysicalExamination()['head_status'] == 'normal' ? 'checked' : '') : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="head_status" {{ isset($patient->getPhysicalExamination()['head_status']) ? ($patient->getPhysicalExamination()['head_status'] == 'abnormal' ? 'checked' : '') : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" value="{{ isset($patient->getPhysicalExamination()['head_remarks']) ? ($patient->getPhysicalExamination()['head_remarks']) : '' }}" name="head_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                            <td>Eyes</td>
                                                                            <td><input  type="radio" name="eyes_status" {{ isset($patient->getPhysicalExamination()['eyes_status']) ? ($patient->getPhysicalExamination()['eyes_status'] == 'normal' ? 'checked' : '') : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="eyes_status" {{ isset($patient->getPhysicalExamination()['eyes_status']) ? ($patient->getPhysicalExamination()['eyes_status'] == 'abnormal' ? 'checked' : '') : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" value="{{ isset($patient->getPhysicalExamination()['eyes_remarks']) ? ($patient->getPhysicalExamination()['eyes_remarks']) : '' }}" name="eyes_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                            <td>Ears/Nose/Throat</td>
                                                                            <td><input  type="radio" name="ears_status" {{ isset($patient->getPhysicalExamination()['ears_status']) ? ($patient->getPhysicalExamination()['ears_status'] == 'normal' ? 'checked' : '') : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="ears_status" {{ isset($patient->getPhysicalExamination()['ears_status']) ? ($patient->getPhysicalExamination()['ears_status'] == 'abnormal' ? 'checked' : '') : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" value="{{ isset($patient->getPhysicalExamination()['ears_remarks']) ? ($patient->getPhysicalExamination()['ears_remarks']) : '' }}" name="ears_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                            <td>Nose/Sinuses</td>
                                                                            <td><input  type="radio" name="nose_status" {{ $patient->getPEAttr('nose_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="nose_status" {{ $patient->getPEAttr('nose_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" value="{{ $patient->getPEAttr('nose_remarks') }}" name="nose_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                            <td>Mouth/Throat</td>
                                                                            <td><input  type="radio" name="mouth_status" {{ $patient->getPEAttr('mouth_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="mouth_status" {{ $patient->getPEAttr('mouth_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" value="{{ $patient->getPEAttr('mouth_remarks') }}" name="mouth_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                            <td>Neck, LN, Thyroid</td>
                                                                            <td><input  type="radio" name="neck_status" {{ $patient->getPEAttr('neck_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="neck_status" {{ $patient->getPEAttr('neck_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" value="{{ $patient->getPEAttr('neck_remarks') }}" name="neck_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                            <td>Chest-Breast-Axilla</td>
                                                                            <td><input  type="radio" name="chest_status" {{ $patient->getPEAttr('chest_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="chest_status" {{ $patient->getPEAttr('chest_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" value="{{ $patient->getPEAttr('chest_rearks') }}" name="chest_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                            <td>Lungs</td>
                                                                            <td><input type="radio" name="lungs_status" {{ $patient->getPEAttr('lungs_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="lungs_status" {{ $patient->getPEAttr('lungs_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" value="{{ $patient->getPEAttr('lungs_remarks') }}" name="lungs_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                            <td>Heart</td>
                                                                            <td><input  type="radio" name="heart_status" {{ $patient->getPEAttr('heart_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="heart_status" {{ $patient->getPEAttr('heart_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" value="{{ $patient->getPEAttr('heart_remarks') }}" name="heart_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                            <td>Abdomen</td>
                                                                            <td><input  type="radio" name="abdomen_status" {{ $patient->getPEAttr('abdomen_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="abdomen_status" {{ $patient->getPEAttr('abdomen_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" value="{{ $patient->getPEAttr('abdomen_remarks') }}" name="abdomen_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                            <td>Back, Flank</td>
                                                                            <td><input  type="radio" name="back_status" {{ $patient->getPEAttr('back_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="back_status" {{ $patient->getPEAttr('back_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" value="{{ $patient->getPEAttr('back_remarks') }}" name="back_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                            <td>Anus-rectum</td>
                                                                            <td><input  type="radio" name="anus_status" {{ $patient->getPEAttr('anus_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="anus_status" {{ $patient->getPEAttr('anus_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" value="{{ $patient->getPEAttr('anus_remarks') }}" name="anus_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                            <td>GU system</td>
                                                                            <td><input  type="radio" name="gu_system_status" {{ $patient->getPEAttr('gu_system_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="gu_system_status" {{ $patient->getPEAttr('gu_system_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" value="{{ $patient->getPEAttr('gu_system_remarks') }}" name="gu_system_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                            <td>Inguinals, Genitals</td>
                                                                            <td><input  type="radio" name="genitals_status" {{ $patient->getPEAttr('genitals_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="genitals_status" {{ $patient->getPEAttr('genitals_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" value="{{ $patient->getPEAttr('genitals_remarks') }}" name="genitals_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                            <td>Reflexes</td>
                                                                            <td><input  type="radio" name="reflexes_status" {{ $patient->getPEAttr('reflexes_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="reflexes_status" {{ $patient->getPEAttr('reflexes_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" value="{{ $patient->getPEAttr('reflexes_remarks') }}" name="reflexes_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                            <td>Extremities</td>
                                                                            <td><input type="radio" name="extremities_status" {{ $patient->getPEAttr('extremities_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="extremities_status" {{ $patient->getPEAttr('extremities_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" value="{{ $patient->getPEAttr('extremities_remarks') }}" name="extremities_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                            <td>Neurologic</td>
                                                                            <td><input  type="radio" name="neurologic_status" {{ $patient->getPEAttr('neurologic_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="neurologic_status" {{ $patient->getPEAttr('neurologic_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" value="{{ $patient->getPEAttr('neurologic_remarks') }}" name="neurologic_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                            <td>Endocrine</td>
                                                                            <td><input  type="radio" name="endocrine_status" {{ $patient->getPEAttr('endocrine_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="endocrine_status" {{ $patient->getPEAttr('endocrine_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" value="{{ $patient->getPEAttr('endocrine_remarks') }}" name="endocrine_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                            <td>Others</td>
                                                                            <td><input  type="radio" name="others_status" {{ $patient->getPEAttr('others_status') == 'normal' ? 'checked' : '' }} value="normal"></td>
                                                                            <td><input type="radio" name="others_status" {{ $patient->getPEAttr('others_status') == 'abnormal' ? 'checked' : '' }} value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" value="{{ $patient->getPEAttr('others_remarks') }}" name="others_remarks" value=""></td>
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
                                                        <input type="number" autocomplete="off" value="{{ $patient->getVitalSignAttr('temperature') }}" class="form-control mb-3 col-sm-3 mt-3" name="temperature" id="temp" value="">
                                                        <label class="form-control-label ml-1 mr-2"><i>°C</i></label>
                                                        <label for="pulse_rate" class="col-sm-2 ml-5 ">Pulse Rate :</label> 
                                                        <input type="number" autocomplete="off" value="{{ $patient->getVitalSignAttr('pulse_rate') }}" class="form-control mb-3 ml-2 col-sm-3 mt-3" name="pulse_rate" id="pulse_rate" value="">
                                                        <label class="form-control-label ml-1 mr-2"><i>bpm</i></label>
                                                    </div>
                                                </div>
                                                <div class="row justify-content-center ">
                                                    <div class="form-inline ">
                                                        <label for="r_r" class="mr-sm-2">Respiratory Rate:</label>
                                                        <input type="number" autocomplete="off" value="{{ $patient->getVitalSignAttr('respiratory_rate') }}" class="form-control mb-3 col-sm-3 mt-3" name="respiratory_rate" id="r_r" value="">
                                                        <label class="form-control-label ml-1 mr-2"><i>bpm</i></label>
                                                        <label for="bp" class="ml-sm-5 ">Blood Pressure :</label> 
                                                        <input type="number" autocomplete="off" value="{{ $patient->getVitalSignAttr('blood_pressure') }}" class="form-control mb-3 ml-2 col-sm-3 mt-3" name="blood_pressure" id="bp" value="">
                                                        <label class="form-control-label ml-1 mr-2"><i>mmhg</i></label>
                                                    </div>
                                                </div>

                                                <div class="row justify-content-center ">
                                                    <div class="form-inline">
                                                        <label for="weight" class="mr-sm-2 mt-4 mb-4"> Weight : </label>
                                                        <input type="number" autocomplete="off" value="{{ $patient->getVitalSignAttr('weight') }}" class="form-control col-sm-5 mt-4 mb-4" name="weight" id="weight" value="">
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
                                                                @foreach($patient->getNursingInterventions() as $key => $value)
                                                                <tr>
                                                                    <td><input class="form-control col-sm-15 mb-2 mt-2" autocomplete="off" type="text" name="nursing_interventions[{{ $key }}][intervention]" value="{{ $value['intervention'] }}"></td>
                                                                    <td><input class="form-control col-sm-14 mb-2 ml-1 mt-2" type="time" name="nursing_interventions[{{ $key }}][time]" value="{{ $value['time'] }}"></td>
                                                                    <td><input class="form-control col-sm-14 mb-2 ml-2 mt-2" autocomplete="off" type="text" name="nursing_interventions[{{ $key }}][by]" value="{{ $value['by'] }}"></td>
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
                                                    <textarea class="form-control" autocomplete="off" name="physically_fit_description">{{ $patient->getAssessmentAttr('physically_fit_description') }}</textarea><br>
                                                </div>
                                                <label >Date of Examination: </label>
                                                <input class="form-control" type="date" name="date_examined" value="{{ $patient->getAssessmentAttr('date_examined') }}">
                                                
                                                
                                                
                                                <br><br>
                                                <div class="form-check-inline">
                                                    <label class="form-control-label mr-2 mb-2">Added By: </label>
                                                    <input class="form-control col-sm-12" autocomplete="off" type="text"  value="{{ $patient->getAssessmentAttr('by') }}" readonly/>
                                                </div>
                                                <div class="form-group">
                                                    <div class="form-check-inline">
                                                        <label class="form-control-label mr-2 "><br>License No.</label>
                                                        <input class="form-control col-sm-8" autocomplete="off" type="number"  value="{{ $patient->getAssessmentAttr('license_no') }}" readonly/>
                                                    </div>
                                                </div> 
                                                <div class="form-check-inline">
                                                    <label class="form-control-label mr-2 mb-2">To be Updated By: </label>
                                                    <input class="form-control col-sm-12" autocomplete="off" type="text" name="by" value="{{ Auth::user()->first_name . " " . Auth::user()->last_name }}" readonly/>
                                                </div>
                                                <div class="form-group">
                                                    <div class="form-check-inline">
                                                        <label class="form-control-label mr-2 "><br>License No.</label>
                                                        <input class="form-control col-sm-8" autocomplete="off" type="number" name="license_no" value="{{ Auth::user()->license_number }}" readonly/>
                                                    </div>
                                                </div> 
                                                
                                                <div class="row justify-content-center">
                                                    <br>
                                                <b class="font-italic">MEDICAL EXAMINER</b> 
                                                </div>
                                                </div>
                                                </div>
                                                </div> {{--end /div tab7--}}
                                        </div>{{--end of tab content--}}
                                    </div>
                                </div>
                            </div> {{--end of second row--}}

                            <div class="mx-auto" style="width: 200px;">
                                <br>
                                <button  type ="submit" class = "btn btn-info">Save</button>
                                <a href="/patients" type ="button" class = "btn btn-secondary">Cancel</button></a>
                            </div><br>
                            </div>
                </form>
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
@stop