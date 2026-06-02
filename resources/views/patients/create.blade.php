@extends('layouts.app')


@section('content')
    

 <div class="card border-info">
        <div class="card-header border-info">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                <a class="nav-link" href="{{ route('patients.index') }}">Patients</a>
                </li>
                <li class="nav-item">
                <a class="nav-link active" href="{{ route('patients.create') }}">Add New Patient</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('patients.archive') }}">Archive</a>
                </li>
            </ul>
         </div>

    {{--BOdy--}}
    <div class="card-body">
        @if ($message = Session::get('success'))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>	
                <strong>{{ $message }}</strong>
            </div>
         @endif
        <form action="{{ route('patients.store') }}" method="POST" enctype="multipart/form-data">
            <div class="form-group text-center">
                <div class="col" style=" margin-top: 3%">
                    <img src="/img/no_avatar.jpg"  alt="create_avatar" class="create_avatar "><br>{{--PRofile pic upload (Restrict user thaht only img/png file can be uploaded--}}
                </div>
            </div>
            <div class="form-group text-center">
                <div class="col"><br>
                <input name="avatar" type="file" style="width: 30%" class="form-control-file border ml-auto mr-auto" accept="image/*">
                </div>
            </div>
                
                <div class="container"><br>

                        
                                    {{--1--}} 
                                <div class="row " >
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="idnum">ID Number:</label>
                                            <input name="id_number" autocomplete="off" type="text" class="form-control" placeholder="ID Number *" id="idnum">
                                        </div>
                                        <div class="form-group">
                                            <label for="fname">First Name:</label>
                                            <input name="first_name" autocomplete="off" type="text" class="form-control" placeholder="First Name *" id="fname">
                                        </div>
                                        <div class="form-group">
                                            <label for="mname">Middle Name:</label>
                                            <input name="middle_name" autocomplete="off" type="text" class="form-control"  placeholder="Middle Name *" id="mname" />
                                        </div>
                                        <div class="form-group">
                                            <label for="lname">Last Name:</label>
                                            <input name="last_name" autocomplete="off" type="text" class="form-control" placeholder="Last Name *" id="lname">
                                        </div>
                                        <div class="form-group">
                                            <label for="hadd">Home Address:</label>
                                            <input name="home_address" autocomplete="off" type="text" class="form-control"  placeholder="Home Address *" id="hadd" />
                                        </div>
                                        <div class="form-group">
                                            <label for="padd">Present Address:</label>
                                            <input name="present_address" autocomplete="off" type="text" class="form-control"  placeholder="Present Address *" id="padd" />
                                        </div>
                                        
                                    </div>

                                    <div class="col">
                                        <div class="form-group">
                                            <label class="mt-3">Gender:</label>
                                            <div class="form-check">

                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" class="custom-control-input"  id="malegender" name="gender" value="male">
                                                    <label class="custom-control-label" for="malegender">Male</label>
                                                  </div>   
                                                  <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" class="custom-control-input mt-4 " id="femalegender" name="gender" value="female">
                                                    <label class="custom-control-label" for="femalegender">Female</label>
                                                    <br>
                                                </div> 
                                                
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="age">Age:</label>
                                            <input name="age" type="number" class="form-control" placeholder="Age *" id="age" value="" />
                                        </div>
                                        <div class="form-group">
                                            <label for="bdate">Birth Date:</label>
                                            <input name="birthdate" type="date" class="form-control" placeholder="Birth Date *" id="bdate" value="" />
                                        </div>
                                        <div class="form-group">
                                            <label for="status">Civil Status:</label>
                                            <select name="status" class="form-control" id="status">
                                                <option class="hidden"  selected disabled>Civil Status</option>
                                                <option>Single</option>
                                                <option>Married</option>
                                                <option>Widowed</option>
                                                <option>Separated</option>
                                                <option>In certain cases</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="cd">College/Department:</label>
                                                <select name="college_department" class="form-control" id="cd">
                                                    <option class="hidden"  selected disabled>Chose College/Department</option>
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
                                            <input name="phone_number" autocomplete="off" type="number" minlength="10" maxlength="13" id="phonenum" class="form-control" placeholder="Phone Number *" />
                                        </div>
                                        
                                
                                        {{-- <div class="form-group">
                                            <label for="role">Role:</label>
                                            <select class="form-control" id="role">
                                                <option class="hidden"  selected disabled>Role</option>
                                                <option>Student</option>
                                                <option>Faculty</option>
                                                <option>Staff</option>
                                                <option>OPD/Dependent</option>
                                            </select>
                                        </div> --}}
                                    </div>
                                </div> {{--end /div 1 row--}}

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
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Allergies"> Allergies<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Amoebiasis"> Amoebiasis<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Anemia"> Anemia<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Arthritis"> Arthritis<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Back and Joints Pains"> Back and Joints Pains<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Bone Fracture"> Bone Fracture<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Breast mass/lump"> Breast mass/lump<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Chest Pains"> Chest Pains<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Chicken Pox"> Chicken Pox<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Diabetes Mellitus"> Diabetes Mellitus<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Epilepsy"> Epilepsy<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Eye or Ear Problem"> Eye or Ear Problem<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Gallbladder Stone"> Gallbladder Stone<br>
                                                                    </div>   
                                                                </div>
                                                            </div>
                                                            <div class="col text-left">
                                                                <div class="form-group">
                                                                    <div class="form-check">
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Goiter"> Goiter<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Gout"> Gout<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Hemorrhoids"> Hemorrhoids<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Hepatitis: A/B/C"> Hepatitis: A/B/C<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Hyperacidity/Ulcer"> Hyperacidity/Ulcer<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Hypertension"> Hypertension<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Kidney/Bladder Stones"> Kidney/Bladder Stones<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Loss of Conciousness"> Loss of Conciousness<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Measles"> Measles<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Mumps"> Mumps<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Pneumonia"> Pneumonia<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Prostate Problems"> Prostate Problems<br>
                                                                        <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Seizure"> Seizure<br>
                                                                    </div>
                                                                </div>
                                                            </div>    
                                                        <div class="col text-left">
                                                            <div class="form-group">
                                                                <div class="form-check">
                                                                    <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Sinusitis/Allergic rhinitis"> Sinusitis/Allergic rhinitis<br>
                                                                    <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Skin Disorders"> Skin Disorders<br>
                                                                    <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="STI/HIV"> STI/HIV<br>
                                                                    <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Stroke"> Stroke<br>
                                                                    <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Surgery/Injury"> Surgery/Injury<br>
                                                                    <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Thyroid Problems"> Thyroid Problems<br>
                                                                    <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Tonsillitis"> Tonsillitis<br>
                                                                    <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="Tuberculosis"> Tuberculosis<br>
                                                                    <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="UTI"> UTI<br>
                                                                    <input style= "margin-left: 2%" type="checkbox" name="pastmedical_history[]" value="others"> Others: 
                                                                    <input style= "margin-left: 2%" autocomplete="off" type="text" name="past_medical_history_others" value="">
                                                                    <br><br>    
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                
                                                    <div class="row">
                                                        <div class="col text-center">
                                                            <b><br><br>For Women:<br> Last Menstrual Period(LMP) </b><br><input class="ml-auto mr-auto mt-2 form-control mb-5 col-sm-5" type="date" name="last_menstrual_period">
                                                        </div>
                                                        <div class="col text-center">
                                                            <h6><br><br><br><b>Menstrual Pattern:</b><h6>
                                                                <div class="custom-control custom-radio custom-control-inline">
                                                                    <input type="radio" class="custom-control-input" id="mp1" name="menstrual_pattern" value="regular">
                                                                    <label class="custom-control-label mt-2" for="mp1">Regular</label>
                                                                  </div>   
                                                                  <div class="custom-control custom-radio custom-control-inline">
                                                                    <input type="radio" class="custom-control-input" id="mp2" name="menstrual_pattern" value="irregular">
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
                                                            
                                                                            <input style= "margin-left: 2%" type="checkbox" name="family_history[]" value="High Blood Pressure"> High Blood Pressure<br>
                                                                            <input style= "margin-left: 2%" type="checkbox" name="family_history[]" value="Tuberculosis"> Tuberculosis<br>
                                                                            <input style= "margin-left: 2%" type="checkbox" name="family_history[]" value="Heart Disease"> Heart Disease<br>
                                                                            <input style= "margin-left: 2%" type="checkbox" name="family_history[]" value="Asthma"> Asthma<br>
                                                                        <br>
                                                                </div> 
                                                            </div>       
                                                        </div>
                                                        <div class="col text-left">
                                                            <div class="form-group">
                                                                <div class="form-check">
                                                                    <br><br><br>
                                                                    <input style= "margin-left: 2%" type="checkbox" name="family_history[]" value="Diabetes"> Diabetes<br>
                                                                    <input style= "margin-left: 2%" type="checkbox" name="family_history[]" value="Allergies"> Allergies<br>
                                                                    <input style= "margin-left: 2%" type="checkbox" name="family_history[]" value="Cancer"> Cancer<br>
                                                                    <input style= "margin-left: 2%" type="checkbox" name="family_history[]" value="Others"> Other:
                                                                    <input style= "margin-left: 2%" type="text" name="family_history_others">
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
                                                                        <input type="radio" class="custom-control-input" id="choice1" name="is_smoking" value="No">
                                                                        <label class="custom-control-label" for="choice1">No</label>
                                                                      </div>
                                                                </div>
                                                                <div class="form-check">   
                                                                    <div class="custom-control custom-radio ">
                                                                        <input style= "margin-left: 9%"type="radio" class="custom-control-input" id="choice2" name="is_smoking" value="Yes">
                                                                        <label class="custom-control-label" for="choice2">Yes</label>
                                                                        <br>
                                                                     </div>
                                                                </div> 
                                                            
                                                                
                                                                <h6><br>If yes, how many packs?</h6>
                                                                <input class="form-control mb-3 col-sm-8" autocomplete="off" type="number" name="packs_smoked">
                                                                <h6> <br> 2. Do you drink alcohol (beer/liquer)? </h6>
                                                                <div class="form-check">
                                                                    <div class="custom-control custom-radio ">
                                                                        <input type="radio" class="custom-control-input" id="choice1a" name="is_drinking_beer" value="No">
                                                                        <label class="custom-control-label" for="choice1a">No</label>
                                                                    </div> 
                                                                </div>
                                                                <div class="form-check">
                                                                    <div class="custom-control custom-radio ">
                                                                        <input type="radio" class="custom-control-input" id="choice2a" name="is_drinking_beer" value="Yes">
                                                                        <label class="custom-control-label" for="choice2a">Yes</label>
                                                                    </div> 
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="sel1"><h6><br>If yes, how frequent?</h6></label>
                                                                    <select class="form-control col-sm-8" id="sel1" name="drinking_frequency">
                                                                    <option class="hidden"  selected disabled>Please Choose:</option>
                                                                      <option>Seldom</option>
                                                                      <option>Occasional</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>   

                                                        <div class="col text-left">
                                                            <div class="form-group">
                                                                 <h6> <br> 3. Do you take medication at present? </h6>
                                                                <div class="form-check">
                                                                    <div class="custom-control custom-radio ">
                                                                        <input type="radio" class="custom-control-input" id="choice3a" name="is_taking_medication" value="No">
                                                                        <label class="custom-control-label" for="choice3a">No</label>
                                                                    </div> 
                                                                </div>
                                                                    <div class="form-check">
                                                                        <div class="custom-control custom-radio ">
                                                                            <input type="radio" class="custom-control-input" id="choice3b" name="is_taking_medication" value="Yes">
                                                                            <label class="custom-control-label" for="choice3b">Yes</label>
                                                                        </div>
                                                                    </div>
                                                                    <h6> <br> If yes, please indicate below</h6>
                                                                    <div class="form-check-inline">
                                                                    <label for="if_yes1" class="mr-2"> 1.</label>
                                                                    <input id="if_yes1" class="form-control mb-3 col-sm-15" autocomplete="off" type="text" name="medications[]"><br>
                                                                    </div>
                                                                    <div class="form-check-inline">
                                                                        <label for="if_yes2" class="mr-2"> 2.</label>
                                                                        <input id="if_yes2" class="form-control mb-3 col-sm-15" autocomplete="off" type="text" name="medications[]"><br>
                                                                    </div>
                                                                    <div class="form-check-inline">
                                                                        <label for="if_yes3" class="mr-2"> 3.</label>
                                                                        <input id="if_yes3" class="form-control mb-3 col-sm-15" autocomplete="off" type="text" name="medications[]"><br>
                                                                    </div>
                                                                   
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
                                                                            <td><input type="radio" name="skin_status" value="normal"></td>
                                                                            <td><input type="radio" name="skin_status" value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" name="skin_remarks"></td>
                                                                            </td>
                                                                            
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Head / Neck / Scalp</td>
                                                                            <td><input  type="radio" name="head_status" value="normal"></td>
                                                                            <td><input type="radio" name="head_status" value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" name="head_remarks"></td>
                                                                            </td>
                                                                            
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Eyes</td>
                                                                            <td><input  type="radio" name="eyes_status" value="normal"></td>
                                                                            <td><input type="radio" name="eyes_status" value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" name="eyes_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Ears/Nose/Throat</td>
                                                                            <td><input  type="radio" name="ears_status" value="normal"></td>
                                                                            <td><input type="radio" name="ears_status" value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" name="ears_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Nose/Sinuses</td>
                                                                            <td><input  type="radio" name="nose_status" value="normal"></td>
                                                                            <td><input type="radio" name="nose_status" value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" name="nose_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Mouth/Throat</td>
                                                                            <td><input  type="radio" name="mouth_status" value="normal"></td>
                                                                            <td><input type="radio" name="mouth_status" value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" name="mouth_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Neck, LN, Thyroid</td>
                                                                            <td><input  type="radio" name="neck_status" value="normal"></td>
                                                                            <td><input type="radio" name="neck_status" value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" name="neck_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Chest-Breast-Axilla</td>
                                                                            <td><input  type="radio" name="chest_status" value="normal"></td>
                                                                            <td><input type="radio" name="chest_status" value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" name="chest_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Lungs</td>
                                                                            <td><input  type="radio" name="lungs_status" value="normal"></td>
                                                                            <td><input type="radio" name="lungs_status" value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" name="lungs_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Heart</td>
                                                                            <td><input  type="radio" name="heart_status" value="normal"></td>
                                                                            <td><input type="radio" name="heart_status" value="0"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" name="heart_remarks"></td>
                                                                            </td>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Abdomen</td>
                                                                            <td><input  type="radio" name="abdomen_status" value="normal"></td>
                                                                            <td><input type="radio" name="abdomen_status" value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" name="abdomen_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Back, Flank</td>
                                                                            <td><input  type="radio" name="back_status" value="normal"></td>
                                                                            <td><input type="radio" name="back_status" value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" name="back_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Anus-rectum</td>
                                                                            <td><input  type="radio" name="anus_status" value="normal"></td>
                                                                            <td><input type="radio" name="anus_status" value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" name="anus_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>GU system</td>
                                                                            <td><input  type="radio" name="gu_system_status" value="normal"></td>
                                                                            <td><input type="radio" name="gu_system_status" value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" name="gu_system_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Inguinals, Genitals</td>
                                                                            <td><input  type="radio" name="genitals_status" value="normal"></td>
                                                                            <td><input type="radio" name="genitals_status" value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" name="genitals_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Reflexes</td>
                                                                            <td><input  type="radio" name="reflexes_status" value="normal"></td>
                                                                            <td><input type="radio" name="reflexes_status" value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" name="reflexes_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Extremities</td>
                                                                            <td><input type="radio" name="extremities_status" value="normal"></td>
                                                                            <td><input type="radio" name="extremities_status" value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" name="extremities_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Neurologic</td>
                                                                            <td><input  type="radio" name="neurologic_status" value="normal"></td>
                                                                            <td><input type="radio" name="neurologic_status" value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" name="neurologic_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Endocrine</td>
                                                                            <td><input  type="radio" name="endocrine_status" value="normal"></td>
                                                                            <td><input type="radio" name="endocrine_status" value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" name="endocrine_remarks"></td>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <td>Others</td>
                                                                            <td><input  type="radio" name="others_status" value="normal"></td>
                                                                            <td><input type="radio" name="others_status" value="abnormal"></td>
                                                                            <td><input class="form-control  col-sm-10" autocomplete="off" type="text" name="others_remarks" value=""></td>
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
                                                                <input type="number" autocomplete="off" class="form-control mb-3 col-sm-3 mt-3" name="temperature" id="temp" value="">
                                                                <label class="form-control-label ml-1 mr-2"><i>°C</i></label>
                                                                <label for="pulse_rate"  class="col-sm-2 ml-5 ">Pulse Rate :</label> 
                                                                <input type="text" autocomplete="off" class="form-control mb-3 ml-2 col-sm-3 mt-3" name="pulse_rate" id="pulse_rate" value="">
                                                                <label class="form-control-label ml-1 mr-2"><i>bpm</i></label>
                                                        </div>
                                                    </div>
                                                    <div class="row justify-content-center ">
                                                        <div class="form-inline ">
                                                            
                                                                <label for="r_r" class="mr-sm-2">Respiratory Rate:</label>
                                                                <input type="text" autocomplete="off" class="form-control mb-3 col-sm-3 mt-3" name="respiratory_rate" id="r_r" value="">
                                                                <label class="form-control-label ml-1 mr-2"><i>bpm</i></label>
                                                                <label for="bp" class="ml-sm-5 ">Blood Pressure :</label> 
                                                                <input type="text" autocomplete="off" class="form-control mb-3 ml-2 col-sm-3 mt-3" name="blood_pressure" id="bp" value="">
                                                                <label class="form-control-label ml-1 mr-2"><i>mmhg</i></label>
                                                        </div>
                                                    </div>

                                                    <div class="row justify-content-center ">
                                                        <div class="form-inline">
                                                            <label for="weight" class="mr-sm-2 mt-4 mb-4"> Weight : </label>
                                                            <input type="number" autocomplete="off" class="form-control col-sm-5 mt-4 mb-4" name="weight" id="weight" value="">
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
                                                                    @php
                                                                        $nursingIntervention = [
                                                                            [
                                                                                'intervention' => null,
                                                                                'time' => null,
                                                                                'by' => null,
                                                                            ]
                                                                        ];
                                                                    @endphp
                                                                    <tr>
                                                                        <td><input autocomplete="off" class="form-control col-sm-15 mb-2 mt-2" type="text" name="nursing_interventions[0][intervention]" value=""></td>
                                                                        <td><input class="form-control col-sm-14 mb-2 ml-1 mt-2" type="time" name="nursing_interventions[0][time]" value=""></td>
                                                                        <td><input autocomplete="off" class="form-control col-sm-14 mb-2 ml-2 mt-2" type="text" name="nursing_interventions[0][by]" value=""></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><input autocomplete="off" class="form-control col-sm-15 mb-2" type="text" name="nursing_interventions[1][intervention]" value=""></td>
                                                                        <td><input class="form-control col-sm-14 mb-2 ml-1" type="time" name="nursing_interventions[1][time]" value=""></td>
                                                                        <td><input autocomplete="off" class="form-control col-sm-14 mb-2 ml-2" type="text" name="nursing_interventions[1][by]" value=""></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><input autocomplete="off" class="form-control col-sm-15 mb-2" type="text" name="nursing_interventions[2][intervention]" value=""></td>
                                                                        <td><input class="form-control col-sm-14 mb-2 ml-1" type="time" name="nursing_interventions[2][time]" value=""></td>
                                                                        <td><input autocomplete="off" class="form-control col-sm-14 mb-2 ml-2" type="text" name="nursing_interventions[2][by]" value=""></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><input autocomplete="off" class="form-control col-sm-15 mb-2" type="text" name="nursing_interventions[3][intervention]" value=""></td>
                                                                        <td><input class="form-control col-sm-14 mb-2 ml-1" type="time" name="nursing_interventions[3][time]" value=""></td>
                                                                        <td><input autocomplete="off" class="form-control col-sm-14 mb-2 ml-2" type="text" name="nursing_interventions[3][by]" value=""></td>
                                                                    </tr>
                                                                    
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
                                                                <input type="radio" class="custom-control-input mb-2" id="customRadio" name="physically_fit" value="Yes">
                                                                <label class="custom-control-label mb-3" for="customRadio">Physically Fit</label>
                                                              </div>   
                                                              <div class="custom-control custom-radio custom-control-inline">
                                                                <input type="radio" class="custom-control-input" id="customRadio1" name="physically_fit" value="No">
                                                                <label class="custom-control-label" for="customRadio1">Not Physically Fit</label>
                                                                <br>
                                                            </div> 
                                                                <div class="form-group">
                                                                    <label >Comment: </label>
                                                                    <textarea class="form-control" name="physically_fit_description" value=""></textarea><br>
                                                                </div>
                                                                <label >Date of Examination: </label>
                                                                <input class="form-control" type="date" name="date_examined" value="">
                                                                
                                                       
            
                                                                <br><br>
                                                                <div class="form-check-inline">
                                                                    <label class="form-control-label mr-2 mb-2">Added By: </label>
                                                                <input class="form-control col-sm-10" autocomplete="off" type="text" name="by" value="{{ Auth::user()->first_name . " " . Auth::user()->last_name }}" readonly/>
                                                                </div>
                                                                <div class="form-group">
                                                                    <div class="form-check-inline">
                                                                        <label class="form-control-label mr-2 "><br>License No.</label>
                                                                        <input class="form-control col-sm-8" type="number" autocomplete="off" name="license_no" value="{{ Auth::user()->license_number }}" readonly/>
                                                                    </div>
                                                                </div> 
                                                                <b class="font-italic">MEDICAL EXAMINER</b> 
                                                        </div>
                                                    </div>
                                                </div> {{--end /div tab7--}}

{{-------------------------------------------------------------------------------------------------}}

                                            </div>{{--end of tab content--}}

{{------------------------------------------------------------------------------------------}}                                           
                                        </div>
                                    </div>
                                </div> {{--end of second row--}}
{{--------------------------------------------------------------------------------------------}}



                                
                                @csrf
                                <div class="mx-auto" style="width: 200px;">
                                    <br><button  type ="submit" class = "btn btn-info">Register</button>
                                    <a href="/patients" type ="button" class = "btn btn-secondary">Cancel</button></a>
                                </div><br>
                            
                    
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
@stop