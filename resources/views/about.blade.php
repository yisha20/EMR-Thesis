@extends('layouts.app')

@section('content')
<style>
body,h1,h2,h3,h4,h5,h6 {font-family: "Lato", sans-serif;}
body, html {
  height: 2500px;
  color: black;
  line-height: 1.8;  background-color: #cccccc;
  background-image: linear-gradient(white,#7FEBFE);

}
h2{
padding-left: 35px;

}

a{

text-align: justify;

}

ul{

  text-align: center;

}
/* Create a Parallax Effect */
.bgimg-1{
  background-attachment: fixed;
  background-position: center;
  background-repeat: no-repeat;
  background-size: cover;
}

/*  Gradient (Title. Full height) */
.bgimg-1 {
  background-image: linear-gradient(#AED6F1,#3498DB);
  min-height: 100%;
}

.w3-wide {letter-spacing: 10px;}
.w3-hover-opacity {cursor: pointer;}

/* Turn off parallax scrolling for tablets and phones */
@media only screen and (max-device-width: 1600px) {
  .bgimg-1{
    background-attachment: scroll;
    min-height: 400px;
  }
}
</style>

<!-- First Parallax Gradient Image Text -->
<div class="bgimg-1 w3-display-container w3-opacity-min">
  <div class="w3-display-middle" style="white-space:nowrap;">
    <span class="w3-center w3-padding-large w3-black w3-xlarge w3-wide w3-animate-opacity">ELECTRONIC <span class="w3-hide-small">MEDICAL</span> RECORDS</span>
  </div>
</div>

<!--<div class="">
    <br><br>
      <img src="img/2.jpg" class="w3-round w3-image" alt="Photo" width="2000" height="1000">
</div> -->

<!-- Container (About Section) -->
<div class="w3-padding-64 w3-center">
  <br><br><br>

  <h3 class="w3-center">MINDANAO STATE UNIVERSITY <br> Iligan Institute of Technology</h3>
  <p class="w3-center"><em>Institute Clinic</em></p>
  <p>As the Mindanao State University-Iligan Institute of Technology aims for the holistic <br> development of the individual, it deems important the health and overall wellbeing of <br> students and employees alike. MSU-IIT constituents can be assured that their medical <br> and health needs are met all throughout their stay in the campus, even on medical <br> emergency situations.<br><br>
The Office of Medical, Dental, and Health Services is responsible for ensuring the <br> provision of medical and dental and other health services to the constituents of the <br> Campus; and establishing and maintaining good public relations and linkages with <br> allied medical agencies in the immediate communities of the Campus.<br><br>
The Office oversees the Institute Clinic which provides fast and competent medical <br> and health care to its clientele.<br><br></p>
  <div class="w3-row">
    <div class="w3-col m6 w3-center w3-padding-large">
    <br><br>
      <img src="img/d1.png" class="w3-round w3-image" alt="Photo" width="500" height="500" padding-bottom: 500px;  />
      <p><b><i class="fa fa-user w3-margin-right"></i>MUHAMMAD M. PUTING, M.D.<br>Chief Administrative Officer Medical and Health Services Division</b></p><br>
    </div>

<br><br>
    <!-- Hide this text on small devices -->
    <div class="w3-col m6 w3-hide-small w3-padding-large">
    <p class="w3-center" style="font-size: 25px"><em>EMR SYSTEM</em></p> 
    
    <a>
      <p>University clinics like in MSU-IIT deals with the health records of their 
        students.Health care professionals acknowledge the importance of
        medical records. <br> <br>

        The proponents have to develop an Electronic Medical Records System for MSU-IIT Clinic, 
        which will help the clinic personnel to manage the medical records of patients. 
        This study solely focuses on the design and development of the Electronic Medical Records (EMR) System 
        intended for the MSU-IIT clinic. <br><br>

        The system aims to help the clinic personnel of MSU-IIT who manages 
        medical records of the institute. The limitation of this study excludes 
        appointments, medical billings, dental services, blood test services, 
        receipt (Rx) and patient's medical records from other sources.
        This study is beneficial to the MSU-IIT clinic since the traditional 
        paper-based medical record will be replaced with a better system. 
        In addition, the Electronic Medical Record System would help the clinic 
        personnel in managing records efficiently. 
        This would also address the problem of retrieving the patient's medical record 
        and can lessen the consumed time for appointments.<br>
        The doctor could easily view and add to the medical history, physical examination, 
        medicine prescriptions, and remarks. <br></p> </a>
    </div>
  </div>
 
<!-- Footer -->
<br><br>
<footer class="w3-black w3-padding-64  ">
<h2>Features of the EMR System</h2>
<br>
<div class="row">
  <div class="col">
    <ul class="round text-left">
      <li>Can add/Store the Profile and Health Record of the Patients</li>
      <li>Can Update the Profile and Health Record of the Patients</li>
      <li>Can Record the Medical History of the Patients</li>
      <li>Can Print the Medical Record of the Patients</li>
      <li>Can Search/ Find the Patients easily</li>
      <li>Can Archive the Patients Medical Information</li>
      <li>Can retrieve the deleted medical records of the patients</li>
    </ul>
  </div>
  <div class="col">
    <ul class="round text-left">
      <li>Can Add New User (Authorized Personnel Only)</li>
      <li>Can View User Information (Authorized Personnel Only)</li>
      <li>Can Update User (Authorized Personnel Only)</li>
      <li>Can Archive User Record (Authorized Personnel Only)</li>
      <li>Can Restore the archived Users Record (Authorized Personnel Only)</li>
      <li>Can Add/View/Edit/Archive & Restore Clinic Services</li>
      <li>Can View Active Users</li>
    </ul>
  </div>
  <div class="w3-xlarge w3-section">
    
  </div>
  <p>
</p>
</footer>
 
<script>
// Modal Image Gallery
function onClick(element) {
  document.getElementById("img01").src = element.src;
  document.getElementById("modal01").style.display = "block";
  var captionText = document.getElementById("caption");
  captionText.innerHTML = element.alt;
}

// Used to toggle the menu on small screens when clicking on the menu button
function toggleFunction() {
    var x = document.getElementById("navDemo");
    if (x.className.indexOf("w3-show") == -1) {
        x.className += " w3-show";
    } else {
        x.className = x.className.replace(" w3-show", "");
    }
}
</script>

@stop